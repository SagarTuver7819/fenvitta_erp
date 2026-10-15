<?php
require_once __DIR__ . '/../../includes/init.php';

$itemId = (int)get('item_id');
$showAll = get('show') === 'all';

$batches = fg_batches(!$showAll);
if ($itemId) {
    $batches = array_values(array_filter($batches, fn($b) => (int)$b['item_id'] === $itemId));
}

// Dispatch details per batch (like Excel "SLF STOCK" sheet: customer / qty / invoice / date)
$details = [];
foreach (rows('SELECT di.batch_id, di.qty, di.grade, d.dispatch_date, d.invoice_no, d.dispatch_no, p.name party
               FROM dispatch_items di JOIN dispatches d ON d.id = di.dispatch_id JOIN parties p ON p.id = d.party_id
               WHERE di.batch_id IS NOT NULL ORDER BY d.dispatch_date') as $d) {
    $details[$d['batch_id']][] = $d;
}

// Product wise summary
$pending = pending_dispatch_by_item();
$summary = rows("SELECT i.id, i.name, i.unit, COALESCE(SUM(l.qty_in - l.qty_out),0) stock
                 FROM items i LEFT JOIN stock_ledger l ON l.item_id = i.id WHERE i.item_type = 'FG' AND i.is_active = 1 GROUP BY i.id ORDER BY i.name");

$pageTitle = 'Finish Goods Stock';
$active = 'fg_stock';
include ROOT_PATH . '/includes/header.php';
?>
<div class="row g-3 mb-3">
    <?php foreach ($summary as $s): $p = $pending[$s['id']] ?? 0; ?>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card <?= $s['stock'] > 0 ? 'success' : '' ?>">
                <div class="icon"><i class="bi bi-capsule"></i></div>
                <div class="label"><?= e($s['name']) ?></div>
                <div class="value"><?= qty($s['stock']) ?> <small class="fs-6 text-muted"><?= e($s['unit']) ?></small></div>
                <div class="sub">Booked: <?= qty($p) ?> &middot; <b class="text-success">Available: <?= qty(max(0, $s['stock'] - $p)) ?></b></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<form class="filter-bar row g-2 align-items-end">
    <div class="col-md-4">
        <label class="form-label">Product</label>
        <select name="item_id" class="form-select form-select-sm">
            <option value="">All Products</option>
            <?php foreach ($summary as $s): ?><option value="<?= $s['id'] ?>" <?= $itemId == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Batches</label>
        <select name="show" class="form-select form-select-sm">
            <option value="">Only batches with stock</option>
            <option value="all" <?= $showAll ? 'selected' : '' ?>>All batches (incl. fully dispatched)</option>
        </select>
    </div>
    <div class="col-md-5 d-flex gap-2">
        <button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Show</button>
        <a href="<?= url('batch_output') ?>" class="btn btn-sm btn-outline-primary ms-auto"><i class="bi bi-box-arrow-in-down"></i> Output Entry (Stock In)</a>
        <a href="<?= url('dispatch/add') ?>" class="btn btn-sm btn-accent"><i class="bi bi-truck"></i> Dispatch (Stock Out)</a>
    </div>
</form>

<div class="card">
    <div class="card-header"><i class="bi bi-boxes"></i> Batch Wise Finish Goods Stock
        <div class="actions">
            <input class="form-control form-control-sm table-search" placeholder="Search..." data-table-search="#tbl">
            <button class="btn btn-sm btn-accent" data-export="#tbl" data-filename="fg-stock"><i class="bi bi-file-earmark-excel"></i> Excel</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover" id="tbl">
            <thead><tr><th>Batch No</th><th>Product</th><th>Mfg Date</th><th>Exp Date</th><th>Plant</th><th class="num">Output Qty</th><th>Dispatch Details (Customer / Qty / Invoice / Date)</th><th class="num">Dispatched</th><th class="num">Stock Available</th></tr></thead>
            <tbody>
            <?php $tp = 0; $td = 0; $tb = 0;
            foreach ($batches as $b): $tp += $b['produced']; $td += $b['dispatched']; $tb += $b['balance'];
                $expSoon = $b['exp_date'] && strtotime($b['exp_date']) < strtotime('+6 months'); ?>
                <tr>
                    <td><a href="<?= url('production/view/' . $b['id']) ?>" class="fw-bold"><?= e($b['batch_no']) ?></a></td>
                    <td><?= e($b['item_name']) ?></td>
                    <td><?= dmy($b['mfg_date']) ?></td>
                    <td class="<?= $expSoon ? 'text-danger fw-semibold' : '' ?>"><?= dmy($b['exp_date']) ?></td>
                    <td class="small"><?= e($b['plant']) ?></td>
                    <td class="num"><?= qty($b['produced']) ?></td>
                    <td class="small">
                        <?php foreach ($details[$b['id']] ?? [] as $d): ?>
                            <div><?= e($d['party']) ?> &middot; <b><?= qty($d['qty']) ?></b> <?= e($d['grade']) ?> &middot; <?= e($d['invoice_no'] ?: $d['dispatch_no']) ?> &middot; <?= dmy($d['dispatch_date']) ?></div>
                        <?php endforeach; ?>
                    </td>
                    <td class="num text-danger"><?= qty($b['dispatched']) ?></td>
                    <td class="num fw-bold <?= $b['balance'] > 0 ? 'text-success' : 'text-muted' ?>"><?= qty($b['balance']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$batches): ?><tr><td colspan="9" class="text-center text-muted py-4">No finished goods stock. Add output in Production &gt; Output Entry.</td></tr><?php endif; ?>
            </tbody>
            <tfoot><tr><td colspan="5">Total</td><td class="num"><?= qty($tp) ?></td><td></td><td class="num"><?= qty($td) ?></td><td class="num"><?= qty($tb) ?></td></tr></tfoot>
        </table>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
