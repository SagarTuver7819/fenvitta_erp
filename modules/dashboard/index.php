<?php
require_once __DIR__ . '/../../includes/init.php';

$fyStart = fy_start();
$fyEnd = fy_end();
$monthStart = date('Y-m-01');

// ---------- Sales ----------
$fySales = row('SELECT COUNT(*) cnt, COALESCE(SUM(subtotal),0) basic, COALESCE(SUM(total),0) total FROM sales WHERE sale_date BETWEEN ? AND ?', [$fyStart, $fyEnd]);
$fySalesQty = (float)val('SELECT COALESCE(SUM(si.qty),0) FROM sale_items si JOIN sales s ON s.id = si.sale_id WHERE s.sale_date BETWEEN ? AND ?', [$fyStart, $fyEnd]);
$monthSales = (float)val('SELECT COALESCE(SUM(total),0) FROM sales WHERE sale_date >= ?', [$monthStart]);
$allSales = (float)val('SELECT COALESCE(SUM(total),0) FROM sales');

// ---------- Outstanding ----------
$receivable = (float)val("SELECT COALESCE(SUM(s.total - IFNULL(p.paid,0)),0) FROM sales s
    LEFT JOIN (SELECT ref_id, SUM(amount+tds) paid FROM payments WHERE pay_type='RECEIPT' GROUP BY ref_id) p ON p.ref_id = s.id");
$payable = (float)val("SELECT COALESCE(SUM(pu.total - IFNULL(p.paid,0)),0) FROM purchases pu
    LEFT JOIN (SELECT ref_id, SUM(amount+tds) paid FROM payments WHERE pay_type='PAYMENT' GROUP BY ref_id) p ON p.ref_id = pu.id");
$overdue = (float)val("SELECT COALESCE(SUM(s.total - IFNULL(p.paid,0)),0) FROM sales s
    LEFT JOIN (SELECT ref_id, SUM(amount+tds) paid FROM payments WHERE pay_type='RECEIPT' GROUP BY ref_id) p ON p.ref_id = s.id
    WHERE s.due_date < CURDATE()");
$fyPurchase = (float)val('SELECT COALESCE(SUM(total),0) FROM purchases WHERE purchase_date BETWEEN ? AND ?', [$fyStart, $fyEnd]);

// ---------- Stock ----------
$pending = pending_dispatch_by_item();
$fgItems = rows("SELECT i.id, i.name, i.unit, i.std_rate, COALESCE(SUM(l.qty_in - l.qty_out),0) stock
    FROM items i LEFT JOIN stock_ledger l ON l.item_id = i.id
    WHERE i.item_type = 'FG' AND i.is_active = 1 GROUP BY i.id ORDER BY i.name");
$fgStockQty = 0;
$fgStockValue = 0;
$availableQty = 0;
foreach ($fgItems as &$fg) {
    $fg['pending'] = $pending[$fg['id']] ?? 0;
    $fg['available'] = max(0, $fg['stock'] - $fg['pending']);
    $fg['rate'] = item_avg_rate((int)$fg['id']);
    $lastSaleRate = val('SELECT si.rate FROM sale_items si JOIN sales s ON s.id = si.sale_id WHERE si.item_id = ? ORDER BY s.sale_date DESC, si.id DESC LIMIT 1', [$fg['id']]);
    if ($lastSaleRate) $fg['rate'] = (float)$lastSaleRate;
    $fg['value'] = $fg['stock'] * $fg['rate'];
    $fgStockQty += $fg['stock'];
    $fgStockValue += $fg['value'];
    $availableQty += $fg['available'];
}
unset($fg);

$rmValue = 0;
$lowStock = [];
foreach (rows("SELECT i.id, i.name, i.unit, i.reorder_level, COALESCE(SUM(l.qty_in - l.qty_out),0) stock
    FROM items i LEFT JOIN stock_ledger l ON l.item_id = i.id
    WHERE i.item_type IN ('RM','PM') AND i.is_active = 1 GROUP BY i.id") as $rm) {
    $rmValue += $rm['stock'] * item_avg_rate((int)$rm['id']);
    if ($rm['reorder_level'] > 0 && $rm['stock'] <= $rm['reorder_level']) $lowStock[] = $rm;
}

// ---------- Month wise sales (current FY) ----------
$monthly = [];
for ($i = 0; $i < 12; $i++) {
    $key = date('Y-m', strtotime("$fyStart +$i month"));
    $monthly[$key] = ['label' => date('M-y', strtotime("$fyStart +$i month")), 'amount' => 0, 'basic' => 0, 'qty' => 0, 'count' => 0];
}
foreach (rows("SELECT DATE_FORMAT(sale_date,'%Y-%m') ym, COUNT(*) cnt, SUM(subtotal) basic, SUM(total) amount FROM sales
    WHERE sale_date BETWEEN ? AND ? GROUP BY ym", [$fyStart, $fyEnd]) as $m) {
    $monthly[$m['ym']]['amount'] = (float)$m['amount'];
    $monthly[$m['ym']]['basic'] = (float)$m['basic'];
    $monthly[$m['ym']]['count'] = (int)$m['cnt'];
}
foreach (rows("SELECT DATE_FORMAT(s.sale_date,'%Y-%m') ym, SUM(si.qty) q FROM sale_items si JOIN sales s ON s.id = si.sale_id
    WHERE s.sale_date BETWEEN ? AND ? GROUP BY ym", [$fyStart, $fyEnd]) as $m) {
    $monthly[$m['ym']]['qty'] = (float)$m['q'];
}

// ---------- FG batch stock ----------
$fgBatches = fg_batches(true);

// ---------- Recent ----------
$recentSales = rows('SELECT s.*, p.name party FROM sales s JOIN parties p ON p.id = s.party_id ORDER BY s.sale_date DESC, s.id DESC LIMIT 6');
$recentDispatch = rows('SELECT d.*, p.name party, (SELECT SUM(qty) FROM dispatch_items WHERE dispatch_id = d.id) qty
    FROM dispatches d JOIN parties p ON p.id = d.party_id ORDER BY d.dispatch_date DESC, d.id DESC LIMIT 6');

// ---------- Top customers (current FY) ----------
$topCustomers = rows('SELECT p.name, SUM(s.total) amount FROM sales s JOIN parties p ON p.id = s.party_id
    WHERE s.sale_date BETWEEN ? AND ? GROUP BY p.id ORDER BY amount DESC LIMIT 5', [$fyStart, $fyEnd]);
$otherAmount = (float)$fySales['total'] - array_sum(array_column($topCustomers, 'amount'));

$hour = (int)date('G');
$greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

$pageTitle = 'Dashboard';
$active = 'dashboard';
include ROOT_PATH . '/includes/header.php';
?>
<div class="hero">
    <img src="<?= url('assets/img/logo-mark.svg') ?>" class="hex h1" alt="">
    <img src="<?= url('assets/img/logo-mark.svg') ?>" class="hex h2" alt="">
    <img src="<?= url('assets/img/logo-mark.svg') ?>" class="hex h3" alt="">
    <div class="d-flex flex-wrap align-items-center gap-3">
        <div class="flex-grow-1">
            <h2><?= $greeting ?>, <?= e(current_user()['name']) ?> &#128075;</h2>
            <p>Here is what's happening at Fenvitta today &middot; <?= date('l, d F Y') ?> &middot; FY <?= e(fy_label()) ?></p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= url('sales/add') ?>" class="quick-btn"><i class="bi bi-receipt"></i> New Sale</a>
            <a href="<?= url('purchase/add') ?>" class="quick-btn"><i class="bi bi-cart-plus"></i> New Purchase</a>
            <a href="<?= url('production/add') ?>" class="quick-btn"><i class="bi bi-gear-wide-connected"></i> Batch</a>
            <a href="<?= url('dispatch/add') ?>" class="quick-btn"><i class="bi bi-truck"></i> Dispatch</a>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card primary">
            <div class="icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="label">Total Sales (FY <?= e(fy_label()) ?>)</div>
            <div class="value" data-count="<?= (float)$fySales['total'] ?>" data-dec="2" data-prefix="₹ "><?= money($fySales['total']) ?></div>
            <div class="sub"><?= (int)$fySales['cnt'] ?> invoices &middot; <?= qty($fySalesQty) ?> kg &middot; Basic <?= money($fySales['basic']) ?></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="icon"><i class="bi bi-calendar-check"></i></div>
            <div class="label">Sales This Month</div>
            <div class="value" data-count="<?= $monthSales ?>" data-dec="2" data-prefix="₹ "><?= money($monthSales) ?></div>
            <div class="sub"><?= date('F Y') ?> &middot; All time <?= money($allSales) ?></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card success">
            <div class="icon"><i class="bi bi-cash-coin"></i></div>
            <div class="label">Sales Outstanding (Receivable)</div>
            <div class="value" data-count="<?= $receivable ?>" data-dec="2" data-prefix="₹ "><?= money($receivable) ?></div>
            <div class="sub text-danger">Overdue: <?= money($overdue) ?></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card danger">
            <div class="icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="label">Purchase Outstanding (Payable)</div>
            <div class="value" data-count="<?= $payable ?>" data-dec="2" data-prefix="₹ "><?= money($payable) ?></div>
            <div class="sub">FY Purchase: <?= money($fyPurchase) ?></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card accent">
            <div class="icon"><i class="bi bi-boxes"></i></div>
            <div class="label">Finish Goods Stock</div>
            <div class="value"><span data-count="<?= $fgStockQty ?>" data-dec="3"><?= qty($fgStockQty) ?></span> <small class="fs-6 text-muted">kg</small></div>
            <div class="sub"><?= count($fgBatches) ?> batches in stock</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card success">
            <div class="icon"><i class="bi bi-bag-check"></i></div>
            <div class="label">Stock Available For Sales</div>
            <div class="value"><span data-count="<?= $availableQty ?>" data-dec="3"><?= qty($availableQty) ?></span> <small class="fs-6 text-muted">kg</small></div>
            <div class="mini-progress mt-2"><div class="progress-bar green" data-width="<?= $fgStockQty > 0 ? round($availableQty / $fgStockQty * 100, 1) : 0 ?>"></div></div>
            <div class="sub"><?= $fgStockQty > 0 ? round($availableQty / $fgStockQty * 100) : 0 ?>% of FG stock free after pending orders</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card warn">
            <div class="icon"><i class="bi bi-currency-rupee"></i></div>
            <div class="label">FG Stock Amount</div>
            <div class="value" data-count="<?= $fgStockValue ?>" data-dec="2" data-prefix="₹ "><?= money($fgStockValue) ?></div>
            <div class="sub">Valued at last sales rate</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="icon"><i class="bi bi-box-seam"></i></div>
            <div class="label">RM + PM Stock Amount</div>
            <div class="value" data-count="<?= $rmValue ?>" data-dec="2" data-prefix="₹ "><?= money($rmValue) ?></div>
            <div class="sub"><?= count($lowStock) ?> item(s) below reorder level</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-bar-chart"></i> Month Wise Sales &middot; FY <?= e(fy_label()) ?></div>
            <div class="card-body"><canvas id="salesChart" height="120"></canvas></div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-bag-check"></i> Stock Available For Sales</div>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead><tr><th>Product</th><th class="num">FG Stock</th><th class="num">Booked</th><th class="num">Available</th></tr></thead>
                    <tbody>
                    <?php foreach ($fgItems as $fg): ?>
                        <tr>
                            <td>
                                <?= e($fg['name']) ?>
                                <?php if ($fg['stock'] > 0): ?>
                                    <div class="mini-progress mt-1" style="height:4px"><div class="progress-bar green" data-width="<?= round($fg['available'] / $fg['stock'] * 100, 1) ?>"></div></div>
                                <?php endif; ?>
                            </td>
                            <td class="num"><?= qty($fg['stock']) ?></td>
                            <td class="num text-warning"><?= qty($fg['pending']) ?></td>
                            <td class="num fw-bold text-success"><?= qty($fg['available']) ?> <?= e($fg['unit']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot><tr><td>Total</td><td class="num"><?= qty($fgStockQty) ?></td><td></td><td class="num"><?= qty($availableQty) ?></td></tr></tfoot>
                </table>
            </div>
            <div class="card-body border-top">
                <div class="small text-muted mb-1">FG Stock Amount</div>
                <?php foreach ($fgItems as $fg): if ($fg['stock'] <= 0) continue; ?>
                    <div class="d-flex justify-content-between small"><span><?= e($fg['name']) ?> @ <?= inr($fg['rate']) ?></span><b><?= money($fg['value']) ?></b></div>
                <?php endforeach; ?>
                <div class="d-flex justify-content-between mt-1 pt-1 border-top"><span class="fw-bold">Total</span><b class="text-primary"><?= money($fgStockValue) ?></b></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-boxes"></i> Finish Good Stock Record (Batch Wise)
                <div class="actions"><a href="<?= url('fg_stock') ?>" class="btn btn-sm btn-outline-primary">View All</a></div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead><tr><th>Batch No</th><th>Product</th><th>Mfg</th><th>Exp</th><th class="num">Output</th><th class="num">Dispatched</th><th class="num">Balance</th></tr></thead>
                    <tbody>
                    <?php foreach ($fgBatches as $b): ?>
                        <tr>
                            <td><a href="<?= url('production/view/' . $b['id']) ?>" class="fw-semibold"><?= e($b['batch_no']) ?></a></td>
                            <td><?= e($b['item_name']) ?></td>
                            <td><?= dmy($b['mfg_date']) ?></td>
                            <td><?= dmy($b['exp_date']) ?></td>
                            <td class="num"><?= qty($b['produced']) ?></td>
                            <td class="num"><?= qty($b['dispatched']) ?></td>
                            <td class="num fw-bold"><?= qty($b['balance']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$fgBatches): ?><tr><td colspan="7" class="text-center text-muted py-4">No finished goods in stock.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-table"></i> Month Wise Sales Summary</div>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead><tr><th>Month</th><th class="num">Inv.</th><th class="num">Qty (kg)</th><th class="num">Basic</th><th class="num">Total (GST)</th></tr></thead>
                    <tbody>
                    <?php $t = ['count' => 0, 'qty' => 0, 'basic' => 0, 'amount' => 0];
                    foreach ($monthly as $m): foreach ($t as $k => $_) $t[$k] += $m[$k]; ?>
                        <tr>
                            <td><?= e($m['label']) ?></td>
                            <td class="num"><?= $m['count'] ?></td>
                            <td class="num"><?= qty($m['qty']) ?></td>
                            <td class="num"><?= inr($m['basic']) ?></td>
                            <td class="num fw-semibold"><?= inr($m['amount']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot><tr><td>Total</td><td class="num"><?= $t['count'] ?></td><td class="num"><?= qty($t['qty']) ?></td><td class="num"><?= inr($t['basic']) ?></td><td class="num"><?= inr($t['amount']) ?></td></tr></tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-3 col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-pie-chart"></i> Top Customers &middot; FY</div>
            <div class="card-body">
                <?php if ($topCustomers): ?>
                    <canvas id="custChart" height="230"></canvas>
                <?php else: ?>
                    <div class="text-muted text-center py-4">No sales this year.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-receipt"></i> Recent Sales</div>
            <ul class="list-group list-group-flush">
                <?php foreach ($recentSales as $s): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <div><a href="<?= url('sales/print/' . $s['id']) ?>" class="fw-semibold"><?= e($s['invoice_no']) ?></a><br><small class="text-muted"><?= e($s['party']) ?> &middot; <?= dmy($s['sale_date']) ?></small></div>
                        <b><?= money($s['total']) ?></b>
                    </li>
                <?php endforeach; ?>
                <?php if (!$recentSales): ?><li class="list-group-item text-muted text-center">No sales yet.</li><?php endif; ?>
            </ul>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-truck"></i> Recent Dispatch</div>
            <ul class="list-group list-group-flush">
                <?php foreach ($recentDispatch as $d): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <div><a href="<?= url('dispatch/print/' . $d['id']) ?>" class="fw-semibold"><?= e($d['dispatch_no']) ?></a><br><small class="text-muted"><?= e($d['party']) ?> &middot; <?= dmy($d['dispatch_date']) ?></small></div>
                        <b><?= qty($d['qty']) ?> kg</b>
                    </li>
                <?php endforeach; ?>
                <?php if (!$recentDispatch): ?><li class="list-group-item text-muted text-center">No dispatch yet.</li><?php endif; ?>
            </ul>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-exclamation-triangle"></i> RM Below Reorder Level</div>
            <ul class="list-group list-group-flush">
                <?php foreach ($lowStock as $l): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><?= e($l['name']) ?></span>
                        <span class="text-danger fw-semibold"><?= qty($l['stock']) ?> / <?= qty($l['reorder_level']) ?> <?= e($l['unit']) ?></span>
                    </li>
                <?php endforeach; ?>
                <?php if (!$lowStock): ?><li class="list-group-item text-muted text-center">All raw materials are above reorder level.</li><?php endif; ?>
            </ul>
        </div>
    </div>
</div>
<?php
$chartLabels = json_encode(array_column(array_values($monthly), 'label'));
$chartAmount = json_encode(array_column(array_values($monthly), 'amount'));
$chartQty = json_encode(array_column(array_values($monthly), 'qty'));
$custLabels = array_map(fn($c) => mb_strimwidth($c['name'], 0, 28, '…'), $topCustomers);
$custData = array_map(fn($c) => round((float)$c['amount'], 2), $topCustomers);
if ($otherAmount > 1) {
    $custLabels[] = 'Others';
    $custData[] = round($otherAmount, 2);
}
$custLabels = json_encode($custLabels);
$custData = json_encode($custData);
$chartDelay = !empty($showWelcome) ? 1700 : 0;
$pageLibs = ['chart.umd.min'];
$pageScripts = <<<HTML
<script>
Chart.defaults.font.family = '"Segoe UI", Inter, Roboto, Arial, sans-serif';
Chart.defaults.color = '#7a6871';
setTimeout(function () {
const custEl = document.getElementById('custChart');
if (custEl) new Chart(custEl, {
    type: 'doughnut',
    data: {
        labels: $custLabels,
        datasets: [{ data: $custData, backgroundColor: ['#632a4a', '#8a4a6c', '#8c7b73', '#b98aa3', '#d4bfb5', '#e9dde3'], borderWidth: 2, borderColor: '#fff', hoverOffset: 10 }]
    },
    options: {
        cutout: '64%',
        animation: { animateRotate: true, duration: 1400 },
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } },
            tooltip: { callbacks: { label: c => ' ₹ ' + Number(c.raw).toLocaleString('en-IN') } }
        }
    }
});
new Chart(document.getElementById('salesChart'), {
    data: {
        labels: $chartLabels,
        datasets: [
            { type: 'bar', label: 'Sales Amount (₹)', data: $chartAmount, backgroundColor: 'rgba(99,42,74,.85)', hoverBackgroundColor: '#4a1f37', borderRadius: 8, yAxisID: 'y' },
            { type: 'line', label: 'Qty (kg)', data: $chartQty, borderColor: '#8c7b73', backgroundColor: '#8c7b73', borderWidth: 3, pointRadius: 4, pointHoverRadius: 7, cubicInterpolationMode: 'monotone', yAxisID: 'y1' }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        animation: { duration: 1400, easing: 'easeOutQuart', delay: c => c.type === 'data' ? c.dataIndex * 70 : 0 },
        plugins: { legend: { labels: { usePointStyle: true } } },
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, grid: { color: '#f1e8ed' }, ticks: { callback: v => '₹' + Number(v).toLocaleString('en-IN') } },
            y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } }
        }
    }
});
}, $chartDelay);
</script>
HTML;
include ROOT_PATH . '/includes/footer.php';
