<?php
require_once __DIR__ . '/../../includes/init.php';

$from = get('from', fy_start());
$to = get('to', fy_end());
$partyId = (int)get('party_id');
$purchaseId = (int)get('purchase_id');

$sql = "SELECT ie.*, pa.name party, pu.po_no,
          (SELECT COALESCE(SUM(qty),0) FROM inward_items WHERE inward_id = ie.id) qty
        FROM inward_entries ie
        JOIN parties pa ON pa.id = ie.party_id
        JOIN purchases pu ON pu.id = ie.purchase_id
        WHERE ie.inward_date BETWEEN ? AND ?";
$params = [$from, $to];
if ($partyId) {
    $sql .= ' AND ie.party_id = ?';
    $params[] = $partyId;
}
if ($purchaseId) {
    $sql .= ' AND ie.purchase_id = ?';
    $params[] = $purchaseId;
}
$list = rows($sql . ' ORDER BY ie.inward_date DESC, ie.id DESC', $params);

$pageTitle = 'Inward Entry (Against PO)';
$active = 'inward';
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
    <div class="col-md-5 d-flex gap-2">
        <button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Filter</button>
        <a href="<?= url('inward/add') . ($purchaseId ? '?purchase_id=' . $purchaseId : '') ?>" class="btn btn-sm btn-accent ms-auto"><i class="bi bi-box-arrow-in-down"></i> New Inward</a>
    </div>
</form>

<div class="card">
    <div class="card-header"><i class="bi bi-box-arrow-in-down"></i> Inward Register (<?= count($list) ?>)
        <div class="actions">
            <input class="form-control form-control-sm table-search" placeholder="Search..." data-table-search="#tbl">
            <button class="btn btn-sm btn-accent" data-export="#tbl" data-filename="inward-register"><i class="bi bi-file-earmark-excel"></i> Excel</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover" id="tbl">
            <thead><tr><th>Date</th><th>Inward No</th><th>Against PO</th><th>Supplier</th><th>Supplier Invoice</th><th>Vehicle</th><th class="num">Qty In</th><th class="no-export"></th></tr></thead>
            <tbody>
            <?php foreach ($list as $r): ?>
                <tr>
                    <td><?= dmy($r['inward_date']) ?></td>
                    <td class="fw-semibold"><?= e($r['inward_no']) ?></td>
                    <td><a href="<?= url('purchase/print/' . $r['purchase_id']) ?>"><?= e($r['po_no']) ?></a></td>
                    <td><?= e($r['party']) ?></td>
                    <td><?= e($r['invoice_no']) ?></td>
                    <td><?= e($r['vehicle_no']) ?></td>
                    <td class="num"><?= qty($r['qty']) ?></td>
                    <td class="text-end no-export text-nowrap">
                        <a href="<?= url('inward/edit/' . $r['id']) ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                        <?php if (is_admin()) echo delete_button('inward', (int)$r['id'], 'Delete this inward? Stock of this entry will be removed.'); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$list): ?><tr><td colspan="8" class="text-center text-muted py-4">No inward entries. <a href="<?= url('inward/add') ?>">Create first inward against a PO</a>.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
