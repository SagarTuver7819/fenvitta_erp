<?php
/*
 * Shared Outstanding List (used by modules/sales/outstanding.php and modules/purchase/outstanding.php)
 * Required variables before include:
 *   $mode = 'sales' or 'purchase'
 */
$isSales = $mode === 'sales';
$payType = $isSales ? 'RECEIPT' : 'PAYMENT';
$table = $isSales ? 'sales' : 'purchases';
$dateCol = $isSales ? 'sale_date' : 'purchase_date';
$partyLabel = $isSales ? 'Customer' : 'Supplier';

$partyId = (int)get('party_id');
$show = get('show', 'due'); // due = only outstanding, all = all bills

$sql = "SELECT t.id, t.$dateCol bill_date, t.invoice_no, " . ($isSales ? "'' po_no" : 't.po_no') . ", t.total, t.payment_terms, t.due_date,
               pa.name party, pa.id party_id,
               COALESCE(p.paid,0) paid, t.total - COALESCE(p.paid,0) balance, DATEDIFF(CURDATE(), t.due_date) overdue_days
        FROM $table t
        JOIN parties pa ON pa.id = t.party_id
        LEFT JOIN (SELECT ref_id, SUM(amount + tds) paid FROM payments WHERE pay_type = '$payType' GROUP BY ref_id) p ON p.ref_id = t.id
        WHERE 1 = 1";
$params = [];
if ($partyId) {
    $sql .= ' AND t.party_id = ?';
    $params[] = $partyId;
}
if ($show === 'due') $sql .= ' HAVING balance > 0.5';
$list = rows($sql . ' ORDER BY t.due_date, t.id', $params);

// Party wise summary
$summary = [];
foreach ($list as $r) {
    if ($r['balance'] <= 0.5) continue;
    $s = &$summary[$r['party']];
    $s['bills'] = ($s['bills'] ?? 0) + 1;
    $s['balance'] = ($s['balance'] ?? 0) + $r['balance'];
    $s['overdue'] = ($s['overdue'] ?? 0) + ($r['overdue_days'] > 0 ? $r['balance'] : 0);
    unset($s);
}
arsort($summary);

