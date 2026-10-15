<?php
require_once __DIR__ . '/../../includes/init.php';

$stages = ['Raw Material Charging', 'Reaction', 'Neutralisation', 'Distillation / Solvent Recovery', 'Filtration', 'Centrifuge', 'Washing',
    'Drying', 'Milling / Sifting', 'Blending', 'QC Sampling', 'Packing', 'Other'];

// ---------- Save ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'save') {
    $batchId = (int)post('batch_id');
    if (!$batchId) {
        flash('danger', 'Please select a batch.');
        redirect('batch_data');
    }
    insert('batch_data', [
        'batch_id' => $batchId,
        'entry_date' => date_or_null(post('entry_date')) ?? today(),
        'stage' => post('stage'),
        'activity' => post('activity'),
        'start_time' => post('start_time') ?: null,
        'end_time' => post('end_time') ?: null,
        'parameter' => post('parameter'),
        'observed_value' => post('observed_value'),
        'done_by' => post('done_by'),
        'remarks' => post('remarks'),
    ]);
    flash('success', 'Batch data saved.');
    redirect('batch_data?batch_id=' . $batchId);
}

// ---------- Delete ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'delete') {
    $id = (int)post('id');
    $batchId = (int)val('SELECT batch_id FROM batch_data WHERE id=?', [$id]);
    q('DELETE FROM batch_data WHERE id=?', [$id]);
    flash('success', 'Entry deleted.');
    redirect('batch_data?batch_id=' . $batchId);
}

$batchId = (int)get('batch_id');
$sql = 'SELECT d.*, b.batch_no, i.name product FROM batch_data d JOIN production_batches b ON b.id = d.batch_id JOIN items i ON i.id = b.item_id';
$params = [];
if ($batchId) {
    $sql .= ' WHERE d.batch_id = ?';
    $params[] = $batchId;
}
$list = rows($sql . ' ORDER BY d.batch_id DESC, d.entry_date, d.start_time, d.id LIMIT 500', $params);

$pageTitle = 'Batch Wise Data Entry';
$active = 'batch_data';
include ROOT_PATH . '/includes/header.php';
?>
<div class="row g-3">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-clipboard-data"></i> New Batch Data Entry</div>
            <div class="card-body">
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="save">
                    <div class="row g-2">
                        <div class="col-12"><label class="form-label">Batch *</label><select name="batch_id" class="form-select" required><?= batch_options($batchId) ?></select></div>
                        <div class="col-6"><label class="form-label">Date *</label><input type="date" name="entry_date" class="form-control" value="<?= today() ?>" required></div>
                        <div class="col-6"><label class="form-label">Stage</label>
                            <select name="stage" class="form-select"><?php foreach ($stages as $s): ?><option><?= e($s) ?></option><?php endforeach; ?></select></div>
                        <div class="col-12"><label class="form-label">Activity / Operation</label><input name="activity" class="form-control" placeholder="e.g. Charged Pyridine in reactor R-101"></div>
                        <div class="col-6"><label class="form-label">Start Time</label><input type="time" name="start_time" class="form-control"></div>
                        <div class="col-6"><label class="form-label">End Time</label><input type="time" name="end_time" class="form-control"></div>
                        <div class="col-6"><label class="form-label">Parameter</label><input name="parameter" class="form-control" list="paramList" placeholder="Temperature">
                            <datalist id="paramList"><option>Temperature (°C)</option><option>pH</option><option>Pressure (kg/cm²)</option><option>Vacuum (mmHg)</option><option>LOD (%)</option><option>Weight (kg)</option><option>RPM</option><option>Assay (%)</option></datalist></div>
                        <div class="col-6"><label class="form-label">Observed Value</label><input name="observed_value" class="form-control"></div>
                        <div class="col-12"><label class="form-label">Done By</label><input name="done_by" class="form-control" value="<?= e(current_user()['name']) ?>"></div>
                        <div class="col-12"><label class="form-label">Remarks</label><textarea name="remarks" class="form-control" rows="2"></textarea></div>
                    </div>
                    <button class="btn btn-primary mt-3"><i class="bi bi-check2"></i> Save Entry</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <form class="filter-bar row g-2 align-items-end">
            <div class="col-md-8"><label class="form-label">Batch</label><select name="batch_id" class="form-select form-select-sm"><?= str_replace('-- Select Batch --', 'All Batches', batch_options($batchId)) ?></select></div>
            <div class="col-md-4"><button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Show</button>
                <?php if ($batchId): ?><a href="<?= url('production/view/' . $batchId) ?>" class="btn btn-sm btn-light">Open Batch</a><?php endif; ?></div>
        </form>
        <div class="card">
            <div class="card-header"><i class="bi bi-list-ul"></i> Batch Process Record
                <div class="actions"><button class="btn btn-sm btn-accent" data-export="#tbl" data-filename="batch-data"><i class="bi bi-file-earmark-excel"></i></button></div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover" id="tbl">
                    <thead><tr><th>Date</th><th>Stage</th><th>Activity</th><th>Time</th><th>Parameter</th><th>Value</th><th>Done By</th><th>Remarks</th><th class="no-export"></th></tr></thead>
                    <tbody>
                    <?php $lastBatch = null; foreach ($list as $d): ?>
                        <?php if ($lastBatch !== $d['batch_no']): $lastBatch = $d['batch_no']; ?>
                            <tr class="table-light"><td colspan="9"><b><?= e($d['batch_no']) ?></b> &middot; <?= e($d['product']) ?></td></tr>
                        <?php endif; ?>
                        <tr>
                            <td><?= dmy($d['entry_date']) ?></td>
                            <td><span class="badge badge-type"><?= e($d['stage']) ?></span></td>
                            <td><?= e($d['activity']) ?></td>
                            <td class="small text-nowrap"><?= $d['start_time'] ? substr($d['start_time'], 0, 5) : '' ?><?= $d['end_time'] ? ' - ' . substr($d['end_time'], 0, 5) : '' ?></td>
                            <td><?= e($d['parameter']) ?></td>
                            <td class="fw-semibold"><?= e($d['observed_value']) ?></td>
                            <td><?= e($d['done_by']) ?></td>
                            <td class="small"><?= e($d['remarks']) ?></td>
                            <td class="no-export text-end">
                                <form method="post" class="d-inline" data-confirm="Delete this entry?">
                                    <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $d['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$list): ?><tr><td colspan="9" class="text-center text-muted py-4">No batch data entered yet.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
