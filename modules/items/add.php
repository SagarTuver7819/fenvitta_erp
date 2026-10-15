<?php
/* Add new item  (/items/add)   or   Edit item  (/items/edit/{id}) */
require_once __DIR__ . '/../../includes/init.php';

$types = ['RM', 'PM', 'FG', 'CONSUMABLE', 'SERVICE'];

$id = (int)get('id');
$item = $id ? row('SELECT * FROM items WHERE id=?', [$id]) : null;
if ($id && !$item) redirect('items');

// ---------- Save ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'item_type' => in_array(post('item_type'), $types) ? post('item_type') : 'RM',
        'code' => post('code'),
        'name' => post('name'),
        'cas_no' => post('cas_no'),
        'grade' => post('grade'),
        'hsn' => post('hsn'),
        'unit' => post('unit') ?: 'kg',
        'gst_rate' => num(post('gst_rate')),
        'std_rate' => num(post('std_rate')),
        'reorder_level' => num(post('reorder_level')),
        'opening_qty' => num(post('opening_qty')),
        'opening_rate' => num(post('opening_rate')),
        'opening_date' => date_or_null(post('opening_date')) ?? today(),
        'shelf_life_months' => (int)post('shelf_life_months'),
        'is_active' => post('is_active') ? 1 : 0,
    ];
    if ($data['name'] === '') {
        flash('danger', 'Item name is required.');
        redirect($id ? "items/edit/$id" : 'items/add');
    }
    if ($id) {
        update('items', $data, $id);
    } else {
        $id = insert('items', $data);
    }

    // Opening stock is kept in stock ledger (only for stock items, FG opening is added via batch output)
    ledger_clear('OPENING', $id);
    if (!in_array($data['item_type'], ['SERVICE', 'FG']) && $data['opening_qty'] > 0) {
        ledger_add($data['opening_date'], $id, $data['opening_qty'], 0, 'OPENING', $id, $data['opening_rate'], null, 'Opening Stock');
    }
    flash('success', 'Item "' . $data['name'] . '" saved.');
    redirect('items?type=' . $data['item_type']);
}

$it = $item ?? [
    'item_type' => in_array(get('type'), $types) ? get('type') : 'RM', 'code' => '', 'name' => '', 'cas_no' => '', 'grade' => '', 'hsn' => '',
    'unit' => 'kg', 'gst_rate' => 18, 'std_rate' => 0, 'reorder_level' => 0, 'shelf_life_months' => 0,
    'opening_qty' => 0, 'opening_rate' => 0, 'opening_date' => today(), 'is_active' => 1,
];

$pageTitle = $item ? 'Edit Item' : 'Add Item';
$active = 'items';
include ROOT_PATH . '/includes/header.php';
?>
<div class="card" style="max-width:900px">
    <div class="card-header"><i class="bi bi-capsule"></i> <?= $pageTitle ?></div>
    <div class="card-body">
        <form method="post">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Item Type *</label>
                    <select name="item_type" class="form-select">
                        <?php foreach ($types as $t): ?><option value="<?= $t ?>" <?= $it['item_type'] === $t ? 'selected' : '' ?>><?= item_type_label($t) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><label class="form-label">Code</label><input name="code" class="form-control" value="<?= e($it['code']) ?>"></div>
                <div class="col-md-6"><label class="form-label">Item Name *</label><input name="name" class="form-control" required value="<?= e($it['name']) ?>"></div>
                <div class="col-md-4"><label class="form-label">CAS No.</label><input name="cas_no" class="form-control" value="<?= e($it['cas_no']) ?>"></div>
                <div class="col-md-4"><label class="form-label">Grade</label><input name="grade" class="form-control" placeholder="IP/USP/BP" value="<?= e($it['grade']) ?>"></div>
                <div class="col-md-4"><label class="form-label">HSN</label><input name="hsn" class="form-control" value="<?= e($it['hsn']) ?>"></div>
                <div class="col-md-3">
                    <label class="form-label">Unit</label>
                    <select name="unit" class="form-select">
                        <?php foreach (['kg', 'Ltr', 'MT', 'gm', 'Nos', 'Drum', 'Bag'] as $u): ?><option <?= $it['unit'] === $u ? 'selected' : '' ?>><?= $u ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label">GST %</label><input type="number" step="0.01" name="gst_rate" class="form-control" value="<?= e($it['gst_rate']) ?>"></div>
                <div class="col-md-3"><label class="form-label">Standard Rate (₹)</label><input type="number" step="0.01" name="std_rate" class="form-control" value="<?= e($it['std_rate']) ?>"></div>
                <div class="col-md-3"><label class="form-label">Reorder Level</label><input type="number" step="0.001" name="reorder_level" class="form-control" value="<?= e($it['reorder_level']) ?>"></div>
                <div class="col-md-3"><label class="form-label">Shelf Life (months)</label><input type="number" name="shelf_life_months" class="form-control" value="<?= e($it['shelf_life_months']) ?>"></div>

                <div class="col-12"><hr class="my-1"><small class="text-muted">Opening Stock (RM / PM / Consumable only. For FG create a batch &amp; output entry.)</small></div>
                <div class="col-md-4"><label class="form-label">Opening Qty</label><input type="number" step="0.001" name="opening_qty" class="form-control" value="<?= e($it['opening_qty']) ?>"></div>
                <div class="col-md-4"><label class="form-label">Rate</label><input type="number" step="0.01" name="opening_rate" class="form-control" value="<?= e($it['opening_rate']) ?>"></div>
                <div class="col-md-4"><label class="form-label">As on</label><input type="date" name="opening_date" class="form-control" value="<?= e($it['opening_date']) ?>"></div>
                <div class="col-12">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="is_active" id="act" value="1" <?= $it['is_active'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="act">Active</label>
                    </div>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <a href="<?= url('items') ?>" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a>
                <button class="btn btn-primary ms-auto"><i class="bi bi-check2"></i> <?= $item ? 'Update' : 'Save' ?> Item</button>
            </div>
        </form>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
