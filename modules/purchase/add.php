<?php
require_once __DIR__ . '/../../includes/init.php';

$id = (int)get('id');
$purchase = $id ? row('SELECT * FROM purchases WHERE id=?', [$id]) : null;
if ($id && !$purchase) redirect('purchase');

// ---------- Save ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = date_or_null(post('purchase_date')) ?? today();
    $terms = (int)post('payment_terms');

    // Collect item rows
    $lines = [];
    $subtotal = 0;
    $gstTotal = 0;
    $itemIds = $_POST['item_id'] ?? [];
    foreach ($itemIds as $k => $itemId) {
        $itemId = (int)$itemId;
        $qtyVal = num($_POST['qty'][$k] ?? 0);
        if (!$itemId || $qtyVal <= 0) continue;
        $rate = num($_POST['rate'][$k] ?? 0);
        $gstRate = num($_POST['gst_rate'][$k] ?? 0);
        $amount = round($qtyVal * $rate, 2);
        $gstAmt = round($amount * $gstRate / 100, 2);
        $subtotal += $amount;
        $gstTotal += $gstAmt;
        $lines[] = [
            'item_id' => $itemId, 'description' => trim($_POST['description'][$k] ?? ''), 'qty' => $qtyVal,
            'unit' => trim($_POST['unit'][$k] ?? ''), 'rate' => $rate, 'amount' => $amount,
            'gst_rate' => $gstRate, 'gst_amount' => $gstAmt, 'total' => $amount + $gstAmt,
        ];
    }

    if (!(int)post('party_id') || !$lines) {
        flash('danger', 'Please select supplier and add at least one item with quantity.');
        redirect(($id ? "purchase/edit/$id" : 'purchase/add'));
    }

    $freight = num(post('freight'));
    $other = num(post('other_charges'));
    $data = [
        'po_no' => post('po_no'),
        'invoice_no' => post('invoice_no'),
        'purchase_date' => $date,
        'party_id' => (int)post('party_id'),
        'category' => post('category'),
        'payment_terms' => $terms,
        'due_date' => date('Y-m-d', strtotime("$date +$terms days")),
        'subtotal' => $subtotal,
        'gst_amount' => $gstTotal,
        'freight' => $freight,
        'other_charges' => $other,
        'total' => round($subtotal + $gstTotal + $freight + $other, 2),
        'delivery_at' => post('delivery_at'),
        'transport' => post('transport'),
        'freight_terms' => post('freight_terms'),
        'remarks' => post('remarks'),
    ];

    db_begin();
    if ($id) {
        update('purchases', $data, $id);
        q('DELETE FROM purchase_items WHERE purchase_id=?', [$id]);
    } else {
        $data['created_by'] = current_user()['id'];
        $id = insert('purchases', $data);
    }

    // Items + stock IN (service items are not stock items)
    ledger_clear('PURCHASE', $id);
    foreach ($lines as $line) {
        $line['purchase_id'] = $id;
        insert('purchase_items', $line);
        $type = val('SELECT item_type FROM items WHERE id=?', [$line['item_id']]);
        if ($type !== 'SERVICE') {
            ledger_add($date, $line['item_id'], $line['qty'], 0, 'PURCHASE', $id, $line['rate'], null, 'Purchase ' . $data['invoice_no']);
        }
    }
    db_commit();

    flash('success', 'Purchase entry saved.');
    redirect(post('save_print') ? "purchase/print/$id" : 'purchase');
}

$lines = $id ? rows('SELECT * FROM purchase_items WHERE purchase_id=? ORDER BY id', [$id]) : [];
if (!$lines) $lines = [['item_id' => '', 'description' => '', 'qty' => '', 'unit' => '', 'rate' => '', 'gst_rate' => 18]];
$items = items_for();
$suppliers = parties_for('supplier');
$p = $purchase ?? [
    'po_no' => next_series('purchases', 'po_no', setting('po_prefix', 'FNV')), 'invoice_no' => '', 'purchase_date' => today(),
    'party_id' => '', 'category' => 'Raw Material', 'payment_terms' => 60, 'freight' => 0, 'other_charges' => 0,
    'delivery_at' => 'Panoli 394116', 'transport' => 'By Private Transport', 'freight_terms' => 'Paid', 'remarks' => '',
];

$rowHtml = function ($l) use ($items) {
    ob_start(); ?>
    <tr>
        <td class="f-sn text-center text-muted"></td>
        <td style="min-width:220px"><select name="item_id[]" class="form-select form-select-sm f-item"><?= item_options($items, $l['item_id']) ?></select>
            <input name="description[]" class="form-control form-control-sm mt-1" placeholder="Description / Make (optional)" value="<?= e($l['description']) ?>"></td>
        <td style="width:110px"><input name="qty[]" type="number" step="0.001" class="form-control form-control-sm f-qty" value="<?= e($l['qty']) ?>"></td>
        <td style="width:80px"><input name="unit[]" class="form-control form-control-sm f-unit" value="<?= e($l['unit']) ?>"></td>
        <td style="width:110px"><input name="rate[]" type="number" step="0.01" class="form-control form-control-sm f-rate" value="<?= e($l['rate']) ?>"></td>
        <td class="num f-amount"></td>
        <td style="width:80px"><input name="gst_rate[]" type="number" step="0.01" class="form-control form-control-sm f-gst" value="<?= e((float)$l['gst_rate']) ?>"></td>
        <td class="num f-total fw-semibold"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger f-remove"><i class="bi bi-x"></i></button></td>
    </tr>
    <?php return ob_get_clean();
};

