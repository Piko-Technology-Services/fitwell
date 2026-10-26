<?php
/**
 * Fitwell bootstrap: config, session, database, helpers.
 * Compatible with PHP 7.4+ (XAMPP).
 */
declare(strict_types=1);

define('APP_DIR', __DIR__);
define('ROOT_DIR', dirname(__DIR__));

$GLOBALS['FW_CONFIG'] = require APP_DIR . '/config.php';
// Server-specific overrides (database password, site URL…) live in config.local.php,
// which is never overwritten when you upload a new version of the site.
if (is_file(APP_DIR . '/config.local.php')) {
    $GLOBALS['FW_CONFIG'] = array_replace_recursive($GLOBALS['FW_CONFIG'], require APP_DIR . '/config.local.php');
}

// Never show PHP errors to visitors on the live site (they are still logged)
$isLocal = in_array($_SERVER['HTTP_HOST'] ?? 'localhost', ['localhost', '127.0.0.1'], true) || PHP_SAPI === 'cli' || PHP_SAPI === 'cli-server';
ini_set('display_errors', (!empty($GLOBALS['FW_CONFIG']['debug']) || $isLocal) ? '1' : '0');
ini_set('log_errors', '1');
$GLOBALS['FW_IMAGES'] = require APP_DIR . '/images.php';

date_default_timezone_set(cfg('timezone', 'UTC'));

