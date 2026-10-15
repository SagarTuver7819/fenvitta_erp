<?php
require_once __DIR__ . '/../../includes/init.php';

// ---------- Delete ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'delete') {
    require_admin();
    $id = (int)post('id');
    if (val('SELECT COUNT(*) FROM dispatches WHERE sale_id=?', [$id]) > 0) {
        flash('warning', 'Dispatch is already made against this invoice. Delete the dispatch entry first.');
    } else {
        db_begin();
        q('DELETE FROM sale_items WHERE sale_id=?', [$id]);
        q("DELETE FROM payments WHERE pay_type='RECEIPT' AND ref_id=?", [$id]);
        q('DELETE FROM sales WHERE id=?', [$id]);
        db_commit();
        flash('success', 'Sales invoice deleted.');
    }
    redirect('sales');
}

$from = get('from', fy_start());
$to = get('to', fy_end());
$partyId = (int)get('party_id');

$sql = "SELECT s.*, pa.name party,
          (SELECT GROUP_CONCAT(CONCAT(i.name, IF(si.grade<>'', CONCAT(' (', si.grade, ')'), '')) SEPARATOR ', ') FROM sale_items si JOIN items i ON i.id = si.item_id WHERE si.sale_id = s.id) products,
          (SELECT SUM(qty) FROM sale_items WHERE sale_id = s.id) qty,
          (SELECT COALESCE(SUM(di.qty),0) FROM dispatch_items di JOIN dispatches d ON d.id = di.dispatch_id WHERE d.sale_id = s.id) dispatched,
          (SELECT COALESCE(SUM(amount+tds),0) FROM payments WHERE pay_type='RECEIPT' AND ref_id = s.id) received
        FROM sales s JOIN parties pa ON pa.id = s.party_id
        WHERE s.sale_date BETWEEN ? AND ?";
$params = [$from, $to];
if ($partyId) {
    $sql .= ' AND s.party_id = ?';
    $params[] = $partyId;
}
$list = rows($sql . ' ORDER BY s.sale_date DESC, s.id DESC', $params);

$pageTitle = 'Sales Entry';
$active = 'sales';
include ROOT_PATH . '/includes/header.php';
?>
<form class="filter-bar row g-2 align-items-end">
    <div class="col-md-2"><label class="form-label">From</label><input type="date" name="from" class="form-control form-control-sm" value="<?= e($from) ?>"></div>
    <div class="col-md-2"><label class="form-label">To</label><input type="date" name="to" class="form-control form-control-sm" value="<?= e($to) ?>"></div>
    <div class="col-md-4">
        <label class="form-label">Customer</label>
        <select name="party_id" class="form-select form-select-sm">
            <option value="">All</option>
            <?php foreach (parties_for('customer') as $p): ?><option value="<?= $p['id'] ?>" <?= $partyId == $p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4 d-flex gap-2">
        <button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Filter</button>
        <a href="<?= url('sales/add') ?>" class="btn btn-sm btn-accent ms-auto"><i class="bi bi-plus-lg"></i> New Sales Invoice</a>
    </div>
</form>

<div class="card">
    <div class="card-header"><i class="bi bi-receipt"></i> Sales Register (<?= count($list) ?>)
        <div class="actions">
            <input class="form-control form-control-sm table-search" placeholder="Search..." data-table-search="#tbl">
            <button class="btn btn-sm btn-accent" data-export="#tbl" data-filename="sales-register"><i class="bi bi-file-earmark-excel"></i> Excel</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover" id="tbl">
            <thead><tr><th>Date</th><th>Invoice No</th><th>Customer</th><th>Location</th><th>Product</th><th class="num">Qty</th><th class="num">Basic</th><th class="num">GST</th><th class="num">Total</th><th>Payment</th><th>Dispatch</th><th class="no-export"></th></tr></thead>
            <tbody>
            <?php $tot = ['qty' => 0, 'sub' => 0, 'gst' => 0, 'total' => 0];
            foreach ($list as $s):
                $tot['qty'] += $s['qty']; $tot['sub'] += $s['subtotal']; $tot['gst'] += $s['gst_amount']; $tot['total'] += $s['total'];
                $bal = $s['total'] - $s['received'];
                $pay = $bal <= 0.5 ? 'Received' : ($s['received'] > 0 ? 'Partial' : 'Pending');
                $disp = $s['dispatched'] <= 0 ? 'Not Dispatched' : ($s['dispatched'] + 0.001 >= $s['qty'] ? 'Dispatched' : 'Partial'); ?>
                <tr>
                    <td><?= dmy($s['sale_date']) ?></td>
                    <td class="fw-semibold"><?= e($s['invoice_no']) ?></td>
                    <td><?= e($s['party']) ?></td>
                    <td><?= e($s['location']) ?></td>
                    <td class="small"><?= e($s['products']) ?></td>
                    <td class="num"><?= qty($s['qty']) ?></td>
                    <td class="num"><?= inr($s['subtotal']) ?></td>
                    <td class="num"><?= inr($s['gst_amount']) ?></td>
                    <td class="num fw-semibold"><?= inr($s['total']) ?></td>
                    <td><?= status_badge($pay) ?></td>
                    <td><?= status_badge($disp) ?></td>
                    <td class="text-end no-export text-nowrap">
                        <?php if ($disp !== 'Dispatched'): ?><a href="<?= url('dispatch/add') ?>?sale_id=<?= $s['id'] ?>" class="btn btn-sm btn-light" title="Make Dispatch"><i class="bi bi-truck"></i></a><?php endif; ?>
                        <a href="<?= url('sales/print/' . $s['id']) ?>" class="btn btn-sm btn-light" title="Print Invoice"><i class="bi bi-printer"></i></a>
                        <a href="<?= url('sales/edit/' . $s['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <?php if (is_admin()): ?>
                            <form method="post" class="d-inline" data-confirm="Delete this sales invoice?">
                                <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $s['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$list): ?><tr><td colspan="12" class="text-center text-muted py-4">No sales for selected period.</td></tr><?php endif; ?>
            </tbody>
            <tfoot><tr><td colspan="5">Total</td><td class="num"><?= qty($tot['qty']) ?></td><td class="num"><?= inr($tot['sub']) ?></td><td class="num"><?= inr($tot['gst']) ?></td><td class="num"><?= inr($tot['total']) ?></td><td colspan="3"></td></tr></tfoot>
        </table>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