$history = rows("SELECT py.*, t.invoice_no, pa.name party FROM payments py
                 JOIN $table t ON t.id = py.ref_id JOIN parties pa ON pa.id = t.party_id
                 WHERE py.pay_type = ? ORDER BY py.pay_date DESC, py.id DESC LIMIT 100", [$payType]);

$totalBal = array_sum(array_column($summary, 'balance'));
$totalOver = array_sum(array_column($summary, 'overdue'));
$parties = parties_for($isSales ? 'customer' : 'supplier');
?>
<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="stat-card primary"><div class="icon"><i class="bi bi-wallet2"></i></div>
        <div class="label">Total Outstanding</div><div class="value"><?= money($totalBal) ?></div><div class="sub"><?= count($summary) ?> parties</div></div></div>
    <div class="col-md-4"><div class="stat-card danger"><div class="icon"><i class="bi bi-alarm"></i></div>
        <div class="label">Overdue (Due date passed)</div><div class="value text-danger"><?= money($totalOver) ?></div><div class="sub">As on <?= date('d-m-Y') ?></div></div></div>
    <div class="col-md-4"><div class="stat-card success"><div class="icon"><i class="bi bi-calendar2-check"></i></div>
        <div class="label">Not Yet Due</div><div class="value"><?= money($totalBal - $totalOver) ?></div><div class="sub">Within payment terms</div></div></div>
</div>

<form class="filter-bar row g-2 align-items-end">
    <div class="col-md-4">
        <label class="form-label"><?= $partyLabel ?></label>
        <select name="party_id" class="form-select form-select-sm">
            <option value="">All</option>
            <?php foreach ($parties as $p): ?><option value="<?= $p['id'] ?>" <?= $partyId == $p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Show</label>
        <select name="show" class="form-select form-select-sm">
            <option value="due" <?= $show === 'due' ? 'selected' : '' ?>>Only Outstanding Bills</option>
            <option value="all" <?= $show === 'all' ? 'selected' : '' ?>>All Bills (incl. paid)</option>
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Filter</button></div>
</form>

<div class="row g-3">
    <div class="col-xl-9">
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-list-check"></i> Outstanding List
                <div class="actions">
                    <input class="form-control form-control-sm table-search" placeholder="Search..." data-table-search="#tbl">
                    <button class="btn btn-sm btn-accent" data-export="#tbl" data-filename="<?= $mode ?>-outstanding"><i class="bi bi-file-earmark-excel"></i> Excel</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover" id="tbl">
                    <thead><tr><th>Date</th><th>Invoice No</th><?php if (!$isSales): ?><th>PO No</th><?php endif; ?><th><?= $partyLabel ?></th><th class="num">Amount</th><th class="num">Terms</th><th>Due Date</th><th class="num">Due Days</th><th class="num"><?= $isSales ? 'Received' : 'Paid' ?></th><th class="num">Balance</th><th>Status</th><th class="no-export"></th></tr></thead>
                    <tbody>
                    <?php foreach ($list as $r):
                        $isPaid = $r['balance'] <= 0.5;
                        $isOver = !$isPaid && $r['overdue_days'] > 0;
                        $status = $isPaid ? ($isSales ? 'Received' : 'Paid') : ($isOver ? 'Overdue' : ($r['paid'] > 0 ? 'Partial' : 'Pending')); ?>
                        <tr class="<?= $isOver ? 'row-overdue' : '' ?>">
                            <td><?= dmy($r['bill_date']) ?></td>
                            <td class="fw-semibold"><?= e($r['invoice_no']) ?></td>
                            <?php if (!$isSales): ?><td><?= e($r['po_no']) ?></td><?php endif; ?>
                            <td><?= e($r['party']) ?></td>
                            <td class="num"><?= inr($r['total']) ?></td>
                            <td class="num"><?= (int)$r['payment_terms'] ?></td>
                            <td><?= dmy($r['due_date']) ?></td>
                            <td class="num <?= $isOver ? 'text-danger fw-bold' : 'text-muted' ?>"><?= $isPaid ? '-' : (int)$r['overdue_days'] ?></td>
                            <td class="num text-success"><?= inr($r['paid']) ?></td>
                            <td class="num fw-bold"><?= inr($r['balance']) ?></td>
                            <td><?= status_badge($status) ?></td>
                            <td class="text-end no-export text-nowrap">
                                <?php if (!$isPaid): ?>
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#payModal"
                                            data-id="<?= $r['id'] ?>" data-inv="<?= e($r['invoice_no']) ?>" data-party="<?= e($r['party']) ?>" data-bal="<?= round($r['balance'], 2) ?>">
                                        <i class="bi bi-cash"></i> <?= $isSales ? 'Receive' : 'Pay' ?>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$list): ?><tr><td colspan="12" class="text-center text-muted py-4">No outstanding bills. 🎉</td></tr><?php endif; ?>
                    </tbody>
                    <tfoot><tr><td colspan="<?= $isSales ? 3 : 4 ?>">Total</td><td class="num"><?= inr(array_sum(array_column($list, 'total'))) ?></td><td colspan="3"></td><td class="num"><?= inr(array_sum(array_column($list, 'paid'))) ?></td><td class="num"><?= inr(array_sum(array_column($list, 'balance'))) ?></td><td colspan="2"></td></tr></tfoot>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="bi bi-clock-history"></i> <?= $isSales ? 'Receipt' : 'Payment' ?> History (last 100)
                <div class="actions"><button class="btn btn-sm btn-accent" data-export="#hist" data-filename="<?= $mode ?>-payments"><i class="bi bi-file-earmark-excel"></i></button></div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover" id="hist">
                    <thead><tr><th>Date</th><th>Invoice</th><th><?= $partyLabel ?></th><th>Mode</th><th>Ref No</th><th class="num">Amount</th><th class="num">TDS</th><th>Remarks</th><th class="no-export"></th></tr></thead>
                    <tbody>
                    <?php foreach ($history as $h): ?>
                        <tr>
                            <td><?= dmy($h['pay_date']) ?></td><td><?= e($h['invoice_no']) ?></td><td><?= e($h['party']) ?></td>
                            <td><?= e($h['mode']) ?></td><td><?= e($h['ref_no']) ?></td>
                            <td class="num fw-semibold"><?= inr($h['amount']) ?></td><td class="num"><?= inr($h['tds']) ?></td><td class="small"><?= e($h['remarks']) ?></td>
                            <td class="no-export text-end">
                                <?php if (is_admin()) echo delete_button('payments', (int)$h['id'], 'Delete this ' . ($isSales ? 'receipt' : 'payment') . ' entry?'); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$history): ?><tr><td colspan="9" class="text-center text-muted py-3">No entries yet.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-3">
        <div class="card">
            <div class="card-header"><i class="bi bi-people"></i> Party Wise Outstanding</div>
            <ul class="list-group list-group-flush">
                <?php foreach ($summary as $name => $s): ?>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between"><span class="fw-semibold small"><?= e($name) ?></span><b><?= inr($s['balance']) ?></b></div>
                        <small class="text-muted"><?= $s['bills'] ?> bill(s)<?= $s['overdue'] > 0 ? ' &middot; <span class="text-danger">Overdue ' . inr($s['overdue']) . '</span>' : '' ?></small>
                    </li>
                <?php endforeach; ?>
                <?php if (!$summary): ?><li class="list-group-item text-muted text-center">Nothing outstanding.</li><?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<!-- Payment / Receipt modal -->
