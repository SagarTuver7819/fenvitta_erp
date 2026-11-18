<?php
/*
 * Browser DB Sync URL
 * -------------------
 * Local:  http://localhost/fenvita_project/sync?key=YOUR_KEY
 * Live:   https://fenvitta.oceanhub.co.in/sync?key=YOUR_KEY
 *
 * Key file (NOT config.php):  modules/sync/sync.key
 * Logged-in Admin can also open /sync without the key.
 */
require_once __DIR__ . '/../../includes/functions.php';

$syncKeyFile = __DIR__ . '/sync.key';
$syncKey = is_file($syncKeyFile) ? trim((string)file_get_contents($syncKeyFile)) : '';
$key = (string)(get('key') !== '' ? get('key') : post('key'));
$okKey = $syncKey !== '' && hash_equals($syncKey, $key);
$okAdmin = current_user() && is_admin();

if (!$okKey && !$okAdmin) {
    http_response_code(403);
    $hint = $syncKey !== '' ? url('sync') . '?key=' . rawurlencode($syncKey) : '(create modules/sync/sync.key)';
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>DB Sync</title>
    <style>body{font-family:system-ui;max-width:640px;margin:60px auto;padding:0 16px;color:#2f1222}
    code{background:#f5ecf1;padding:2px 6px;border-radius:4px}</style></head><body>
    <h1>DB Sync locked</h1>
    <p>Open this page as <b>Admin</b>, or use the key from <code>modules/sync/sync.key</code>:</p>
    <p><code>' . htmlspecialchars($hint) . '</code></p>
    <p><a href="' . htmlspecialchars(url('login')) . '">Login</a></p></body></html>';
    exit;
}

$log = [];
$ran = [];

function sync_log(string $msg): void
{
    global $log;
    $log[] = $msg;
}

function sync_table_exists(string $table): bool
{
    return (bool)val(
        'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
        [$table]
    );
}

function sync_ensure_migrations_table(): void
{
    q("CREATE TABLE IF NOT EXISTS schema_migrations (
      id VARCHAR(80) PRIMARY KEY,
      applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      note VARCHAR(255) NULL
    ) ENGINE=InnoDB");
}

function sync_done(string $id): bool
{
    return (int)val('SELECT COUNT(*) FROM schema_migrations WHERE id=?', [$id]) > 0;
}

function sync_mark(string $id, string $note = ''): void
{
    q('INSERT INTO schema_migrations (id, note) VALUES (?, ?) ON DUPLICATE KEY UPDATE note=VALUES(note)', [$id, $note]);
}

// ---------- Migrations (add new ones at the bottom; never rename old ids) ----------
$migrations = [];

$migrations['2026_11_18_inward_tables'] = function () {
    if (!sync_table_exists('inward_entries')) {
        q("CREATE TABLE inward_entries (
          id INT AUTO_INCREMENT PRIMARY KEY,
          inward_no VARCHAR(40) NOT NULL,
          inward_date DATE NOT NULL,
          purchase_id INT NOT NULL,
          party_id INT NOT NULL,
          invoice_no VARCHAR(60) NULL,
          vehicle_no VARCHAR(60) NULL,
          remarks TEXT NULL,
          created_by INT NULL,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          INDEX (inward_date), INDEX (purchase_id), INDEX (party_id)
        ) ENGINE=InnoDB");
        sync_log('Created table inward_entries');
    } else {
        sync_log('Table inward_entries already exists');
    }

    if (!sync_table_exists('inward_items')) {
        q("CREATE TABLE inward_items (
          id INT AUTO_INCREMENT PRIMARY KEY,
          inward_id INT NOT NULL,
          purchase_item_id INT NOT NULL,
          item_id INT NOT NULL,
          qty DECIMAL(14,3) NOT NULL DEFAULT 0,
          unit VARCHAR(15) NULL,
          rate DECIMAL(14,2) NOT NULL DEFAULT 0,
          INDEX (inward_id), INDEX (purchase_item_id), INDEX (item_id)
        ) ENGINE=InnoDB");
        sync_log('Created table inward_items');
    } else {
        sync_log('Table inward_items already exists');
    }

    q("INSERT INTO settings (skey, svalue) VALUES ('inward_prefix', 'GRN') ON DUPLICATE KEY UPDATE skey = skey");
    sync_log('Setting inward_prefix = GRN (if missing)');
};

$migrations['2026_11_18_inward_convert_purchase_stock'] = function () {
    if (!sync_table_exists('inward_entries')) {
        sync_log('Skip convert: inward tables missing');
        return;
    }

    $purchases = rows("SELECT DISTINCT ref_id AS id FROM stock_ledger WHERE ref_type='PURCHASE' ORDER BY ref_id");
    if (!$purchases) {
        sync_log('No old PURCHASE stock rows to convert');
        return;
    }

    $n = 0;
    foreach ($purchases as $p) {
        $pid = (int)$p['id'];
        $po = row('SELECT * FROM purchases WHERE id=?', [$pid]);
        if (!$po) {
            ledger_clear('PURCHASE', $pid);
            continue;
        }

        // Skip if this PO already has any inward
        if ((int)val('SELECT COUNT(*) FROM inward_entries WHERE purchase_id=?', [$pid]) > 0) {
            ledger_clear('PURCHASE', $pid);
            sync_log('Cleared leftover PURCHASE ledger for PO ' . $po['po_no'] . ' (inward already present)');
            continue;
        }

        $inwardNo = next_series('inward_entries', 'inward_no', setting('inward_prefix', 'GRN'), $po['purchase_date']);
        $inwardId = insert('inward_entries', [
            'inward_no' => $inwardNo,
            'inward_date' => $po['purchase_date'],
            'purchase_id' => $pid,
            'party_id' => (int)$po['party_id'],
            'invoice_no' => $po['invoice_no'],
            'remarks' => 'Auto-converted from old Purchase stock (DB Sync)',
            'created_by' => $po['created_by'],
        ]);

        foreach (rows('SELECT * FROM purchase_items WHERE purchase_id=? ORDER BY id', [$pid]) as $pi) {
            $type = val('SELECT item_type FROM items WHERE id=?', [$pi['item_id']]);
            if ($type === 'SERVICE') continue;
            insert('inward_items', [
                'inward_id' => $inwardId,
                'purchase_item_id' => (int)$pi['id'],
                'item_id' => (int)$pi['item_id'],
                'qty' => $pi['qty'],
                'unit' => $pi['unit'],
                'rate' => $pi['rate'],
            ]);
            ledger_add(
                $po['purchase_date'],
                (int)$pi['item_id'],
                (float)$pi['qty'],
                0,
                'INWARD',
                $inwardId,
                (float)$pi['rate'],
                null,
                'Inward ' . $inwardNo . ' against PO ' . $po['po_no']
            );
        }
        ledger_clear('PURCHASE', $pid);
        $n++;
        sync_log('Converted PO ' . $po['po_no'] . ' -> ' . $inwardNo);
    }
    sync_log("Converted $n purchase stock entry(ies) to Inward");
};

// ---------- Run ----------
$doRun = $_SERVER['REQUEST_METHOD'] === 'POST' || get('run') === '1';
$status = [];
$error = '';

try {
    sync_ensure_migrations_table();
    foreach ($migrations as $id => $fn) {
        $status[$id] = sync_done($id) ? 'done' : 'pending';
    }

    if ($doRun) {
        foreach ($migrations as $id => $fn) {
            if (sync_done($id)) {
                sync_log("Skip $id (already applied)");
                continue;
            }
            sync_log("--- Running $id ---");
            $fn();
            sync_mark($id, 'ok');
            $ran[] = $id;
            $status[$id] = 'done';
            sync_log("Marked $id as applied");
        }
        if (!$ran) sync_log('Nothing new to apply. Database is up to date.');
    }
} catch (Throwable $e) {
    $error = $e->getMessage();
    sync_log('ERROR: ' . $error);
}

$pending = array_keys(array_filter($status, fn($s) => $s === 'pending'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DB Sync | Fenvitta</title>
    <link rel="stylesheet" href="<?= url('assets/vendor/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/vendor/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <style>
        body { background: #f5ecf1; }
        .sync-wrap { max-width: 820px; margin: 40px auto; padding: 0 16px; }
        .sync-log { background: #1e1a1c; color: #d6f5c9; font: 13px/1.5 ui-monospace, Consolas, monospace; border-radius: 12px; padding: 16px; max-height: 420px; overflow: auto; white-space: pre-wrap; }
        .mig { display: flex; justify-content: space-between; gap: 12px; padding: 10px 12px; border-bottom: 1px solid #eee; }
        .mig:last-child { border-bottom: 0; }
    </style>
</head>
<body>
<div class="sync-wrap">
    <div class="card mb-3">
        <div class="card-header"><i class="bi bi-database-gear"></i> Database Sync
            <span class="ms-auto small text-muted"><?= e(DB_NAME) ?> @ <?= e(DB_HOST) ?></span>
        </div>
        <div class="card-body">
            <p class="mb-2">Open this URL on Live after every code update that needs DB changes:</p>
            <p class="mb-3"><code><?= e(full_url('sync') . ($syncKey !== '' ? '?key=' . $syncKey : '')) ?></code></p>
            <p class="small text-muted mb-0">Key file: <code>modules/sync/sync.key</code> (config.php change nathi)</p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><i class="bi bi-x-circle"></i> <?= e($error) ?></div>
            <?php elseif ($doRun && !$error): ?>
                <div class="alert alert-success"><i class="bi bi-check2-circle"></i> Sync finished. <?= $ran ? count($ran) . ' migration(s) applied.' : 'Already up to date.' ?></div>
            <?php endif; ?>

            <h6 class="text-muted text-uppercase small mb-2">Migrations</h6>
            <div class="border rounded-3 mb-3 bg-white">
                <?php foreach ($status as $id => $st): ?>
                    <div class="mig">
                        <code><?= e($id) ?></code>
                        <?php if ($st === 'done'): ?>
                            <span class="badge text-bg-success">Applied</span>
                        <?php else: ?>
                            <span class="badge text-bg-warning">Pending</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($pending): ?>
                <form method="post" class="d-flex gap-2 flex-wrap">
                    <?= csrf_field() ?>
                    <?php if ($key !== ''): ?><input type="hidden" name="key" value="<?= e($key) ?>"><?php endif; ?>
                    <button class="btn btn-primary"><i class="bi bi-play-fill"></i> Run pending migrations (<?= count($pending) ?>)</button>
                    <a class="btn btn-accent" href="<?= url('sync') ?>?run=1<?= $key !== '' ? '&key=' . rawurlencode($key) : '' ?>"><i class="bi bi-lightning"></i> One-click run</a>
                </form>
            <?php else: ?>
                <a class="btn btn-light" href="<?= url('sync') . ($key !== '' ? '?key=' . rawurlencode($key) : '') ?>"><i class="bi bi-arrow-clockwise"></i> Refresh</a>
                <?php if ($okAdmin): ?><a class="btn btn-primary" href="<?= url() ?>">Go to Dashboard</a><?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($log): ?>
        <div class="card">
            <div class="card-header"><i class="bi bi-terminal"></i> Log</div>
            <div class="card-body p-0"><div class="sync-log m-3"><?php foreach ($log as $line) echo e($line) . "\n"; ?></div></div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
