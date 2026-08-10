# Namecheap Deployment Guide — Using the Auto-Installer

Complete step-by-step guide for deploying APTrades on Namecheap shared hosting using the built-in web installer.

---

## Overview

The platform includes a **4-step web installer** at `/install` that handles:
- Requirements check (PHP version, extensions, permissions)
- Database configuration (enter your DB credentials)
- Admin account creation
- Automatic installation (migrations, seeders, symlinks, permissions, cache)

**You don't need SSH or Terminal** — everything runs through your browser.

---

## Step 1: Create Database in cPanel

1. Log into **cPanel** on Namecheap
2. Go to **MySQL Databases** (under Databases section)
3. Create a new database:
   - Database Name: `investgada` (your cPanel username will be prefixed, e.g. `username_investgada`)
4. Create a new user:
   - Username: `investgada` (becomes `username_investgada`)
   - Password: Choose a strong password and **save it**
5. Add user to database:
   - Select the user and database
   - Check **ALL PRIVILEGES**
   - Click **Add**

**Write down these values — you'll need them:**
```
Database:   username_investgada
Username:   username_investgada
Password:   (the password you set)
Host:       localhost
```

---

## Step 2: Set PHP Version in cPanel

1. Go to **cPanel → Software → Select PHP Version**
2. Set PHP version to **8.2** or **8.3**
3. Switch to **Extensions** tab and make sure these are checked:
   ```
   ✓ pdo_mysql
   ✓ mbstring
   ✓ xml
   ✓ curl
   ✓ gd
   ✓ zip
   ✓ bcmath
   ✓ openssl
   ✓ fileinfo
   ✓ ctype
   ✓ tokenizer
   ✓ json
   ✓ dom
   ✓ simplexml
   ```
4. Click **Save**

---

## Step 3: Upload Files

### Option A: Upload via cPanel File Manager (easiest)

1. Download the project ZIP from GitHub:
   - Go to https://github.com/gadachat/Investgada
   - Click **Code → Download ZIP**
2. **Remove the `vendor/` folder** from the ZIP (if present) — it's large and we'll install it via cPanel Terminal
3. Go to **cPanel → File Manager**
4. Navigate to `public_html/`
5. Click **Upload** and select the ZIP file
6. Once uploaded, right-click → **Extract**
7. If the files extracted into a subfolder (e.g. `Investgada/`), **move all contents** up to `public_html/`

Your structure should look like:
```
public_html/
  ├── app/
  ├── bootstrap/
  ├── config/
  ├── database/
  ├── public/          ← Laravel's public folder
  ├── resources/
  ├── routes/
  ├── storage/
  ├── .env.example
  ├── artisan
  ├── composer.json
  └── ...
```

### Option B: Upload via Git (if Terminal/SSH available)

If your Namecheap plan includes Terminal:
```bash
cd ~/public_html
git clone https://github.com/gadachat/Investgada.git .
```

---

## Step 4: Install Composer Dependencies

### If cPanel Terminal is available:

1. Go to **cPanel → Advanced → Terminal**
2. Run:
   ```bash
   cd ~/public_html
   composer install --no-dev --optimize-autoloader
   ```
3. Wait for it to finish (1-2 minutes)

### If Terminal is NOT available:

1. Install Composer on your **local computer**:
   ```bash
   # Mac/Linux
   curl -sS https://getcomposer.org/installer | php
   php composer.phar install --no-dev --optimize-autoloader

   # Windows: download from https://getcomposer.org/download/
   ```
2. This creates a `vendor/` folder
3. Zip the `vendor/` folder
4. Upload `vendor.zip` via cPanel File Manager
5. Extract it in `public_html/`

---

## Step 5: Prepare .env File

1. In cPanel File Manager, navigate to `public_html/`
2. If `.env` doesn't exist:
   - Copy `.env.example` → rename to `.env`
