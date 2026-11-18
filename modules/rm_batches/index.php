<?php
require_once __DIR__ . '/../../includes/init.php';

$itemId = (int)get('item_id');
$show = get('show', 'open'); // open | all

$sql = "SELECT rb.*, i.name item_name, i.unit, i.code item_code, pa.name party, ie.inward_no,
               rb.qty - COALESCE((SELECT SUM(br.qty) FROM batch_rm br WHERE br.rm_batch_id = rb.id),0) available,
               COALESCE((SELECT SUM(br.qty) FROM batch_rm br WHERE br.rm_batch_id = rb.id),0) used_qty
        FROM rm_batches rb
        JOIN items i ON i.id = rb.item_id
        LEFT JOIN parties pa ON pa.id = rb.party_id
        LEFT JOIN inward_entries ie ON ie.id = rb.inward_id
        WHERE 1=1";
$params = [];
if ($itemId) {
    $sql .= ' AND rb.item_id = ?';
    $params[] = $itemId;
}
$list = rows($sql . ' ORDER BY rb.inward_date DESC, rb.id DESC', $params);
if ($show === 'open') {
    $list = array_values(array_filter($list, fn($r) => (float)$r['available'] > 0.0005));
}

$pageTitle = 'RM Batches (Inventory)';
$active = 'rm_batches';
include ROOT_PATH . '/includes/header.php';
?>
<form class="filter-bar row g-2 align-items-end">
    <div class="col-md-4">
        <label class="form-label">Material</label>
        <select name="item_id" class="form-select form-select-sm">
            <option value="">All</option>
            <?= item_options(items_for(['RM', 'PM', 'CONSUMABLE']), $itemId) ?>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Show</label>
        <select name="show" class="form-select form-select-sm">
            <option value="open" <?= $show === 'open' ? 'selected' : '' ?>>Open (available qty)</option>
            <option value="all" <?= $show === 'all' ? 'selected' : '' ?>>All batches</option>
        </select>
    </div>
    <div class="col-md-5 d-flex gap-2">
        <button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Filter</button>
        <a href="<?= url('inward/add') ?>" class="btn btn-sm btn-accent ms-auto"><i class="bi bi-box-arrow-in-down"></i> New Inward</a>
    </div>
</form>

<div class="card">
    <div class="card-header"><i class="bi bi-layers"></i> RM Batches (<?= count($list) ?>)
        <span class="small text-muted ms-2">Created automatically on Inward · Use in Production via Batch Wise RM Use</span>
        <div class="actions">
            <input class="form-control form-control-sm table-search" placeholder="Search..." data-table-search="#tbl">
            <button class="btn btn-sm btn-accent" data-export="#tbl" data-filename="rm-batches"><i class="bi bi-file-earmark-excel"></i></button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover" id="tbl">
            <thead><tr><th>RM Batch No</th><th>Date</th><th>Material</th><th>Supplier</th><th>Inward</th><th class="num">In Qty</th><th class="num">Used</th><th class="num">Available</th><th class="num">Rate</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($list as $r):
                $st = (float)$r['available'] <= 0.0005 ? 'Consumed' : ((float)$r['used_qty'] > 0 ? 'Partial' : 'Open'); ?>
                <tr>
                    <td class="fw-semibold"><?= e($r['batch_no']) ?></td>
                    <td><?= dmy($r['inward_date']) ?></td>
                    <td><?= e($r['item_name']) ?> <small class="text-muted"><?= e($r['item_code']) ?></small></td>
                    <td><?= e($r['party']) ?></td>
                    <td><a href="<?= url('inward') ?>"><?= e($r['inward_no']) ?></a></td>
                    <td class="num"><?= qty($r['qty']) ?> <?= e($r['unit']) ?></td>
                    <td class="num"><?= qty($r['used_qty']) ?></td>
                    <td class="num fw-semibold <?= $st === 'Open' ? 'text-success' : ($st === 'Partial' ? 'text-warning' : 'text-muted') ?>"><?= qty($r['available']) ?></td>
                    <td class="num"><?= inr($r['rate']) ?></td>
                    <td><?= status_badge($st === 'Open' ? 'Pending' : ($st === 'Partial' ? 'Partial' : 'Complete')) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$list): ?><tr><td colspan="10" class="text-center text-muted py-4">No RM batches. Do <a href="<?= url('inward/add') ?>">Inward against PO</a> first.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
