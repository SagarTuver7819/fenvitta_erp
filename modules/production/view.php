<?php
require_once __DIR__ . '/../../includes/init.php';

$id = (int)get('id');
$b = row('SELECT b.*, i.name product, i.unit, i.cas_no, i.grade FROM production_batches b JOIN items i ON i.id = b.item_id WHERE b.id=?', [$id]);
if (!$b) redirect('production');

// ---------- Quick O/P kg (manual box + OK) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'output_ok') {
    if (in_array($b['status'], ['Production Done', 'Completed', 'Cancelled'], true)) {
        flash('danger', 'This batch is already ' . $b['status'] . '. Open the next O/P batch.');
        redirect("production/view/$id");
    }
    $qtyVal = num(post('op_qty'));
    if ($qtyVal <= 0) {
        flash('danger', 'Enter O/P kg.');
        redirect("production/view/$id");
    }
    $date = date_or_null(post('op_date')) ?? today();
    $oid = insert('batch_outputs', [
        'batch_id' => $id,
        'output_date' => $date,
        'qty' => $qtyVal,
        'grade' => $b['grade'] ?: 'IP/USP',
        'containers' => 0,
        'pack_type' => 'HDPE Drum',
        'qc_status' => 'Approved',
        'remarks' => post('op_remarks') !== '' ? post('op_remarks') : 'O/P entry',
    ]);
    sync_output_stock($oid);
    $newBatchId = refresh_production_status($id);

    if ($newBatchId) {
        $nb = row('SELECT batch_no FROM production_batches WHERE id=?', [$newBatchId]);
        flash('success', 'O/P ' . qty($qtyVal) . ' kg saved. Batch reached 1000 kg → Production Done. New O/P batch: ' . ($nb['batch_no'] ?? ''));
        redirect('production/view/' . $newBatchId);
    }
    flash('success', 'O/P ' . qty($qtyVal) . ' kg saved in batch ' . $b['batch_no'] . '.');
    redirect("production/view/$id");
}

