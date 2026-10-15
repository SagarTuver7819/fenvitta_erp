<?php
require_once __DIR__ . '/../../includes/init.php';

$id = (int)get('id');
$dispatch = $id ? row('SELECT * FROM dispatches WHERE id=?', [$id]) : null;
if ($id && !$dispatch) redirect('dispatch');

// ---------- Save ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = date_or_null(post('dispatch_date')) ?? today();
    $back = ($id ? "dispatch/edit/$id" : 'dispatch/add');

    $lines = [];
    foreach ($_POST['batch_id'] ?? [] as $k => $batchId) {
        $batchId = (int)$batchId;
        $qtyVal = num($_POST['qty'][$k] ?? 0);
        if (!$batchId || $qtyVal <= 0) continue;
        $lines[] = [
            'batch_id' => $batchId, 'qty' => $qtyVal, 'grade' => trim($_POST['grade'][$k] ?? ''),
            'packs' => (int)($_POST['packs'][$k] ?? 0), 'pack_type' => trim($_POST['pack_type'][$k] ?? ''),
        ];
    }
    if (!(int)post('party_id') || !$lines) {
        flash('danger', 'Select customer and add at least one batch with quantity.');
        redirect($back);
    }

    $saleId = (int)post('sale_id') ?: null;
    $data = [
        'dispatch_date' => $date,
        'sale_id' => $saleId,
        'party_id' => (int)post('party_id'),
        'invoice_no' => post('invoice_no'),
        'destination' => post('destination'),
        'transporter' => post('transporter'),
        'vehicle_no' => strtoupper(post('vehicle_no')),
        'lr_no' => post('lr_no'),
        'driver_name' => post('driver_name'),
        'driver_mobile' => post('driver_mobile'),
        'remarks' => post('remarks'),
    ];

    db_begin();
    if ($id) {
        // Return old stock first, then check again with new quantities
        ledger_clear('DISPATCH', $id);
        q('DELETE FROM dispatch_items WHERE dispatch_id=?', [$id]);
    }

    // Stock check per batch (same batch may come in 2 rows)
    $need = [];
    foreach ($lines as $l) $need[$l['batch_id']] = ($need[$l['batch_id']] ?? 0) + $l['qty'];
    foreach ($need as $batchId => $q) {
        $bal = batch_balance($batchId);
        if ($q > $bal + 0.0001) {
            db_rollback();
            $bn = val('SELECT batch_no FROM production_batches WHERE id=?', [$batchId]);
            flash('danger', "Batch $bn has only " . qty($bal) . ' kg in stock, you entered ' . qty($q) . ' kg.');
            redirect($back);
        }
    }

    if ($id) {
        update('dispatches', $data, $id);
    } else {
        $data['dispatch_no'] = next_series('dispatches', 'dispatch_no', setting('dispatch_prefix', 'DSP'), $date);
        $data['qr_token'] = bin2hex(random_bytes(8));
        $data['created_by'] = current_user()['id'];
        $id = insert('dispatches', $data);
    }
    $dispatchNo = val('SELECT dispatch_no FROM dispatches WHERE id=?', [$id]);
    $partyName = val('SELECT name FROM parties WHERE id=?', [$data['party_id']]);

    foreach ($lines as $l) {
        $itemId = (int)val('SELECT item_id FROM production_batches WHERE id=?', [$l['batch_id']]);
        $l['dispatch_id'] = $id;
        $l['item_id'] = $itemId;
        insert('dispatch_items', $l);
        ledger_add($date, $itemId, 0, $l['qty'], 'DISPATCH', $id, 0, $l['batch_id'], "Dispatch $dispatchNo to $partyName");
    }
    db_commit();

    flash('success', "Dispatch $dispatchNo saved. FG stock updated.");
    redirect("dispatch/print/$id");
}

// ---------- Load form data ----------
$saleId = (int)get('sale_id') ?: (int)($dispatch['sale_id'] ?? 0);
$sale = $saleId ? row('SELECT s.*, p.city FROM sales s JOIN parties p ON p.id = s.party_id WHERE s.id=?', [$saleId]) : null;

$lines = $id ? rows('SELECT * FROM dispatch_items WHERE dispatch_id=? ORDER BY id', [$id]) : [];

// Batches with stock (plus batches already used in this dispatch while editing)
$batches = fg_batches(false);
$usedInThis = [];
foreach ($lines as $l) $usedInThis[$l['batch_id']] = ($usedInThis[$l['batch_id']] ?? 0) + $l['qty'];
$batches = array_values(array_filter($batches, function ($b) use ($usedInThis) {
    return $b['balance'] > 0 || isset($usedInThis[$b['id']]);
}));
foreach ($batches as &$b) $b['balance'] += $usedInThis[$b['id']] ?? 0;
unset($b);

