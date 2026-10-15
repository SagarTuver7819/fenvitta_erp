<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name('FENVITTA_INV');
    session_start();
}

/* ---------------- Output helpers ---------------- */
function e($v): string
{
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
}

/** Indian number format: 12,34,567.89 */
function inr($n, int $dec = 2): string
{
    $n = (float)$n;
    $neg = $n < 0;
    $n = abs($n);
    $parts = explode('.', number_format($n, $dec, '.', ''));
    $int = $parts[0];
    $last3 = substr($int, -3);
    $rest = substr($int, 0, -3);
    if ($rest !== '' && $rest !== false) {
        $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
        $int = $rest . ',' . $last3;
    }
    $out = $int . ($dec > 0 ? '.' . $parts[1] : '');
    return ($neg ? '-' : '') . $out;
}

function money($n): string
{
    return '₹ ' . inr($n);
}

function qty($n, int $dec = 2): string
{
    $s = inr($n, $dec);
    return $dec > 0 ? rtrim(rtrim($s, '0'), '.') ?: '0' : $s;
}

function dmy(?string $d): string
{
    if (!$d || $d === '0000-00-00') return '';
    return date('d-m-Y', strtotime($d));
}

function today(): string
{
    return date('Y-m-d');
}

/* ---------------- URL helpers ---------------- */
/** Clean URL inside the project: url('purchase/add') -> /fenvita_project/purchase/add */
function url(string $path = ''): string
{
    return BASE_URL . ltrim($path, '/');
}

/** URL of a file inside assets/ with version (so browser loads new file after update) */
function asset(string $file): string
{
    return url('assets/' . ltrim($file, '/')) . '?v=' . APP_VERSION;
}

/** Full URL with domain (used inside QR codes) */
function full_url(string $path = ''): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . url($path);
}

/* ---------------- Request helpers ---------------- */
function post(string $k, $default = '')
{
    $v = $_POST[$k] ?? $default;
    return is_string($v) ? trim($v) : $v;
}

function get(string $k, $default = '')
{
    $v = $_GET[$k] ?? $default;
    return is_string($v) ? trim($v) : $v;
}

function num($v): float
{
    return (float)str_replace(',', '', (string)$v);
}

function date_or_null($v): ?string
{
    $v = trim((string)$v);
    return $v === '' ? null : date('Y-m-d', strtotime($v));
}

/** redirect('purchase') or redirect('purchase/edit/5') -> project URL. Full URLs are used as they are. */
function redirect(string $path): void
{
    $isFull = preg_match('#^(https?:)?/#', $path);
    header('Location: ' . ($isFull ? $path : url($path)));
    exit;
}

/** Delete / save handlers accept only POST requests */
function require_post(string $back): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect($back);
}

function flash(string $type, string $msg): void
{
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}

function get_flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/* ---------------- Auth / CSRF ---------------- */
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_admin(): bool
{
    return (current_user()['role'] ?? '') === 'admin';
}

function require_login(): void
{
    if (!current_user()) redirect('login');
}

function require_admin(): void
{
    if (!is_admin()) {
        flash('danger', 'Only administrator can perform this action.');
        redirect('');
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
}

function verify_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals(csrf_token(), (string)($_POST['_token'] ?? ''))) {
        http_response_code(419);
        die('Session expired. Please go back, refresh the page and try again.');
    }
}

/* ---------------- Settings ---------------- */
function setting(string $key, string $default = ''): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (rows('SELECT skey, svalue FROM settings') as $r) $cache[$r['skey']] = $r['svalue'];
    }
    return $cache[$key] ?? $default;
}

/* ---------------- Financial year ---------------- */
function fy_start(?string $date = null): string
{
    $t = strtotime($date ?: today());
    $y = (int)date('Y', $t);
    if ((int)date('n', $t) < 4) $y--;
    return "$y-04-01";
}

function fy_end(?string $date = null): string
{
    return date('Y-m-d', strtotime(fy_start($date) . ' +1 year -1 day'));
}

function fy_label(?string $date = null): string
{
    $y = (int)substr(fy_start($date), 0, 4);
    return substr((string)$y, 2) . '-' . substr((string)($y + 1), 2);
}

/** Next number like PREFIX/{n}/26-27 (sales) */
function next_sale_invoice(?string $date = null): string
{
    $p = setting('sale_prefix', 'FNV');
    $fy = fy_label($date);
    $max = 0;
    foreach (rows('SELECT invoice_no FROM sales WHERE invoice_no LIKE ?', ["$p/%/$fy"]) as $r) {
        $parts = explode('/', $r['invoice_no']);
        $max = max($max, (int)($parts[1] ?? 0));
    }
    return "$p/" . ($max + 1) . "/$fy";
}

/** Next number like PREFIX/26-27/001 */
function next_series(string $table, string $col, string $prefix, ?string $date = null): string
{
    $fy = fy_label($date);
    $max = 0;
    foreach (rows("SELECT `$col` v FROM `$table` WHERE `$col` LIKE ?", ["$prefix/$fy/%"]) as $r) {
        $parts = explode('/', $r['v']);
        $max = max($max, (int)end($parts));
    }
    return "$prefix/$fy/" . str_pad((string)($max + 1), 3, '0', STR_PAD_LEFT);
}

