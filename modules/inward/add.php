<?php
/* Add inward  (/inward/add?purchase_id=)   or   Edit  (/inward/edit/{id}) */
require_once __DIR__ . '/../../includes/init.php';

$id = (int)get('id');
$inward = $id ? row('SELECT * FROM inward_entries WHERE id=?', [$id]) : null;
if ($id && !$inward) redirect('inward');

$purchaseId = $id ? (int)$inward['purchase_id'] : (int)get('purchase_id');

// ---------- Save ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $purchaseId = (int)post('purchase_id');
    $po = row('SELECT * FROM purchases WHERE id=?', [$purchaseId]);
    $back = $id ? "inward/edit/$id" : ('inward/add' . ($purchaseId ? "?purchase_id=$purchaseId" : ''));

    if (!$po) {
        flash('danger', 'Please select a Purchase Order.');
        redirect($back);
    }

    $date = date_or_null(post('inward_date')) ?? today();
    $lines = purchase_inward_lines($purchaseId, $id);
    $recv = [];
    $piIds = $_POST['purchase_item_id'] ?? [];
    foreach ($piIds as $k => $piId) {
        $piId = (int)$piId;
        $qtyVal = num($_POST['recv_qty'][$k] ?? 0);
        if ($piId <= 0 || $qtyVal <= 0) continue;
        $recv[$piId] = $qtyVal;
    }

    $saveLines = [];
    foreach ($lines as $l) {
        if ($l['item_type'] === 'SERVICE') continue;
        $q = $recv[(int)$l['id']] ?? 0;
        if ($q <= 0) continue;
        if ($q > $l['pending'] + 0.0005) {
            flash('danger', 'Receive qty for "' . $l['item_name'] . '" cannot exceed pending ' . qty($l['pending']) . ' ' . $l['unit'] . '.');
            redirect($back);
        }
        $saveLines[] = [
            'purchase_item_id' => (int)$l['id'],
            'item_id' => (int)$l['item_id'],
            'qty' => $q,
            'unit' => $l['unit'],
            'rate' => (float)$l['rate'],
        ];
    }

    if (!$saveLines) {
        flash('danger', 'Enter receive qty for at least one item (partial inward is allowed).');
        redirect($back);
    }

    $data = [
        'inward_no' => post('inward_no') ?: next_series('inward_entries', 'inward_no', setting('inward_prefix', 'GRN'), $date),
        'inward_date' => $date,
        'purchase_id' => $purchaseId,
        'party_id' => (int)$po['party_id'],
        'invoice_no' => post('invoice_no'),
        'vehicle_no' => post('vehicle_no'),
        'remarks' => post('remarks'),
    ];

    db_begin();
    if ($id) {
        update('inward_entries', $data, $id);
        q('DELETE FROM inward_items WHERE inward_id=?', [$id]);
        ledger_clear('INWARD', $id);
    } else {
        $data['created_by'] = current_user()['id'];
        $id = insert('inward_entries', $data);
    }

    foreach ($saveLines as $line) {
        $line['inward_id'] = $id;
        insert('inward_items', $line);
        ledger_add($date, $line['item_id'], $line['qty'], 0, 'INWARD', $id, $line['rate'], null,
            'Inward ' . $data['inward_no'] . ' against PO ' . $po['po_no']);
    }
    db_commit();

    $tot = purchase_inward_totals($purchaseId);
    flash('success', 'Inward saved. PO status: ' . $tot['status'] . ' (Received ' . qty($tot['received']) . ' / ' . qty($tot['ordered']) . ').');
    redirect('inward');
}

// Open POs that still have pending qty (always include current PO when editing)
$openPos = rows(
    "SELECT pu.id, pu.po_no, pu.purchase_date, pu.invoice_no, pa.name party,
            (SELECT COALESCE(SUM(pi.qty),0) FROM purchase_items pi JOIN items i ON i.id=pi.item_id WHERE pi.purchase_id=pu.id AND i.item_type<>'SERVICE') ordered,
            (SELECT COALESCE(SUM(ii.qty),0) FROM inward_items ii JOIN inward_entries ie ON ie.id=ii.inward_id WHERE ie.purchase_id=pu.id) received
     FROM purchases pu JOIN parties pa ON pa.id = pu.party_id
     HAVING ordered - received > 0.0005 OR pu.id = ?
     ORDER BY pu.purchase_date DESC, pu.id DESC",
    [$purchaseId]
);

$po = $purchaseId ? row('SELECT pu.*, pa.name party FROM purchases pu JOIN parties pa ON pa.id = pu.party_id WHERE pu.id=?', [$purchaseId]) : null;
$lines = $po ? purchase_inward_lines($purchaseId, $id) : [];
$savedQty = [];
if ($id) {
    foreach (rows('SELECT purchase_item_id, qty FROM inward_items WHERE inward_id=?', [$id]) as $r) {
        $savedQty[(int)$r['purchase_item_id']] = (float)$r['qty'];
    }
}

$iv = $inward ?? [
    'inward_no' => next_series('inward_entries', 'inward_no', setting('inward_prefix', 'GRN')),
    'inward_date' => today(),
    'invoice_no' => $po['invoice_no'] ?? '',
    'vehicle_no' => '',
    'remarks' => '',
];

