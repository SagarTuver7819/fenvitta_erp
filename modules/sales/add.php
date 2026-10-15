<?php
require_once __DIR__ . '/../../includes/init.php';

$id = (int)get('id');
$sale = $id ? row('SELECT * FROM sales WHERE id=?', [$id]) : null;
if ($id && !$sale) redirect('sales');

// ---------- Save ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = date_or_null(post('sale_date')) ?? today();
    $terms = (int)post('payment_terms');
    $invoiceNo = post('invoice_no') ?: next_sale_invoice($date);

    if (val('SELECT COUNT(*) FROM sales WHERE invoice_no=? AND id<>?', [$invoiceNo, $id]) > 0) {
        flash('danger', "Invoice No. $invoiceNo already exists.");
        redirect(($id ? "sales/edit/$id" : 'sales/add'));
    }

    $lines = [];
    $subtotal = 0;
    $gstTotal = 0;
    foreach ($_POST['item_id'] ?? [] as $k => $itemId) {
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
            'item_id' => $itemId, 'grade' => trim($_POST['grade'][$k] ?? ''), 'qty' => $qtyVal, 'unit' => trim($_POST['unit'][$k] ?? ''),
            'rate' => $rate, 'amount' => $amount, 'gst_rate' => $gstRate, 'gst_amount' => $gstAmt, 'total' => $amount + $gstAmt,
        ];
    }

    if (!(int)post('party_id') || !$lines) {
        flash('danger', 'Please select customer and add at least one product with quantity.');
        redirect(($id ? "sales/edit/$id" : 'sales/add'));
    }

    $freight = num(post('freight'));
    $data = [
        'invoice_no' => $invoiceNo,
        'sale_date' => $date,
        'party_id' => (int)post('party_id'),
        'location' => post('location'),
        'agent_name' => post('agent_name'),
        'po_ref' => post('po_ref'),
        'payment_terms' => $terms,
        'due_date' => date('Y-m-d', strtotime("$date +$terms days")),
        'subtotal' => $subtotal,
        'freight' => $freight,
        'gst_amount' => $gstTotal,
        'total' => round($subtotal + $gstTotal + $freight),
        'remarks' => post('remarks'),
    ];

    db_begin();
    if ($id) {
        update('sales', $data, $id);
        q('DELETE FROM sale_items WHERE sale_id=?', [$id]);
    } else {
        $data['created_by'] = current_user()['id'];
        $id = insert('sales', $data);
    }
    foreach ($lines as $line) {
        $line['sale_id'] = $id;
        insert('sale_items', $line);
    }
    db_commit();

    flash('success', "Sales invoice $invoiceNo saved. Stock will reduce when you make the Dispatch entry.");
    if (post('save_dispatch')) redirect("dispatch/add?sale_id=$id");
    redirect(post('save_print') ? "sales/print/$id" : 'sales');
}

$lines = $id ? rows('SELECT * FROM sale_items WHERE sale_id=? ORDER BY id', [$id]) : [];
$blank = ['item_id' => '', 'grade' => '', 'qty' => '', 'unit' => 'kg', 'rate' => '', 'gst_rate' => 18];
if (!$lines) $lines = [$blank];
$items = items_for(['FG', 'RM']);
$customers = parties_for('customer');
$s = $sale ?? [
    'invoice_no' => next_sale_invoice(), 'sale_date' => today(), 'party_id' => '', 'location' => '', 'agent_name' => '',
    'po_ref' => '', 'payment_terms' => 30, 'freight' => 0, 'remarks' => '',
];

// FG stock available (for info)
$pending = pending_dispatch_by_item();
$fgInfo = rows("SELECT i.id, i.name, i.unit, COALESCE(SUM(l.qty_in-l.qty_out),0) stock FROM items i LEFT JOIN stock_ledger l ON l.item_id=i.id WHERE i.item_type='FG' AND i.is_active=1 GROUP BY i.id");

$rowHtml = function ($l) use ($items) {
    ob_start(); ?>
    <tr>
        <td class="f-sn text-center text-muted"></td>
        <td style="min-width:220px"><select name="item_id[]" class="form-select form-select-sm f-item"><?= item_options($items, $l['item_id']) ?></select></td>
        <td style="width:110px"><input name="grade[]" class="form-control form-control-sm f-grade" value="<?= e($l['grade']) ?>" placeholder="IP/USP"></td>
        <td style="width:110px"><input name="qty[]" type="number" step="0.001" class="form-control form-control-sm f-qty" value="<?= e($l['qty']) ?>"></td>
        <td style="width:70px"><input name="unit[]" class="form-control form-control-sm f-unit" value="<?= e($l['unit']) ?>"></td>
        <td style="width:110px"><input name="rate[]" type="number" step="0.01" class="form-control form-control-sm f-rate" value="<?= e($l['rate']) ?>"></td>
        <td class="num f-amount"></td>
        <td style="width:75px"><input name="gst_rate[]" type="number" step="0.01" class="form-control form-control-sm f-gst" value="<?= e((float)$l['gst_rate']) ?>"></td>
        <td class="num f-total fw-semibold"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger f-remove"><i class="bi bi-x"></i></button></td>
    </tr>
    <?php return ob_get_clean();
};