/* ---------------- Stock ---------------- */
function ledger_clear(string $refType, int $refId): void
{
    q('DELETE FROM stock_ledger WHERE ref_type=? AND ref_id=?', [$refType, $refId]);
}

function ledger_add(string $date, int $itemId, float $in, float $out, string $refType, int $refId, float $rate = 0, ?int $batchId = null, string $remarks = ''): void
{
    if ($in == 0 && $out == 0) return;
    insert('stock_ledger', [
        'trans_date' => $date, 'item_id' => $itemId, 'batch_id' => $batchId, 'ref_type' => $refType,
        'ref_id' => $refId, 'qty_in' => $in, 'qty_out' => $out, 'rate' => $rate, 'remarks' => mb_substr($remarks, 0, 250),
    ]);
}

function stock_qty(int $itemId): float
{
    return (float)val('SELECT COALESCE(SUM(qty_in-qty_out),0) FROM stock_ledger WHERE item_id=?', [$itemId]);
}

function item_avg_rate(int $itemId): float
{
    $r = row('SELECT SUM(qty_in*rate) v, SUM(qty_in) q FROM stock_ledger WHERE item_id=? AND qty_in>0 AND rate>0', [$itemId]);
    if ($r && (float)$r['q'] > 0) return round((float)$r['v'] / (float)$r['q'], 2);
    return (float)val('SELECT std_rate FROM items WHERE id=?', [$itemId]);
}

function batch_balance(int $batchId): float
{
    return (float)val('SELECT COALESCE(SUM(qty_in-qty_out),0) FROM stock_ledger WHERE batch_id=?', [$batchId]);
}

/** Qty sold (invoiced) but not yet dispatched, per item. */
function pending_dispatch_by_item(): array
{
    $sql = "SELECT s.item_id, SUM(GREATEST(s.sq - IFNULL(d.dq,0), 0)) pending
            FROM (SELECT sale_id, item_id, SUM(qty) sq FROM sale_items GROUP BY sale_id, item_id) s
            LEFT JOIN (SELECT d.sale_id, di.item_id, SUM(di.qty) dq FROM dispatch_items di
                       JOIN dispatches d ON d.id = di.dispatch_id WHERE d.sale_id IS NOT NULL
                       GROUP BY d.sale_id, di.item_id) d ON d.sale_id = s.sale_id AND d.item_id = s.item_id
            GROUP BY s.item_id";
    $out = [];
    foreach (rows($sql) as $r) $out[(int)$r['item_id']] = (float)$r['pending'];
    return $out;
}

/** FG batches with balance (for dropdowns / stock pages). */
function fg_batches(bool $onlyAvailable = true): array
{
    $sql = "SELECT b.*, i.name item_name, i.unit, i.cas_no, i.grade item_grade,
                   COALESCE(SUM(CASE WHEN l.ref_type='BATCH_OUTPUT' THEN l.qty_in ELSE 0 END),0) produced,
                   COALESCE(SUM(CASE WHEN l.ref_type='DISPATCH' THEN l.qty_out ELSE 0 END),0) dispatched,
                   COALESCE(SUM(l.qty_in - l.qty_out),0) balance
            FROM production_batches b
            JOIN items i ON i.id = b.item_id
            LEFT JOIN stock_ledger l ON l.batch_id = b.id
            WHERE b.status <> 'Cancelled'
            GROUP BY b.id";
    if ($onlyAvailable) $sql .= ' HAVING balance > 0';
    $sql .= ' ORDER BY b.mfg_date, b.batch_no';
    return rows($sql);
}

/** <option> list of production batches. $onlyOpen = hide Completed / Cancelled */
function batch_options($selected = null, bool $onlyOpen = false): string
{
    $sql = "SELECT b.id, b.batch_no, b.status, i.name item_name FROM production_batches b JOIN items i ON i.id = b.item_id WHERE b.status <> 'Cancelled'";
    if ($onlyOpen) $sql .= " AND b.status <> 'Completed'";
    $html = '<option value="">-- Select Batch --</option>';
    foreach (rows($sql . ' ORDER BY b.id DESC') as $b) {
        $html .= '<option value="' . $b['id'] . '"' . ((string)$selected === (string)$b['id'] ? ' selected' : '') . '>'
            . e($b['batch_no'] . ' - ' . $b['item_name'] . ' (' . $b['status'] . ')') . '</option>';
    }
    return $html;
}

/** Suggested next batch no, e.g. FV26SLF005 (same style as Excel) */
function next_batch_no(array $item): string
{
    $short = [
        'sucralfate' => 'SLF', 'vonoprazan' => 'VPF', 'bempedoic' => 'BPA', 'bampedoic' => 'BPA', 'ferric' => 'FCM',
    ];
    $code = strtoupper(substr(preg_replace('/[^a-z]/i', '', $item['name']), 0, 3));
    foreach ($short as $word => $abbr) {
        if (stripos($item['name'], $word) !== false) $code = $abbr;
    }
    $prefix = 'FV' . date('y') . $code;
    $max = 0;
    foreach (rows('SELECT batch_no FROM production_batches WHERE batch_no LIKE ?', [$prefix . '%']) as $r) {
        $max = max($max, (int)substr($r['batch_no'], strlen($prefix)));
    }
    return $prefix . str_pad((string)($max + 1), 3, '0', STR_PAD_LEFT);
}