$pageTitle = $inward ? 'Edit Inward Entry' : 'New Inward Entry';
$active = 'inward';
include ROOT_PATH . '/includes/header.php';
?>
<form method="post">
    <?= csrf_field() ?>
    <div class="card mb-3">
        <div class="card-header"><i class="bi bi-box-arrow-in-down"></i> Inward against Purchase Order</div>
        <div class="card-body row g-2">
            <div class="col-md-5">
                <label class="form-label">Purchase Order *</label>
                <?php if ($id): ?>
                    <input type="hidden" name="purchase_id" value="<?= $purchaseId ?>">
                    <input class="form-control" readonly value="<?= e(($po['po_no'] ?? '') . ' · ' . ($po['party'] ?? '')) ?>">
                <?php else: ?>
                    <select name="purchase_id" class="form-select" required onchange="if(this.value) location='<?= url('inward/add') ?>?purchase_id='+this.value">
                        <option value="">-- Select PO (pending inward) --</option>
                        <?php foreach ($openPos as $p):
                            $pend = (float)$p['ordered'] - (float)$p['received'];
                            $st = (float)$p['received'] > 0 ? 'Partial' : 'Pending'; ?>
                            <option value="<?= $p['id'] ?>" <?= $purchaseId == $p['id'] ? 'selected' : '' ?>>
                                <?= e($p['po_no']) ?> · <?= e($p['party']) ?> · <?= dmy($p['purchase_date']) ?> · <?= $st ?> (pending <?= qty($pend) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
            <div class="col-md-2"><label class="form-label">Inward Date *</label><input type="date" name="inward_date" class="form-control" required value="<?= e($iv['inward_date']) ?>"></div>
            <div class="col-md-2"><label class="form-label">Inward / GRN No.</label><input name="inward_no" class="form-control" value="<?= e($iv['inward_no']) ?>"></div>
            <div class="col-md-3"><label class="form-label">Supplier Invoice No.</label><input name="invoice_no" class="form-control" value="<?= e($iv['invoice_no']) ?>"></div>
            <div class="col-md-3"><label class="form-label">Vehicle / LR No.</label><input name="vehicle_no" class="form-control" value="<?= e($iv['vehicle_no']) ?>"></div>
            <div class="col-md-9"><label class="form-label">Remarks</label><input name="remarks" class="form-control" value="<?= e($iv['remarks']) ?>"></div>
        </div>
    </div>

    <?php if ($po):
        $tot = purchase_inward_totals($purchaseId); ?>
        <div class="alert alert-light border d-flex flex-wrap gap-3 align-items-center mb-3">
            <span><b>Supplier:</b> <?= e($po['party']) ?></span>
            <span><b>PO Date:</b> <?= dmy($po['purchase_date']) ?></span>
            <span><?= status_badge($tot['status']) ?></span>
            <span class="ms-auto small">Ordered <b><?= qty($tot['ordered']) ?></b> · Received <b><?= qty($tot['received']) ?></b> · Pending <b class="text-danger"><?= qty($tot['pending']) ?></b></span>
            <a href="<?= url('purchase/print/' . $purchaseId) ?>" class="btn btn-sm btn-light" target="_blank"><i class="bi bi-printer"></i> PO</a>
        </div>

        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-list-check"></i> Receive Items
                <span class="small text-muted ms-2">Partial inward allowed — enter only the qty received this time</span>
                <div class="actions">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="fillPending">Fill all pending</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clearRecv">Clear</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm item-table">
                    <thead><tr><th>#</th><th>Item</th><th class="num">PO Qty</th><th class="num">Already In</th><th class="num">Pending</th><th>Unit</th><th class="num">Rate</th><th style="width:140px">Receive Now *</th></tr></thead>
                    <tbody>
                    <?php $n = 0; foreach ($lines as $l):
                        if ($l['item_type'] === 'SERVICE') continue;
                        $n++;
                        $thisQty = $savedQty[(int)$l['id']] ?? ($id ? 0 : $l['pending']);
                        $max = $l['pending']; ?>
                        <tr class="<?= $max <= 0 && !$id ? 'text-muted' : '' ?>">
                            <td><?= $n ?></td>
                            <td class="fw-semibold"><?= e($l['item_name']) ?>
                                <small class="text-muted d-block"><?= e($l['item_code']) ?><?= $l['description'] ? ' · ' . e($l['description']) : '' ?></small>
                                <input type="hidden" name="purchase_item_id[]" value="<?= (int)$l['id'] ?>">
                            </td>
                            <td class="num"><?= qty($l['qty']) ?></td>
                            <td class="num"><?= qty($l['received']) ?></td>
                            <td class="num fw-semibold <?= $max > 0 ? 'text-danger' : 'text-success' ?>"><?= qty($max) ?></td>
                            <td><?= e($l['unit']) ?></td>
                            <td class="num"><?= inr($l['rate']) ?></td>
                            <td>
                                <input type="number" step="0.001" min="0" max="<?= e($max) ?>" name="recv_qty[]"
                                       class="form-control form-control-sm text-end recv-qty" data-pending="<?= e($max) ?>"
                                       value="<?= $thisQty > 0 ? e(qty($thisQty)) : '' ?>" <?= $max <= 0 ? 'readonly' : '' ?>>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$n): ?><tr><td colspan="8" class="text-center text-muted py-4">No stock items on this PO.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Select a Purchase Order above to load pending items for partial / full inward.</div>
    <?php endif; ?>

    <div class="d-flex gap-2">
        <a href="<?= url('inward') ?>" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a>
        <?php if ($po): ?><button class="btn btn-primary ms-auto"><i class="bi bi-check2"></i> <?= $inward ? 'Update' : 'Save' ?> Inward</button><?php endif; ?>
    </div>
</form>
<?php
$pageScripts = <<<'HTML'
<script>
document.getElementById('fillPending')?.addEventListener('click', () => {
    document.querySelectorAll('.recv-qty').forEach(i => { if (!i.readOnly) i.value = i.dataset.pending; });
});
document.getElementById('clearRecv')?.addEventListener('click', () => {
    document.querySelectorAll('.recv-qty').forEach(i => { if (!i.readOnly) i.value = ''; });
});
</script>
HTML;
include ROOT_PATH . '/includes/footer.php';
