# APTrades — Main Domain Root Installation Guide

This guide covers installing APTrades directly on your **main domain root** (e.g., `https://yoursite.com/`), NOT an addon domain or subdirectory.

---

## Prerequisites

- **PHP 8.2+** with extensions: `pdo_mysql, mbstring, openssl, tokenizer, xml, cURL, fileinfo, bcmath, gd`
- **MySQL 5.7+** or **MariaDB 10.3+**
- **Composer** (installed on server or use `composer.phar`)
- **SSH/CLI access** to the server (recommended) or cPanel File Manager

---

## Step 1: Upload Files to Main Domain Root

### Option A: cPanel (Shared Hosting)

1. Go to **cPanel → File Manager**
2. Navigate to `public_html/` (this IS your main domain root)
3. **Remove or back up** the default `index.html` / `default.html` in `public_html/`
4. Upload ALL project files into `public_html/`
   - The structure should look like:
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
       ├── vendor/          ← installed via Composer
       ├── .htaccess        ← auto-created by installer
       ├── artisan
       ├── composer.json
       └── ...
     ```

### Option B: SSH (VPS / Dedicated)

```bash
# Navigate to web root
cd /var/www/html   # or wherever your main domain points

# Clone the repo
git clone https://github.com/YOUR-REPO/aptrades.git .

# Or upload via SCP/rsync from local:
# rsync -avz --exclude vendor --exclude node_modules ./ user@server:/var/www/html/
```

---

## Step 2: Set Document Root (IMPORTANT)

For main domain root installation, your web server document root should point to the Laravel `public/` subdirectory.

### cPanel (Shared Hosting)

1. Go to **cPanel → Domains → Modify Domain Document Root**
2. Select your main domain
3. Set document root to: `public_html/public`
   - If your host doesn't allow this, **don't worry** — the installer will auto-create a root `.htaccess` that routes all traffic to `public/index.php` automatically.

### Apache (VPS)

Edit your virtual host config:
```apache
<VirtualHost *:80>
    ServerName yoursite.com
    DocumentRoot /var/www/html/public

    <Directory /var/www/html/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Nginx (VPS)

```nginx
server {
    listen 80;
    server_name yoursite.com;
    root /var/www/html/public;

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## Step 3: Install Composer Dependencies

### Via SSH:
```bash
cd /var/www/html  # or public_html
composer install --no-dev --optimize-autoloader
```

### Via cPanel (no SSH):
1. Download `composer.phar` from https://getcomposer.org/composer.phar
2. Upload to project root
3. Run via terminal or cron:
   ```
   php composer.phar install --no-dev --optimize-autoloader
   ```

---

## Step 4: Set Permissions

```bash
# Storage and bootstrap/cache must be writable
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/framework storage/logs bootstrap/cache

# On shared hosting, use cPanel File Manager → Right-click → Permissions
```

---

## Step 5: Run the Installer

1. Visit: **`https://yoursite.com/install`**
2. The installer will:
   - Check server requirements (PHP version, extensions, permissions)
   - Auto-detect hosting provider and show tips
   - Ask for database credentials
   - Create the `.env` file with APP_KEY
   - Run all migrations
   - Create storage symlink
   - Create your admin account
   - Seed default settings, packages, and deposit addresses
   - Auto-create root `.htaccess` (if document root points to `public_html/` not `public_html/public/`)
   - Clear caches and mark installation complete

### If the installer page doesn't load:

**Problem:** You see a 404 or directory listing instead of the installer.

**Fix — If document root = `public_html/` (not `public_html/public/`):**

The installer auto-creates this `.htaccess` in `public_html/` during `run()`, but you need a minimal one FIRST so the installer page itself loads:

Create `public_html/.htaccess` with:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>

# Protect sensitive files
<FilesMatch "\.(env|json|lock|md|sql|yaml|yml)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Disable directory browsing
Options -Indexes

# Increase upload limits
php_value upload_max_filesize 50M
php_value post_max_size 50M
php_value max_execution_time 300
php_value memory_limit 256M
```

This routes ALL traffic from `public_html/` → `public_html/public/index.php` (Laravel's front controller).

---

## Step 6: Create Database

### cPanel:
1. Go to **cPanel → MySQL Databases**
2. Create a new database (e.g., `yoursite_aptrades`)
3. Create a database user with strong password
4. **Add user to database** with **ALL PRIVILEGES**

### SSH/CLI:
```sql
CREATE DATABASE aptrades CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'aptrades_user'@'localhost' IDENTIFIED BY 'YOUR_STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON aptrades.* TO 'aptrades_user'@'localhost';
FLUSH PRIVILEGES;
```

Enter these credentials in the installer's database step.

---

## Step 7: Post-Installation

After the installer completes:

1. **Test the site:** Visit `https://yoursite.com/` — should show the landing page
2. **Test admin login:** Visit `https://yoursite.com/admin/login`
3. **Enable SSL:** Use cPanel → AutoSSL or Let's Encrypt
4. **Set cron job** (for scheduled tasks):
   ```
   * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
   ```
   In cPanel: **Cron Jobs → Add New Cron Job → Every Minute**

5. **Optimize for production:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan event:cache
   ```

---

## Troubleshooting

### 500 Internal Server Error
- Check `storage/logs/laravel.log`
- Ensure `storage/` and `bootstrap/cache/` are writable (755 or 775)
- Verify `.env` file was created by installer
- Check PHP version is 8.2+

### Blank White Page
- Check Apache error logs: `/var/log/apache2/error.log` or cPanel → Errors
- Ensure `mod_rewrite` is enabled
- Verify `.htaccess` exists in project root

### Installer says "Already Installed"
- Delete `storage/app/installed` file
- Delete `.env` file
- Revisit `/install`

### Storage link broken (images not showing)
```bash
php artisan storage:link
# Or manually: ln -s ../storage/app/public public/storage
```

### Database connection refused
- Verify DB host is `127.0.0.1` (not `localhost`)
- Check DB credentials in `.env`
- Ensure MySQL service is running
- Verify user has ALL PRIVILEGES on the database

---

## File Structure After Installation

```
public_html/                    ← Main domain root (document root)
├── .htaccess                  ← Routes traffic to public/ (auto-created)
├── .env                       ← Created by installer (NEVER commit this)
├── app/
├── bootstrap/
│   └── cache/                 ← Must be writable
├── config/
├── database/
│   └── migrations/            ← 31 migration files
├── public/                     ← Laravel's public directory
│   ├── .htaccess              ← Laravel rewrite rules
│   ├── index.php              ← Front controller
│   ├── storage → ../storage/app/public  (symlink)
│   └── ...
├── resources/
│   └── views/                 ← 126 Blade templates
├── routes/
│   ├── web.php                ← 234 routes
│   └── auth.php               ← Auth routes
├── storage/                   ← Must be writable
│   ├── app/
│   │   ├── installed          ← Installation marker
│   │   └── public/            ← Uploaded files
│   ├── framework/
│   │   ├── cache/
│   │   ├── sessions/
│   │   └── views/
│   └── logs/
├── vendor/                    ← Composer dependencies
├── artisan
└── composer.json
```

---

## Security Notes

- `.env` is protected by `.htaccess` — never accessible via browser
- `/install` is disabled after installation (checks `storage/app/installed`)
- All admin routes require `auth + role:admin + security.gate` middleware
- KYC module is toggled off by default (enable in Admin → Settings → Features)
- Master Trader management is restricted to admin role only
