<?php
/* Add user  (/users/add)   or   Edit user  (/users/edit/{id}) */
require_once __DIR__ . '/../../includes/init.php';
require_admin();

$id = (int)get('id');
$user = $id ? row('SELECT * FROM users WHERE id=?', [$id]) : null;
if ($id && !$user) redirect('users');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => post('name'),
        'username' => strtolower(post('username')),
        'role' => post('role') === 'admin' ? 'admin' : 'user',
        'is_active' => post('is_active') ? 1 : 0,
    ];
    if (post('password') !== '') {
        $data['password'] = password_hash(post('password'), PASSWORD_DEFAULT);
    }

    $error = '';
    if ($data['name'] === '' || $data['username'] === '') {
        $error = 'Name and username are required.';
    } elseif (val('SELECT COUNT(*) FROM users WHERE username=? AND id<>?', [$data['username'], $id]) > 0) {
        $error = 'Username already exists.';
    } elseif (!$id && empty($data['password'])) {
        $error = 'Password is required for new user.';
    } elseif ($id === (int)current_user()['id'] && (!$data['is_active'] || $data['role'] !== 'admin')) {
        $error = 'You cannot deactivate or downgrade your own account.';
    }
    if ($error) {
        flash('danger', $error);
        redirect($id ? "users/edit/$id" : 'users/add');
    }

    if ($id) {
        update('users', $data, $id);
        flash('success', 'User "' . $data['username'] . '" updated.');
    } else {
        insert('users', $data);
        flash('success', 'User "' . $data['username'] . '" added.');
    }
    redirect('users');
}

$u = $user ?? ['name' => '', 'username' => '', 'role' => 'user', 'is_active' => 1];

$pageTitle = $user ? 'Edit User' : 'Add User';
$active = 'users';
include ROOT_PATH . '/includes/header.php';
?>
<div class="card" style="max-width:640px">
    <div class="card-header"><i class="bi bi-person-gear"></i> <?= $pageTitle ?></div>
    <div class="card-body">
        <form method="post">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Full Name *</label><input name="name" class="form-control" required value="<?= e($u['name']) ?>"></div>
                <div class="col-md-6"><label class="form-label">Username *</label><input name="username" class="form-control" required value="<?= e($u['username']) ?>"></div>
                <div class="col-md-6"><label class="form-label">Password <?= $user ? '<small class="text-muted">(blank = keep same)</small>' : '*' ?></label>
                    <input type="password" name="password" class="form-control" minlength="6" <?= $user ? '' : 'required' ?>></div>
                <div class="col-md-6"><label class="form-label">Role</label>
                    <select name="role" class="form-select">
                        <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>User (entry only, cannot delete)</option>
                        <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin (full access)</option>
                    </select></div>
                <div class="col-12">
                    <div class="form-check"><input type="checkbox" class="form-check-input" name="is_active" id="act" value="1" <?= $u['is_active'] ? 'checked' : '' ?>><label class="form-check-label" for="act">Active</label></div>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <a href="<?= url('users') ?>" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a>
                <button class="btn btn-primary ms-auto"><i class="bi bi-check2"></i> <?= $user ? 'Update' : 'Save' ?> User</button>
            </div>
        </form>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