$pageTitle = $sale ? 'Edit Sales Invoice' : 'New Sales Invoice';
$active = 'sales';
include ROOT_PATH . '/includes/header.php';
?>
<form method="post" data-line-items>
    <?= csrf_field() ?>
    <div class="row g-3 mb-3">
        <div class="col-xl-9">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-receipt"></i> Invoice Details</div>
                <div class="card-body row g-2">
                    <div class="col-md-3"><label class="form-label">Invoice No. *</label><input name="invoice_no" class="form-control fw-bold" required value="<?= e($s['invoice_no']) ?>"></div>
                    <div class="col-md-3"><label class="form-label">Date *</label><input type="date" name="sale_date" class="form-control" required value="<?= e($s['sale_date']) ?>"></div>
                    <div class="col-md-6">
                        <label class="form-label">Customer *</label>
                        <select name="party_id" class="form-select" required data-party-terms="#terms" data-party-city="#location">
                            <option value="">-- Select Customer --</option>
                            <?php foreach ($customers as $c): ?>
                                <option value="<?= $c['id'] ?>" data-terms="<?= (int)$c['payment_terms'] ?>" data-city="<?= e($c['city']) ?>" <?= $s['party_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?> <?= $c['city'] ? '- ' . e($c['city']) : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small><a href="<?= url('parties') ?>?new=Customer" target="_blank">+ Add new customer</a></small>
                    </div>
                    <div class="col-md-3"><label class="form-label">Location</label><input name="location" id="location" class="form-control" value="<?= e($s['location']) ?>"></div>
                    <div class="col-md-3"><label class="form-label">Agent Name</label><input name="agent_name" class="form-control" value="<?= e($s['agent_name']) ?>" placeholder="NA"></div>
                    <div class="col-md-3"><label class="form-label">Customer PO Ref.</label><input name="po_ref" class="form-control" value="<?= e($s['po_ref']) ?>"></div>
                    <div class="col-md-3"><label class="form-label">Payment Terms (days)</label><input type="number" name="payment_terms" id="terms" class="form-control" value="<?= e($s['payment_terms']) ?>"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-bag-check"></i> Stock Available For Sale</div>
                <ul class="list-group list-group-flush small">
                    <?php foreach ($fgInfo as $f): $av = $f['stock'] - ($pending[$f['id']] ?? 0); ?>
                        <li class="list-group-item d-flex justify-content-between"><span><?= e($f['name']) ?></span><b class="<?= $av > 0 ? 'text-success' : 'text-muted' ?>"><?= qty($av) ?> <?= e($f['unit']) ?></b></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><i class="bi bi-list-ol"></i> Products
            <div class="actions"><button type="button" class="btn btn-sm btn-accent add-row"><i class="bi bi-plus-lg"></i> Add Product</button></div>
        </div>
        <div class="table-responsive">
            <table class="table item-table">
                <thead><tr><th>#</th><th>Product</th><th>Grade</th><th>Qty</th><th>Unit</th><th>Rate / kg</th><th class="num">Amount</th><th>GST %</th><th class="num">Total</th><th></th></tr></thead>
                <tbody><?php foreach ($lines as $l) echo $rowHtml($l); ?></tbody>
            </table>
        </div>
        <template class="row-tpl"><?= $rowHtml($blank) ?></template>
    </div>

    <div class="row g-3">
        <div class="col-md-7">
            <div class="card"><div class="card-body">
                <label class="form-label">Remarks</label>
                <textarea name="remarks" class="form-control" rows="4"><?= e($s['remarks']) ?></textarea>
            </div></div>
        </div>
        <div class="col-md-5">
            <div class="totals-box">
                <div class="row"><div class="col-7">Basic Amount</div><div class="col-5 num t-sub">0.00</div></div>
                <div class="row"><div class="col-7">GST Amount</div><div class="col-5 num t-gst">0.00</div></div>
                <div class="row align-items-center"><div class="col-7">Freight</div><div class="col-5"><input type="number" step="0.01" name="freight" class="form-control form-control-sm text-end" value="<?= e($s['freight']) ?>"></div></div>
                <div class="row grand"><div class="col-7">Grand Total ₹ <small class="fw-normal">(rounded on save)</small></div><div class="col-5 num t-total">0.00</div></div>
            </div>
            <div class="d-flex gap-2 mt-3 justify-content-end flex-wrap">
                <a href="<?= url('sales') ?>" class="btn btn-light">Cancel</a>
                <button name="save_dispatch" value="1" class="btn btn-outline-primary"><i class="bi bi-truck"></i> Save &amp; Dispatch</button>
                <button name="save_print" value="1" class="btn btn-accent"><i class="bi bi-printer"></i> Save &amp; Print</button>
                <button class="btn btn-primary"><i class="bi bi-check2"></i> Save</button>
            </div>
        </div>
    </div>
</form>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
