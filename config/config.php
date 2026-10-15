<?php
/*
 * Server specific values (database login etc.) are kept in config/config.local.php
 * That file is NOT uploaded to GitHub. Copy config.local.example.php -> config.local.php
 * on every new server and fill the details.
 */
if (is_file(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

/* ---------------- Database (defaults = local XAMPP) ---------------- */
defined('DB_HOST') || define('DB_HOST', 'localhost');
defined('DB_PORT') || define('DB_PORT', 3306);
defined('DB_NAME') || define('DB_NAME', 'fenvita_inventory');
defined('DB_USER') || define('DB_USER', 'root');
defined('DB_PASS') || define('DB_PASS', '');

/* ---------------- Application ---------------- */
define('APP_NAME', 'Fenvitta Inventory');
define('APP_VERSION', '2.0.1');

// Absolute path of project root on disk (used for include / require)
define('ROOT_PATH', dirname(__DIR__));

// Web path of the project, detected automatically:
//   http://localhost/fenvita_project/      ->  '/fenvita_project/'
//   https://fenvitta.oceanhub.co.in/       ->  '/'
if (!defined('BASE_URL')) {
    $docRoot = str_replace('\\', '/', (string)realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
    $appRoot = str_replace('\\', '/', ROOT_PATH);
    $sub = ($docRoot !== '' && stripos($appRoot, $docRoot) === 0) ? trim(substr($appRoot, strlen($docRoot)), '/') : '';
    define('BASE_URL', $sub === '' ? '/' : '/' . $sub . '/');
}

date_default_timezone_set('Asia/Kolkata');

/* ---------------- Dropdown lists ---------------- */
// Categories used in Purchase Entry (same as Excel "PURCHASE OUTSTANDING SHEET")
define('PURCHASE_CATEGORIES', ['Raw Material', 'Packing Material', 'Job Work', 'Transport', 'Analysis', 'Machinery', 'Rent', 'Salary', 'Other']);
define('PAYMENT_MODES', ['Bank', 'Cash', 'Cheque', 'PDC', 'UPI', 'NEFT/RTGS']);
define('PLANTS', ['Altitude Interchem - Panoli (Job Work)', 'Fenvitta - Kothariya, Rajkot', 'Other']);
