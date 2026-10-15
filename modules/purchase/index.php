<?php
require_once __DIR__ . '/../../includes/init.php';

// ---------- Delete ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'delete') {
    require_admin();
    $id = (int)post('id');
    db_begin();
    q('DELETE FROM purchase_items WHERE purchase_id=?', [$id]);
    q("DELETE FROM payments WHERE pay_type='PAYMENT' AND ref_id=?", [$id]);
    q('DELETE FROM purchases WHERE id=?', [$id]);
    ledger_clear('PURCHASE', $id);
    db_commit();
    flash('success', 'Purchase entry deleted.');
    redirect('purchase');
}

$from = get('from', fy_start());
$to = get('to', fy_end());
$partyId = (int)get('party_id');
$category = get('category');

$sql = "SELECT pu.*, pa.name party,
          (SELECT GROUP_CONCAT(i.name SEPARATOR ', ') FROM purchase_items pi JOIN items i ON i.id = pi.item_id WHERE pi.purchase_id = pu.id) products,
          (SELECT SUM(qty) FROM purchase_items WHERE purchase_id = pu.id) qty,
          (SELECT COALESCE(SUM(amount+tds),0) FROM payments WHERE pay_type='PAYMENT' AND ref_id = pu.id) paid
        FROM purchases pu JOIN parties pa ON pa.id = pu.party_id
        WHERE pu.purchase_date BETWEEN ? AND ?";
$params = [$from, $to];
if ($partyId) {
    $sql .= ' AND pu.party_id = ?';
    $params[] = $partyId;
}
if ($category !== '') {
    $sql .= ' AND pu.category = ?';
    $params[] = $category;
}
$list = rows($sql . ' ORDER BY pu.purchase_date DESC, pu.id DESC', $params);

$pageTitle = 'Purchase Entry';
$active = 'purchases';
include ROOT_PATH . '/includes/header.php';
?>
<form class="filter-bar row g-2 align-items-end">
    <div class="col-md-2"><label class="form-label">From</label><input type="date" name="from" class="form-control form-control-sm" value="<?= e($from) ?>"></div>
    <div class="col-md-2"><label class="form-label">To</label><input type="date" name="to" class="form-control form-control-sm" value="<?= e($to) ?>"></div>
    <div class="col-md-3">
        <label class="form-label">Supplier</label>
        <select name="party_id" class="form-select form-select-sm">
            <option value="">All</option>
            <?php foreach (parties_for('supplier') as $p): ?><option value="<?= $p['id'] ?>" <?= $partyId == $p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">Category</label>
        <select name="category" class="form-select form-select-sm">
            <option value="">All</option>
            <?php foreach (PURCHASE_CATEGORIES as $c): ?><option <?= $category === $c ? 'selected' : '' ?>><?= $c ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Filter</button>
        <a href="<?= url('purchase/add') ?>" class="btn btn-sm btn-accent ms-auto"><i class="bi bi-plus-lg"></i> New Purchase</a>
    </div>
</form>

<div class="card">
    <div class="card-header"><i class="bi bi-cart"></i> Purchase Register (<?= count($list) ?>)
        <div class="actions">
            <input class="form-control form-control-sm table-search" placeholder="Search..." data-table-search="#tbl">
            <button class="btn btn-sm btn-accent" data-export="#tbl" data-filename="purchase-register"><i class="bi bi-file-earmark-excel"></i> Excel</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover" id="tbl">
            <thead><tr><th>Date</th><th>PO No</th><th>Invoice No</th><th>Supplier</th><th>Category</th><th>Products</th><th class="num">Qty</th><th class="num">Basic</th><th class="num">GST</th><th class="num">Total</th><th>Status</th><th class="no-export"></th></tr></thead>
            <tbody>
            <?php $tot = ['sub' => 0, 'gst' => 0, 'total' => 0];
            foreach ($list as $p):
                $tot['sub'] += $p['subtotal']; $tot['gst'] += $p['gst_amount']; $tot['total'] += $p['total'];
                $bal = $p['total'] - $p['paid'];
                $status = $bal <= 0.5 ? 'Paid' : ($p['paid'] > 0 ? 'Partial' : 'Unpaid'); ?>
                <tr>
                    <td><?= dmy($p['purchase_date']) ?></td>
                    <td><?= e($p['po_no']) ?></td>
                    <td><?= e($p['invoice_no']) ?></td>
                    <td class="fw-semibold"><?= e($p['party']) ?></td>
                    <td><span class="badge badge-type"><?= e($p['category']) ?></span></td>
                    <td class="small"><?= e($p['products']) ?></td>
                    <td class="num"><?= qty($p['qty']) ?></td>
                    <td class="num"><?= inr($p['subtotal']) ?></td>
                    <td class="num"><?= inr($p['gst_amount']) ?></td>
                    <td class="num fw-semibold"><?= inr($p['total']) ?></td>
                    <td><?= status_badge($status) ?></td>
                    <td class="text-end no-export text-nowrap">
                        <a href="<?= url('purchase/print/' . $p['id']) ?>" class="btn btn-sm btn-light" title="Print PO"><i class="bi bi-printer"></i></a>
                        <a href="<?= url('purchase/edit/' . $p['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <?php if (is_admin()): ?>
                            <form method="post" class="d-inline" data-confirm="Delete this purchase? Stock and payments of this entry will also be removed.">
                                <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$list): ?><tr><td colspan="12" class="text-center text-muted py-4">No purchase entries for selected period.</td></tr><?php endif; ?>
            </tbody>
            <tfoot><tr><td colspan="7">Total</td><td class="num"><?= inr($tot['sub']) ?></td><td class="num"><?= inr($tot['gst']) ?></td><td class="num"><?= inr($tot['total']) ?></td><td colspan="2"></td></tr></tfoot>
        </table>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
