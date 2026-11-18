<?php
require_once __DIR__ . '/../../includes/init.php';

// ---------- Save (RM batch wise) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'save') {
    $batchId = (int)post('batch_id');
    $date = date_or_null(post('issue_date')) ?? today();
    $batch = row('SELECT * FROM production_batches WHERE id=?', [$batchId]);
    if (!$batch) {
        flash('danger', 'Please select a production batch.');
        redirect('batch_rm');
    }
    if (in_array($batch['status'], ['Production Done', 'Completed', 'Cancelled'], true)) {
        flash('danger', 'This production batch is ' . $batch['status'] . '. RM cannot be issued.');
        redirect('batch_rm?batch_id=' . $batchId);
    }

    $lines = [];
    foreach ($_POST['rm_batch_id'] ?? [] as $k => $rmBatchId) {
        $rmBatchId = (int)$rmBatchId;
        $qtyVal = num($_POST['qty'][$k] ?? 0);
        if (!$rmBatchId || $qtyVal <= 0) continue;
        $rb = row('SELECT rb.*, i.name item_name, i.unit FROM rm_batches rb JOIN items i ON i.id = rb.item_id WHERE rb.id=?', [$rmBatchId]);
        if (!$rb) {
            flash('danger', 'Invalid RM batch selected.');
            redirect('batch_rm?batch_id=' . $batchId);
        }
        $avail = rm_batch_available($rmBatchId);
        if ($qtyVal > $avail + 0.0001) {
            flash('danger', 'Not enough qty in RM batch ' . $rb['batch_no'] . ' (' . $rb['item_name'] . '). Available: ' . qty($avail) . ' ' . $rb['unit']);
            redirect('batch_rm?batch_id=' . $batchId);
        }
        $lines[] = [
            'rm_batch_id' => $rmBatchId,
            'item_id' => (int)$rb['item_id'],
            'qty' => $qtyVal,
            'rate' => (float)$rb['rate'],
            'remarks' => trim($_POST['remarks'][$k] ?? ''),
            'rm_batch_no' => $rb['batch_no'],
            'item_name' => $rb['item_name'],
        ];
    }
    if (!$lines) {
        flash('danger', 'Select at least one RM batch and enter quantity.');
        redirect('batch_rm?batch_id=' . $batchId);
    }

    db_begin();
    foreach ($lines as $l) {
        $id = insert('batch_rm', [
            'batch_id' => $batchId,
            'issue_date' => $date,
            'item_id' => $l['item_id'],
            'rm_batch_id' => $l['rm_batch_id'],
            'qty' => $l['qty'],
            'rate' => $l['rate'],
            'remarks' => $l['remarks'] !== '' ? $l['remarks'] : ('From RM batch ' . $l['rm_batch_no']),
        ]);
        ledger_add($date, $l['item_id'], 0, $l['qty'], 'BATCH_RM', $id, $l['rate'], null,
            'Issued to ' . $batch['batch_no'] . ' from ' . $l['rm_batch_no']);
    }
    refresh_production_status($batchId);
    db_commit();

    flash('success', count($lines) . ' RM batch line(s) issued to production batch ' . $batch['batch_no'] . '.');
    redirect('batch_rm?batch_id=' . $batchId);
}

// ---------- Delete ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'delete') {
    $id = (int)post('id');
    $row = row('SELECT * FROM batch_rm WHERE id=?', [$id]);
    $batchId = (int)($row['batch_id'] ?? 0);
    if ($row) {
        ledger_clear('BATCH_RM', $id);
        q('DELETE FROM batch_rm WHERE id=?', [$id]);
        if ($batchId) refresh_production_status($batchId);
        flash('success', 'Entry deleted. Qty returned to RM batch.');
    }
    redirect('batch_rm?batch_id=' . $batchId);
}

$batchId = (int)get('batch_id');
$sql = 'SELECT r.*, i.name item_name, i.unit, b.batch_no, f.name product, rb.batch_no rm_batch_no
        FROM batch_rm r
        JOIN items i ON i.id = r.item_id
        JOIN production_batches b ON b.id = r.batch_id
        JOIN items f ON f.id = b.item_id
        LEFT JOIN rm_batches rb ON rb.id = r.rm_batch_id';
$params = [];
if ($batchId) {
    $sql .= ' WHERE r.batch_id = ?';
    $params[] = $batchId;
}
$list = rows($sql . ' ORDER BY r.batch_id DESC, r.issue_date, r.id LIMIT 500', $params);

$rmOpen = rm_batches_available();
$prodBatch = $batchId ? row('SELECT b.*, i.name product FROM production_batches b JOIN items i ON i.id=b.item_id WHERE b.id=?', [$batchId]) : null;

