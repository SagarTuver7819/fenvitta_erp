<?php
require_once __DIR__ . '/../../includes/init.php';

$types = ['RM', 'PM', 'FG', 'CONSUMABLE', 'SERVICE'];

$filter = get('type');
$sql = 'SELECT i.*, (SELECT COALESCE(SUM(qty_in-qty_out),0) FROM stock_ledger WHERE item_id=i.id) stock FROM items i';
$params = [];
if ($filter !== '') {
    $sql .= ' WHERE item_type=?';
    $params[] = $filter;
}
$list = rows($sql . ' ORDER BY item_type, name', $params);

$pageTitle = 'Items / Products';
$active = 'items';
include ROOT_PATH . '/includes/header.php';
?>
<div class="card">
    <div class="card-header"><i class="bi bi-list-ul"></i> Item List (<?= count($list) ?>)
        <div class="actions">
            <form>
                <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <?php foreach ($types as $t): ?><option value="<?= $t ?>" <?= $filter === $t ? 'selected' : '' ?>><?= item_type_label($t) ?></option><?php endforeach; ?>
                </select>
            </form>
            <input class="form-control form-control-sm table-search" placeholder="Search..." data-table-search="#tbl">
            <button class="btn btn-sm btn-accent" data-export="#tbl" data-filename="items"><i class="bi bi-file-earmark-excel"></i></button>
            <a href="<?= url('items/add' . ($filter ? '?type=' . urlencode($filter) : '')) ?>" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Add Item</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover" id="tbl">
            <thead><tr><th>Code</th><th>Name</th><th>Type</th><th>CAS No</th><th>Unit</th><th class="num">GST</th><th class="num">Std Rate</th><th class="num">Stock</th><th class="no-export"></th></tr></thead>
            <tbody>
            <?php foreach ($list as $i): ?>
                <tr class="<?= $i['is_active'] ? '' : 'text-muted' ?>">
                    <td><?= e($i['code']) ?></td>
                    <td class="fw-semibold"><?= e($i['name']) ?> <?= $i['grade'] ? '<small class="text-muted">(' . e($i['grade']) . ')</small>' : '' ?></td>
                    <td><span class="badge badge-type"><?= e($i['item_type']) ?></span></td>
                    <td><?= e($i['cas_no']) ?></td>
                    <td><?= e($i['unit']) ?></td>
                    <td class="num"><?= (float)$i['gst_rate'] ?>%</td>
                    <td class="num"><?= inr($i['std_rate']) ?></td>
                    <td class="num fw-semibold"><?= $i['item_type'] === 'SERVICE' ? '-' : qty($i['stock']) ?></td>
                    <td class="text-end no-export text-nowrap">
                        <?php if ($i['item_type'] !== 'SERVICE'): ?><a href="<?= url('stock_ledger?item_id=' . $i['id']) ?>" class="btn btn-sm btn-light" title="Ledger"><i class="bi bi-list-columns-reverse"></i></a><?php endif; ?>
                        <a href="<?= url('items/edit/' . $i['id']) ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                        <?php if (is_admin()) echo delete_button('items', (int)$i['id'], 'Delete this item?'); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$list): ?><tr><td colspan="9" class="text-center text-muted py-4">No items found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
