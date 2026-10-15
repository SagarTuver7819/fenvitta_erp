<?php
require_once __DIR__ . '/../../includes/functions.php';

if (current_user()) redirect('');

$error = '';
$loginAs = post('login_as') === 'user' ? 'user' : 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $u = row('SELECT * FROM users WHERE username=? AND is_active=1', [strtolower(post('username'))]);

    if (!$u || !password_verify((string)post('password'), $u['password'])) {
        $error = 'Invalid Credential ID or Encryption Key.';
    } elseif ($loginAs === 'admin' && $u['role'] !== 'admin') {
        $error = 'This account does not have Admin access. Please choose "User Login".';
    } else {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int)$u['id'], 'name' => $u['name'], 'username' => $u['username'], 'role' => $u['role']];
        $_SESSION['welcome'] = 1;
        redirect('');
    }
}

// Molecule ring (benzene) drawn in SVG - used as rotating background element
$molecule = '<svg viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="3">'
    . '<polygon points="50,14 81,32 81,68 50,86 19,68 19,32"/><circle cx="50" cy="50" r="18"/>'
    . '<line x1="50" y1="14" x2="50" y2="2"/><line x1="81" y1="32" x2="92" y2="25"/><line x1="81" y1="68" x2="92" y2="75"/>'
    . '<line x1="50" y1="86" x2="50" y2="98"/><line x1="19" y1="68" x2="8" y2="75"/><line x1="19" y1="32" x2="8" y2="25"/>'
    . '<circle cx="50" cy="2" r="2.5" fill="currentColor"/><circle cx="92" cy="25" r="2.5" fill="currentColor"/><circle cx="92" cy="75" r="2.5" fill="currentColor"/>'
    . '<circle cx="50" cy="98" r="2.5" fill="currentColor"/><circle cx="8" cy="75" r="2.5" fill="currentColor"/><circle cx="8" cy="25" r="2.5" fill="currentColor"/></svg>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | <?= e(APP_NAME) ?></title>
    <link rel="icon" href="<?= url('assets/img/logo-mark.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= url('assets/vendor/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/vendor/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