// New dispatch from sales invoice: pre-fill pending qty using FIFO (oldest batch first)
$saleHint = [];
if (!$id && $sale) {
    $pendingRows = rows('SELECT si.item_id, si.grade, SUM(si.qty) qty, i.name FROM sale_items si JOIN items i ON i.id = si.item_id WHERE si.sale_id=? GROUP BY si.item_id, si.grade', [$saleId]);
    foreach ($pendingRows as $pr) {
        $done = (float)val('SELECT COALESCE(SUM(di.qty),0) FROM dispatch_items di JOIN dispatches d ON d.id = di.dispatch_id WHERE d.sale_id=? AND di.item_id=?', [$saleId, $pr['item_id']]);
        $left = $pr['qty'] - $done;
        if ($left <= 0) continue;
        $saleHint[] = $pr['name'] . ': ' . qty($left) . ' kg pending';
        foreach ($batches as $b) {
            if ($left <= 0) break;
            if ((int)$b['item_id'] !== (int)$pr['item_id'] || $b['balance'] <= 0) continue;
            $take = min($left, $b['balance']);
            $lines[] = ['batch_id' => $b['id'], 'qty' => $take, 'grade' => $pr['grade'], 'packs' => '', 'pack_type' => 'HDPE Drum'];
            $left -= $take;
        }
        if ($left > 0) $saleHint[] = 'Short stock for ' . $pr['name'] . ': ' . qty($left) . ' kg';
    }
}
$blank = ['batch_id' => '', 'qty' => '', 'grade' => 'IP/USP', 'packs' => '', 'pack_type' => 'HDPE Drum'];
if (!$lines) $lines = [$blank];

$d = $dispatch ?? [
    'dispatch_no' => next_series('dispatches', 'dispatch_no', setting('dispatch_prefix', 'DSP')), 'dispatch_date' => today(),
    'party_id' => $sale['party_id'] ?? '', 'invoice_no' => $sale['invoice_no'] ?? '', 'destination' => $sale['location'] ?? ($sale['city'] ?? ''),
    'transporter' => '', 'vehicle_no' => '', 'lr_no' => '', 'driver_name' => '', 'driver_mobile' => '', 'remarks' => '',
];
$customers = parties_for('customer');
$openSales = rows("SELECT s.id, s.invoice_no, p.name FROM sales s JOIN parties p ON p.id = s.party_id ORDER BY s.id DESC LIMIT 200");

$batchOptions = function ($selected) use ($batches) {
    $html = '<option value="">-- Select Batch --</option>';
    foreach ($batches as $b) {
        $html .= '<option value="' . $b['id'] . '" data-bal="' . (float)$b['balance'] . '"' . ((string)$selected === (string)$b['id'] ? ' selected' : '') . '>'
            . e($b['batch_no'] . ' | ' . $b['item_name'] . ' | Mfg ' . dmy($b['mfg_date']) . ' | Stock ' . qty($b['balance'])) . '</option>';
    }
    return $html;
};
$rowHtml = function ($l) use ($batchOptions) {
    ob_start(); ?>
    <tr>
        <td style="min-width:320px"><select name="batch_id[]" class="form-select form-select-sm f-batch"><?= $batchOptions($l['batch_id']) ?></select></td>
        <td style="width:110px"><input name="grade[]" class="form-control form-control-sm" value="<?= e($l['grade']) ?>"></td>
        <td style="width:120px"><input name="qty[]" type="number" step="0.001" class="form-control form-control-sm f-qty" value="<?= e($l['qty']) ?>"></td>
        <td style="width:90px"><input name="packs[]" type="number" class="form-control form-control-sm" value="<?= e($l['packs']) ?>"></td>
        <td style="width:140px"><input name="pack_type[]" class="form-control form-control-sm" list="packList" value="<?= e($l['pack_type']) ?>"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger f-remove"><i class="bi bi-x"></i></button></td>
    </tr>
    <?php return ob_get_clean();
};

