<?php
require_once __DIR__ . '/../../includes/init.php';

// ---------- Save ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'save') {
    $batchId = (int)post('batch_id');
    $qtyVal = num(post('qty'));
    if (!$batchId || $qtyVal <= 0) {
        flash('danger', 'Select batch and enter output quantity (O/P kg).');
        redirect('batch_output');
    }
    $batch = row('SELECT * FROM production_batches WHERE id=?', [$batchId]);
    if ($batch && in_array($batch['status'], ['Production Done', 'Completed', 'Cancelled'], true)) {
        flash('danger', 'This production batch is already ' . $batch['status'] . '. Use the next O/P batch.');
        redirect('batch_output');
    }

    $date = date_or_null(post('output_date')) ?? today();
    $qc = in_array(post('qc_status'), ['Pending', 'Approved', 'Rejected']) ? post('qc_status') : 'Approved';
    $id = insert('batch_outputs', [
        'batch_id' => $batchId,
        'output_date' => $date,
        'qty' => $qtyVal,
        'grade' => post('grade'),
        'containers' => (int)post('containers'),
        'pack_type' => post('pack_type'),
        'qc_status' => $qc,
        'remarks' => post('remarks'),
    ]);
    sync_output_stock($id);
    $newBatchId = refresh_production_status($batchId);

    $msg = 'O/P ' . qty($qtyVal) . ' kg saved' . ($qc === 'Approved' ? ' into FG stock.' : ' (pending QC).');
    if ($newBatchId) {
        $nb = row('SELECT batch_no FROM production_batches WHERE id=?', [$newBatchId]);
        $msg .= ' Batch reached 1000 kg → Production Done. New O/P batch created: ' . ($nb['batch_no'] ?? '');
        flash('success', $msg);
        redirect('production/view/' . $newBatchId);
    }
    flash('success', $msg);
    redirect('batch_output?batch_id=' . $batchId);
}

// ---------- QC status change ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'qc') {
    $id = (int)post('id');
    $o = row('SELECT * FROM batch_outputs WHERE id=?', [$id]);
    $newStatus = post('qc_status');
    if ($o && in_array($newStatus, ['Pending', 'Approved', 'Rejected'])) {
        if ($o['qc_status'] === 'Approved' && $newStatus !== 'Approved' && batch_balance((int)$o['batch_id']) < $o['qty'] - 0.0001) {
            flash('danger', 'Part of this output is already dispatched, so QC status cannot be changed.');
        } else {
            update('batch_outputs', ['qc_status' => $newStatus], $id);
            sync_output_stock($id);
            if (!empty($o['batch_id'])) refresh_production_status((int)$o['batch_id']);
            flash('success', 'QC status updated to ' . $newStatus . '.');
        }
    }
    redirect('batch_output?batch_id=' . ($o['batch_id'] ?? ''));
}

// ---------- Delete ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'delete') {
    $id = (int)post('id');
    $o = row('SELECT * FROM batch_outputs WHERE id=?', [$id]);
    if ($o && $o['qc_status'] === 'Approved' && batch_balance((int)$o['batch_id']) < $o['qty'] - 0.0001) {
        flash('danger', 'This output is already dispatched, so it cannot be deleted.');
    } elseif ($o) {
        ledger_clear('BATCH_OUTPUT', $id);
        q('DELETE FROM batch_outputs WHERE id=?', [$id]);
        if (!empty($o['batch_id'])) refresh_production_status((int)$o['batch_id']);
        flash('success', 'Output entry deleted.');
    }
    redirect('batch_output?batch_id=' . ($o['batch_id'] ?? ''));
}

$batchId = (int)get('batch_id');
$sql = 'SELECT o.*, b.batch_no, b.batch_size, i.name product, i.unit FROM batch_outputs o
        JOIN production_batches b ON b.id = o.batch_id JOIN items i ON i.id = b.item_id';
$params = [];
if ($batchId) {
    $sql .= ' WHERE o.batch_id = ?';
    $params[] = $batchId;
}
$list = rows($sql . ' ORDER BY o.output_date DESC, o.id DESC LIMIT 500', $params);
$defaultGrade = $batchId ? val('SELECT i.grade FROM production_batches b JOIN items i ON i.id=b.item_id WHERE b.id=?', [$batchId]) : 'IP/USP';

