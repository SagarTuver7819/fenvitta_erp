<?php
require_once __DIR__ . '/../../includes/init.php';

// ---------- Save (multiple materials at once) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'save') {
    $batchId = (int)post('batch_id');
    $date = date_or_null(post('issue_date')) ?? today();
    $batch = row('SELECT * FROM production_batches WHERE id=?', [$batchId]);
    if (!$batch) {
        flash('danger', 'Please select a batch.');
        redirect('batch_rm');
    }

    // Validate stock first
    $lines = [];
    foreach ($_POST['item_id'] ?? [] as $k => $itemId) {
        $itemId = (int)$itemId;
        $qtyVal = num($_POST['qty'][$k] ?? 0);
        if (!$itemId || $qtyVal <= 0) continue;
        $lines[] = ['item_id' => $itemId, 'qty' => $qtyVal, 'remarks' => trim($_POST['remarks'][$k] ?? '')];
        $stock = stock_qty($itemId);
        if ($qtyVal > $stock + 0.0001) {
            $name = val('SELECT name FROM items WHERE id=?', [$itemId]);
            flash('danger', "Not enough stock of $name. Available: " . qty($stock));
            redirect('batch_rm?batch_id=' . $batchId);
        }
    }
    if (!$lines) {
        flash('danger', 'Add at least one material with quantity.');
        redirect('batch_rm?batch_id=' . $batchId);
    }

    db_begin();
    foreach ($lines as $l) {
        $rate = item_avg_rate($l['item_id']);
        $id = insert('batch_rm', [
            'batch_id' => $batchId, 'issue_date' => $date, 'item_id' => $l['item_id'], 'qty' => $l['qty'], 'rate' => $rate, 'remarks' => $l['remarks'],
        ]);
        ledger_add($date, $l['item_id'], 0, $l['qty'], 'BATCH_RM', $id, $rate, null, 'Issued to batch ' . $batch['batch_no']);
    }
    if ($batch['status'] === 'Planned') update('production_batches', ['status' => 'In Process'], $batchId);
    db_commit();

    flash('success', count($lines) . ' material(s) issued to batch ' . $batch['batch_no'] . '.');
    redirect('batch_rm?batch_id=' . $batchId);
}

// ---------- Delete ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'delete') {
    $id = (int)post('id');
    $batchId = (int)val('SELECT batch_id FROM batch_rm WHERE id=?', [$id]);
    ledger_clear('BATCH_RM', $id);
    q('DELETE FROM batch_rm WHERE id=?', [$id]);
    flash('success', 'Entry deleted and stock returned.');
    redirect('batch_rm?batch_id=' . $batchId);
}

$batchId = (int)get('batch_id');
$sql = 'SELECT r.*, i.name item_name, i.unit, b.batch_no, f.name product
        FROM batch_rm r JOIN items i ON i.id = r.item_id JOIN production_batches b ON b.id = r.batch_id JOIN items f ON f.id = b.item_id';
$params = [];
if ($batchId) {
    $sql .= ' WHERE r.batch_id = ?';
    $params[] = $batchId;
}
$list = rows($sql . ' ORDER BY r.batch_id DESC, r.issue_date, r.id LIMIT 500', $params);
$materials = items_for(['RM', 'PM', 'CONSUMABLE']);
$matOptions = item_options($materials, null, true);