3. Right-click `.env` → **Edit**
4. Set these values:
   ```env
   APP_NAME=APTrades
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com

   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=username_investgada
   DB_USERNAME=username_investgada
   DB_PASSWORD=your_db_password
   ```
5. Save the file

---

## Step 6: Fix Document Root

Namecheap serves from `public_html/`, but Laravel needs `public_html/public/`.

### Method A: .htaccess Rewrite (recommended — no file moving)

Create a `.htaccess` file in `public_html/` (or edit the existing one):

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Route everything through Laravel's public directory
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

This keeps the Laravel structure intact and just rewrites all traffic through `public/index.php`.

### Method B: Move public/ contents (alternative)

If the .htaccess method doesn't work:

1. Move everything from `public/` to `public_html/`:
   ```
   public_html/public/index.php  →  public_html/index.php
   public_html/public/.htaccess  →  public_html/.htaccess
   ```
2. Edit `index.php` and update paths:
   ```php
   require __DIR__.'/vendor/autoload.php';
   $app = require_once __DIR__.'/bootstrap/app.php';
   ```
   (These should already work since the app root is `public_html/`)

---

## Step 7: Set Permissions

In cPanel File Manager:

1. Right-click on `storage/` → **Permissions** → set to **775** (apply recursively)
2. Right-click on `bootstrap/cache/` → **Permissions** → set to **775**

Or via Terminal:
```bash
chmod -R 775 storage bootstrap/cache
```

---

## Step 8: Run the Web Installer

Open your browser and go to:

```
https://yourdomain.com/install
```

### The installer will guide you through 4 steps:

#### Step 1: Welcome & Requirements Check
- Shows your PHP version and installed extensions
- Green = good, Red = missing (fix before continuing)
- Click **Next** when all green

#### Step 2: Database Configuration
- Enter your database credentials (from Step 1 of this guide)
  - Host: `localhost`
  - Port: `3306`
  - Database: `username_investgada`
  - Username: `username_investgada`
  - Password: your DB password
- Click **Test Connection**
- When it shows "Connected" → Click **Next**

#### Step 3: Admin Account Setup
- Enter your admin details:
  - Name: Your name (e.g. "Super Admin")
  - Email: admin@yourdomain.com
  - Password: Choose a strong password
  - Confirm Password
- Click **Install Now**

#### Step 4: Automatic Installation
The installer will run all of these automatically:
```
✓ Application Key Generated
✓ Database Tables Created (29 migrations, 47 tables)
✓ Storage Symlink Created (or fallback if symlinks disabled)
✓ Folder Permissions Set
✓ Admin Account Created
✓ Default Settings Configured (platform config, ranks, features)
✓ Investment Packages Seeded
✓ Deposit Addresses Seeded
✓ Shared Hosting .htaccess Created
✓ Caches Cleared
✓ Installation Complete!
```

Once done, you'll see a **Login** button.

---

## Step 9: Set Up Cron Job

1. Go to **cPanel → Cron Jobs**
2. Under **Add New Cron Job**:
   - Common Settings: **Every Minute (* * * * *)**
   - Command:
   ```bash
   cd /home/username/public_html && php artisan schedule:run >> /dev/null 2>&1
   ```
   (Replace `username` with your cPanel username)
3. Click **Add New Cron Job**

---

## Step 10: Post-Installation

After the installer completes:

1. **Login to admin panel**: `https://yourdomain.com/admin/login`
2. Configure settings:
   - Platform name and logo
   - Investment packages
   - Crypto deposit addresses
   - Trading pairs
   - MLM ranks and commissions
   - Tawk.to chat widget
   - Email/SMTP settings

3. **Test the user flow**:
   - Register a test user account
   - Test the deposit page
   - Test the dashboard
   - Check mobile (iPhone/Android) — PWA install should work

---

## Troubleshooting on Namecheap

### "500 Internal Server Error"

**Cause**: Missing `.env` or wrong permissions.

