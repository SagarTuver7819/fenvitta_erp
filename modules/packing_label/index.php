<?php
require_once __DIR__ . '/../../includes/init.php';

// ---------- Generate (save label set) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $batchId = (int)post('batch_id');
    $count = max(1, min((int)post('label_count'), 300));
    if (!$batchId) {
        flash('danger', 'Please select a batch.');
        redirect('packing_label');
    }
    $labelId = insert('packing_labels', [
        'batch_id' => $batchId,
        'label_count' => $count,
        'start_no' => max(1, (int)post('start_no')),
        'net_wt' => num(post('net_wt')),
        'tare_wt' => num(post('tare_wt')),
        'pack_type' => post('pack_type'),
        'storage' => post('storage'),
        'created_by' => current_user()['id'],
    ]);
    redirect("packing_label?label_id=$labelId");
}

$labelId = (int)get('label_id');
$set = $labelId ? row('SELECT * FROM packing_labels WHERE id=?', [$labelId]) : null;
$batchId = $set ? (int)$set['batch_id'] : (int)get('batch_id');
$batch = $batchId ? row('SELECT b.*, i.name product, i.cas_no, i.grade, i.unit FROM production_batches b JOIN items i ON i.id = b.item_id WHERE b.id=?', [$batchId]) : null;

// Default values from output entry of this batch
$output = $batchId ? row('SELECT SUM(qty) qty, SUM(containers) containers, MAX(grade) grade, MAX(pack_type) pack_type FROM batch_outputs WHERE batch_id=?', [$batchId]) : null;
$defCount = (int)get('containers') ?: (int)($output['containers'] ?? 0) ?: 1;
$defNet = ($output && $output['qty'] > 0 && $defCount > 0) ? round($output['qty'] / $defCount, 3) : 25;

$history = rows('SELECT pl.*, b.batch_no, i.name product FROM packing_labels pl JOIN production_batches b ON b.id = pl.batch_id
                 JOIN items i ON i.id = b.item_id ORDER BY pl.id DESC LIMIT 30');

$pageTitle = 'Packing Label (Batch Wise)';
$active = 'packing_label';
include ROOT_PATH . '/includes/header.php';
?>
<?php if (!$set): ?>
<div class="row g-3">
    <div class="col-xl-5">
        <div class="card">
            <div class="card-header"><i class="bi bi-tags"></i> Generate Packing Labels</div>
            <div class="card-body">
                <form method="get" class="mb-3">
                    <label class="form-label">Batch *</label>
                    <select name="batch_id" class="form-select" onchange="this.form.submit()"><?= batch_options($batchId) ?></select>
                </form>
                <?php if ($batch): ?>
                    <div class="bg-light rounded p-2 small mb-3">
                        <b><?= e($batch['product']) ?></b> &middot; Batch <b><?= e($batch['batch_no']) ?></b><br>
                        Mfg: <?= dmy($batch['mfg_date']) ?: '<span class="text-danger">not set</span>' ?> &middot; Exp: <?= dmy($batch['exp_date']) ?: '<span class="text-danger">not set</span>' ?>
                        &middot; Output: <?= qty($output['qty'] ?? 0) ?> kg
                        <?php if (!$batch['mfg_date'] || !$batch['exp_date']): ?><br><a href="<?= url('production/edit/' . $batchId) ?>">Set Mfg / Exp date in batch</a><?php endif; ?>
                    </div>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="batch_id" value="<?= $batchId ?>">
                        <div class="row g-2">
                            <div class="col-6"><label class="form-label">No. of Containers (Labels) *</label><input type="number" name="label_count" class="form-control" value="<?= $defCount ?>" min="1" max="300" required></div>
                            <div class="col-6"><label class="form-label">Start Container No.</label><input type="number" name="start_no" class="form-control" value="1" min="1"></div>
                            <div class="col-4"><label class="form-label">Net Wt. (kg)</label><input type="number" step="0.001" name="net_wt" class="form-control" value="<?= e($defNet) ?>" required></div>
                            <div class="col-4"><label class="form-label">Tare Wt. (kg)</label><input type="number" step="0.001" name="tare_wt" class="form-control" value="1.5"></div>
                            <div class="col-4"><label class="form-label">Pack Type</label><input name="pack_type" class="form-control" value="<?= e($output['pack_type'] ?? 'HDPE Drum') ?>"></div>
                            <div class="col-12"><label class="form-label">Storage Condition</label><textarea name="storage" class="form-control" rows="2"><?= e(setting('label_storage')) ?></textarea></div>
                        </div>
                        <button class="btn btn-primary mt-3"><i class="bi bi-tags"></i> Generate Labels</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header"><i class="bi bi-clock-history"></i> Recently Generated Labels</div>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead><tr><th>Date</th><th>Batch</th><th>Product</th><th class="num">Labels</th><th class="num">Net Wt</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($history as $h): ?>
                        <tr>
                            <td><?= date('d-m-Y H:i', strtotime($h['created_at'])) ?></td><td class="fw-bold"><?= e($h['batch_no']) ?></td><td><?= e($h['product']) ?></td>
                            <td class="num"><?= (int)$h['label_count'] ?></td><td class="num"><?= qty($h['net_wt'], 3) ?></td>
                            <td class="text-end"><a href="?label_id=<?= $h['id'] ?>" class="btn btn-sm btn-light"><i class="bi bi-printer"></i> Reprint</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$history): ?><tr><td colspan="6" class="text-center text-muted py-4">No labels generated yet.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<?php