/* ---------------- Payments ---------------- */
function paid_amount(string $type, int $refId): float
{
    return (float)val('SELECT COALESCE(SUM(amount+tds),0) FROM payments WHERE pay_type=? AND ref_id=?', [$type, $refId]);
}

/* ---------------- Lookups ---------------- */
function parties_for(string $kind): array
{
    $types = $kind === 'customer' ? "('Customer','Both')" : "('Supplier','Both','Job Worker','Transporter')";
    return rows("SELECT id, name, city, payment_terms FROM parties WHERE is_active=1 AND party_type IN $types ORDER BY name");
}

function items_for(array $types = []): array
{
    if (!$types) return rows('SELECT * FROM items WHERE is_active=1 ORDER BY item_type, name');
    $in = implode(',', array_fill(0, count($types), '?'));
    return rows("SELECT * FROM items WHERE is_active=1 AND item_type IN ($in) ORDER BY name", $types);
}

/** <option> list for item dropdowns (data-* used by assets/js/app.js to auto fill unit / gst / rate) */
function item_options(array $items, $selected = null, bool $withStock = false): string
{
    $html = '<option value="">-- Select --</option>';
    $group = '';
    foreach ($items as $it) {
        if ($group !== $it['item_type']) {
            if ($group !== '') $html .= '</optgroup>';
            $group = $it['item_type'];
            $html .= '<optgroup label="' . e(item_type_label($group)) . '">';
        }
        $extra = '';
        if ($withStock) {
            $extra = ' data-stock="' . qty(stock_qty((int)$it['id'])) . '" data-rate="' . item_avg_rate((int)$it['id']) . '"';
        } else {
            $extra = ' data-rate="' . e($it['std_rate']) . '"';
        }
        $html .= '<option value="' . $it['id'] . '" data-unit="' . e($it['unit']) . '" data-gst="' . e((float)$it['gst_rate']) . '" data-grade="' . e($it['grade']) . '"'
            . $extra . ((string)$selected === (string)$it['id'] ? ' selected' : '') . '>' . e($it['name']) . '</option>';
    }
    if ($group !== '') $html .= '</optgroup>';
    return $html;
}

function item_type_label(string $t): string
{
    return [
        'RM' => 'Raw Material', 'PM' => 'Packing Material', 'FG' => 'Finished Goods',
        'CONSUMABLE' => 'Consumable / Others', 'SERVICE' => 'Service / Expense',
    ][$t] ?? $t;
}

/** Small delete button (POST form) that calls {module}/delete with the record id */
function delete_button(string $module, int $id, string $confirm = 'Delete this entry?'): string
{
    return '<form method="post" action="' . url($module . '/delete') . '" class="d-inline" data-confirm="' . e($confirm) . '">'
        . csrf_field() . '<input type="hidden" name="id" value="' . $id . '">'
        . '<button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button></form>';
}

function status_badge(string $s): string
{
    $map = [
        'Paid' => 'success', 'Received' => 'success', 'Completed' => 'success', 'Approved' => 'success', 'Dispatched' => 'success',
        'Partial' => 'warning', 'In Process' => 'warning', 'Pending' => 'secondary', 'Planned' => 'info',
        'Overdue' => 'danger', 'Cancelled' => 'dark', 'Rejected' => 'danger', 'Unpaid' => 'secondary', 'Not Dispatched' => 'secondary',
    ];
    return '<span class="badge text-bg-' . ($map[$s] ?? 'secondary') . '">' . e($s) . '</span>';
}

/* ---------------- Amount in words (Indian) ---------------- */
function amount_in_words(float $amount): string
{
    $amount = round($amount, 2);
    $rupees = (int)floor($amount);
    $paise = (int)round(($amount - $rupees) * 100);
    $w = num_to_words_in($rupees) ?: 'Zero';
    $s = $w . ' Rupees';
    if ($paise > 0) $s .= ' and ' . num_to_words_in($paise) . ' Paise';
    return $s . ' Only';
}

function num_to_words_in(int $n): string
{
    $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve',
        'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
    $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
    $two = function (int $x) use ($ones, $tens): string {
        if ($x < 20) return $ones[$x];
        return trim($tens[intdiv($x, 10)] . ' ' . $ones[$x % 10]);
    };
    if ($n === 0) return '';
    $parts = [];
    foreach ([[10000000, 'Crore'], [100000, 'Lakh'], [1000, 'Thousand'], [100, 'Hundred']] as [$div, $label]) {
        if ($n >= $div) {
            $c = intdiv($n, $div);
            $parts[] = ($div === 10000000 ? num_to_words_in($c) : $two($c)) . ' ' . $label;
            $n %= $div;
        }
    }
    if ($n > 0) $parts[] = $two($n);
    return implode(' ', $parts);
}