<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= url('payments/add') ?>" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="pay_type" value="<?= $payType ?>">
            <input type="hidden" name="ref_id" id="pm_id">
            <div class="modal-header"><h5 class="modal-title text-primary"><?= $isSales ? 'Payment Received' : 'Payment Paid' ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body row g-2">
                <div class="col-12 small bg-light rounded p-2"><b id="pm_inv"></b> &middot; <span id="pm_party"></span><br>Balance: <b class="text-danger" id="pm_bal"></b></div>
                <div class="col-6"><label class="form-label">Date</label><input type="date" name="pay_date" class="form-control" value="<?= today() ?>" required></div>
                <div class="col-6"><label class="form-label">Mode</label><select name="mode" class="form-select"><?php foreach (PAYMENT_MODES as $m): ?><option><?= $m ?></option><?php endforeach; ?></select></div>
                <div class="col-6"><label class="form-label">Amount ₹</label><input type="number" step="0.01" name="amount" id="pm_amount" class="form-control" required></div>
                <div class="col-6"><label class="form-label">TDS / Deduction ₹</label><input type="number" step="0.01" name="tds" class="form-control" value="0"></div>
                <div class="col-12"><label class="form-label">Cheque / UTR / Ref No.</label><input name="ref_no" class="form-control"></div>
                <div class="col-12"><label class="form-label">Remarks</label><input name="remarks" class="form-control"></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button><button class="btn btn-primary"><i class="bi bi-check2"></i> Save</button></div>
        </form>
    </div>
</div>
<?php
$pageScripts = <<<HTML
<script>
document.getElementById('payModal').addEventListener('show.bs.modal', function (e) {
    const b = e.relatedTarget.dataset;
    document.getElementById('pm_id').value = b.id;
    document.getElementById('pm_inv').textContent = b.inv;
    document.getElementById('pm_party').textContent = b.party;
    document.getElementById('pm_bal').textContent = '₹ ' + fvFmt(b.bal);
    document.getElementById('pm_amount').value = b.bal;
});
</script>
HTML;
