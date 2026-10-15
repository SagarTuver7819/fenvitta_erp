<?php
require_once __DIR__ . '/../../includes/init.php';

// ---------- Change password ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = row('SELECT * FROM users WHERE id = ?', [current_user()['id']]);
    $old = post('old_password');
    $new = post('new_password');

    if (!password_verify($old, $user['password'])) {
        flash('danger', 'Current password is wrong.');
    } elseif (strlen($new) < 6) {
        flash('danger', 'New password must be at least 6 characters.');
    } elseif ($new !== post('confirm_password')) {
        flash('danger', 'New password and confirm password do not match.');
    } else {
        update('users', ['password' => password_hash($new, PASSWORD_DEFAULT)], $user['id']);
        flash('success', 'Password changed successfully.');
    }
    redirect('profile');
}

$pageTitle = 'Change Password';
include ROOT_PATH . '/includes/header.php';
?>
<div class="card" style="max-width:480px">
    <div class="card-header"><i class="bi bi-key"></i> Change Password</div>
    <div class="card-body">
        <form method="post">
            <?= csrf_field() ?>
            <div class="mb-3"><label class="form-label">Current Password</label><input type="password" name="old_password" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">New Password</label><input type="password" name="new_password" class="form-control" required minlength="6"></div>
            <div class="mb-3"><label class="form-label">Confirm New Password</label><input type="password" name="confirm_password" class="form-control" required></div>
            <button class="btn btn-primary"><i class="bi bi-check2"></i> Update Password</button>
        </form>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
