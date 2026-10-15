<?php
/*
 * Common handler for:
 *   RECEIPT  -> payment received against a Sales invoice
 *   PAYMENT  -> payment paid against a Purchase bill
 */
require_once __DIR__ . '/../../includes/init.php';
require_post('sales/outstanding');

$type = post('pay_type') === 'RECEIPT' ? 'RECEIPT' : 'PAYMENT';
$back = $type === 'RECEIPT' ? 'sales/outstanding' : 'purchase/outstanding';

$refId = (int)post('ref_id');
$amount = num(post('amount'));
$tds = num(post('tds'));

if (!$refId || ($amount + $tds) <= 0) {
    flash('danger', 'Please enter a valid amount.');
    redirect($back);
}

insert('payments', [
    'pay_type' => $type,
    'ref_id' => $refId,
    'pay_date' => date_or_null(post('pay_date')) ?? today(),
    'amount' => $amount,
    'tds' => $tds,
    'mode' => post('mode'),
    'ref_no' => post('ref_no'),
    'remarks' => post('remarks'),
    'created_by' => current_user()['id'],
]);

flash('success', ($type === 'RECEIPT' ? 'Receipt' : 'Payment') . ' of ' . money($amount) . ' saved.');
redirect($back);
