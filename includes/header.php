<?php
/*
 * Common page top: <head>, sidebar menu and top bar.
 * Set before include:  $pageTitle  (page heading)   $active  (menu key to highlight)
 */
require_login();
$pageTitle = $pageTitle ?? 'Dashboard';
$active = $active ?? '';
$navUser = current_user();

// Show welcome splash only once, just after login
$showWelcome = !empty($_SESSION['welcome']);
unset($_SESSION['welcome']);

// [Label, icon, clean URL, menu key]
$navMenu = [
    ['Dashboard', 'bi-speedometer2', '', 'dashboard'],
    ['ACCOUNT ENTRY', null, [
        ['Purchase Entry', 'bi-cart-plus', 'purchase', 'purchase'],
        ['Purchase Outstanding', 'bi-hourglass-split', 'purchase/outstanding', 'purchase_outstanding'],
        ['Sales Entry', 'bi-receipt', 'sales', 'sales'],
        ['Sales Outstanding', 'bi-cash-coin', 'sales/outstanding', 'sales_outstanding'],
    ]],
    ['WAREHOUSE', null, [
        ['Raw Material Stock', 'bi-box-seam', 'rm_stock', 'rm_stock'],
        ['RM Utilities Entry', 'bi-droplet-half', 'rm_utilities', 'rm_utilities'],
        ['Finish Goods Stock', 'bi-boxes', 'fg_stock', 'fg_stock'],
        ['Dispatch Record', 'bi-journal-text', 'dispatch/register', 'dispatch_register'],
    ]],
    ['PRODUCTION', null, [
        ['Production Batches', 'bi-gear-wide-connected', 'production', 'production'],
        ['Batch Wise RM Use', 'bi-diagram-3', 'batch_rm', 'batch_rm'],
        ['Batch Wise Data Entry', 'bi-clipboard-data', 'batch_data', 'batch_data'],
        ['Output Entry', 'bi-box-arrow-in-down', 'batch_output', 'batch_output'],
    ]],
    ['DISPATCH', null, [
        ['Packing Label (Batch)', 'bi-tags', 'packing_label', 'packing_label'],
        ['Dispatch QR Code', 'bi-qr-code', 'dispatch/qr', 'dispatch_qr'],
        ['Dispatch Record Entry', 'bi-truck', 'dispatch', 'dispatch'],
    ]],
    ['MASTERS', null, [
        ['Parties (Supplier/Customer)', 'bi-people', 'parties', 'parties'],
        ['Items / Products', 'bi-capsule', 'items', 'items'],
        ['Stock Ledger', 'bi-list-columns-reverse', 'stock_ledger', 'stock_ledger'],
    ]],
];
if (is_admin()) {
    $navMenu[] = ['ADMIN', null, [
        ['Users', 'bi-person-gear', 'users', 'users'],
        ['Company Settings', 'bi-sliders', 'settings', 'settings'],
    ]];
}
$initials = strtoupper(substr($navUser['name'], 0, 1) . (strpos($navUser['name'], ' ') ? substr($navUser['name'], strpos($navUser['name'], ' ') + 1, 1) : ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
    <link rel="icon" href="<?= url('assets/img/logo-mark.svg') ?>">
    <link rel="stylesheet" href="<?= url('assets/vendor/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/vendor/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <script>
        // Apply saved sidebar state before page paints (no flicker)
        if (localStorage.getItem('fv-sb-mini') === '1') document.documentElement.classList.add('sb-mini');
    </script>
</head>
<body>
<!-- Top progress bar (shown while next page loads) -->
<div id="topLoader"></div>

<!-- First paint loader -->
<div id="pageLoader">
    <div class="loader-mark"><img src="<?= url('assets/img/logo-mark.svg') ?>" alt=""><span class="ring"></span></div>
</div>

<?php if ($showWelcome): ?>
<div id="welcomeSplash">
    <div class="ws-inner">
        <div class="loader-mark big"><img src="<?= url('assets/img/logo-mark.svg') ?>" alt=""><span class="ring"></span></div>
        <h2>Welcome back, <?= e($navUser['name']) ?>!</h2>
        <p>Preparing your dashboard&hellip;</p>
        <div class="ws-bar"><span></span></div>
    </div>
</div>
<?php endif; ?>

<div class="app-wrap">
    <aside class="sidebar" id="sidebar">
        <a href="<?= url() ?>" class="brand">
            <img src="<?= url('assets/img/logo-wide.png') ?>" alt="Fenvitta Lifesciences LLP" class="brand-full">
            <img src="<?= url('assets/img/logo-mark.svg') ?>" alt="Fenvitta" class="brand-mark">
        </a>
        <div class="side-search">
            <i class="bi bi-search"></i>
            <input type="text" id="menuSearch" placeholder="Search menu..." autocomplete="off">
            <button type="button" id="menuSearchClear" title="Clear"><i class="bi bi-x-lg"></i></button>
        </div>
        <nav class="side-nav">
            <div class="nav-empty" id="menuEmpty"><i class="bi bi-search"></i>No menu found</div>
            <?php foreach ($navMenu as $navItem): ?>
                <?php if (is_array($navItem[2])): ?>
                    <div class="nav-section" data-section="<?= e($navItem[0]) ?>"><span><?= e($navItem[0]) ?></span></div>
                    <?php foreach ($navItem[2] as $navSub): ?>
                        <a class="nav-link <?= $active === $navSub[3] ? 'active' : '' ?>" href="<?= url($navSub[2]) ?>" data-title="<?= e($navSub[0]) ?>" data-section="<?= e($navItem[0]) ?>">
                            <i class="bi <?= e($navSub[1]) ?>"></i><span><?= e($navSub[0]) ?></span>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <a class="nav-link <?= $active === $navItem[3] ? 'active' : '' ?>" href="<?= url($navItem[2]) ?>" data-title="<?= e($navItem[0]) ?>" data-section="">
                        <i class="bi <?= e($navItem[1]) ?>"></i><span><?= e($navItem[0]) ?></span>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
        <div class="side-foot"><i class="bi bi-calendar-range"></i><span> FY <?= e(fy_label()) ?> &middot; v<?= APP_VERSION ?></span></div>
    </aside>
    <div class="sidebar-backdrop" id="sbBackdrop"></div>

    <main class="main">
        <header class="topbar no-print">
            <button class="sb-toggle" id="sbToggle" type="button" title="Toggle sidebar">
                <span></span><span></span><span></span>
            </button>
            <div>
                <h1 class="page-title"><?= e($pageTitle) ?></h1>
                <div class="crumb"><a href="<?= url() ?>" class="text-reset text-decoration-none"><i class="bi bi-house-door"></i> Home</a> <i class="bi bi-chevron-right"></i> <?= e($pageTitle) ?></div>
            </div>
            <div class="ms-auto d-flex align-items-center gap-3">
                <div class="info-pill">
                    <div class="ip-item">
                        <span class="ip-icon red"><i class="bi bi-clock"></i></span>
                        <span>Time : <b id="liveClock"><?= date('h:i:s A') ?></b></span>
                    </div>
                    <div class="ip-item">
                        <span class="ip-icon green"><i class="bi bi-calendar-check"></i></span>
                        <span>Day : <b id="liveDay"><?= date('l') ?></b></span>
                    </div>
                    <div class="ip-item">
                        <span class="ip-icon blue"><i class="bi bi-person-check"></i></span>
                        <span>Logged in as : <b><?= e($navUser['name']) ?> (@<?= e($navUser['username']) ?>)</b></span>
                    </div>
                </div>
                <div class="dropdown">
                    <button class="btn-user dropdown-toggle" data-bs-toggle="dropdown">
                        <span class="avatar"><?= e($initials) ?></span>
                        <span class="d-none d-sm-inline"><?= e($navUser['name']) ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 anim-drop">
                        <li><span class="dropdown-item-text small text-muted">Signed in as <b><?= e($navUser['username']) ?></b> (<?= e(ucfirst($navUser['role'])) ?>)</span></li>
                        <li><a class="dropdown-item" href="<?= url('profile') ?>"><i class="bi bi-key"></i> Change Password</a></li>
                        <?php if (is_admin()): ?><li><a class="dropdown-item" href="<?= url('settings') ?>"><i class="bi bi-sliders"></i> Settings</a></li><?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= url('logout') ?>"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>
        <div class="content">