$pageTitle = $dispatch ? 'Edit Dispatch ' . $dispatch['dispatch_no'] : 'New Dispatch Entry';
$active = 'dispatch';
include ROOT_PATH . '/includes/header.php';
?>
<form method="post" data-line-items>
    <?= csrf_field() ?>
    <datalist id="packList"><option>HDPE Drum</option><option>Fibre Drum</option><option>Bag</option><option>Carboy</option></datalist>
    <div class="card mb-3">
        <div class="card-header"><i class="bi bi-truck"></i> Dispatch Details <span class="badge text-bg-primary ms-2"><?= e($d['dispatch_no']) ?></span></div>
        <div class="card-body row g-2">
            <div class="col-md-4">
                <label class="form-label">Against Sales Invoice</label>
                <select name="sale_id" class="form-select" onchange="if(this.value && !<?= $id ? 'true' : 'false' ?>) location='<?= url('dispatch/add') ?>?sale_id='+this.value">
                    <option value="">-- Without invoice --</option>
                    <?php foreach ($openSales as $s): ?><option value="<?= $s['id'] ?>" <?= $saleId == $s['id'] ? 'selected' : '' ?>><?= e($s['invoice_no'] . ' - ' . $s['name']) ?></option><?php endforeach; ?>
                </select>
                <?php foreach ($saleHint as $h): ?><small class="d-block text-primary fw-semibold"><?= e($h) ?></small><?php endforeach; ?>
            </div>
            <div class="col-md-2"><label class="form-label">Dispatch Date *</label><input type="date" name="dispatch_date" class="form-control" required value="<?= e($d['dispatch_date']) ?>"></div>
            <div class="col-md-4">
                <label class="form-label">Customer *</label>
                <select name="party_id" class="form-select" required>
                    <option value="">-- Select Customer --</option>
                    <?php foreach ($customers as $c): ?><option value="<?= $c['id'] ?>" <?= $d['party_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2"><label class="form-label">Invoice No.</label><input name="invoice_no" class="form-control" value="<?= e($d['invoice_no']) ?>"></div>
            <div class="col-md-3"><label class="form-label">Destination</label><input name="destination" class="form-control" value="<?= e($d['destination']) ?>"></div>
            <div class="col-md-3"><label class="form-label">Transporter</label><input name="transporter" class="form-control" value="<?= e($d['transporter']) ?>"></div>
            <div class="col-md-2"><label class="form-label">Vehicle No.</label><input name="vehicle_no" class="form-control" value="<?= e($d['vehicle_no']) ?>"></div>
            <div class="col-md-2"><label class="form-label">LR No.</label><input name="lr_no" class="form-control" value="<?= e($d['lr_no']) ?>"></div>
            <div class="col-md-2"><label class="form-label">Driver Mobile</label><input name="driver_mobile" class="form-control" value="<?= e($d['driver_mobile']) ?>"></div>
            <div class="col-md-3"><label class="form-label">Driver Name</label><input name="driver_name" class="form-control" value="<?= e($d['driver_name']) ?>"></div>
            <div class="col-md-9"><label class="form-label">Remarks</label><input name="remarks" class="form-control" value="<?= e($d['remarks']) ?>"></div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><i class="bi bi-boxes"></i> Batch Wise Dispatch Qty
            <div class="actions"><button type="button" class="btn btn-sm btn-accent add-row"><i class="bi bi-plus-lg"></i> Add Batch</button></div>
        </div>
        <div class="table-responsive">
            <table class="table item-table">
                <thead><tr><th>Batch (Product / Mfg / Stock)</th><th>Grade</th><th>Qty (kg)</th><th>No. of Packs</th><th>Pack Type</th><th></th></tr></thead>
                <tbody><?php foreach ($lines as $l) echo $rowHtml($l); ?></tbody>
                <tfoot><tr><td colspan="2" class="text-end">Total Qty</td><td class="num" id="totQty">0</td><td colspan="3"></td></tr></tfoot>
            </table>
        </div>
        <template class="row-tpl"><?= $rowHtml($blank) ?></template>
    </div>
    <div class="d-flex gap-2 justify-content-end">
        <a href="<?= url('dispatch') ?>" class="btn btn-light">Cancel</a>
        <button class="btn btn-primary"><i class="bi bi-check2"></i> Save Dispatch &amp; Generate QR</button>
    </div>
</form>
<?php
$pageScripts = <<<HTML
<script>
// Show total qty and warn if qty is more than batch stock
document.querySelector('form[data-line-items]').addEventListener('input', check);
document.querySelector('form[data-line-items]').addEventListener('change', check);
function check() {
    let t = 0;
    document.querySelectorAll('.item-table tbody tr').forEach(tr => {
        const q = fvNum(tr.querySelector('.f-qty').value);
        const o = tr.querySelector('.f-batch').selectedOptions[0];
        const bal = o ? fvNum(o.dataset.bal) : 0;
        tr.querySelector('.f-qty').classList.toggle('is-invalid', o && o.value && q > bal);
        t += q;
    });
    document.getElementById('totQty').textContent = fvFmt(t, 3);
}
check();
</script>
HTML;
include ROOT_PATH . '/includes/footer.php';
