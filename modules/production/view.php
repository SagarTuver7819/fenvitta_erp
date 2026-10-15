<?php
require_once __DIR__ . '/../../includes/init.php';

$id = (int)get('id');
$b = row('SELECT b.*, i.name product, i.unit, i.cas_no FROM production_batches b JOIN items i ON i.id = b.item_id WHERE b.id=?', [$id]);
if (!$b) redirect('production');

$rm = rows('SELECT r.*, i.name item_name, i.unit FROM batch_rm r JOIN items i ON i.id = r.item_id WHERE r.batch_id=? ORDER BY r.issue_date, r.id', [$id]);
$data = rows('SELECT * FROM batch_data WHERE batch_id=? ORDER BY entry_date, start_time, id', [$id]);
$outputs = rows('SELECT * FROM batch_outputs WHERE batch_id=? ORDER BY output_date, id', [$id]);
$dispatches = rows('SELECT di.*, d.dispatch_no, d.dispatch_date, d.invoice_no, d.id did, p.name party FROM dispatch_items di
                    JOIN dispatches d ON d.id = di.dispatch_id JOIN parties p ON p.id = d.party_id WHERE di.batch_id=? ORDER BY d.dispatch_date', [$id]);

$rmQty = array_sum(array_column($rm, 'qty'));
$rmCost = 0;
foreach ($rm as $r) $rmCost += $r['qty'] * $r['rate'];
$outQty = 0;
foreach ($outputs as $o) if ($o['qc_status'] === 'Approved') $outQty += $o['qty'];
$dispQty = array_sum(array_column($dispatches, 'qty'));
$balance = batch_balance($id);
$yield = $b['batch_size'] > 0 ? $outQty / $b['batch_size'] * 100 : 0;

$pageTitle = 'Batch ' . $b['batch_no'];
$active = 'production';
include ROOT_PATH . '/includes/header.php';
?>
<div class="d-flex gap-2 mb-3 flex-wrap no-print">
    <a href="<?= url('production') ?>" class="btn btn-light"><i class="bi bi-arrow-left"></i> Batches</a>
    <a href="<?= url('production/edit/' . $id) ?>" class="btn btn-outline-primary"><i class="bi bi-pencil"></i> Edit Batch</a>
    <a href="<?= url('batch_rm') ?>?batch_id=<?= $id ?>" class="btn btn-outline-primary"><i class="bi bi-diagram-3"></i> RM Use</a>
    <a href="<?= url('batch_data') ?>?batch_id=<?= $id ?>" class="btn btn-outline-primary"><i class="bi bi-clipboard-data"></i> Data Entry</a>
    <a href="<?= url('batch_output') ?>?batch_id=<?= $id ?>" class="btn btn-outline-primary"><i class="bi bi-box-arrow-in-down"></i> Output</a>
    <a href="<?= url('packing_label') ?>?batch_id=<?= $id ?>" class="btn btn-accent"><i class="bi bi-tags"></i> Packing Labels</a>
    <button onclick="window.print()" class="btn btn-primary ms-auto"><i class="bi bi-printer"></i> Print Batch Record</button>
</div>

<div class="card mb-3">
    <div class="card-header"><i class="bi bi-gear-wide-connected"></i> <?= e($b['batch_no']) ?> &middot; <?= e($b['product']) ?> <span class="ms-2"><?= status_badge($b['status']) ?></span></div>
    <div class="card-body">
        <div class="info-grid">
            <div><div class="k">Product</div><div class="v"><?= e($b['product']) ?></div></div>
            <div><div class="k">CAS No.</div><div class="v"><?= e($b['cas_no']) ?: '-' ?></div></div>
            <div><div class="k">Plant</div><div class="v"><?= e($b['plant']) ?: '-' ?></div></div>
            <div><div class="k">Batch Size</div><div class="v"><?= qty($b['batch_size']) ?> <?= e($b['unit']) ?></div></div>
            <div><div class="k">Start / End</div><div class="v"><?= dmy($b['start_date']) ?: '-' ?> / <?= dmy($b['end_date']) ?: '-' ?></div></div>
            <div><div class="k">Mfg / Exp</div><div class="v"><?= dmy($b['mfg_date']) ?: '-' ?> / <?= dmy($b['exp_date']) ?: '-' ?></div></div>
            <div><div class="k">RM Used</div><div class="v"><?= qty($rmQty) ?> kg</div></div>
            <div><div class="k">RM Cost</div><div class="v"><?= money($rmCost) ?></div></div>
            <div><div class="k">Output (QC Approved)</div><div class="v text-success"><?= qty($outQty) ?> <?= e($b['unit']) ?></div></div>
            <div><div class="k">Yield vs Batch Size</div><div class="v"><?= number_format($yield, 1) ?>%</div></div>
            <div><div class="k">RM Cost / kg Output</div><div class="v"><?= $outQty > 0 ? money($rmCost / $outQty) : '-' ?></div></div>
            <div><div class="k">Dispatched / Balance</div><div class="v"><?= qty($dispQty) ?> / <span class="text-primary"><?= qty($balance) ?></span></div></div>
        </div>
        <?php if ($b['remarks']): ?><div class="mt-3 small"><b>Remarks:</b> <?= nl2br(e($b['remarks'])) ?></div><?php endif; ?>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-diagram-3"></i> Batch Wise RM Use</div>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>Date</th><th>Material</th><th class="num">Qty</th><th class="num">Rate</th><th class="num">Cost</th></tr></thead>
                    <tbody>
                    <?php foreach ($rm as $r): ?>
                        <tr><td><?= dmy($r['issue_date']) ?></td><td><?= e($r['item_name']) ?></td><td class="num"><?= qty($r['qty'], 3) ?> <?= e($r['unit']) ?></td><td class="num"><?= inr($r['rate']) ?></td><td class="num"><?= inr($r['qty'] * $r['rate']) ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (!$rm): ?><tr><td colspan="5" class="text-center text-muted py-3">No RM issued. <a href="<?= url('batch_rm') ?>?batch_id=<?= $id ?>">Issue now</a></td></tr><?php endif; ?>
                    </tbody>
                    <tfoot><tr><td colspan="2">Total</td><td class="num"><?= qty($rmQty, 3) ?></td><td></td><td class="num"><?= inr($rmCost) ?></td></tr></tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-box-arrow-in-down"></i> Output Entry</div>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>Date</th><th>Grade</th><th class="num">Qty</th><th>Containers</th><th>QC</th></tr></thead>
                    <tbody>
                    <?php foreach ($outputs as $o): ?>
                        <tr><td><?= dmy($o['output_date']) ?></td><td><?= e($o['grade']) ?></td><td class="num fw-semibold"><?= qty($o['qty'], 3) ?></td><td><?= (int)$o['containers'] ?> <?= e($o['pack_type']) ?></td><td><?= status_badge($o['qc_status']) ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (!$outputs): ?><tr><td colspan="5" class="text-center text-muted py-3">No output yet. <a href="<?= url('batch_output') ?>?batch_id=<?= $id ?>">Add output</a></td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="bi bi-clipboard-data"></i> Batch Process Data</div>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>Date</th><th>Stage</th><th>Activity</th><th>Time</th><th>Parameter</th><th>Value</th><th>Done By</th><th>Remarks</th></tr></thead>
                    <tbody>
                    <?php foreach ($data as $d): ?>
                        <tr><td><?= dmy($d['entry_date']) ?></td><td><?= e($d['stage']) ?></td><td><?= e($d['activity']) ?></td>
                            <td class="text-nowrap"><?= $d['start_time'] ? substr($d['start_time'], 0, 5) : '' ?><?= $d['end_time'] ? ' - ' . substr($d['end_time'], 0, 5) : '' ?></td>
                            <td><?= e($d['parameter']) ?></td><td class="fw-semibold"><?= e($d['observed_value']) ?></td><td><?= e($d['done_by']) ?></td><td class="small"><?= e($d['remarks']) ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (!$data): ?><tr><td colspan="8" class="text-center text-muted py-3">No process data. <a href="<?= url('batch_data') ?>?batch_id=<?= $id ?>">Add data</a></td></tr><?php endif; ?>
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
                    <thead><tr><th>Date</th><th>Dispatch No</th><th>Invoice</th><th>Customer</th><th>Grade</th><th class="num">Qty</th><th class="num">Packs</th></tr></thead>
                    <tbody>
                    <?php foreach ($dispatches as $d): ?>
                        <tr><td><?= dmy($d['dispatch_date']) ?></td><td><a href="<?= url('dispatch/print/' . $d['did']) ?>"><?= e($d['dispatch_no']) ?></a></td><td><?= e($d['invoice_no']) ?></td><td><?= e($d['party']) ?></td><td><?= e($d['grade']) ?></td><td class="num"><?= qty($d['qty'], 3) ?></td><td class="num"><?= (int)$d['packs'] ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (!$dispatches): ?><tr><td colspan="7" class="text-center text-muted py-3">Not dispatched yet.</td></tr><?php endif; ?>
                    </tbody>
                    <tfoot><tr><td colspan="5">Total Dispatched / Balance in Stock</td><td class="num"><?= qty($dispQty, 3) ?></td><td class="num"><?= qty($balance, 3) ?></td></tr></tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