**Fix**:
```bash
# Via Terminal
cp .env.example .env
chmod -R 775 storage bootstrap/cache
```

Or check `storage/logs/laravel.log` for the actual error.

### Blank White Page

**Cause**: PHP error not displaying.

**Fix**: Temporarily edit `.env`:
```env
APP_DEBUG=true
```
Then refresh — you'll see the error message.

### "No application encryption key"

**Fix**: The installer generates this. If you skipped the installer:
```bash
php artisan key:generate
```

### Storage Symlink Failed

**Cause**: Namecheap may disable `symlink()` for security.

**Fix**: The installer detects this and creates a **fallback** automatically (copies files instead of symlinking). If it still doesn't work:

1. Go to cPanel File Manager
2. Create folder: `public_html/public/storage/`
3. The platform handles this automatically after that

### "Composer not found"

**Fix**: Install it locally on your computer, run `composer install`, then upload the `vendor/` folder via File Manager.

### Installer says "Already Installed"

**Cause**: The `storage/app/installed` marker file exists.

**Fix** (only if you want to re-install):
1. Delete `storage/app/installed` file
2. Drop all tables in your database (via phpMyAdmin)
3. Visit `/install` again

### Cron Job Not Running

**Fix**:
1. In cPanel → Cron Jobs, check the command is correct
2. The PHP path might differ. Find it with:
   ```bash
   which php
   ```
   Use the full path in the cron command, e.g.:
   ```bash
   /opt/alt/php82/usr/bin/php artisan schedule:run
   ```
3. Check cron email notifications in cPanel

### Images/Logos Not Uploading

**Cause**: Storage symlink issue.

**Fix**:
1. Via File Manager: `public_html/public/storage/` should exist
2. Or run via Terminal: `php artisan storage:link`
3. Check `storage/app/public/` is writable (chmod 775)

### PWA Not Working

**Cause**: `.htaccess` might block manifest.json or sw.js.

**Fix**: Make sure the root `.htaccess` allows these files:
```apache
<FilesMatch "\.(json|js)$">
    Header set Cache-Control "no-cache, must-revalidate"
</FilesMatch>
```

---

## Quick Reference

| What | Where |
|------|-------|
| cPanel login | https://yourdomain.com:2083 or https://servername.namecheap.com:2083 |
| phpMyAdmin | cPanel → Databases → phpMyAdmin |
| File Manager | cPanel → Files → File Manager |
| PHP Version | cPanel → Software → Select PHP Version |
| Terminal | cPanel → Advanced → Terminal (if available) |
| Cron Jobs | cPanel → Advanced → Cron Jobs |
| Web Installer | https://yourdomain.com/install |
| Admin Login | https://yourdomain.com/admin/login |
| User Login | https://yourdomain.com/login |

## File Structure on Namecheap

```
public_html/                    ← cPanel web root
  ├── .env                      ← Your config file
  ├── .htaccess                  ← Root rewrite rules
  ├── app/
  ├── bootstrap/
  ├── config/
  ├── database/
  ├── public/
  │   ├── index.php             ← Laravel entry point
  │   ├── .htaccess             ← Laravel routing
  │   ├── manifest.json         ← PWA manifest
  │   ├── sw.js                 ← Service worker
  │   ├── icons/                ← App icons
  │   ├── favicon.png
  │   ├── robots.txt
  │   └── storage/              ← Symlink or fallback
  ├── resources/
  ├── routes/
  ├── storage/
  │   ├── app/
  │   │   ├── public/           ← Uploaded files
  │   │   └── installed         ← Installation marker
  │   ├── framework/
  │   └── logs/
  ├── vendor/                   ← Composer dependencies
  ├── artisan
  └── composer.json
```

---

*The auto-installer is designed to handle Namecheap's shared hosting environment automatically, including symlink fallbacks and permission fixes. In most cases, you just upload files, set PHP version, create the database, and visit /install.*
