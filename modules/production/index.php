<?php
require_once __DIR__ . '/../../includes/init.php';

$statuses = ['Planned', 'In Process', 'Completed', 'Cancelled'];

// ---------- Save batch ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'save') {
    $id = (int)post('id');
    $data = [
        'batch_no' => strtoupper(post('batch_no')),
        'item_id' => (int)post('item_id'),
        'plant' => post('plant'),
        'batch_size' => num(post('batch_size')),
        'start_date' => date_or_null(post('start_date')),
        'end_date' => date_or_null(post('end_date')),
        'mfg_date' => date_or_null(post('mfg_date')),
        'exp_date' => date_or_null(post('exp_date')),
        'status' => in_array(post('status'), $statuses) ? post('status') : 'Planned',
        'remarks' => post('remarks'),
    ];
    if ($data['batch_no'] === '' || !$data['item_id']) {
        flash('danger', 'Batch No and Product are required.');
        redirect('production');
    }
    if (val('SELECT COUNT(*) FROM production_batches WHERE batch_no=? AND id<>?', [$data['batch_no'], $id]) > 0) {
        flash('danger', 'Batch No ' . $data['batch_no'] . ' already exists.');
        redirect($id ? "production/edit/$id" : 'production');
    }
    if ($id) {
        update('production_batches', $data, $id);
        flash('success', 'Batch updated.');
    } else {
        $data['created_by'] = current_user()['id'];
        $id = insert('production_batches', $data);
        flash('success', 'Batch ' . $data['batch_no'] . ' created. Now add RM use, batch data and output.');
    }
    redirect("production/view/$id");
}

// ---------- Delete batch ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'delete') {
    require_admin();
    $id = (int)post('id');
    if (val('SELECT COUNT(*) FROM dispatch_items WHERE batch_id=?', [$id]) > 0) {
        flash('warning', 'This batch is already dispatched, so it cannot be deleted. Mark it Cancelled instead.');
    } else {
        db_begin();
        foreach (rows('SELECT id FROM batch_rm WHERE batch_id=?', [$id]) as $r) ledger_clear('BATCH_RM', (int)$r['id']);
        foreach (rows('SELECT id FROM batch_outputs WHERE batch_id=?', [$id]) as $r) ledger_clear('BATCH_OUTPUT', (int)$r['id']);
        q('DELETE FROM batch_rm WHERE batch_id=?', [$id]);
        q('DELETE FROM batch_outputs WHERE batch_id=?', [$id]);
        q('DELETE FROM batch_data WHERE batch_id=?', [$id]);
        q('DELETE FROM packing_labels WHERE batch_id=?', [$id]);
        q('DELETE FROM production_batches WHERE id=?', [$id]);
        db_commit();
        flash('success', 'Batch deleted.');
    }
    redirect('production');
}

$edit = get('edit') ? row('SELECT * FROM production_batches WHERE id=?', [(int)get('edit')]) : null;
$fgItems = items_for(['FG']);

$status = get('status');
$itemId = (int)get('item_id');
$sql = "SELECT b.*, i.name item_name, i.unit,
          (SELECT COALESCE(SUM(qty),0) FROM batch_rm WHERE batch_id = b.id) rm_qty,
          (SELECT COALESCE(SUM(qty*rate),0) FROM batch_rm WHERE batch_id = b.id) rm_cost,
          (SELECT COALESCE(SUM(qty),0) FROM batch_outputs WHERE batch_id = b.id) output_qty,
          (SELECT COALESCE(SUM(qty_in-qty_out),0) FROM stock_ledger WHERE batch_id = b.id) balance
        FROM production_batches b JOIN items i ON i.id = b.item_id WHERE 1=1";
$params = [];
if ($status !== '') {
    $sql .= ' AND b.status = ?';
    $params[] = $status;
}
if ($itemId) {
    $sql .= ' AND b.item_id = ?';
    $params[] = $itemId;
}
$list = rows($sql . ' ORDER BY b.id DESC', $params);

// Suggested batch numbers for each product (used by JS when product changes)
$suggest = [];
foreach ($fgItems as $it) $suggest[$it['id']] = ['no' => next_batch_no($it), 'shelf' => (int)$it['shelf_life_months']];

$b = $edit ?? ['id' => '', 'batch_no' => '', 'item_id' => '', 'plant' => PLANTS[0], 'batch_size' => 1000, 'start_date' => today(),
    'end_date' => '', 'mfg_date' => '', 'exp_date' => '', 'status' => 'In Process', 'remarks' => ''];

