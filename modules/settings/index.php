<?php
require_once __DIR__ . '/../../includes/init.php';
require_admin();

$fields = [
    'company_name' => ['Company Name', 'text'],
    'company_tagline' => ['Tagline', 'text'],
    'office_address' => ['Office Address', 'textarea'],
    'mfg_address' => ['Manufacturing Address (printed on labels)', 'textarea'],
    'phone' => ['Phone', 'text'],
    'email' => ['Email', 'text'],
    'website' => ['Website', 'text'],
    'gstin' => ['GSTIN', 'text'],
    'sale_prefix' => ['Sales Invoice Prefix (e.g. FNV -> FNV/1/26-27)', 'text'],
    'po_prefix' => ['Purchase Order Prefix (e.g. FNV -> FNV/26-27/001)', 'text'],
    'dispatch_prefix' => ['Dispatch No Prefix (e.g. DSP -> DSP/26-27/001)', 'text'],
    'label_storage' => ['Default Storage Condition (Packing Label)', 'textarea'],
    'po_terms' => ['Purchase Order Terms & Conditions', 'textarea'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($fields as $key => $f) {
        q('INSERT INTO settings (skey, svalue) VALUES (?, ?) ON DUPLICATE KEY UPDATE svalue = VALUES(svalue)', [$key, post($key)]);
    }
    flash('success', 'Settings saved.');
    redirect('settings');
}

$pageTitle = 'Company Settings';
$active = 'settings';
include ROOT_PATH . '/includes/header.php';
?>
<div class="card" style="max-width:900px">
    <div class="card-header"><i class="bi bi-sliders"></i> Company Settings</div>
    <div class="card-body">
        <form method="post" class="row g-3">
            <?= csrf_field() ?>
            <?php foreach ($fields as $key => [$label, $type]): ?>
                <div class="<?= $type === 'textarea' ? 'col-12' : 'col-md-6' ?>">
                    <label class="form-label"><?= e($label) ?></label>
                    <?php if ($type === 'textarea'): ?>
                        <textarea name="<?= $key ?>" class="form-control" rows="2"><?= e(setting($key)) ?></textarea>
                    <?php else: ?>
                        <input name="<?= $key ?>" class="form-control" value="<?= e(setting($key)) ?>">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <div class="col-12"><button class="btn btn-primary"><i class="bi bi-check2"></i> Save Settings</button></div>
        </form>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
