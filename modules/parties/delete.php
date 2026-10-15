<?php
require_once __DIR__ . '/../../includes/init.php';
require_post('parties');
require_admin();

$id = (int)post('id');
$used = val('SELECT (SELECT COUNT(*) FROM purchases WHERE party_id=?) + (SELECT COUNT(*) FROM sales WHERE party_id=?) + (SELECT COUNT(*) FROM dispatches WHERE party_id=?)', [$id, $id, $id]);

if ($used > 0) {
    flash('warning', 'This party has transactions, so it cannot be deleted. You can mark it Inactive.');
} else {
    q('DELETE FROM parties WHERE id=?', [$id]);
    flash('success', 'Party deleted.');
}
redirect('parties');