$pageTitle = 'Production Batches';
$active = 'production';
include ROOT_PATH . '/includes/header.php';
?>
<div class="row g-3">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-gear-wide-connected"></i> <?= $edit ? 'Edit Batch' : 'Create New Batch' ?></div>
            <div class="card-body">
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="id" value="<?= e($b['id']) ?>">
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label">Product *</label>
                            <select name="item_id" id="bItem" class="form-select" required>
                                <option value="">-- Select Product --</option>
                                <?php foreach ($fgItems as $it): ?><option value="<?= $it['id'] ?>" <?= $b['item_id'] == $it['id'] ? 'selected' : '' ?>><?= e($it['name']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-7"><label class="form-label">Batch No *</label><input name="batch_no" id="bNo" class="form-control fw-bold" required value="<?= e($b['batch_no']) ?>"></div>
                        <div class="col-5"><label class="form-label">Batch Size (kg)</label><input type="number" step="0.001" name="batch_size" class="form-control" value="<?= e($b['batch_size']) ?>"></div>
                        <div class="col-12">
                            <label class="form-label">Plant / Location</label>
                            <input name="plant" class="form-control" list="plantList" value="<?= e($b['plant']) ?>">
                            <datalist id="plantList"><?php foreach (PLANTS as $pl): ?><option><?= e($pl) ?></option><?php endforeach; ?></datalist>
                        </div>
                        <div class="col-6"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control" value="<?= e($b['start_date']) ?>"></div>
                        <div class="col-6"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control" value="<?= e($b['end_date']) ?>"></div>
                        <div class="col-6"><label class="form-label">Mfg. Date</label><input type="date" name="mfg_date" id="bMfg" class="form-control" value="<?= e($b['mfg_date']) ?>"></div>
                        <div class="col-6"><label class="form-label">Exp. Date</label><input type="date" name="exp_date" id="bExp" class="form-control" value="<?= e($b['exp_date']) ?>"></div>
                        <div class="col-12">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select"><?php foreach ($statuses as $s): ?><option <?= $b['status'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select>
                        </div>
                        <div class="col-12"><label class="form-label">Remarks</label><textarea name="remarks" class="form-control" rows="2"><?= e($b['remarks']) ?></textarea></div>
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button class="btn btn-primary"><i class="bi bi-check2"></i> Save Batch</button>
                        <?php if ($edit): ?><a href="<?= url('production') ?>" class="btn btn-light">Cancel</a><?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <form class="filter-bar row g-2 align-items-end">
            <div class="col-md-4"><label class="form-label">Product</label>
                <select name="item_id" class="form-select form-select-sm"><option value="">All</option>
                    <?php foreach ($fgItems as $it): ?><option value="<?= $it['id'] ?>" <?= $itemId == $it['id'] ? 'selected' : '' ?>><?= e($it['name']) ?></option><?php endforeach; ?>
                </select></div>
            <div class="col-md-4"><label class="form-label">Status</label>
                <select name="status" class="form-select form-select-sm"><option value="">All</option>
                    <?php foreach ($statuses as $s): ?><option <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
                </select></div>
            <div class="col-md-4"><button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Filter</button></div>
        </form>
        <div class="card">
            <div class="card-header"><i class="bi bi-list-ul"></i> Batch List (<?= count($list) ?>)
                <div class="actions">
                    <input class="form-control form-control-sm table-search" placeholder="Search..." data-table-search="#tbl">
                    <button class="btn btn-sm btn-accent" data-export="#tbl" data-filename="production-batches"><i class="bi bi-file-earmark-excel"></i></button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover" id="tbl">
                    <thead><tr><th>Batch No</th><th>Product</th><th>Start</th><th>Mfg / Exp</th><th class="num">Size</th><th class="num">RM Used</th><th class="num">Output</th><th class="num">Yield %</th><th class="num">In Stock</th><th>Status</th><th class="no-export"></th></tr></thead>
                    <tbody>
                    <?php foreach ($list as $r): $yield = $r['batch_size'] > 0 ? $r['output_qty'] / $r['batch_size'] * 100 : 0; ?>
                        <tr>
                            <td><a href="<?= url('production/view/' . $r['id']) ?>" class="fw-bold"><?= e($r['batch_no']) ?></a></td>
                            <td><?= e($r['item_name']) ?></td>
                            <td><?= dmy($r['start_date']) ?></td>
                            <td class="small"><?= dmy($r['mfg_date']) ?><br><?= dmy($r['exp_date']) ?></td>
                            <td class="num"><?= qty($r['batch_size']) ?></td>
                            <td class="num"><?= qty($r['rm_qty']) ?></td>
                            <td class="num fw-semibold"><?= qty($r['output_qty']) ?></td>
                            <td class="num"><?= $r['output_qty'] > 0 ? number_format($yield, 1) . '%' : '-' ?></td>
                            <td class="num"><?= qty($r['balance']) ?></td>
                            <td><?= status_badge($r['status']) ?></td>
                            <td class="text-end no-export text-nowrap">
                                <a href="<?= url('production/view/' . $r['id']) ?>" class="btn btn-sm btn-light" title="Open"><i class="bi bi-eye"></i></a>
                                <a href="<?= url('production/edit/' . $r['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <?php if (is_admin()): ?>
                                    <form method="post" class="d-inline" data-confirm="Delete this batch with all its RM use, data and output entries?">
                                        <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$list): ?><tr><td colspan="11" class="text-center text-muted py-4">No batches yet. Create the first batch.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
$suggestJson = json_encode($suggest);
$isEdit = $edit ? 'true' : 'false';
$pageScripts = <<<HTML
<script>
const suggest = $suggestJson;
const isEdit = $isEdit;
const itemSel = document.getElementById('bItem'), noInp = document.getElementById('bNo'), mfg = document.getElementById('bMfg'), exp = document.getElementById('bExp');
itemSel.addEventListener('change', () => { const s = suggest[itemSel.value]; if (s && !isEdit) noInp.value = s.no; });
mfg.addEventListener('change', () => {
    const s = suggest[itemSel.value];
    if (!s || !s.shelf || !mfg.value) return;
    const d = new Date(mfg.value); d.setMonth(d.getMonth() + s.shelf); d.setDate(d.getDate() - 1);
    exp.value = d.toISOString().slice(0, 10);
});
</script>
HTML;
include ROOT_PATH . '/includes/footer.php';
