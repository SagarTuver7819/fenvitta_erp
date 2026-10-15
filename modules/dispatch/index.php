<?php
require_once __DIR__ . '/../../includes/init.php';

// ---------- Delete ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'delete') {
    require_admin();
    $id = (int)post('id');
    db_begin();
    ledger_clear('DISPATCH', $id);
    q('DELETE FROM dispatch_items WHERE dispatch_id=?', [$id]);
    q('DELETE FROM dispatches WHERE id=?', [$id]);
    db_commit();
    flash('success', 'Dispatch deleted and stock returned to batch.');
    redirect('dispatch');
}

$from = get('from', fy_start());
$to = get('to', today());
$list = rows("SELECT d.*, p.name party,
                (SELECT SUM(qty) FROM dispatch_items WHERE dispatch_id = d.id) qty,
                (SELECT SUM(packs) FROM dispatch_items WHERE dispatch_id = d.id) packs,
                (SELECT GROUP_CONCAT(DISTINCT b.batch_no SEPARATOR ', ') FROM dispatch_items di JOIN production_batches b ON b.id = di.batch_id WHERE di.dispatch_id = d.id) batches,
                (SELECT GROUP_CONCAT(DISTINCT i.name SEPARATOR ', ') FROM dispatch_items di JOIN items i ON i.id = di.item_id WHERE di.dispatch_id = d.id) products
              FROM dispatches d JOIN parties p ON p.id = d.party_id
              WHERE d.dispatch_date BETWEEN ? AND ? ORDER BY d.dispatch_date DESC, d.id DESC", [$from, $to]);

// Sales invoices waiting for dispatch
$pendingSales = rows("SELECT s.id, s.invoice_no, s.sale_date, p.name party,
                        (SELECT SUM(qty) FROM sale_items WHERE sale_id = s.id) qty,
                        (SELECT COALESCE(SUM(di.qty),0) FROM dispatch_items di JOIN dispatches d ON d.id = di.dispatch_id WHERE d.sale_id = s.id) done
                      FROM sales s JOIN parties p ON p.id = s.party_id
                      HAVING qty - done > 0.001 ORDER BY s.sale_date");

$pageTitle = 'Dispatch Record Entry';
$active = 'dispatch';
include ROOT_PATH . '/includes/header.php';
?>
<?php if ($pendingSales): ?>
<div class="card mb-3 border-warning">
    <div class="card-header text-warning-emphasis"><i class="bi bi-exclamation-circle"></i> Sales Invoices Pending for Dispatch (<?= count($pendingSales) ?>)</div>
    <div class="card-body d-flex flex-wrap gap-2">
        <?php foreach ($pendingSales as $s): ?>
            <a href="<?= url('dispatch/add') ?>?sale_id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-truck"></i> <?= e($s['invoice_no']) ?> &middot; <?= e($s['party']) ?> &middot; <b><?= qty($s['qty'] - $s['done']) ?> kg</b>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<form class="filter-bar row g-2 align-items-end">
    <div class="col-md-2"><label class="form-label">From</label><input type="date" name="from" class="form-control form-control-sm" value="<?= e($from) ?>"></div>
    <div class="col-md-2"><label class="form-label">To</label><input type="date" name="to" class="form-control form-control-sm" value="<?= e($to) ?>"></div>
    <div class="col-md-8 d-flex gap-2">
        <button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Filter</button>
        <a href="<?= url('dispatch/add') ?>" class="btn btn-sm btn-accent ms-auto"><i class="bi bi-plus-lg"></i> New Dispatch</a>
    </div>
</form>

<div class="card">
    <div class="card-header"><i class="bi bi-truck"></i> Dispatch List (<?= count($list) ?>)
        <div class="actions">
            <input class="form-control form-control-sm table-search" placeholder="Search..." data-table-search="#tbl">
            <button class="btn btn-sm btn-accent" data-export="#tbl" data-filename="dispatch-list"><i class="bi bi-file-earmark-excel"></i> Excel</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover" id="tbl">
            <thead><tr><th>Date</th><th>Dispatch No</th><th>Invoice No</th><th>Customer</th><th>Destination</th><th>Product</th><th>Batch No(s)</th><th class="num">Qty</th><th class="num">Packs</th><th>Vehicle / LR</th><th class="no-export"></th></tr></thead>
            <tbody>
            <?php $tq = 0; foreach ($list as $d): $tq += $d['qty']; ?>
                <tr>
                    <td><?= dmy($d['dispatch_date']) ?></td>
                    <td class="fw-bold"><?= e($d['dispatch_no']) ?></td>
                    <td><?= e($d['invoice_no']) ?></td>
                    <td><?= e($d['party']) ?></td>
                    <td><?= e($d['destination']) ?></td>
                    <td class="small"><?= e($d['products']) ?></td>
                    <td class="small"><?= e($d['batches']) ?></td>
                    <td class="num fw-semibold"><?= qty($d['qty']) ?></td>
                    <td class="num"><?= (int)$d['packs'] ?></td>
                    <td class="small"><?= e(trim($d['vehicle_no'] . ' / ' . $d['lr_no'], ' /')) ?></td>
                    <td class="text-end no-export text-nowrap">
                        <a href="<?= url('dispatch/print/' . $d['id']) ?>" class="btn btn-sm btn-light" title="Challan + QR"><i class="bi bi-printer"></i></a>
                        <a href="<?= url('dispatch/qr') ?>?id=<?= $d['id'] ?>" class="btn btn-sm btn-light" title="QR Stickers"><i class="bi bi-qr-code"></i></a>
                        <a href="<?= url('dispatch/edit/' . $d['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <?php if (is_admin()): ?>
                            <form method="post" class="d-inline" data-confirm="Delete this dispatch? Stock will go back to the batch.">
                                <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $d['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$list): ?><tr><td colspan="11" class="text-center text-muted py-4">No dispatch in this period.</td></tr><?php endif; ?>
            </tbody>
            <tfoot><tr><td colspan="7">Total</td><td class="num"><?= qty($tq) ?></td><td colspan="3"></td></tr></tfoot>
        </table>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
