<?php
require_once __DIR__ . '/../../includes/init.php';
require_admin();

$list = rows('SELECT * FROM users ORDER BY name');

$pageTitle = 'Users';
$active = 'users';
include ROOT_PATH . '/includes/header.php';
?>
<div class="card">
    <div class="card-header"><i class="bi bi-people"></i> Users (<?= count($list) ?>)
        <div class="actions">
            <input class="form-control form-control-sm table-search" placeholder="Search..." data-table-search="#tbl">
            <a href="<?= url('users/add') ?>" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Add User</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover" id="tbl">
            <thead><tr><th>Name</th><th>Username</th><th>Role</th><th>Status</th><th>Created</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($list as $u): ?>
                <tr>
                    <td class="fw-semibold"><?= e($u['name']) ?></td><td><?= e($u['username']) ?></td>
                    <td><span class="badge <?= $u['role'] === 'admin' ? 'text-bg-primary' : 'badge-type' ?>"><?= e(ucfirst($u['role'])) ?></span></td>
                    <td><?= $u['is_active'] ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-secondary">Inactive</span>' ?></td>
                    <td><?= dmy($u['created_at']) ?></td>
                    <td class="text-end text-nowrap">
                        <a href="<?= url('users/edit/' . $u['id']) ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                        <?php if ((int)$u['id'] !== (int)current_user()['id']) echo delete_button('users', (int)$u['id'], 'Delete user "' . $u['username'] . '"?'); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