$rm = rows('SELECT r.*, i.name item_name, i.unit, rb.batch_no rm_batch_no
            FROM batch_rm r JOIN items i ON i.id = r.item_id
            LEFT JOIN rm_batches rb ON rb.id = r.rm_batch_id
            WHERE r.batch_id=? ORDER BY r.issue_date, r.id', [$id]);
$data = rows('SELECT * FROM batch_data WHERE batch_id=? ORDER BY entry_date, start_time, id', [$id]);
$outputs = rows('SELECT * FROM batch_outputs WHERE batch_id=? ORDER BY output_date, id', [$id]);
$dispatches = rows('SELECT di.*, d.dispatch_no, d.dispatch_date, d.invoice_no, d.id did, p.name party FROM dispatch_items di
                    JOIN dispatches d ON d.id = di.dispatch_id JOIN parties p ON p.id = d.party_id WHERE di.batch_id=? ORDER BY d.dispatch_date', [$id]);

// reload batch after possible status change path
$b = row('SELECT b.*, i.name product, i.unit, i.cas_no, i.grade FROM production_batches b JOIN items i ON i.id = b.item_id WHERE b.id=?', [$id]);

$rmQty = array_sum(array_column($rm, 'qty'));
$rmCost = 0;
foreach ($rm as $r) $rmCost += $r['qty'] * $r['rate'];
$outQty = 0;
foreach ($outputs as $o) if ($o['qc_status'] === 'Approved') $outQty += $o['qty'];
$dispQty = array_sum(array_column($dispatches, 'qty'));
$balance = batch_balance($id);
$batchSize = (float)$b['batch_size'] ?: 1000;
$pendingOp = max(0, $batchSize - $outQty);
$pct = min(100, $batchSize > 0 ? ($outQty / $batchSize * 100) : 0);
$isOpen = !in_array($b['status'], ['Production Done', 'Completed', 'Cancelled'], true);

$pageTitle = 'Batch ' . $b['batch_no'];
$active = 'production';
include ROOT_PATH . '/includes/header.php';
?>
<div class="d-flex gap-2 mb-3 flex-wrap no-print">
    <a href="<?= url('production') ?>" class="btn btn-light"><i class="bi bi-arrow-left"></i> Batches</a>
    <a href="<?= url('production/edit/' . $id) ?>" class="btn btn-outline-primary"><i class="bi bi-pencil"></i> Edit Batch</a>
    <a href="<?= url('batch_rm') ?>?batch_id=<?= $id ?>" class="btn btn-outline-primary"><i class="bi bi-diagram-3"></i> RM Use</a>
    <a href="<?= url('batch_data') ?>?batch_id=<?= $id ?>" class="btn btn-outline-primary"><i class="bi bi-clipboard-data"></i> Data Entry</a>
    <a href="<?= url('batch_output') ?>?batch_id=<?= $id ?>" class="btn btn-outline-primary"><i class="bi bi-box-arrow-in-down"></i> Full Output Form</a>
    <a href="<?= url('packing_label') ?>?batch_id=<?= $id ?>" class="btn btn-accent"><i class="bi bi-tags"></i> Packing Labels</a>
    <button onclick="window.print()" class="btn btn-primary ms-auto"><i class="bi bi-printer"></i> Print Batch Record</button>
</div>

<div class="card mb-3">
    <div class="card-header"><i class="bi bi-gear-wide-connected"></i> Production Batch <b><?= e($b['batch_no']) ?></b> (fixed)
        <span class="ms-2"><?= status_badge($b['status']) ?></span>
        <span class="ms-auto small">Target <b><?= qty($batchSize) ?> kg</b></span>
    </div>
    <div class="card-body">
        <div class="info-grid">
            <div><div class="k">Product</div><div class="v"><?= e($b['product']) ?></div></div>
            <div><div class="k">Plant</div><div class="v"><?= e($b['plant']) ?: '-' ?></div></div>
            <div><div class="k">RM Used (all inward batches)</div><div class="v"><?= qty($rmQty) ?> kg</div></div>
            <div><div class="k">O/P Done</div><div class="v text-success"><?= qty($outQty) ?> / <?= qty($batchSize) ?> kg</div></div>
            <div><div class="k">Pending for 1000 kg</div><div class="v text-danger"><?= qty($pendingOp) ?> kg</div></div>
            <div><div class="k">FG Balance</div><div class="v"><?= qty($balance) ?> <?= e($b['unit']) ?></div></div>
        </div>
        <div class="progress mt-3" style="height:12px">
            <div class="progress-bar <?= $pct >= 100 ? 'bg-success' : 'bg-primary' ?>" style="width:<?= number_format($pct, 1) ?>%"><?= number_format($pct, 0) ?>%</div>
        </div>
        <small class="text-muted">When O/P reaches 1000 kg → status <b>Production Done</b> and next O/P batch is auto-generated.</small>
    </div>
</div>

<div class="row g-3 mb-3 no-print">
    <div class="col-lg-5">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white"><i class="bi bi-input-cursor"></i> O/P kg (manual) — OK</div>
            <div class="card-body">
                <?php if ($isOpen): ?>
                <form method="post" class="row g-2 align-items-end">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="output_ok">
                    <div class="col-5">
                        <label class="form-label">O/P Date</label>
                        <input type="date" name="op_date" class="form-control" value="<?= today() ?>" required>
                    </div>
                    <div class="col-4">
                        <label class="form-label">O/P kg *</label>
                        <input type="number" step="0.001" min="0.001" name="op_qty" class="form-control form-control-lg fw-bold" placeholder="e.g. 150" required autofocus>
                    </div>
                    <div class="col-3">
                        <button class="btn btn-primary btn-lg w-100"><i class="bi bi-check2"></i> OK</button>
                    </div>
                    <div class="col-12"><input name="op_remarks" class="form-control form-control-sm" placeholder="Remarks (optional)"></div>
                </form>
                <small class="text-muted d-block mt-2">OK pachi aa qty FG / batch output ma store thase. Multiple entries allowed until 1000 kg.</small>
                <?php else: ?>
                    <div class="alert alert-success mb-0">This batch is <b><?= e($b['status']) ?></b>. Navi O/P batch use karo.
                        <a href="<?= url('production') ?>" class="alert-link">Open production list</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-diagram-3"></i> Used RM Batches (multiple inward inventory)
                <a href="<?= url('batch_rm') ?>?batch_id=<?= $id ?>" class="btn btn-sm btn-accent ms-auto"><i class="bi bi-plus-lg"></i> Issue RM</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>RM Batch No</th><th>Material</th><th>Date</th><th class="num">Qty Used</th><th class="num">Cost</th></tr></thead>
                    <tbody>
                    <?php foreach ($rm as $r): ?>
                        <tr>
                            <td><span class="badge text-bg-primary"><?= e($r['rm_batch_no'] ?: '-') ?></span></td>
                            <td class="fw-semibold"><?= e($r['item_name']) ?></td>
                            <td><?= dmy($r['issue_date']) ?></td>
                            <td class="num"><?= qty($r['qty'], 3) ?> <?= e($r['unit']) ?></td>
                            <td class="num"><?= inr($r['qty'] * $r['rate']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$rm): ?><tr><td colspan="5" class="text-center text-muted py-3">Haju RM issue nathi. <a href="<?= url('batch_rm') ?>?batch_id=<?= $id ?>">Multiple RM batches mathi use karo</a></td></tr><?php endif; ?>
                    </tbody>
                    <?php if ($rm): ?><tfoot><tr><td colspan="3">Total RM used</td><td class="num"><?= qty($rmQty, 3) ?></td><td class="num"><?= inr($rmCost) ?></td></tr></tfoot><?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-box-arrow-in-down"></i> O/P History (this batch)</div>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>Date</th><th>Grade</th><th class="num">O/P kg</th><th>QC</th></tr></thead>
                    <tbody>
                    <?php foreach ($outputs as $o): ?>
                        <tr><td><?= dmy($o['output_date']) ?></td><td><?= e($o['grade']) ?></td><td class="num fw-semibold"><?= qty($o['qty'], 3) ?></td><td><?= status_badge($o['qc_status']) ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (!$outputs): ?><tr><td colspan="4" class="text-center text-muted py-3">No O/P yet — upar ni box ma kg nakhi OK dabavo.</td></tr><?php endif; ?>
                    </tbody>
                    <tfoot><tr><td colspan="2">Total O/P</td><td class="num"><?= qty($outQty, 3) ?> / <?= qty($batchSize) ?></td><td></td></tr></tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-clipboard-data"></i> Batch Process Data</div>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>Date</th><th>Stage</th><th>Activity</th><th>Value</th></tr></thead>
                    <tbody>
                    <?php foreach ($data as $d): ?>
                        <tr><td><?= dmy($d['entry_date']) ?></td><td><?= e($d['stage']) ?></td><td><?= e($d['activity']) ?></td><td class="fw-semibold"><?= e($d['observed_value']) ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (!$data): ?><tr><td colspan="4" class="text-center text-muted py-3"><a href="<?= url('batch_data') ?>?batch_id=<?= $id ?>">Add process data</a></td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="bi bi-truck"></i> Dispatch From This Batch</div>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>Date</th><th>Dispatch No</th><th>Invoice</th><th>Customer</th><th class="num">Qty</th></tr></thead>
                    <tbody>
                    <?php foreach ($dispatches as $d): ?>
                        <tr><td><?= dmy($d['dispatch_date']) ?></td><td><a href="<?= url('dispatch/print/' . $d['did']) ?>"><?= e($d['dispatch_no']) ?></a></td><td><?= e($d['invoice_no']) ?></td><td><?= e($d['party']) ?></td><td class="num"><?= qty($d['qty'], 3) ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (!$dispatches): ?><tr><td colspan="5" class="text-center text-muted py-3">Not dispatched yet.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