/* ---------- Session ---------- */
if (session_status() !== PHP_SESSION_ACTIVE && PHP_SAPI !== 'cli') {
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_name('fitwell_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => base_path() . '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/* ---------- Config ---------- */
function cfg(string $key, $default = null)
{
    $value = $GLOBALS['FW_CONFIG'];
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

/* ---------- URLs ---------- */
function base_path(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    $root = realpath(ROOT_DIR);
    if ($docRoot && $root && strpos($root, $docRoot) === 0) {
        $rel = str_replace('\\', '/', substr($root, strlen($docRoot)));
        $segments = array_filter(explode('/', $rel), 'strlen');
        $base = $segments ? '/' . implode('/', array_map('rawurlencode', $segments)) : '';
    } else {
        $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
        $script = preg_replace('#/admin$#', '', $script);
        $base = rtrim($script, '/');
    }
    return $base;
}

function url(string $path = ''): string
{
    return base_path() . '/' . ltrim($path, '/');
}

/** Absolute site origin, e.g. https://fitwellstudio.com (auto-detected unless set in config). */
function site_origin(): string
{
    $configured = rtrim((string) cfg('site_url', ''), '/');
    if ($configured !== '') {
        return $configured;
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    $host = preg_replace('/[^A-Za-z0-9.\-:]/', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
    return ($https ? 'https' : 'http') . '://' . $host . base_path();
}

/** Absolute URL for a site path ('' = homepage). */
function abs_url(string $path = ''): string
{
    if (preg_match('#^https?://#', $path)) {
        return $path;
    }
    $origin = site_origin();
    // base_path() is already part of an auto-detected origin; strip it from url() results
    $base = base_path();
    if ($base !== '' && strpos($path, $base . '/') === 0) {
        $path = substr($path, strlen($base));
    }
    return $origin . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    $file = ROOT_DIR . '/assets/' . $path;
    $v = is_file($file) ? '?v=' . filemtime($file) : '';
    return url('assets/' . $path) . $v;
}

function current_page(): string
{
    return basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
}

function redirect(string $path): void
{
    header('Location: ' . (preg_match('#^(https?:)?/#', $path) ? $path : url($path)));
    exit;
}

/** Only allow redirects back into this site. */
function safe_next(?string $next, string $fallback = 'account.php'): string
{
    if (!$next) {
        return $fallback;
    }
    if (preg_match('#^(admin/)?[a-z0-9\-]+\.php([?\#][^\s<>"\'\\\\]*)?$#i', $next)) {
        return $next;
    }
    return $fallback;
}

/* ---------- Output helpers ---------- */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Real value if configured, otherwise a clearly marked placeholder. */
function info(string $key, string $placeholderLabel): string
{
    $value = cfg($key);
    if ($value !== null && $value !== '') {
        return e($value);
    }
    return placeholder($placeholderLabel);
}

function placeholder(string $label): string
{
    if (!cfg('show_placeholder_markers', true)) {
        return '';
    }
    return '<span class="ph" title="Placeholder — set the real value in app/config.php">' . e($label) . '</span>';
}

/* ---------- WhatsApp ---------- */
function whatsapp_url(?string $message = null): ?string
{
    $num = preg_replace('/\D/', '', (string) cfg('company.whatsapp'));
    if ($num === '') {
        return null;
    }
    $message = $message ?? cfg('company.whatsapp_message', '');
    return 'https://wa.me/' . $num . ($message ? '?text=' . rawurlencode($message) : '');
}

function whatsapp_icon(string $class = 'icon'): string
{
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 2.2a9.7 9.7 0 0 0-8.4 14.6L2.3 21.7l5-1.3A9.7 9.7 0 1 0 12 2.2zm0 17.7c-1.5 0-3-.4-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8 8 0 1 1 12 19.9zm4.4-6c-.2-.1-1.4-.7-1.7-.8-.2-.1-.4-.1-.5.1l-.8 1c-.1.2-.3.2-.5.1a6.6 6.6 0 0 1-3.3-2.9c-.2-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.5-.4h-.5a.9.9 0 0 0-.7.3 2.8 2.8 0 0 0-.9 2.1 4.9 4.9 0 0 0 1 2.6 11.2 11.2 0 0 0 4.3 3.8c1.6.7 2.2.7 3 .6.5-.1 1.4-.6 1.6-1.1.2-.6.2-1 .1-1.1l-.5-.3z"/></svg>';
}

function price_label(?array $plan): string
{
    if ($plan && $plan['price'] !== null && $plan['price'] !== '') {
        return '<span class="price__amount">' . e(cfg('currency_prefix', '') . $plan['price']) . '</span>';
    }
    return '<span class="price__amount price__amount--tbc">' . placeholder('Pricing') . '</span>';
}

/* ---------- Images ---------- */
function image_src(string $src, int $w, ?int $h = null): string
{
    if (strpos($src, 'unsplash:') === 0) {
        $id = substr($src, 9);
        $q = 'auto=format&fit=crop&q=72&w=' . $w . ($h ? '&h=' . $h : '');
        return 'https://images.unsplash.com/photo-' . $id . '?' . $q;
    }
    if (strpos($src, 'media:') === 0) {
        return url('media.php?id=' . (int) substr($src, 6));
    }
    if (preg_match('#^https?://#', $src)) {
        return $src;
    }
    return url($src);
}

/** Absolute 1200x630 share image for a library key / class image, or the default branded card. */
function share_image(?string $key = null): string
{
    $lib = $GLOBALS['FW_IMAGES'];
    $src = $key ? ($lib[$key]['src'] ?? $key) : null;
    if ($src && strpos($src, 'unsplash:') === 0) {
        // Plain JPG (no auto=format) so every chat app can render the preview
        return 'https://images.unsplash.com/photo-' . substr($src, 9) . '?fm=jpg&fit=crop&crop=faces,center&q=80&w=1200&h=630';
    }
    if ($src && strpos($src, 'media:') === 0) {
        return abs_url(image_src($src, 0));
    }
    return abs_url(asset('img/og-default.jpg'));
}

/** Animated brand-gradient placeholder used wherever no photo is set. */
function media_placeholder(string $label = '', array $attrs = []): string
{
    $cls = 'media-ph' . (isset($attrs['class']) ? ' ' . $attrs['class'] : '');
    return '<span class="' . e($cls) . '" role="img" aria-label="' . e($label ?: 'Fitwell') . '">'
        . '<span class="media-ph__mark" aria-hidden="true" style="background-image:url(\'' . e(asset('img/logo-mark.png')) . '\')"></span></span>';
}

/**
 * Responsive <img>. $key is a key from app/images.php, a local path or a URL.
 * $ratio = width/height used for cropping remote images.
 */
function img(string $key, string $sizes = '100vw', array $attrs = [], ?float $ratio = null, int $maxW = 1800): string
{
    $lib = $GLOBALS['FW_IMAGES'];
    if (isset($lib[$key])) {
        $item = $lib[$key];
    } elseif (preg_match('#^(unsplash:|media:|https?://)|[/.]#', $key)) {
        $item = ['src' => $key, 'alt' => '']; // a path, URL or uploaded photo
    } else {
        $item = ['src' => null, 'alt' => '']; // unknown/removed key -> gradient
    }
    $src = (string) ($item['src'] ?? '');
    $alt = $attrs['alt'] ?? $item['alt'] ?? '';
    unset($attrs['alt']);
    if ($src === '') {
        return media_placeholder($alt ?: ($item['alt'] ?? ''), $attrs);
    }

    $widths = array_values(array_filter([480, 800, 1200, 1600, 2000], function ($w) use ($maxW) { return $w <= $maxW; }));
    $isRemote = strpos($src, 'unsplash:') === 0;
    $html = '<img src="' . e(image_src($src, $isRemote ? 1200 : 0, ($isRemote && $ratio) ? (int) round(1200 / $ratio) : null)) . '"';
    if ($isRemote) {
        $set = [];
        foreach ($widths as $w) {
            $set[] = image_src($src, $w, $ratio ? (int) round($w / $ratio) : null) . ' ' . $w . 'w';
        }
        $html .= ' srcset="' . e(implode(', ', $set)) . '" sizes="' . e($sizes) . '"';
    }
    if (!isset($attrs['loading'])) {
        $attrs['loading'] = 'lazy';
    }
    $attrs['decoding'] = 'async';
    foreach ($attrs as $k => $v) {
        $html .= ' ' . $k . '="' . e($v) . '"';
    }
    return $html . ' alt="' . e($alt) . '">';
}

/* ---------- Icons (minimal line set) ---------- */
function icon(string $name, string $class = 'icon'): string
{
    $paths = [
        'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'arrow-left' => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
        'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'pin'      => '<path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
        'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 4.5-6 8-6s6.5 2 8 6"/>',
        'users'    => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c1-3.5 3.5-5.5 6.5-5.5s5.5 2 6.5 5.5"/><path d="M16 4.6a3.5 3.5 0 0 1 0 6.8M18 14.8c1.8.7 3 2.5 3.5 5.2"/>',
        'heart'    => '<path d="M12 20s-7.5-4.6-9-9.5C2 7 4.3 4.5 7.2 4.5c2 0 3.6 1.1 4.8 2.8 1.2-1.7 2.8-2.8 4.8-2.8C19.7 4.5 22 7 21 10.5 19.5 15.4 12 20 12 20z"/>',
        'pulse'    => '<path d="M3 12h4l2.5-6 4 12 2.5-6H21"/>',
        'spark'    => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M5.6 5.6l2.8 2.8M15.6 15.6l2.8 2.8M5.6 18.4l2.8-2.8M15.6 8.4l2.8-2.8"/>',
        'calendar' => '<rect x="3.5" y="5" width="17" height="15" rx="3"/><path d="M3.5 10h17M8 3v4M16 3v4"/>',
        'check'    => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
        'x'        => '<path d="M6 6l12 12M18 6L6 18"/>',
        'menu'     => '<path d="M4 7h16M4 12h16M4 17h10"/>',
        'mail'     => '<rect x="3" y="5" width="18" height="14" rx="3"/><path d="M4 7l8 6 8-6"/>',
        'phone'    => '<path d="M6.5 3.5h3l1.5 4.5-2 1.5a12 12 0 0 0 5.5 5.5l1.5-2 4.5 1.5v3a2 2 0 0 1-2 2A16.5 16.5 0 0 1 4.5 5.5a2 2 0 0 1 2-2z"/>',
        'eye'      => '<path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"/><circle cx="12" cy="12" r="3"/>',
        'building' => '<rect x="4" y="3.5" width="16" height="17" rx="2"/><path d="M8.5 8h2M13.5 8h2M8.5 12h2M13.5 12h2M10 20.5v-4h4v4"/>',
        'logout'   => '<path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3M10 16l-4-4 4-4M6 12h10"/>',
        'level'    => '<path d="M5 19v-4M10 19v-8M15 19V8M20 19V4"/>',
        'sun'      => '<circle cx="12" cy="12" r="4"/><path d="M12 2.5v2M12 19.5v2M2.5 12h2M19.5 12h2M5.3 5.3l1.4 1.4M17.3 17.3l1.4 1.4M5.3 18.7l1.4-1.4M17.3 6.7l1.4-1.4"/>',
        'chat'     => '<path d="M4 5.5h16v10H9l-5 4z"/>',
        'shield'   => '<path d="M12 3l7.5 3v5.5c0 4.5-3.2 8-7.5 9.5-4.3-1.5-7.5-5-7.5-9.5V6z"/>',
    ];
    $p = $paths[$name] ?? '';
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $p . '</svg>';
}

/* ---------- Flash messages ---------- */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/* ---------- Old input for forms ---------- */
function old(string $key, string $default = ''): string
{
    return e($_SESSION['old'][$key] ?? $default);
}

function keep_old(array $data): void
{
    unset($data['password'], $data['password_confirm'], $data['_csrf']);
    $_SESSION['old'] = $data;
}

function clear_old(): void
{
    unset($_SESSION['old']);
}

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['_csrf'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        flash('error', 'Your session expired. Please try that again.');
        redirect(safe_next($_POST['return'] ?? null, 'index.php'));
    }
}

function require_post(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        redirect('');
    }
}

/* ---------- Database ---------- */
function db(): PDO
{
    static $pdo = null;
    if ($pdo) {
        return $pdo;
    }
    $c = cfg('db');
    $opts = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    if ($c['driver'] === 'sqlite') {
        $pdo = new PDO('sqlite:' . $c['sqlite_path'], null, null, $opts);
        $pdo->exec('PRAGMA foreign_keys = ON');
    } else {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $c['host'], $c['port'], $c['name']);
        $pdo = new PDO($dsn, $c['user'], $c['pass'], $opts);
    }
    return $pdo;
}

function is_mysql(): bool
{
    return cfg('db.driver') !== 'sqlite';
}

/** True when the database is reachable and installed. */
function db_ready(): bool
{
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }
    try {
        db()->query('SELECT 1 FROM classes LIMIT 1');
        $ready = true;
    } catch (Throwable $e) {
        $ready = false;
    }
    return $ready;
}

function now_sql(): string
{
    return date('Y-m-d H:i:s');
}

/* ---------- Auth ---------- */
function current_user(): ?array
{
    static $user = false;
    if ($user !== false) {
        return $user;
    }
    $user = null;
    if (!empty($_SESSION['uid']) && db_ready()) {
        $st = db()->prepare('SELECT id, name, email, phone, role, created_at FROM users WHERE id = ?');
        $st->execute([(int) $_SESSION['uid']]);
        $user = $st->fetch() ?: null;
        if (!$user) {
            unset($_SESSION['uid']);
        }
    }
    return $user;
}

function is_admin(): bool
{
    $u = current_user();
    return $u && $u['role'] === 'admin';
}

function require_login(string $next = 'account.php'): array
{
    $u = current_user();
    if (!$u) {
        flash('info', 'Please log in to continue.');
        redirect('login.php?next=' . rawurlencode($next));
    }
    return $u;
}

function require_admin(): array
{
    $u = current_user();
    if (!$u || $u['role'] !== 'admin') {
        http_response_code(403);
        flash('error', 'That area is for studio administrators.');
        redirect($u ? 'account.php' : 'login.php');
    }
    return $u;
}

function login_user(int $id): void
{
    session_regenerate_id(true);
    $_SESSION['uid'] = $id;
}

/* ---------- Dates ---------- */
function fmt_time(string $dt): string
{
    return date('H:i', strtotime($dt));
}

function fmt_day(string $dt, bool $long = false): string
{
    return date($long ? 'l j F' : 'D j M', strtotime($dt));
}

function relative_day(string $dt): string
{
    $d = date('Y-m-d', strtotime($dt));
    if ($d === date('Y-m-d')) {
        return 'Today';
    }
    if ($d === date('Y-m-d', strtotime('+1 day'))) {
        return 'Tomorrow';
    }
    return date('l', strtotime($dt));
}

function str_starts(string $h, string $n): bool
{
    return substr($h, 0, strlen($n)) === $n;
}

require_once APP_DIR . '/booking.php';
require_once APP_DIR . '/components.php';
