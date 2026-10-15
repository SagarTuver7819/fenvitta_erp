<?php
require_once __DIR__ . '/../../includes/init.php';
require_post('sales/outstanding');
require_admin();

$id = (int)post('id');
$pay = row('SELECT * FROM payments WHERE id=?', [$id]);
$back = ($pay['pay_type'] ?? '') === 'PAYMENT' ? 'purchase/outstanding' : 'sales/outstanding';

if ($pay) {
    q('DELETE FROM payments WHERE id=?', [$id]);
    flash('success', ($pay['pay_type'] === 'RECEIPT' ? 'Receipt' : 'Payment') . ' entry deleted.');
}
redirect($back);