$gross = $set['net_wt'] + $set['tare_wt'];
$total = $set['start_no'] + $set['label_count'] - 1;
?>
<div class="no-print mb-3 d-flex gap-2">
    <a href="<?= url('packing_label') ?>?batch_id=<?= $batchId ?>" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a>
    <span class="align-self-center"><?= (int)$set['label_count'] ?> labels for batch <b><?= e($batch['batch_no']) ?></b></span>
    <button onclick="window.print()" class="btn btn-primary ms-auto"><i class="bi bi-printer"></i> Print Labels</button>
</div>
<div class="label-sheet">
    <?php for ($n = $set['start_no']; $n <= $total; $n++):
        $qr = implode("\n", [
            'FENVITTA LIFESCIENCES LLP', 'Product: ' . $batch['product'] . ' ' . $batch['grade'], 'CAS: ' . $batch['cas_no'],
            'Batch: ' . $batch['batch_no'], 'Mfg: ' . dmy($batch['mfg_date']) . ' Exp: ' . dmy($batch['exp_date']),
            'Net: ' . qty($set['net_wt'], 3) . ' kg Tare: ' . qty($set['tare_wt'], 3) . ' kg Gross: ' . qty($gross, 3) . ' kg',
            'Container: ' . $n . '/' . $total,
        ]); ?>
        <div class="pk-label">
            <div class="lh">
                <img src="assets/img/logo.jpg" alt="Fenvitta">
                <span class="cno"><?= $n ?> / <?= $total ?></span>
            </div>
            <div class="d-flex gap-2">
                <div class="flex-grow-1">
                    <div class="pname"><?= e($batch['product']) ?> <?= e($batch['grade']) ?></div>
                    <table>
                        <tr><td>CAS No.</td><td>: <b><?= e($batch['cas_no']) ?></b></td></tr>
                        <tr><td>Batch No.</td><td>: <b><?= e($batch['batch_no']) ?></b></td></tr>
                        <tr><td>Mfg. Date</td><td>: <?= $batch['mfg_date'] ? date('M Y', strtotime($batch['mfg_date'])) : '' ?></td></tr>
                        <tr><td>Exp. Date</td><td>: <?= $batch['exp_date'] ? date('M Y', strtotime($batch['exp_date'])) : '' ?></td></tr>
                        <tr><td>Net Wt.</td><td>: <b><?= qty($set['net_wt'], 3) ?> kg</b></td></tr>
                        <tr><td>Tare Wt.</td><td>: <?= qty($set['tare_wt'], 3) ?> kg</td></tr>
                        <tr><td>Gross Wt.</td><td>: <?= qty($gross, 3) ?> kg</td></tr>
                        <?php if ($set['pack_type']): ?><tr><td>Packing</td><td>: <?= e($set['pack_type']) ?></td></tr><?php endif; ?>
                    </table>
                </div>
                <div class="qr" data-qr="<?= e($qr) ?>"></div>
            </div>
            <div class="lf">
                <b>Storage:</b> <?= e($set['storage']) ?><br>
                <b>Mfd. by:</b> <?= e(setting('company_name')) ?>, <?= e(setting('mfg_address')) ?>
            </div>
        </div>
    <?php endfor; ?>
</div>
<?php
$pageScripts = <<<HTML
<script src="assets/vendor/qrcode.min.js"></script>
<script>
document.querySelectorAll('[data-qr]').forEach(el => new QRCode(el, { text: el.dataset.qr, width: 96, height: 96, correctLevel: QRCode.CorrectLevel.L }));
</script>
HTML;
?>
<?php endif; ?>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
