<?php
require_once __DIR__ . '/../../includes/init.php';
require_post('items');
require_admin();

$id = (int)post('id');
$used = val("SELECT COUNT(*) FROM stock_ledger WHERE item_id=? AND ref_type<>'OPENING'", [$id])
    + val('SELECT COUNT(*) FROM purchase_items WHERE item_id=?', [$id])
    + val('SELECT COUNT(*) FROM sale_items WHERE item_id=?', [$id]);

if ($used > 0) {
    flash('warning', 'This item is used in transactions, so it cannot be deleted. Mark it Inactive instead.');
} else {
    ledger_clear('OPENING', $id);
    q('DELETE FROM items WHERE id=?', [$id]);
    flash('success', 'Item deleted.');
}
redirect('items');
