<?php
require_once __DIR__ . '/../../includes/init.php';
require_post('inward');
require_admin();

$id = (int)post('id');

if (rm_batches_used_on_inward($id)) {
    flash('danger', 'This inward has RM batches already used in Production. Cannot delete.');
    redirect('inward');
}

db_begin();
q('DELETE FROM rm_batches WHERE inward_id=?', [$id]);
q('DELETE FROM inward_items WHERE inward_id=?', [$id]);
q('DELETE FROM inward_entries WHERE id=?', [$id]);
ledger_clear('INWARD', $id);
db_commit();
flash('success', 'Inward entry deleted. Stock and RM batches removed.');
redirect('inward');
