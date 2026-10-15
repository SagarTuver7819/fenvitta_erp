<?php
require_once __DIR__ . '/../../includes/init.php';

// Entry type => stock direction (IN adds stock, OUT reduces stock)
$entryTypes = [
    'Utility Consumption' => 'OUT',
    'Lab / QC Use' => 'OUT',
    'Wastage / Loss' => 'OUT',
    'Return to Vendor' => 'OUT',
    'Adjustment (-)' => 'OUT',
    'Solvent Recovery' => 'IN',
    'Return from Plant' => 'IN',
    'Adjustment (+)' => 'IN',
];

// ---------- Save ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'save') {
    $itemId = (int)post('item_id');
    $type = post('entry_type');
    $qtyVal = num(post('qty'));
    $date = date_or_null(post('entry_date')) ?? today();

    if (!$itemId || $qtyVal <= 0 || !isset($entryTypes[$type])) {
        flash('danger', 'Please select material, type and enter quantity.');
        redirect('rm_utilities');
    }
    if ($entryTypes[$type] === 'OUT' && $qtyVal > stock_qty($itemId) + 0.0001) {
        flash('danger', 'Quantity is more than available stock (' . qty(stock_qty($itemId)) . ').');
        redirect('rm_utilities');
    }

    $rate = num(post('rate')) ?: item_avg_rate($itemId);
    $id = insert('rm_utilities', [
        'entry_date' => $date, 'item_id' => $itemId, 'entry_type' => $type, 'qty' => $qtyVal, 'rate' => $rate,
        'area' => post('area'), 'ref_no' => post('ref_no'), 'remarks' => post('remarks'), 'created_by' => current_user()['id'],
    ]);
    $isIn = $entryTypes[$type] === 'IN';
    ledger_add($date, $itemId, $isIn ? $qtyVal : 0, $isIn ? 0 : $qtyVal, 'RM_UTILITY', $id, $rate, null, $type . ' ' . post('area'));

    flash('success', 'RM utility entry saved.');
    redirect('rm_utilities');
}

// ---------- Delete ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'delete') {
    require_admin();
    $id = (int)post('id');
    ledger_clear('RM_UTILITY', $id);
    q('DELETE FROM rm_utilities WHERE id=?', [$id]);
    flash('success', 'Entry deleted.');
    redirect('rm_utilities');
}

