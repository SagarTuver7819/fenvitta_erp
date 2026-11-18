<?php
require_once __DIR__ . '/../../includes/init.php';
require_post('inward');
require_admin();

$id = (int)post('id');
db_begin();
q('DELETE FROM inward_items WHERE inward_id=?', [$id]);
q('DELETE FROM inward_entries WHERE id=?', [$id]);
ledger_clear('INWARD', $id);
db_commit();
flash('success', 'Inward entry deleted. Stock reversed.');
redirect('inward');