<div class="auth-page">

    <!-- Animated background -->
    <div class="auth-bg">
        <div class="mol m1"><?= $molecule ?></div>
        <div class="mol m2"><?= $molecule ?></div>
        <div class="mol m3"><?= $molecule ?></div>
        <div class="mol m4"><?= $molecule ?></div>
        <i class="bi bi-droplet-fill drop"></i>
        <i class="bi bi-droplet-fill drop"></i>
        <i class="bi bi-droplet-fill drop"></i>
        <i class="bi bi-droplet-fill drop"></i>
        <i class="bi bi-capsule cap c1"></i>
        <i class="bi bi-capsule-pill cap c2"></i>
        <i class="bi bi-capsule cap c3"></i>
        <span class="dot d1"></span><span class="dot d2"></span><span class="dot d3"></span><span class="dot d4"></span>
        <i class="bi bi-gear-wide-connected gear"></i>
    </div>
    <!-- Production line: reactors -> filling nozzle -> conveyor -> QC scanner -->
    <div class="auth-plant" aria-hidden="true">
        <?php foreach (['r1', 'r2'] as $r): ?>
            <div class="reactor <?= $r ?>">
                <span class="liquid"></span>
                <span class="stir"></span>
                <span class="bub b1"></span><span class="bub b2"></span><span class="bub b3"></span>
            </div>
        <?php endforeach; ?>
        <div class="pipe"><span class="flow"></span></div>
        <div class="conveyor">
            <div class="nozzle"><span class="fill-drop"></span><span class="fill-drop"></span></div>
            <div class="qc-gate"><span class="beam"></span><b>QC</b></div>
            <span class="pack drum" style="--delay:0s"><i>API</i></span>
            <span class="pack bottle" style="--delay:-3s"></span>
            <span class="pack box" style="--delay:-6s"></span>
            <span class="pack drum" style="--delay:-9s"><i>API</i></span>
            <span class="pack bag" style="--delay:-12s"></span>
            <span class="pack bottle" style="--delay:-15s"></span>
            <div class="belt"></div>
        </div>
    </div>

    <!-- Login card -->
    <div class="auth-card<?= $error ? ' shake' : '' ?>">
        <span class="corner tl"></span><span class="corner br"></span>
        <span class="hub-badge">Inventory Hub</span>

        <div class="since">API Manufacturer</div>
        <img src="<?= url('assets/img/logo-wide.png') ?>" alt="Fenvitta Lifesciences LLP" class="logo">
        <h1>Inventory Management System</h1>
        <div class="sub">The Core of Every Cure &middot; Precision Pharma ERP</div>

        <?php if ($error): ?>
            <div class="auth-alert"><i class="bi bi-exclamation-octagon-fill"></i> <?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" autocomplete="off" id="loginForm">
            <?= csrf_field() ?>
            <span class="fl">Login as</span>
            <div class="role-grid">
                <label class="role-opt">
                    <input type="radio" name="login_as" value="admin" <?= $loginAs === 'admin' ? 'checked' : '' ?>>
                    <span class="box">
                        <span class="radio"></span>
                        <span class="ico"><i class="bi bi-person-gear"></i></span>
                        <b>Admin Login</b>
                        <small>Admin &amp; management</small>
                    </span>
                </label>
                <label class="role-opt">
                    <input type="radio" name="login_as" value="user" <?= $loginAs === 'user' ? 'checked' : '' ?>>
                    <span class="box">
                        <span class="radio"></span>
                        <span class="ico"><i class="bi bi-person-badge"></i></span>
                        <b>User Login</b>
                        <small>Store &amp; production staff</small>
                    </span>
                </label>
            </div>

            <label class="fl" for="username">Credential ID</label>
            <div class="auth-input">
                <i class="bi bi-shield-lock"></i>
                <input type="text" name="username" id="username" placeholder="Enter your username" required autofocus value="<?= e(post('username')) ?>">
            </div>

            <label class="fl" for="password">Encryption Key</label>
            <div class="auth-input">
                <i class="bi bi-lock"></i>
                <input type="password" name="password" id="password" placeholder="Enter your password" required>
                <button type="button" class="eye" id="togglePass" tabindex="-1" title="Show / hide password"><i class="bi bi-eye"></i></button>
            </div>

            <button type="submit" class="btn-auth" id="loginBtn">
                <span class="idle">Validate &amp; Initiate Session <i class="bi bi-lightning-charge-fill"></i></span>
                <span class="busy"><span class="spin"></span> Authenticating...</span>
            </button>
        </form>

        <div class="auth-credit">
            <small>Designed &amp; Developed By</small>
            <a href="https://www.oceaninfotech.co.in/" target="_blank" rel="noopener">Ocean Infotech</a>
        </div>
    </div>

    <div class="auth-copy">&copy; <?= date('Y') ?> Fenvitta Lifesciences LLP &middot; Secure Inventory Portal</div>
</div>

<!-- Full screen loader while signing in -->
<div id="loginLoader">
    <div>
        <div class="loader-mark big mx-auto"><img src="<?= url('assets/img/logo-mark.svg') ?>" alt=""><span class="ring"></span></div>
        <p>Initiating secure session&hellip;</p>
    </div>
</div>

<script>
document.getElementById('togglePass').addEventListener('click', function () {
    var input = document.getElementById('password');
    var show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    this.innerHTML = show ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
});

document.getElementById('loginForm').addEventListener('submit', function (e) {
    var form = this;
    if (form.dataset.sent) return;
    e.preventDefault();
    form.dataset.sent = '1';
    document.getElementById('loginBtn').classList.add('loading');
    // Button shows "Authenticating...", then full screen loader, then submit
    setTimeout(function () { document.getElementById('loginLoader').classList.add('show'); }, 450);
    setTimeout(function () { form.submit(); }, 1000);
});

window.addEventListener('pageshow', function () {
    document.getElementById('loginLoader').classList.remove('show');
    document.getElementById('loginBtn').classList.remove('loading');
    delete document.getElementById('loginForm').dataset.sent;
});
</script>
</body>
</html>
