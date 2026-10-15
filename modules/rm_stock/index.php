<?php
require_once __DIR__ . '/../../includes/init.php';

$from = get('from', date('Y-m-01'));
$to = get('to', today());
$type = get('type', 'RM');
$typeList = $type === 'ALL' ? "'RM','PM','CONSUMABLE'" : "'" . (in_array($type, ['RM', 'PM', 'CONSUMABLE']) ? $type : 'RM') . "'";

// Same format as Excel "ALTITUDE STOCK UPDATE": Opening + Purchase + Other In - Used in batch - Other Out = Closing
$sql = "SELECT i.id, i.code, i.name, i.unit, i.item_type, i.reorder_level,
          COALESCE(SUM(CASE WHEN l.trans_date < ? THEN l.qty_in - l.qty_out ELSE 0 END),0) opening,
          COALESCE(SUM(CASE WHEN l.trans_date BETWEEN ? AND ? AND l.ref_type = 'PURCHASE' THEN l.qty_in ELSE 0 END),0) purchase,
          COALESCE(SUM(CASE WHEN l.trans_date BETWEEN ? AND ? AND l.ref_type <> 'PURCHASE' THEN l.qty_in ELSE 0 END),0) other_in,
          COALESCE(SUM(CASE WHEN l.trans_date BETWEEN ? AND ? AND l.ref_type = 'BATCH_RM' THEN l.qty_out ELSE 0 END),0) used_batch,
          COALESCE(SUM(CASE WHEN l.trans_date BETWEEN ? AND ? AND l.ref_type <> 'BATCH_RM' THEN l.qty_out ELSE 0 END),0) other_out,
          COALESCE(SUM(CASE WHEN l.trans_date <= ? THEN l.qty_in - l.qty_out ELSE 0 END),0) closing
        FROM items i
        LEFT JOIN stock_ledger l ON l.item_id = i.id
        WHERE i.item_type IN ($typeList) AND i.is_active = 1
        GROUP BY i.id ORDER BY i.item_type, i.name";
$list = rows($sql, [$from, $from, $to, $from, $to, $from, $to, $from, $to, $to]);

$totalValue = 0;
foreach ($list as &$r) {
    $r['rate'] = item_avg_rate((int)$r['id']);
    $r['value'] = $r['closing'] * $r['rate'];
    $totalValue += $r['value'];
}
unset($r);

$pageTitle = 'Raw Material Stock';
$active = 'rm_stock';
include ROOT_PATH . '/includes/header.php';
?>
<form class="filter-bar row g-2 align-items-end">
    <div class="col-md-2"><label class="form-label">From</label><input type="date" name="from" class="form-control form-control-sm" value="<?= e($from) ?>"></div>
    <div class="col-md-2"><label class="form-label">To</label><input type="date" name="to" class="form-control form-control-sm" value="<?= e($to) ?>"></div>
    <div class="col-md-3">
        <label class="form-label">Item Type</label>
        <select name="type" class="form-select form-select-sm">
            <?php foreach (['RM' => 'Raw Material', 'PM' => 'Packing Material', 'CONSUMABLE' => 'Consumable', 'ALL' => 'All (RM + PM + Consumable)'] as $k => $v): ?>
                <option value="<?= $k ?>" <?= $type === $k ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-5 d-flex gap-2">
        <button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Show</button>
        <a href="<?= url('purchase/add') ?>" class="btn btn-sm btn-outline-primary ms-auto"><i class="bi bi-cart-plus"></i> Purchase (Stock In)</a>
        <a href="<?= url('rm_utilities') ?>" class="btn btn-sm btn-accent"><i class="bi bi-droplet-half"></i> RM Utilities Entry</a>
    </div>
</form>

<div class="card">
    <div class="card-header"><i class="bi bi-box-seam"></i> Stock Statement <?= dmy($from) ?> to <?= dmy($to) ?>
        <span class="badge text-bg-primary ms-2">Stock Value: <?= money($totalValue) ?></span>
        <div class="actions">
            <input class="form-control form-control-sm table-search" placeholder="Search..." data-table-search="#tbl">
            <button class="btn btn-sm btn-accent" data-export="#tbl" data-filename="rm-stock"><i class="bi bi-file-earmark-excel"></i> Excel</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover" id="tbl">
            <thead><tr><th>Code</th><th>Material</th><th>Unit</th><th class="num">Opening</th><th class="num">Purchase Add</th><th class="num">Other In<br><small>(Recovery/Adj.)</small></th><th class="num">Used in Batch</th><th class="num">Other Out<br><small>(Utilities)</small></th><th class="num">Closing Stock</th><th class="num">Avg Rate</th><th class="num">Value ₹</th><th>Status</th><th class="no-export"></th></tr></thead>
            <tbody>
            <?php foreach ($list as $r):
                $low = $r['reorder_level'] > 0 && $r['closing'] <= $r['reorder_level']; ?>
                <tr>
                    <td><?= e($r['code']) ?></td>
                    <td class="fw-semibold"><?= e($r['name']) ?></td>
                    <td><?= e($r['unit']) ?></td>
                    <td class="num"><?= qty($r['opening']) ?></td>
                    <td class="num text-success"><?= qty($r['purchase']) ?></td>
                    <td class="num text-success"><?= qty($r['other_in']) ?></td>
                    <td class="num text-danger"><?= qty($r['used_batch']) ?></td>
                    <td class="num text-danger"><?= qty($r['other_out']) ?></td>
                    <td class="num fw-bold"><?= qty($r['closing']) ?></td>
                    <td class="num"><?= inr($r['rate']) ?></td>
                    <td class="num"><?= inr($r['value']) ?></td>
                    <td><?= $low ? '<span class="badge text-bg-danger">Reorder</span>' : '<span class="badge text-bg-success">OK</span>' ?></td>
                    <td class="no-export"><a href="<?= url('stock_ledger') ?>?item_id=<?= $r['id'] ?>&from=<?= e($from) ?>&to=<?= e($to) ?>" class="btn btn-sm btn-light" title="Ledger"><i class="bi bi-list-columns-reverse"></i></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot><tr><td colspan="10">Total Stock Value</td><td class="num"><?= inr($totalValue) ?></td><td colspan="2"></td></tr></tfoot>
        </table>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
