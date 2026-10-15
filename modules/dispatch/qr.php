<?php
require_once __DIR__ . '/../../includes/init.php';

$id = (int)get('id');
$pageTitle = 'Dispatch QR Code';
$active = 'dispatch_qr';

// No dispatch selected -> show list to choose from
if (!$id) {
    $list = rows('SELECT d.id, d.dispatch_no, d.dispatch_date, d.invoice_no, p.name party,
                    (SELECT SUM(qty) FROM dispatch_items WHERE dispatch_id = d.id) qty,
                    (SELECT SUM(packs) FROM dispatch_items WHERE dispatch_id = d.id) packs
                  FROM dispatches d JOIN parties p ON p.id = d.party_id ORDER BY d.id DESC LIMIT 200');
    include ROOT_PATH . '/includes/header.php'; ?>
    <div class="card">
        <div class="card-header"><i class="bi bi-qr-code"></i> Select Dispatch to Generate QR Code
            <div class="actions"><input class="form-control form-control-sm table-search" placeholder="Search..." data-table-search="#tbl"></div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover" id="tbl">
                <thead><tr><th>Date</th><th>Dispatch No</th><th>Invoice</th><th>Customer</th><th class="num">Qty</th><th class="num">Packs</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($list as $d): ?>
                    <tr>
                        <td><?= dmy($d['dispatch_date']) ?></td><td class="fw-bold"><?= e($d['dispatch_no']) ?></td><td><?= e($d['invoice_no']) ?></td>
                        <td><?= e($d['party']) ?></td><td class="num"><?= qty($d['qty']) ?></td><td class="num"><?= (int)$d['packs'] ?></td>
                        <td class="text-end"><a href="?id=<?= $d['id'] ?>" class="btn btn-sm btn-primary"><i class="bi bi-qr-code"></i> Generate QR</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$list): ?><tr><td colspan="7" class="text-center text-muted py-4">No dispatch found. <a href="<?= url('dispatch/add') ?>">Create dispatch</a></td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include ROOT_PATH . '/includes/footer.php';
    exit;
}

include ROOT_PATH . '/includes/dispatch_qr_data.php';

// One sticker per pack (drum). User can change count.
$totalPacks = array_sum(array_column($dspItems, 'packs'));
$count = (int)get('count', $totalPacks ?: 1);
$count = max(1, min($count, 200));

// Build sticker list: each pack shows its own batch
$stickers = [];
foreach ($dspItems as $it) {
    $packs = max(1, (int)$it['packs']);
    for ($n = 1; $n <= $packs; $n++) $stickers[] = $it;
}
if (!$stickers) $stickers = [['item_name' => '', 'batch_no' => '', 'qty' => 0, 'packs' => 1, 'unit' => '', 'grade' => '']];

include ROOT_PATH . '/includes/header.php';
?>
<form class="filter-bar row g-2 align-items-end no-print">
    <input type="hidden" name="id" value="<?= $id ?>">
    <div class="col-md-4"><b><?= e($dsp['dispatch_no']) ?></b> &middot; <?= e($dsp['party']) ?> &middot; <?= dmy($dsp['dispatch_date']) ?></div>
    <div class="col-md-2"><label class="form-label">No. of Stickers</label><input type="number" name="count" class="form-control form-control-sm" value="<?= $count ?>" min="1" max="200"></div>
    <div class="col-md-6 d-flex gap-2">
        <button class="btn btn-sm btn-outline-primary">Update</button>
        <a href="<?= url('dispatch/print/' . $id) ?>" class="btn btn-sm btn-light">Challan</a>
        <button type="button" onclick="window.print()" class="btn btn-sm btn-primary ms-auto"><i class="bi bi-printer"></i> Print Stickers</button>
    </div>
</form>

<div class="qr-sheet">
    <?php for ($n = 0; $n < $count; $n++): $it = $stickers[$n % count($stickers)]; ?>
        <div class="qr-sticker">
            <img src="assets/img/logo.jpg" style="height:30px" alt="">
            <div><div class="qr" data-qr></div></div>
            <div class="fw-bold" style="color:#632a4a"><?= e($dsp['dispatch_no']) ?></div>
            <div><?= e($it['item_name']) ?> <?= e($it['grade']) ?></div>
            <div>Batch: <b><?= e($it['batch_no']) ?></b></div>
            <div><?= e($dsp['party']) ?></div>
            <div class="text-muted">Pack <?= $n + 1 ?> of <?= $count ?> &middot; Inv <?= e($dsp['invoice_no']) ?></div>
        </div>
    <?php endfor; ?>
</div>
<?php
$qrJson = json_encode($qrText);
$pageScripts = <<<HTML
<script src="assets/vendor/qrcode.min.js"></script>
<script>
document.querySelectorAll('[data-qr]').forEach(el => new QRCode(el, { text: $qrJson, width: 130, height: 130, correctLevel: QRCode.CorrectLevel.M }));
</script>
HTML;
include ROOT_PATH . '/includes/footer.php';