$from = get('from', date('Y-m-01', strtotime('-2 month')));
$to = get('to', today());
$list = rows('SELECT u.*, i.name item_name, i.unit FROM rm_utilities u JOIN items i ON i.id = u.item_id
              WHERE u.entry_date BETWEEN ? AND ? ORDER BY u.entry_date DESC, u.id DESC', [$from, $to]);
$items = items_for(['RM', 'PM', 'CONSUMABLE']);

$pageTitle = 'Entry of RM Utilities';
$active = 'rm_utilities';
include ROOT_PATH . '/includes/header.php';
?>
<div class="row g-3">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-droplet-half"></i> New RM Utility Entry</div>
            <div class="card-body">
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="save">
                    <div class="row g-2">
                        <div class="col-6"><label class="form-label">Date *</label><input type="date" name="entry_date" class="form-control" value="<?= today() ?>" required></div>
                        <div class="col-6">
                            <label class="form-label">Entry Type *</label>
                            <select name="entry_type" class="form-select" required>
                                <optgroup label="Stock OUT">
                                    <?php foreach ($entryTypes as $t => $dir) if ($dir === 'OUT') echo '<option>' . e($t) . '</option>'; ?>
                                </optgroup>
                                <optgroup label="Stock IN">
                                    <?php foreach ($entryTypes as $t => $dir) if ($dir === 'IN') echo '<option>' . e($t) . '</option>'; ?>
                                </optgroup>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Material *</label>
                            <select name="item_id" class="form-select" required data-stock-hint="#stockHint" data-rate-target="#rate"><?= item_options($items, null, true) ?></select>
                            <small class="text-primary fw-semibold" id="stockHint"></small>
                        </div>
                        <div class="col-6"><label class="form-label">Quantity *</label><input type="number" step="0.001" name="qty" class="form-control" required></div>
                        <div class="col-6"><label class="form-label">Rate (₹)</label><input type="number" step="0.01" name="rate" id="rate" class="form-control"></div>
                        <div class="col-6"><label class="form-label">Area / Used For</label><input name="area" class="form-control" list="areaList" placeholder="Boiler, Lab, Cleaning...">
                            <datalist id="areaList"><option>Boiler</option><option>Lab / QC</option><option>Cleaning</option><option>Solvent Recovery Plant</option><option>Effluent Treatment</option><option>Altitude Plant</option></datalist></div>
                        <div class="col-6"><label class="form-label">Ref / Batch No.</label><input name="ref_no" class="form-control"></div>
                        <div class="col-12"><label class="form-label">Remarks</label><input name="remarks" class="form-control"></div>
                    </div>
                    <button class="btn btn-primary mt-3"><i class="bi bi-check2"></i> Save Entry</button>
                </form>
                <div class="alert alert-light small mt-3 mb-0">
                    <b>Note:</b> RM used inside a production batch should be entered in <a href="<?= url('batch_rm') ?>">Batch Wise RM Use</a>.
                    Use this page for utilities, lab use, wastage, solvent recovery (e.g. Pyridine / Chloroform recovered) and stock adjustment.
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <form class="filter-bar row g-2 align-items-end">
            <div class="col-md-4"><label class="form-label">From</label><input type="date" name="from" class="form-control form-control-sm" value="<?= e($from) ?>"></div>
            <div class="col-md-4"><label class="form-label">To</label><input type="date" name="to" class="form-control form-control-sm" value="<?= e($to) ?>"></div>
            <div class="col-md-4"><button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Filter</button></div>
        </form>
        <div class="card">
            <div class="card-header"><i class="bi bi-list-ul"></i> RM Utility Register (<?= count($list) ?>)
                <div class="actions">
                    <input class="form-control form-control-sm table-search" placeholder="Search..." data-table-search="#tbl">
                    <button class="btn btn-sm btn-accent" data-export="#tbl" data-filename="rm-utilities"><i class="bi bi-file-earmark-excel"></i></button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover" id="tbl">
                    <thead><tr><th>Date</th><th>Material</th><th>Type</th><th class="num">In</th><th class="num">Out</th><th class="num">Rate</th><th>Area</th><th>Ref</th><th>Remarks</th><th class="no-export"></th></tr></thead>
                    <tbody>
                    <?php foreach ($list as $u): $in = ($entryTypes[$u['entry_type']] ?? 'OUT') === 'IN'; ?>
                        <tr>
                            <td><?= dmy($u['entry_date']) ?></td>
                            <td class="fw-semibold"><?= e($u['item_name']) ?></td>
                            <td><span class="badge <?= $in ? 'text-bg-success' : 'text-bg-warning' ?>"><?= e($u['entry_type']) ?></span></td>
                            <td class="num text-success"><?= $in ? qty($u['qty']) . ' ' . e($u['unit']) : '' ?></td>
                            <td class="num text-danger"><?= $in ? '' : qty($u['qty']) . ' ' . e($u['unit']) ?></td>
                            <td class="num"><?= inr($u['rate']) ?></td>
                            <td><?= e($u['area']) ?></td>
                            <td><?= e($u['ref_no']) ?></td>
                            <td class="small"><?= e($u['remarks']) ?></td>
                            <td class="no-export text-end">
                                <?php if (is_admin()): ?>
                                    <form method="post" class="d-inline" data-confirm="Delete this entry? Stock will be reversed.">
                                        <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$list): ?><tr><td colspan="10" class="text-center text-muted py-4">No entries in this period.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