$pageTitle = 'Batch Wise RM Use';
$active = 'batch_rm';
include ROOT_PATH . '/includes/header.php';
?>
<div class="row g-3">
    <div class="col-xl-5">
        <div class="card">
            <div class="card-header"><i class="bi bi-diagram-3"></i> Issue RM Batch to Production</div>
            <div class="card-body">
                <?php if ($prodBatch): ?>
                    <div class="alert alert-light border small mb-3">
                        Production Batch: <b><?= e($prodBatch['batch_no']) ?></b> · <?= e($prodBatch['product']) ?>
                        · Size <b><?= qty($prodBatch['batch_size']) ?> kg</b> · <?= status_badge($prodBatch['status']) ?>
                    </div>
                <?php endif; ?>
                <form method="post" data-line-items>
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="save">
                    <div class="row g-2 mb-2">
                        <div class="col-8">
                            <label class="form-label">Production Batch *</label>
                            <select name="batch_id" class="form-select" required onchange="if(this.value) location='<?= url('batch_rm') ?>?batch_id='+this.value">
                                <?= batch_options($batchId, true) ?>
                            </select>
                        </div>
                        <div class="col-4"><label class="form-label">Issue Date *</label><input type="date" name="issue_date" class="form-control" value="<?= today() ?>" required></div>
                    </div>
                    <table class="table item-table">
                        <thead><tr><th>RM Batch (Item · Available)</th><th style="width:110px">Use Qty</th><th>Remarks</th><th></th></tr></thead>
                        <tbody>
                        <?php for ($n = 0; $n < 3; $n++): ?>
                            <tr>
                                <td>
                                    <select name="rm_batch_id[]" class="form-select form-select-sm">
                                        <option value="">-- Select RM Batch --</option>
                                        <?php foreach ($rmOpen as $rb): ?>
                                            <option value="<?= (int)$rb['id'] ?>" data-avail="<?= e($rb['available']) ?>">
                                                <?= e($rb['batch_no']) ?> · <?= e($rb['item_name']) ?> · Avail <?= qty($rb['available']) ?> <?= e($rb['unit']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><input type="number" step="0.001" name="qty[]" class="form-control form-control-sm f-qty"></td>
                                <td><input name="remarks[]" class="form-control form-control-sm"></td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger f-remove"><i class="bi bi-x"></i></button></td>
                            </tr>
                        <?php endfor; ?>
                        </tbody>
                    </table>
                    <template class="row-tpl">
                        <tr>
                            <td>
                                <select name="rm_batch_id[]" class="form-select form-select-sm">
                                    <option value="">-- Select RM Batch --</option>
                                    <?php foreach ($rmOpen as $rb): ?>
                                        <option value="<?= (int)$rb['id'] ?>"><?= e($rb['batch_no']) ?> · <?= e($rb['item_name']) ?> · Avail <?= qty($rb['available']) ?> <?= e($rb['unit']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="number" step="0.001" name="qty[]" class="form-control form-control-sm f-qty"></td>
                            <td><input name="remarks[]" class="form-control form-control-sm"></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger f-remove"><i class="bi bi-x"></i></button></td>
                        </tr>
                    </template>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-accent add-row"><i class="bi bi-plus-lg"></i> Add Row</button>
                        <button class="btn btn-primary ms-auto" <?= !$rmOpen ? 'disabled' : '' ?>><i class="bi bi-check2"></i> Issue to Batch</button>
                    </div>
                    <small class="text-muted d-block mt-2">RM batches are created automatically when you do <a href="<?= url('inward/add') ?>">Inward against PO</a>. Production batch size is fixed <b>1000 kg</b>.</small>
                    <?php if (!$rmOpen): ?><div class="alert alert-warning mt-2 mb-0">No open RM batches with available qty. Do Inward first.</div><?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-7">
        <form class="filter-bar row g-2 align-items-end">
            <div class="col-md-8"><label class="form-label">Production Batch</label><select name="batch_id" class="form-select form-select-sm"><?= str_replace('-- Select Batch --', 'All Batches', batch_options($batchId)) ?></select></div>
            <div class="col-md-4"><button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Show</button>
                <?php if ($batchId): ?><a href="<?= url('production/view/' . $batchId) ?>" class="btn btn-sm btn-light">Open Batch</a><?php endif; ?></div>
        </form>
        <div class="card">
            <div class="card-header"><i class="bi bi-list-ul"></i> RM Used (which RM batch · how much)
                <div class="actions"><button class="btn btn-sm btn-accent" data-export="#tbl" data-filename="batch-rm-use"><i class="bi bi-file-earmark-excel"></i></button></div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover" id="tbl">
                    <thead><tr><th>Prod. Batch</th><th>Date</th><th>Material</th><th>RM Batch</th><th class="num">Qty</th><th class="num">Rate</th><th class="num">Cost ₹</th><th class="no-export"></th></tr></thead>
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
                            <td><span class="badge badge-type"><?= e($r['rm_batch_no'] ?: '-') ?></span></td>
                            <td class="num"><?= qty($r['qty'], 3) ?> <?= e($r['unit']) ?></td>
                            <td class="num"><?= inr($r['rate']) ?></td>
                            <td class="num"><?= inr($r['qty'] * $r['rate']) ?></td>
                            <td class="no-export text-end">
                                <form method="post" class="d-inline" data-confirm="Delete this entry? Qty will return to RM batch.">
                                    <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$list): ?><tr><td colspan="8" class="text-center text-muted py-4">No RM issued yet.</td></tr><?php endif; ?>
                    </tbody>
                    <?php if ($batchId && $list): ?><tfoot><tr><td colspan="4">Total</td><td class="num"><?= qty($tq, 3) ?></td><td></td><td class="num"><?= inr($tc) ?></td><td></td></tr></tfoot><?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
