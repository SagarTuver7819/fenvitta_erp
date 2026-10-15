<?php
require_once __DIR__ . '/../../includes/init.php';

$types = ['Supplier', 'Customer', 'Both', 'Job Worker', 'Transporter'];

$filter = get('type');
$sql = 'SELECT * FROM parties';
$params = [];
if ($filter !== '') {
    $sql .= ' WHERE party_type = ?';
    $params[] = $filter;
}
$list = rows($sql . ' ORDER BY name', $params);

$pageTitle = 'Parties (Supplier / Customer)';
$active = 'parties';
include ROOT_PATH . '/includes/header.php';
?>
<div class="card">
    <div class="card-header"><i class="bi bi-people"></i> Party List (<?= count($list) ?>)
        <div class="actions">
            <form class="d-flex gap-2">
                <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <?php foreach ($types as $t): ?><option <?= $filter === $t ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?>
                </select>
            </form>
            <input class="form-control form-control-sm table-search" placeholder="Search..." data-table-search="#tbl">
            <button class="btn btn-sm btn-accent" data-export="#tbl" data-filename="parties"><i class="bi bi-file-earmark-excel"></i></button>
            <a href="<?= url('parties/add') ?>" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Add Party</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover" id="tbl">
            <thead><tr><th>Name</th><th>Type</th><th>City</th><th>Contact</th><th>GSTIN</th><th class="num">Terms</th><th class="no-export"></th></tr></thead>
            <tbody>
            <?php foreach ($list as $p): ?>
                <tr class="<?= $p['is_active'] ? '' : 'text-muted' ?>">
                    <td class="fw-semibold"><?= e($p['name']) ?><?= $p['is_active'] ? '' : ' <small>(Inactive)</small>' ?></td>
                    <td><span class="badge badge-type"><?= e($p['party_type']) ?></span></td>
                    <td><?= e($p['city']) ?></td>
                    <td><?= e($p['contact_person']) ?> <small class="text-muted d-block"><?= e($p['phone']) ?></small></td>
                    <td><?= e($p['gstin']) ?></td>
                    <td class="num"><?= (int)$p['payment_terms'] ?> d</td>
                    <td class="text-end no-export text-nowrap">
                        <a href="<?= url('parties/edit/' . $p['id']) ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                        <?php if (is_admin()) echo delete_button('parties', (int)$p['id'], 'Delete this party?'); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$list): ?><tr><td colspan="7" class="text-center text-muted py-4">No parties found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