$pageTitle = $purchase ? 'Edit Purchase Entry' : 'New Purchase Entry';
$active = 'purchases';
include ROOT_PATH . '/includes/header.php';
?>
<form method="post" data-line-items>
    <?= csrf_field() ?>
    <div class="card mb-3">
        <div class="card-header"><i class="bi bi-cart-plus"></i> Purchase Details</div>
        <div class="card-body row g-2">
            <div class="col-md-2"><label class="form-label">Date *</label><input type="date" name="purchase_date" class="form-control" required value="<?= e($p['purchase_date']) ?>"></div>
            <div class="col-md-2"><label class="form-label">PO No.</label><input name="po_no" class="form-control" value="<?= e($p['po_no']) ?>"></div>
            <div class="col-md-2"><label class="form-label">Supplier Invoice No.</label><input name="invoice_no" class="form-control" value="<?= e($p['invoice_no']) ?>"></div>
            <div class="col-md-4">
                <label class="form-label">Supplier *</label>
                <select name="party_id" class="form-select" required data-party-terms="#terms">
                    <option value="">-- Select Supplier --</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= $s['id'] ?>" data-terms="<?= (int)$s['payment_terms'] ?>" <?= $p['party_id'] == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?> <?= $s['city'] ? '- ' . e($s['city']) : '' ?></option>
                    <?php endforeach; ?>
                </select>
                <small><a href="<?= url('parties') ?>?new=Supplier" target="_blank">+ Add new supplier</a></small>
            </div>
            <div class="col-md-2">
                <label class="form-label">Category</label>
                <select name="category" class="form-select">
                    <?php foreach (PURCHASE_CATEGORIES as $c): ?><option <?= $p['category'] === $c ? 'selected' : '' ?>><?= $c ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2"><label class="form-label">Payment Terms (days)</label><input type="number" name="payment_terms" id="terms" class="form-control" value="<?= e($p['payment_terms']) ?>"></div>
            <div class="col-md-2"><label class="form-label">Freight Terms</label><input name="freight_terms" class="form-control" list="freightList" value="<?= e($p['freight_terms']) ?>">
                <datalist id="freightList"><option>Paid</option><option>To Pay</option><option>Included in Bill</option></datalist></div>
            <div class="col-md-3"><label class="form-label">Delivery At</label><input name="delivery_at" class="form-control" value="<?= e($p['delivery_at']) ?>"></div>
            <div class="col-md-3"><label class="form-label">Transport Details</label><input name="transport" class="form-control" value="<?= e($p['transport']) ?>"></div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><i class="bi bi-list-ol"></i> Items
            <div class="actions"><button type="button" class="btn btn-sm btn-accent add-row"><i class="bi bi-plus-lg"></i> Add Item</button></div>
        </div>
        <div class="table-responsive">
            <table class="table item-table">
                <thead><tr><th>#</th><th>Item</th><th>Qty</th><th>Unit</th><th>Rate</th><th class="num">Amount</th><th>GST %</th><th class="num">Total</th><th></th></tr></thead>
                <tbody><?php foreach ($lines as $l) echo $rowHtml($l); ?></tbody>
            </table>
        </div>
        <template class="row-tpl"><?= $rowHtml(['item_id' => '', 'description' => '', 'qty' => '', 'unit' => '', 'rate' => '', 'gst_rate' => 18]) ?></template>
    </div>

    <div class="row g-3">
        <div class="col-md-7">
            <div class="card"><div class="card-body">
                <label class="form-label">Remarks</label>
                <textarea name="remarks" class="form-control" rows="4"><?= e($p['remarks']) ?></textarea>
            </div></div>
        </div>
        <div class="col-md-5">
            <div class="totals-box">
                <div class="row"><div class="col-7">Basic Amount</div><div class="col-5 num t-sub">0.00</div></div>
                <div class="row"><div class="col-7">GST Amount</div><div class="col-5 num t-gst">0.00</div></div>
                <div class="row align-items-center"><div class="col-7">Freight</div><div class="col-5"><input type="number" step="0.01" name="freight" class="form-control form-control-sm text-end" value="<?= e($p['freight']) ?>"></div></div>
                <div class="row align-items-center"><div class="col-7">Other Charges / Round Off</div><div class="col-5"><input type="number" step="0.01" name="other_charges" class="form-control form-control-sm text-end" value="<?= e($p['other_charges']) ?>"></div></div>
                <div class="row grand"><div class="col-7">Grand Total ₹</div><div class="col-5 num t-total">0.00</div></div>
            </div>
            <div class="d-flex gap-2 mt-3 justify-content-end">
                <a href="<?= url('purchase') ?>" class="btn btn-light">Cancel</a>
                <button name="save_print" value="1" class="btn btn-accent"><i class="bi bi-printer"></i> Save &amp; Print PO</button>
                <button class="btn btn-primary"><i class="bi bi-check2"></i> Save</button>
            </div>
        </div>
    </div>
</form>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