$pageTitle = 'Batch Wise RM Use';
$active = 'batch_rm';
include ROOT_PATH . '/includes/header.php';
?>
<div class="row g-3">
    <div class="col-xl-5">
        <div class="card">
            <div class="card-header"><i class="bi bi-diagram-3"></i> Issue Raw Material to Batch</div>
            <div class="card-body">
                <form method="post" data-line-items>
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="save">
                    <div class="row g-2 mb-2">
                        <div class="col-8"><label class="form-label">Batch *</label><select name="batch_id" class="form-select" required><?= batch_options($batchId) ?></select></div>
                        <div class="col-4"><label class="form-label">Issue Date *</label><input type="date" name="issue_date" class="form-control" value="<?= today() ?>" required></div>
                    </div>
                    <table class="table item-table">
                        <thead><tr><th>Material</th><th style="width:110px">Qty</th><th>Remarks</th><th></th></tr></thead>
                        <tbody>
                        <?php for ($n = 0; $n < 3; $n++): ?>
                            <tr>
                                <td><select name="item_id[]" class="form-select form-select-sm f-item"><?= $matOptions ?></select></td>
                                <td><input type="number" step="0.001" name="qty[]" class="form-control form-control-sm f-qty"></td>
                                <td><input name="remarks[]" class="form-control form-control-sm"></td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger f-remove"><i class="bi bi-x"></i></button></td>
                            </tr>
                        <?php endfor; ?>
                        </tbody>
                    </table>
                    <template class="row-tpl">
                        <tr>
                            <td><select name="item_id[]" class="form-select form-select-sm f-item"><?= $matOptions ?></select></td>
                            <td><input type="number" step="0.001" name="qty[]" class="form-control form-control-sm f-qty"></td>
                            <td><input name="remarks[]" class="form-control form-control-sm"></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger f-remove"><i class="bi bi-x"></i></button></td>
                        </tr>
                    </template>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-accent add-row"><i class="bi bi-plus-lg"></i> Add Row</button>
                        <button class="btn btn-primary ms-auto"><i class="bi bi-check2"></i> Issue to Batch</button>
                    </div>
                    <small class="text-muted d-block mt-2">Stock is reduced from RM stock at average purchase rate. Dropdown shows current available stock.</small>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-7">
        <form class="filter-bar row g-2 align-items-end">
            <div class="col-md-8"><label class="form-label">Batch</label><select name="batch_id" class="form-select form-select-sm"><?= str_replace('-- Select Batch --', 'All Batches', batch_options($batchId)) ?></select></div>
            <div class="col-md-4"><button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Show</button>
                <?php if ($batchId): ?><a href="<?= url('production/view/' . $batchId) ?>" class="btn btn-sm btn-light">Open Batch</a><?php endif; ?></div>
        </form>
        <div class="card">
            <div class="card-header"><i class="bi bi-list-ul"></i> RM Used (Batch Wise)
                <div class="actions"><button class="btn btn-sm btn-accent" data-export="#tbl" data-filename="batch-rm-use"><i class="bi bi-file-earmark-excel"></i></button></div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover" id="tbl">
                    <thead><tr><th>Batch</th><th>Date</th><th>Material</th><th class="num">Qty</th><th class="num">Rate</th><th class="num">Cost ₹</th><th>Remarks</th><th class="no-export"></th></tr></thead>
                    <tbody>
                    <?php $tq = 0; $tc = 0; $lastBatch = null;
                    foreach ($list as $r): $tq += $r['qty']; $tc += $r['qty'] * $r['rate']; ?>
                        <?php if ($lastBatch !== $r['batch_no']): $lastBatch = $r['batch_no']; ?>
                            <tr class="table-light"><td colspan="8"><b><?= e($r['batch_no']) ?></b> &middot; <?= e($r['product']) ?></td></tr>
                        <?php endif; ?>
                        <tr>
                            <td></td>
                            <td><?= dmy($r['issue_date']) ?></td>
                            <td class="fw-semibold"><?= e($r['item_name']) ?></td>
                            <td class="num"><?= qty($r['qty'], 3) ?> <?= e($r['unit']) ?></td>
                            <td class="num"><?= inr($r['rate']) ?></td>
                            <td class="num"><?= inr($r['qty'] * $r['rate']) ?></td>
                            <td class="small"><?= e($r['remarks']) ?></td>
                            <td class="no-export text-end">
                                <form method="post" class="d-inline" data-confirm="Delete this entry? Material will be returned to stock.">
                                    <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$list): ?><tr><td colspan="8" class="text-center text-muted py-4">No RM issued yet.</td></tr><?php endif; ?>
                    </tbody>
                    <?php if ($batchId && $list): ?><tfoot><tr><td colspan="3">Total</td><td class="num"><?= qty($tq, 3) ?></td><td></td><td class="num"><?= inr($tc) ?></td><td colspan="2"></td></tr></tfoot><?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
