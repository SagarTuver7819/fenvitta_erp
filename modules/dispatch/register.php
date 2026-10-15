<?php
require_once __DIR__ . '/../../includes/init.php';

$from = get('from', fy_start());
$to = get('to', today());
$partyId = (int)get('party_id');
$itemId = (int)get('item_id');

$sql = 'SELECT d.id, d.dispatch_no, d.dispatch_date, d.invoice_no, d.destination, d.transporter, d.vehicle_no, d.lr_no,
               p.name party, i.name item_name, i.unit, di.grade, di.qty, di.packs, di.pack_type, b.batch_no,
               (SELECT si.rate FROM sale_items si WHERE si.sale_id = d.sale_id AND si.item_id = di.item_id LIMIT 1) rate
        FROM dispatch_items di
        JOIN dispatches d ON d.id = di.dispatch_id
        JOIN parties p ON p.id = d.party_id
        JOIN items i ON i.id = di.item_id
        LEFT JOIN production_batches b ON b.id = di.batch_id
        WHERE d.dispatch_date BETWEEN ? AND ?';
$params = [$from, $to];
if ($partyId) {
    $sql .= ' AND d.party_id = ?';
    $params[] = $partyId;
}
if ($itemId) {
    $sql .= ' AND di.item_id = ?';
    $params[] = $itemId;
}
$list = rows($sql . ' ORDER BY d.dispatch_date DESC, d.id DESC', $params);

$pageTitle = 'Dispatch Record (Warehouse)';
$active = 'dispatch_register';
include ROOT_PATH . '/includes/header.php';
?>
<form class="filter-bar row g-2 align-items-end">
    <div class="col-md-2"><label class="form-label">From</label><input type="date" name="from" class="form-control form-control-sm" value="<?= e($from) ?>"></div>
    <div class="col-md-2"><label class="form-label">To</label><input type="date" name="to" class="form-control form-control-sm" value="<?= e($to) ?>"></div>
    <div class="col-md-3">
        <label class="form-label">Customer</label>
        <select name="party_id" class="form-select form-select-sm"><option value="">All</option>
            <?php foreach (parties_for('customer') as $p): ?><option value="<?= $p['id'] ?>" <?= $partyId == $p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Product</label>
        <select name="item_id" class="form-select form-select-sm"><option value="">All</option>
            <?php foreach (items_for(['FG']) as $i): ?><option value="<?= $i['id'] ?>" <?= $itemId == $i['id'] ? 'selected' : '' ?>><?= e($i['name']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Filter</button></div>
</form>

<div class="card">
    <div class="card-header"><i class="bi bi-journal-text"></i> Dispatch Record (<?= count($list) ?> lines)
        <div class="actions">
            <input class="form-control form-control-sm table-search" placeholder="Search..." data-table-search="#tbl">
            <button class="btn btn-sm btn-accent" data-export="#tbl" data-filename="dispatch-record"><i class="bi bi-file-earmark-excel"></i> Excel</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover" id="tbl">
            <thead><tr><th>Date</th><th>Dispatch No</th><th>Invoice No</th><th>Customer</th><th>Destination</th><th>Product</th><th>Grade</th><th>Batch No</th><th class="num">Qty</th><th class="num">Packs</th><th class="num">Rate</th><th>Transporter / Vehicle / LR</th><th class="no-export"></th></tr></thead>
            <tbody>
            <?php $tq = 0; foreach ($list as $r): $tq += $r['qty']; ?>
                <tr>
                    <td><?= dmy($r['dispatch_date']) ?></td>
                    <td class="fw-semibold"><?= e($r['dispatch_no']) ?></td>
                    <td><?= e($r['invoice_no']) ?></td>
                    <td><?= e($r['party']) ?></td>
                    <td><?= e($r['destination']) ?></td>
                    <td><?= e($r['item_name']) ?></td>
                    <td><?= e($r['grade']) ?></td>
                    <td><?= e($r['batch_no']) ?></td>
                    <td class="num fw-semibold"><?= qty($r['qty']) ?> <?= e($r['unit']) ?></td>
                    <td class="num"><?= (int)$r['packs'] ?> <?= e($r['pack_type']) ?></td>
                    <td class="num"><?= $r['rate'] ? inr($r['rate']) : '' ?></td>
                    <td class="small"><?= e(implode(' / ', array_filter([$r['transporter'], $r['vehicle_no'], $r['lr_no']]))) ?></td>
                    <td class="no-export"><a href="<?= url('dispatch/print/' . $r['id']) ?>" class="btn btn-sm btn-light"><i class="bi bi-qr-code"></i></a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$list): ?><tr><td colspan="13" class="text-center text-muted py-4">No dispatch in this period.</td></tr><?php endif; ?>
            </tbody>
            <tfoot><tr><td colspan="8">Total Dispatch Qty</td><td class="num"><?= qty($tq) ?></td><td colspan="4"></td></tr></tfoot>
        </table>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
