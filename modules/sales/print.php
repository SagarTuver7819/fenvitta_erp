<?php
require_once __DIR__ . '/../../includes/init.php';

$id = (int)get('id');
$inv = row('SELECT s.*, pa.name party, pa.address, pa.city, pa.state, pa.gstin, pa.phone
            FROM sales s JOIN parties pa ON pa.id = s.party_id WHERE s.id=?', [$id]);
if (!$inv) redirect('sales');

$lines = rows('SELECT si.*, i.name item_name, i.hsn, i.cas_no FROM sale_items si JOIN items i ON i.id = si.item_id WHERE si.sale_id=? ORDER BY si.id', [$id]);
$batches = rows('SELECT di.item_id, b.batch_no, b.mfg_date, b.exp_date, di.qty FROM dispatch_items di
                 JOIN dispatches d ON d.id = di.dispatch_id LEFT JOIN production_batches b ON b.id = di.batch_id WHERE d.sale_id=?', [$id]);
$sameState = strcasecmp(trim((string)$inv['state']), 'Gujarat') === 0;

$pageTitle = 'Invoice ' . $inv['invoice_no'];
$active = 'sales';
include ROOT_PATH . '/includes/header.php';
?>
<div class="no-print mb-3 d-flex gap-2">
    <a href="<?= url('sales') ?>" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a>
    <a href="<?= url('sales/edit/' . $id) ?>" class="btn btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
    <a href="<?= url('dispatch/add') ?>?sale_id=<?= $id ?>" class="btn btn-outline-primary"><i class="bi bi-truck"></i> Dispatch</a>
    <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> Print</button>
</div>
<div class="doc">
    <?php $docTitle = 'TAX INVOICE'; include ROOT_PATH . '/includes/doc_header.php'; ?>
    <div class="row g-2 mb-3">
        <div class="col-7"><div class="box">
            <h6>Bill To</h6>
            <b><?= e($inv['party']) ?></b><br>
            <?= nl2br(e($inv['address'])) ?><?= $inv['city'] ? '<br>' . e($inv['city']) . ', ' . e($inv['state']) : '' ?>
            <?= $inv['gstin'] ? '<br>GSTIN: <b>' . e($inv['gstin']) . '</b>' : '' ?>
            <?= $inv['phone'] ? '<br>Ph: ' . e($inv['phone']) : '' ?>
        </div></div>
        <div class="col-5"><div class="box">
            <table class="w-100">
                <tr><td>Invoice No.</td><td class="fw-bold"><?= e($inv['invoice_no']) ?></td></tr>
                <tr><td>Date</td><td><?= dmy($inv['sale_date']) ?></td></tr>
                <?php if ($inv['po_ref']): ?><tr><td>Buyer PO</td><td><?= e($inv['po_ref']) ?></td></tr><?php endif; ?>
                <tr><td>Payment Terms</td><td><?= (int)$inv['payment_terms'] ?> days</td></tr>
                <tr><td>Due Date</td><td><?= dmy($inv['due_date']) ?></td></tr>
                <?php if ($inv['location']): ?><tr><td>Place of Supply</td><td><?= e($inv['location']) ?></td></tr><?php endif; ?>
            </table>
        </div></div>
    </div>
    <table class="table table-bordered table-sm">
        <thead><tr><th>Sr.</th><th>Product</th><th>HSN</th><th class="num">Qty</th><th class="num">Rate</th><th class="num">Taxable</th><th class="num">GST</th><th class="num">Total</th></tr></thead>
        <tbody>
        <?php foreach ($lines as $n => $l): ?>
            <tr>
                <td><?= $n + 1 ?></td>
                <td><b><?= e($l['item_name']) ?></b> <?= $l['grade'] ? e($l['grade']) : '' ?>
                    <?php if ($l['cas_no']): ?><br><small>CAS No: <?= e($l['cas_no']) ?></small><?php endif; ?>
                    <?php foreach ($batches as $b): if ($b['item_id'] != $l['item_id'] || !$b['batch_no']) continue; ?>
                        <br><small>Batch: <?= e($b['batch_no']) ?> (<?= qty($b['qty']) ?>) Mfg <?= date('m/Y', strtotime($b['mfg_date'] ?? 'now')) ?> Exp <?= $b['exp_date'] ? date('m/Y', strtotime($b['exp_date'])) : '-' ?></small>
                    <?php endforeach; ?>
                </td>
                <td><?= e($l['hsn']) ?></td>
                <td class="num"><?= qty($l['qty'], 3) ?> <?= e($l['unit']) ?></td>
                <td class="num"><?= inr($l['rate']) ?></td>
                <td class="num"><?= inr($l['amount']) ?></td>
                <td class="num"><?= (float)$l['gst_rate'] ?>%<br><small><?= inr($l['gst_amount']) ?></small></td>
                <td class="num"><?= inr($l['total']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr><td colspan="5" class="text-end">Taxable Value</td><td class="num"><?= inr($inv['subtotal']) ?></td><td class="num"><?= inr($inv['gst_amount']) ?></td><td class="num"><?= inr($inv['subtotal'] + $inv['gst_amount']) ?></td></tr>
            <?php if ($sameState): ?>
                <tr><td colspan="7" class="text-end">CGST</td><td class="num"><?= inr($inv['gst_amount'] / 2) ?></td></tr>
                <tr><td colspan="7" class="text-end">SGST</td><td class="num"><?= inr($inv['gst_amount'] / 2) ?></td></tr>
            <?php else: ?>
                <tr><td colspan="7" class="text-end">IGST</td><td class="num"><?= inr($inv['gst_amount']) ?></td></tr>
            <?php endif; ?>
            <?php if ($inv['freight'] > 0): ?><tr><td colspan="7" class="text-end">Freight</td><td class="num"><?= inr($inv['freight']) ?></td></tr><?php endif; ?>
            <tr><td colspan="7" class="text-end">GRAND TOTAL ₹ (Rounded)</td><td class="num"><?= inr($inv['total']) ?></td></tr>
        </tfoot>
    </table>
    <p class="small"><b>Amount in Words:</b> <?= e(amount_in_words((float)$inv['total'])) ?></p>
    <?php if ($inv['remarks']): ?><p class="small"><b>Remarks:</b> <?= nl2br(e($inv['remarks'])) ?></p><?php endif; ?>
    <div class="row text-center small mt-5 pt-4">
        <div class="col-4 border-top pt-1">Receiver's Signature</div>
        <div class="col-4"></div>
        <div class="col-4 border-top pt-1">For <?= e(setting('company_name')) ?><br>Authorised Signatory</div>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
