#!/usr/bin/env php
<?php
/**
 * NetBill Pro - Cron Job
 * Run every 5 minutes:
 * */5 * * * * php /path/to/hotspot-billing/cron.php >> /var/log/netbill_cron.log 2>&1
 */

define('NETBILL', true);
require_once __DIR__ . '/init.php';

$start = microtime(true);
echo date('[Y-m-d H:i:s]') . " Cron started\n";

// ── 1. Expire overdue recharges ───────────────────────────
$package = new Package($pdo);
$expired = $package->processExpired();
if ($expired > 0) {
    echo "  Expired $expired recharge(s)\n";
    activity_log('Cron', "Expired $expired recharges");
}

// ── 2. Auto-renewal using balance ─────────────────────────
if (($app_config['auto_renewal'] ?? 'yes') === 'yes') {
    $stmt = $pdo->query(
        "SELECT ur.*, c.id as cust_id, c.balance, c.auto_renewal as cust_renewal
         FROM tbl_user_recharges ur
         JOIN tbl_customers c ON c.id = ur.customer_id
         WHERE ur.status = 'off'
           AND c.auto_renewal = 1
           AND c.balance > 0"
    );
    $renewed = 0;
    foreach ($stmt->fetchAll() as $row) {
        $plan = (new Plan($pdo))->find($row['plan_id']);
        if ($plan && $row['balance'] >= $plan['price']) {
            $result = $package->rechargeWithBalance($row['cust_id'], $row['plan_id'], $row['routers']);
            if ($result) {
                $renewed++;
                echo "  Auto-renewed: {$row['username']} → {$plan['name_plan']}\n";
            }
        }
    }
    if ($renewed > 0) activity_log('Cron', "Auto-renewed $renewed subscriptions");
}

// ── 3. Expiry reminders ───────────────────────────────────
$expiring = $package->upcomingExpirations(1);
if (!empty($expiring)) {
    echo "  " . count($expiring) . " expiry reminder(s) due\n";
    // Here you would send SMS/email/Telegram notifications
}

$elapsed = round((microtime(true) - $start) * 1000);
echo date('[Y-m-d H:i:s]') . " Cron finished in {$elapsed}ms\n";
