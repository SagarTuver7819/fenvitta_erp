<?php
require_once __DIR__ . '/../../includes/init.php';

$id = (int)get('id');
$po = row('SELECT pu.*, pa.name party, pa.address, pa.city, pa.state, pa.gstin, pa.phone, pa.email, pa.contact_person
           FROM purchases pu JOIN parties pa ON pa.id = pu.party_id WHERE pu.id=?', [$id]);
if (!$po) redirect('purchase');
$lines = rows('SELECT pi.*, i.name item_name FROM purchase_items pi JOIN items i ON i.id = pi.item_id WHERE pi.purchase_id=? ORDER BY pi.id', [$id]);

$pageTitle = 'Purchase Order ' . $po['po_no'];
$active = 'purchases';
include ROOT_PATH . '/includes/header.php';
?>
<div class="no-print mb-3 d-flex gap-2">
    <a href="<?= url('purchase') ?>" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a>
    <a href="<?= url('purchase/edit/' . $id) ?>" class="btn btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
    <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> Print</button>
</div>
<div class="doc">
    <?php $docTitle = 'PURCHASE ORDER'; include ROOT_PATH . '/includes/doc_header.php'; ?>
    <div class="row g-2 mb-3">
        <div class="col-6"><div class="box">
            <h6>Vendor Details</h6>
            <b><?= e($po['party']) ?></b><br>
            <?= nl2br(e($po['address'])) ?><?= $po['city'] ? '<br>' . e($po['city']) . ', ' . e($po['state']) : '' ?>
            <?= $po['contact_person'] ? '<br>Kind Attn: ' . e($po['contact_person']) : '' ?>
            <?= $po['phone'] ? '<br>Ph: ' . e($po['phone']) : '' ?><?= $po['email'] ? ' &middot; ' . e($po['email']) : '' ?>
            <?= $po['gstin'] ? '<br>GSTIN: ' . e($po['gstin']) : '' ?>
        </div></div>
        <div class="col-6"><div class="box">
            <table class="w-100">
                <tr><td>PO No.</td><td class="fw-bold"><?= e($po['po_no']) ?></td></tr>
                <tr><td>PO Date</td><td><?= dmy($po['purchase_date']) ?></td></tr>
                <?php if ($po['invoice_no']): ?><tr><td>Supplier Inv.</td><td><?= e($po['invoice_no']) ?></td></tr><?php endif; ?>
                <tr><td>Delivery At</td><td><?= e($po['delivery_at']) ?></td></tr>
                <tr><td>Payment Terms</td><td><?= (int)$po['payment_terms'] ?> days</td></tr>
                <tr><td>Freight</td><td><?= e($po['freight_terms']) ?></td></tr>
                <tr><td>Transport</td><td><?= e($po['transport']) ?></td></tr>
            </table>
        </div></div>
    </div>
    <table class="table table-bordered table-sm">
        <thead><tr><th>Sr.</th><th>Description</th><th class="num">Qty</th><th>Unit</th><th class="num">Unit Price</th><th class="num">Amount</th><th class="num">GST</th><th class="num">Total</th></tr></thead>
        <tbody>
        <?php foreach ($lines as $n => $l): ?>
            <tr>
                <td><?= $n + 1 ?></td>
                <td><b><?= e($l['item_name']) ?></b><?= $l['description'] ? '<br><small>' . e($l['description']) . '</small>' : '' ?></td>
                <td class="num"><?= qty($l['qty'], 3) ?></td>
                <td><?= e($l['unit']) ?></td>
                <td class="num"><?= inr($l['rate']) ?></td>
                <td class="num"><?= inr($l['amount']) ?></td>
                <td class="num"><?= (float)$l['gst_rate'] ?>%<br><small><?= inr($l['gst_amount']) ?></small></td>
                <td class="num"><?= inr($l['total']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr><td colspan="5" class="text-end">Basic Amount</td><td class="num"><?= inr($po['subtotal']) ?></td><td class="num"><?= inr($po['gst_amount']) ?></td><td class="num"><?= inr($po['subtotal'] + $po['gst_amount']) ?></td></tr>
            <?php if ($po['freight'] > 0): ?><tr><td colspan="7" class="text-end">Freight</td><td class="num"><?= inr($po['freight']) ?></td></tr><?php endif; ?>
            <?php if ($po['other_charges'] != 0): ?><tr><td colspan="7" class="text-end">Other / Round Off</td><td class="num"><?= inr($po['other_charges']) ?></td></tr><?php endif; ?>
            <tr><td colspan="7" class="text-end">TOTAL AMOUNT ₹</td><td class="num"><?= inr($po['total']) ?></td></tr>
        </tfoot>
    </table>
    <p class="small"><b>Amount in Words:</b> <?= e(amount_in_words((float)$po['total'])) ?></p>
    <?php if ($po['remarks']): ?><p class="small"><b>Remarks:</b> <?= nl2br(e($po['remarks'])) ?></p><?php endif; ?>
    <div class="box mb-4"><h6>Instructions &amp; Conditions</h6><div class="small"><?= nl2br(e(setting('po_terms'))) ?></div></div>
    <div class="row text-center small mt-5 pt-4">
        <div class="col-4 border-top pt-1">Prepared By</div>
        <div class="col-4"></div>
        <div class="col-4 border-top pt-1">Authorised Signatory<br><b><?= e(setting('company_name')) ?></b></div>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
