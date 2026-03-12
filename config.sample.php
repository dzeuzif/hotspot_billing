<?php
/**
 * NetBill Pro - Hotspot Billing System
 * Configuration File
 * Copy this file to config.php and fill in your details.
 */

// ── Database ──────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'netbill');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', '3306');
define('DB_CHARSET', 'utf8mb4');

// ── Application ───────────────────────────────────────────
define('APP_URL', 'http://localhost/hotspot-billing');
define('APP_KEY', 'change-this-to-a-random-secret-key-32chars');  // 32+ random chars
define('APP_ENV', 'production');  // production | development
define('APP_TIMEZONE', 'UTC');

// ── Session ───────────────────────────────────────────────
define('SESSION_NAME', 'netbill_sess');
define('SESSION_LIFETIME', 86400);  // seconds (1 day)
