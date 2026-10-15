<?php
require_once __DIR__ . '/../../includes/init.php';
require_post('users');
require_admin();

$id = (int)post('id');
$tables = ['purchases', 'sales', 'payments', 'production_batches', 'rm_utilities', 'dispatches', 'packing_labels'];
$used = 0;
foreach ($tables as $t) {
    $used += (int)val("SELECT COUNT(*) FROM $t WHERE created_by=?", [$id]);
}

if ($id === (int)current_user()['id']) {
    flash('danger', 'You cannot delete your own account.');
} elseif ($used > 0) {
    flash('warning', 'This user has made entries, so it cannot be deleted. You can mark it Inactive.');
} else {
    q('DELETE FROM users WHERE id=?', [$id]);
    flash('success', 'User deleted.');
}
redirect('users');
