<?php
require_once __DIR__ . '/../../includes/init.php';

$itemId = (int)get('item_id');
$from = get('from', fy_start());
$to = get('to', today());

$refLabels = [
    'OPENING' => 'Opening Stock', 'PURCHASE' => 'Purchase', 'RM_UTILITY' => 'RM Utility',
    'BATCH_RM' => 'Issued to Batch', 'BATCH_OUTPUT' => 'Production Output', 'DISPATCH' => 'Dispatch',
];

$item = $itemId ? row('SELECT * FROM items WHERE id=?', [$itemId]) : null;
$opening = 0;
$list = [];
if ($item) {
    $opening = (float)val('SELECT COALESCE(SUM(qty_in-qty_out),0) FROM stock_ledger WHERE item_id=? AND trans_date < ?', [$itemId, $from]);
    $list = rows('SELECT l.*, b.batch_no FROM stock_ledger l LEFT JOIN production_batches b ON b.id = l.batch_id
                  WHERE l.item_id=? AND l.trans_date BETWEEN ? AND ? ORDER BY l.trans_date, l.id', [$itemId, $from, $to]);
}

$pageTitle = 'Stock Ledger';
$active = 'stock_ledger';
include ROOT_PATH . '/includes/header.php';
?>
<form class="filter-bar row g-2 align-items-end">
    <div class="col-md-4">
        <label class="form-label">Item *</label>
        <select name="item_id" class="form-select form-select-sm" required>
            <?= item_options(rows("SELECT * FROM items WHERE item_type <> 'SERVICE' ORDER BY item_type, name"), $itemId) ?>
        </select>
    </div>
    <div class="col-md-2"><label class="form-label">From</label><input type="date" name="from" class="form-control form-control-sm" value="<?= e($from) ?>"></div>
    <div class="col-md-2"><label class="form-label">To</label><input type="date" name="to" class="form-control form-control-sm" value="<?= e($to) ?>"></div>
    <div class="col-md-2"><button class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Show Ledger</button></div>
</form>

<?php if ($item): ?>
<div class="card">
    <div class="card-header"><i class="bi bi-list-columns-reverse"></i> <?= e($item['name']) ?> (<?= e($item['unit']) ?>)
        <div class="actions"><button class="btn btn-sm btn-accent" data-export="#tbl" data-filename="ledger-<?= e($item['code']) ?>"><i class="bi bi-file-earmark-excel"></i> Excel</button></div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover" id="tbl">
            <thead><tr><th>Date</th><th>Type</th><th>Batch</th><th>Particulars</th><th class="num">Rate</th><th class="num">In</th><th class="num">Out</th><th class="num">Balance</th></tr></thead>
            <tbody>
                <tr class="table-light"><td><?= dmy($from) ?></td><td colspan="6"><b>Opening Balance</b></td><td class="num fw-bold"><?= qty($opening, 3) ?></td></tr>
                <?php $bal = $opening; $ti = 0; $to2 = 0;
                foreach ($list as $l): $bal += $l['qty_in'] - $l['qty_out']; $ti += $l['qty_in']; $to2 += $l['qty_out']; ?>
                    <tr>
                        <td><?= dmy($l['trans_date']) ?></td>
                        <td><span class="badge badge-type"><?= e($refLabels[$l['ref_type']] ?? $l['ref_type']) ?></span></td>
                        <td><?= e($l['batch_no']) ?></td>
                        <td class="small"><?= e($l['remarks']) ?></td>
                        <td class="num"><?= $l['rate'] > 0 ? inr($l['rate']) : '' ?></td>
                        <td class="num text-success"><?= $l['qty_in'] > 0 ? qty($l['qty_in'], 3) : '' ?></td>
                        <td class="num text-danger"><?= $l['qty_out'] > 0 ? qty($l['qty_out'], 3) : '' ?></td>
                        <td class="num fw-semibold"><?= qty($bal, 3) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot><tr><td colspan="5">Closing Balance</td><td class="num"><?= qty($ti, 3) ?></td><td class="num"><?= qty($to2, 3) ?></td><td class="num"><?= qty($bal, 3) ?></td></tr></tfoot>
        </table>
    </div>
</div>
<?php else: ?>
    <div class="alert alert-light">Select an item to see its full stock movement (In / Out / Balance).</div>
<?php endif; ?>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
