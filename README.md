# ⚡ NetBill Pro — Hotspot Billing System

A modern, full-featured PHP hotspot billing system for managing Mikrotik routers, internet plans, customers, and payments.

## Features

- **Customer Management** — Add, edit, view, and manage customers with status control and balance top-up
- **Internet Plans** — Hotspot, PPPoE, and Balance plans with flexible validity (Minutes/Hours/Days/Months/Period)
- **Voucher System** — Generate, print, and redeem prepaid vouchers
- **Transaction Tracking** — Full invoice history with CSV export
- **Bandwidth Profiles** — Define download/upload speed tiers per plan
- **Router Management** — Add multiple Mikrotik routers/NAS devices
- **Revenue Reports** — Daily, monthly, by-router, and by-plan reports with charts
- **Auto Renewal** — Automatically renew expired plans using customer balance
- **Customer Portal** — Self-service portal for customers to view plans, recharge with vouchers, and check history
- **Admin Panel** — Full dark-theme admin panel with role-based access (SuperAdmin, Admin, Agent, Sales)
- **Install Wizard** — 4-step browser-based installation
- **Activity Logs** — Track all admin and system actions

## Requirements

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Apache with mod_rewrite (or Nginx)
- PHP Extensions: PDO, PDO_MySQL, mbstring, json, openssl

## Installation

1. Upload all files to your web server
2. Create a MySQL database
3. Navigate to `http://yourdomain.com/hotspot-billing/install/`
4. Follow the 4-step installation wizard
5. **Delete the `install/` directory after installation**
6. Log in at `http://yourdomain.com/hotspot-billing/admin/`

## Configuration

After installation, edit `config.php`:
```php
define('APP_URL', 'https://yourdomain.com/hotspot-billing');
define('APP_ENV', 'production');
define('APP_TIMEZONE', 'Asia/Jakarta');
```

## Cron Job Setup

Add to crontab (every 5 minutes):
```
*/5 * * * * php /var/www/html/hotspot-billing/cron.php >> /var/log/netbill.log 2>&1
```

## Directory Structure

```
hotspot-billing/
├── admin/              # Admin panel entry
├── install/            # Installation wizard
├── system/
│   ├── autoload/       # Model classes (Customer, Plan, Transaction...)
│   ├── controllers/    # Route handlers
│   │   ├── admin/      # Admin controllers
│   │   └── customer/   # Customer portal controllers
│   └── devices/        # Router device drivers
├── ui/
│   └── templates/      # HTML templates
├── config.php          # Configuration (created by installer)
├── init.php            # Bootstrap
├── index.php           # Customer portal entry
└── cron.php            # Scheduled tasks
```

## Default Admin Login

After installation with the wizard:
- Username: `admin` (or what you set)
- Password: what you set during install

## License

MIT License — Free to use and modify.

---

*Built from scratch. Inspired by PHPNuxBill/Hotspot Billing System.*
