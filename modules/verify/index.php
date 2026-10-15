<?php
/* Public page opened when someone scans the dispatch QR code (no login needed).  URL: /verify?t={token} */
require_once __DIR__ . '/../../includes/functions.php';

$token = get('t');
$dsp = $token ? row('SELECT d.*, p.name party FROM dispatches d JOIN parties p ON p.id = d.party_id WHERE d.qr_token=?', [$token]) : null;
$items = $dsp ? rows('SELECT di.*, i.name item_name, i.unit, b.batch_no, b.mfg_date, b.exp_date FROM dispatch_items di
                     JOIN items i ON i.id = di.item_id LEFT JOIN production_batches b ON b.id = di.batch_id WHERE di.dispatch_id=?', [$dsp['id']]) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dispatch Verification | Fenvitta</title>
    <link rel="icon" href="<?= url('assets/img/logo-mark.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= url('assets/vendor/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/vendor/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
<div class="auth-page">
    <div class="auth-card" style="max-width:520px">
        <span class="corner tl"></span><span class="corner br"></span>
        <span class="hub-badge">QR Verify</span>
        <div class="since">Dispatch Verification</div>
        <img src="<?= url('assets/img/logo-wide.png') ?>" alt="Fenvitta" class="logo mb-3">

        <?php if (!$dsp): ?>
            <div class="verify-bad"><i class="bi bi-x-octagon"></i>Invalid or unknown QR code.</div>
        <?php else: ?>
            <div class="verify-ok mb-3"><i class="bi bi-patch-check-fill"></i> Genuine dispatch from Fenvitta Lifesciences LLP</div>
            <table class="table table-sm small">
                <tr><th>Dispatch No.</th><td><?= e($dsp['dispatch_no']) ?></td></tr>
                <tr><th>Date</th><td><?= dmy($dsp['dispatch_date']) ?></td></tr>
                <tr><th>Invoice No.</th><td><?= e($dsp['invoice_no']) ?></td></tr>
                <tr><th>Customer</th><td><?= e($dsp['party']) ?></td></tr>
                <?php if ($dsp['destination']): ?><tr><th>Destination</th><td><?= e($dsp['destination']) ?></td></tr><?php endif; ?>
            </table>
            <?php foreach ($items as $it): ?>
                <div class="border rounded-3 p-2 mb-2 small">
                    <b class="text-primary"><?= e($it['item_name']) ?> <?= e($it['grade']) ?></b><br>
                    Batch: <b><?= e($it['batch_no']) ?></b> &middot; Qty: <b><?= qty($it['qty']) ?> <?= e($it['unit']) ?></b> &middot; Packs: <?= (int)$it['packs'] ?><br>
                    Mfg: <?= dmy($it['mfg_date']) ?> &middot; Exp: <?= dmy($it['exp_date']) ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <p class="text-center text-muted small mt-3 mb-0"><?= e(setting('website')) ?> &middot; <?= e(setting('phone')) ?></p>
    </div>
</div>
</body>
</html>
