# APTrades Investment Platform

A modular Laravel 12 investment platform with crypto/forex trading, MLM, and management tools.

## Requirements

- PHP 8.2+
- MySQL 5.7+ / MariaDB 10.3+
- Composer
- Node.js & NPM (for frontend assets, optional)

## Installation

📖 **[Full Installation Guide](INSTALL.md)** — Covers shared hosting, VPS, local dev, cron setup, troubleshooting, and security hardening.

### Quick Start

### Option 1: Web Installer (Recommended)

1. Upload all files to your server
2. Visit `https://yourdomain.com/install`
3. Follow the 4-step wizard:
   - Step 1: Requirements check
   - Step 2: Database configuration
   - Step 3: Admin account setup
   - Step 4: Installation runs automatically (migrations, seeding, key generation)
4. Login at `/admin/login` with your admin credentials

### Option 2: Manual Installation

```bash
# Install dependencies
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Edit .env with your database credentials
# DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD

# Run migrations and seeders
php artisan migrate --force
php artisan db:seed --force

# Create storage symlink
php artisan storage:link

# Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Create admin user (via tinker)
php artisan tinker
>>> User::create([
...     'name' => 'Admin',
...     'email' => 'admin@example.com',
...     'password' => bcrypt('your-password'),
...     'role' => 'admin',
...     'is_admin' => true,
...     'status' => 'active',
... ]);
```

## Platform Modules (50 features across 7 categories)

### Core
- User management, Authentication, Dashboard, Wallets, Settings, Ranks

### Investment
- Investment Packages, Investments, Deposits, Withdrawals, Transactions

### Trading
- Trade Positions, Auto Trading, Trading Signals, Copy Trading

### MLM
- Referrals, Binary Tree, Leadership Bonuses, Profit Share

### User Features
- KYC Verification, Support Tickets, Notifications, Profile, Activity Log

### Admin
- Dashboard, Users, Deposits, Withdrawals, Packages, Settings, Security, KYC, Support, Reports, Audit Logs, Announcements, Funds, Trading, Signals, Profit Share, Leadership, Master Traders, Auto Trade, Chat Widget, Landing Page, Site Settings

### Advanced
- 2FA/TOTP (Google Authenticator), PDF Invoices, Referral Marketing, Web3 Wallets

## Key URLs

| Page | URL |
|------|-----|
| Landing page | `/` |
| User login | `/login` |
| User register | `/register` |
| Admin login | `/admin/login` |
| Installer | `/install` (pre-installation only) |
| Dashboard | `/dashboard` |
| Admin panel | `/admin` |

## Tech Stack

- **Backend:** Laravel 12, PHP 8.2+
- **Frontend:** Blade templates, Bootstrap 5, ApexCharts
- **Database:** MySQL/MariaDB
- **Auth:** Custom auth with 2FA/TOTP support
- **Theme:** Purple/blue gradient (#6366f1, #7c3aed, #a855f7, #3b82f6, #2563eb)

## Security Features

- Admin-only login endpoint (rejects non-admin accounts)
- 2FA with Google Authenticator (TOTP)
- Security module: audit trail, IP management, session management
- Account status checks (active/suspended/banned)
- KYC verification gate for withdrawals
- Feature toggle system (enable/disable any module from admin panel)

## File Counts

- 43 Models
- 55 Controllers (23 Admin, 27 User, 3 Auth, 2 Other)
- 29 Migrations (47 tables)
- 118 Blade views
- 226+ Routes
- 5 Services (CommissionEngine, FundService, TotpService, NotifyService, AuditLog)
- 10 Middleware
- 3 Mail templates

## License

Proprietary. All rights reserved.
