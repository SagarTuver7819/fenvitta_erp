<?php
require_once __DIR__ . '/../../includes/init.php';

$id = (int)get('id');
include ROOT_PATH . '/includes/dispatch_qr_data.php';

$pageTitle = 'Dispatch ' . $dsp['dispatch_no'];
$active = 'dispatch';
include ROOT_PATH . '/includes/header.php';
?>
<div class="no-print mb-3 d-flex gap-2 flex-wrap">
    <a href="<?= url('dispatch') ?>" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a>
    <a href="<?= url('dispatch/edit/' . $id) ?>" class="btn btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
    <a href="<?= url('dispatch/qr') ?>?id=<?= $id ?>" class="btn btn-accent"><i class="bi bi-qr-code"></i> Print QR Stickers</a>
    <?php if ($dsp['sale_id']): ?><a href="<?= url('sales/print/' . $dsp['sale_id']) ?>" class="btn btn-outline-primary"><i class="bi bi-receipt"></i> Invoice</a><?php endif; ?>
    <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> Print Challan</button>
</div>
<div class="doc">
    <?php $docTitle = 'DELIVERY CHALLAN / DISPATCH NOTE'; include ROOT_PATH . '/includes/doc_header.php'; ?>
    <div class="row g-2 mb-3">
        <div class="col-5"><div class="box">
            <h6>Consignee</h6>
            <b><?= e($dsp['party']) ?></b><br>
            <?= nl2br(e($dsp['address'])) ?><?= $dsp['city'] ? '<br>' . e($dsp['city']) . ', ' . e($dsp['state']) : '' ?>
            <?= $dsp['gstin'] ? '<br>GSTIN: ' . e($dsp['gstin']) : '' ?>
            <?= $dsp['destination'] ? '<br><b>Destination:</b> ' . e($dsp['destination']) : '' ?>
        </div></div>
        <div class="col-4"><div class="box">
            <table class="w-100">
                <tr><td>Dispatch No.</td><td class="fw-bold"><?= e($dsp['dispatch_no']) ?></td></tr>
                <tr><td>Date</td><td><?= dmy($dsp['dispatch_date']) ?></td></tr>
                <tr><td>Invoice No.</td><td><?= e($dsp['invoice_no']) ?></td></tr>
                <tr><td>Transporter</td><td><?= e($dsp['transporter']) ?></td></tr>
                <tr><td>Vehicle No.</td><td><?= e($dsp['vehicle_no']) ?></td></tr>
                <tr><td>LR No.</td><td><?= e($dsp['lr_no']) ?></td></tr>
                <?php if ($dsp['driver_name'] || $dsp['driver_mobile']): ?><tr><td>Driver</td><td><?= e($dsp['driver_name']) ?> <?= e($dsp['driver_mobile']) ?></td></tr><?php endif; ?>
            </table>
        </div></div>
        <div class="col-3"><div class="box text-center">
            <div id="qrMain" class="d-inline-block"></div>
            <div class="small text-muted mt-1">Scan to verify dispatch</div>
        </div></div>
    </div>
    <table class="table table-bordered table-sm">
        <thead><tr><th>Sr.</th><th>Product</th><th>Grade</th><th>Batch No</th><th>Mfg Date</th><th>Exp Date</th><th class="num">Qty</th><th class="num">Packs</th></tr></thead>
        <tbody>
        <?php $tq = 0; $tp = 0; foreach ($dspItems as $n => $it): $tq += $it['qty']; $tp += $it['packs']; ?>
            <tr>
                <td><?= $n + 1 ?></td>
                <td><b><?= e($it['item_name']) ?></b><?= $it['cas_no'] ? '<br><small>CAS: ' . e($it['cas_no']) . '</small>' : '' ?></td>
                <td><?= e($it['grade']) ?></td>
                <td class="fw-bold"><?= e($it['batch_no']) ?></td>
                <td><?= dmy($it['mfg_date']) ?></td>
                <td><?= dmy($it['exp_date']) ?></td>
                <td class="num"><?= qty($it['qty'], 3) ?> <?= e($it['unit']) ?></td>
                <td class="num"><?= (int)$it['packs'] ?> <?= e($it['pack_type']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr><td colspan="6" class="text-end">Total</td><td class="num"><?= qty($tq, 3) ?></td><td class="num"><?= $tp ?></td></tr></tfoot>
    </table>
    <?php if ($dsp['remarks']): ?><p class="small"><b>Remarks:</b> <?= nl2br(e($dsp['remarks'])) ?></p><?php endif; ?>
    <div class="row text-center small mt-5 pt-4">
        <div class="col-4 border-top pt-1">Prepared By (Warehouse)</div>
        <div class="col-4 border-top pt-1">Received By (Transporter)</div>
        <div class="col-4 border-top pt-1">For <?= e(setting('company_name')) ?></div>
    </div>
</div>
<?php
$qrJson = json_encode($qrText);
$pageScripts = <<<HTML
<script src="assets/vendor/qrcode.min.js"></script>
<script>
new QRCode(document.getElementById('qrMain'), { text: $qrJson, width: 150, height: 150, colorDark: '#000000', correctLevel: QRCode.CorrectLevel.M });
</script>
HTML;
include ROOT_PATH . '/includes/footer.php';