$pageTitle = 'Output Entry';
$active = 'batch_output';
include ROOT_PATH . '/includes/header.php';
?>
<div class="row g-3">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-box-arrow-in-down"></i> New Output Entry</div>
            <div class="card-body">
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="save">
                    <div class="row g-2">
                        <div class="col-12"><label class="form-label">Production Batch *</label><select name="batch_id" class="form-select" required><?= batch_options($batchId, true) ?></select></div>
                        <div class="col-6"><label class="form-label">Output Date *</label><input type="date" name="output_date" class="form-control" value="<?= today() ?>" required></div>
                        <div class="col-6"><label class="form-label">O/P kg *</label><input type="number" step="0.001" name="qty" class="form-control fw-bold" placeholder="e.g. 150" required></div>
                        <div class="col-6"><label class="form-label">Grade</label><input name="grade" class="form-control" value="<?= e($defaultGrade ?: 'IP/USP') ?>"></div>
                        <div class="col-6"><label class="form-label">QC Status</label>
                            <select name="qc_status" class="form-select"><option>Approved</option><option>Pending</option><option>Rejected</option></select></div>
                        <div class="col-6"><label class="form-label">No. of Containers</label><input type="number" name="containers" class="form-control"></div>
                        <div class="col-6"><label class="form-label">Pack Type</label><input name="pack_type" class="form-control" list="packList" value="HDPE Drum">
                            <datalist id="packList"><option>HDPE Drum</option><option>Fibre Drum</option><option>Bag</option><option>Carboy</option></datalist></div>
                        <div class="col-12"><label class="form-label">Remarks</label><input name="remarks" class="form-control"></div>
                        <div class="col-12"><small class="text-muted">1000 kg pure thay tyare <b>Production Done</b> + <b>navi O/P batch</b> auto create thase. Simple entry mate Production Batch open kari O/P kg → OK pan use kari sako.</small></div>
                    </div>
                    <button class="btn btn-primary mt-3"><i class="bi bi-check2"></i> OK — Save O/P</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <form class="filter-bar row g-2 align-items-end">
            <div class="col-md-8"><label class="form-label">Batch</label><select name="batch_id" class="form-select form-select-sm"><?= str_replace('-- Select Batch --', 'All Batches', batch_options($batchId)) ?></select></div>
            <div class="col-md-4"><button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Show</button>
                <?php if ($batchId): ?><a href="<?= url('packing_label') ?>?batch_id=<?= $batchId ?>" class="btn btn-sm btn-accent"><i class="bi bi-tags"></i> Labels</a><?php endif; ?></div>
        </form>
        <div class="card">
            <div class="card-header"><i class="bi bi-list-ul"></i> Output Register
                <div class="actions"><button class="btn btn-sm btn-accent" data-export="#tbl" data-filename="batch-output"><i class="bi bi-file-earmark-excel"></i></button></div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover" id="tbl">
                    <thead><tr><th>Date</th><th>Batch</th><th>Product</th><th>Grade</th><th class="num">Qty</th><th class="num">Yield</th><th>Containers</th><th>QC</th><th>Remarks</th><th class="no-export"></th></tr></thead>
                    <tbody>
                    <?php $tq = 0; foreach ($list as $o): $tq += $o['qty']; ?>
                        <tr>
                            <td><?= dmy($o['output_date']) ?></td>
                            <td><a href="<?= url('production/view/' . $o['batch_id']) ?>" class="fw-bold"><?= e($o['batch_no']) ?></a></td>
                            <td><?= e($o['product']) ?></td>
                            <td><?= e($o['grade']) ?></td>
                            <td class="num fw-semibold"><?= qty($o['qty'], 3) ?> <?= e($o['unit']) ?></td>
                            <td class="num"><?= $o['batch_size'] > 0 ? number_format($o['qty'] / $o['batch_size'] * 100, 1) . '%' : '-' ?></td>
                            <td><?= (int)$o['containers'] ?> <?= e($o['pack_type']) ?></td>
                            <td>
                                <form method="post" class="d-inline">
                                    <?= csrf_field() ?><input type="hidden" name="action" value="qc"><input type="hidden" name="id" value="<?= $o['id'] ?>">
                                    <select name="qc_status" class="form-select form-select-sm" onchange="this.form.submit()" style="width:110px">
                                        <?php foreach (['Approved', 'Pending', 'Rejected'] as $s): ?><option <?= $o['qc_status'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                            <td class="small"><?= e($o['remarks']) ?></td>
                            <td class="no-export text-end text-nowrap">
                                <a href="<?= url('packing_label') ?>?batch_id=<?= $o['batch_id'] ?>&containers=<?= (int)$o['containers'] ?>" class="btn btn-sm btn-light" title="Packing Labels"><i class="bi bi-tags"></i></a>
                                <form method="post" class="d-inline" data-confirm="Delete this output entry?">
                                    <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $o['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$list): ?><tr><td colspan="10" class="text-center text-muted py-4">No output entries yet.</td></tr><?php endif; ?>
                    </tbody>
                    <tfoot><tr><td colspan="4">Total</td><td class="num"><?= qty($tq, 3) ?></td><td colspan="5"></td></tr></tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
