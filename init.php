<?php
/**
 * NetBill Pro - Hotspot Billing System
 * Bootstrap / Initialization
 */

if (!defined('NETBILL')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access not allowed.');
}

define('ROOT_PATH', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
define('SYSTEM_PATH', ROOT_PATH . 'system' . DIRECTORY_SEPARATOR);
define('UI_PATH', ROOT_PATH . 'ui' . DIRECTORY_SEPARATOR);

// ── Load Config ───────────────────────────────────────────
$config_file = ROOT_PATH . 'config.php';
if (!file_exists($config_file)) {
    header('Location: install/');
    exit();
}
require_once $config_file;

// ── Timezone ──────────────────────────────────────────────
date_default_timezone_set(defined('APP_TIMEZONE') ? APP_TIMEZONE : 'UTC');

// ── Session ───────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_name(defined('SESSION_NAME') ? SESSION_NAME : 'netbill_sess');
    session_start();
}

// ── Autoloader ────────────────────────────────────────────
spl_autoload_register(function ($class) {
    $file = SYSTEM_PATH . 'autoload' . DIRECTORY_SEPARATOR . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// ── Database ──────────────────────────────────────────────
try {
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    if (APP_ENV === 'development') {
        die('Database connection failed: ' . $e->getMessage());
    }
    die('Database connection failed. Please check your configuration.');
}

// ── Load App Config from DB ───────────────────────────────
$app_config = [];
try {
    $stmt = $pdo->query("SELECT setting, value FROM tbl_appconfig");
    while ($row = $stmt->fetch()) {
        $app_config[$row['setting']] = $row['value'];
    }
} catch (Exception $e) {
    // Table might not exist yet (during install)
}

// ── Helper Functions ──────────────────────────────────────

/**
 * HTML-safe output
 */
function e($str): string {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

/**
 * Get POST value safely
 */
function post(string $key, $default = ''): string {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

/**
 * Get GET value safely
 */
function get(string $key, $default = ''): string {
    return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
}

/**
 * Redirect with flash message
 */
function redirect(string $url, string $type = '', string $message = ''): void {
    if ($type && $message) {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }
    header('Location: ' . $url);
    exit();
}

/**
 * Format currency
 */
function money(float $amount): string {
    global $app_config;
    $symbol = $app_config['currency_symbol'] ?? '$';
    return $symbol . number_format($amount, 2);
}

/**
 * Generate random alphanumeric string
 */
function random_str(int $length = 16): string {
    return strtoupper(substr(bin2hex(random_bytes($length)), 0, $length));
}

/**
 * Generate invoice number
 */
function gen_invoice(): string {
    return 'INV-' . date('YmdHis') . '-' . random_str(4);
}

/**
 * Log activity
 */
function activity_log(string $type, string $description, int $user_id = 0): void {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO tbl_logs (type, description, user_id, ip) VALUES (?, ?, ?, ?)");
        $stmt->execute([$type, $description, $user_id, $_SERVER['REMOTE_ADDR'] ?? '']);
    } catch (Exception $e) {}
}

/**
 * Check admin authentication
 */
function require_admin(array $roles = []): array {
    if (empty($_SESSION['admin_id'])) {
        redirect(APP_URL . '/admin/login.php');
    }
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM tbl_admins WHERE id = ? AND status = 1");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch();
    if (!$admin) {
        session_destroy();
        redirect(APP_URL . '/admin/login.php');
    }
    if (!empty($roles) && !in_array($admin['user_type'], $roles)) {
        redirect(APP_URL . '/admin/?page=dashboard', 'error', 'You do not have permission.');
    }
    return $admin;
}

/**
 * Check customer authentication
 */
function require_customer(): array {
    if (empty($_SESSION['customer_id'])) {
        redirect(APP_URL . '/index.php?page=login');
    }
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM tbl_customers WHERE id = ? AND status = 'Active'");
    $stmt->execute([$_SESSION['customer_id']]);
    $customer = $stmt->fetch();
    if (!$customer) {
        session_destroy();
        redirect(APP_URL . '/index.php?page=login');
    }
    return $customer;
}

/**
 * Get and clear flash message
 */
function get_flash(): array {
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Render a template (simple PHP include)
 */
function render(string $template, array $data = []): void {
    extract($data);
    require UI_PATH . 'templates' . DIRECTORY_SEPARATOR . $template . '.php';
}

/**
 * Hash password
 */
function hash_pass(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password
 */
function verify_pass(string $password, string $hash): bool {
    return password_verify($password, $hash);
}
