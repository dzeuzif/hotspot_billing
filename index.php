<?php
/**
 * NetBill Pro - Customer Portal Entry Point
 */
define('NETBILL', true);
require_once __DIR__ . '/init.php';

$page = get('page', 'login');

$allowed_pages = ['login', 'register', 'logout', 'dashboard', 'plans', 'order', 'history', 'profile', 'voucher'];
if (!in_array($page, $allowed_pages)) {
    $page = 'login';
}

$auth_pages = ['dashboard', 'plans', 'order', 'history', 'profile'];
if (in_array($page, $auth_pages)) {
    $customer = require_customer();
}

require_once SYSTEM_PATH . 'controllers/customer/' . $page . '.php';
