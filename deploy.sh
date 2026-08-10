#!/bin/bash
# ╔══════════════════════════════════════════════════════════════╗
# ║   CommandCode Investment Platform — Deployment Script        ║
# ║   Automates installation, configuration, and setup           ║
# ╚══════════════════════════════════════════════════════════════╝

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m'

# Config
APP_DIR="$(cd "$(dirname "$0")" && pwd)"
ENV_FILE="$APP_DIR/.env"
LOG_FILE="$APP_DIR/storage/logs/deploy.log"
TIMESTAMP=$(date "+%Y-%m-%d %H:%M:%S")

# Helper functions
log()     { echo -e "${GREEN}[$TIMESTAMP]${NC} $1"; }
warn()    { echo -e "${YELLOW}[$TIMESTAMP] ⚠${NC} $1"; }
error()   { echo -e "${RED}[$TIMESTAMP] ✗${NC} $1"; }
success() { echo -e "${GREEN}[$TIMESTAMP] ✓${NC} $1"; }
info()    { echo -e "${BLUE}[$TIMESTAMP] ℹ${NC} $1"; }
step()    { echo -e "\n${PURPLE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"; echo -e "${PURPLE}  $1${NC}"; echo -e "${PURPLE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"; }

# Banner
echo ""
echo -e "${PURPLE}╔══════════════════════════════════════════════╗${NC}"
echo -e "${PURPLE}║   CommandCode Investment Platform             ║${NC}"
echo -e "${PURPLE}║   Automated Deployment Script v1.0           ║${NC}"
echo -e "${PURPLE}╚══════════════════════════════════════════════╝${NC}"
echo ""

# ─── CHECK ROOT ───
if [ "$(id -u)" -eq 0 ]; then
    warn "Running as root. It's recommended to run as the web server user."
    echo "Continue? (y/n)"
    read -r CONTINUE
    [ "$CONTINUE" != "y" ] && exit 1
fi

# ─── COLLECT CONFIGURATION ───
step "Step 1: Configuration"

# Check if .env already exists
if [ -f "$ENV_FILE" ]; then
    warn ".env file already exists."
    echo "  1) Use existing .env"
    echo "  2) Overwrite with new configuration"
    echo "  3) Exit"
    read -r ENV_CHOICE
    case $ENV_CHOICE in
        1) USE_EXISTING_ENV=true ;;
        2) USE_EXISTING_ENV=false ;;
        3) exit 0 ;;
        *) error "Invalid choice"; exit 1 ;;
    esac
else
    USE_EXISTING_ENV=false
fi

if [ "$USE_EXISTING_ENV" = false ]; then
    echo ""
    echo "Please provide the following configuration:"
    echo ""

    read -p "App Name [CommandCode]: " APP_NAME
    APP_NAME=${APP_NAME:-CommandCode}

    read -p "App URL [http://localhost]: " APP_URL
    APP_URL=${APP_URL:-http://localhost}

    read -p "DB Host [127.0.0.1]: " DB_HOST
    DB_HOST=${DB_HOST:-127.0.0.1}

    read -p "DB Port [3306]: " DB_PORT
    DB_PORT=${DB_PORT:-3306}

    read -p "DB Name: " DB_NAME
    if [ -z "$DB_NAME" ]; then error "Database name is required"; exit 1; fi

    read -p "DB Username: " DB_USER
    if [ -z "$DB_USER" ]; then error "Database username is required"; exit 1; fi

    read -s -p "DB Password: " DB_PASS
    echo ""

    read -p "Admin Name [Super Admin]: " ADMIN_NAME
    ADMIN_NAME=${ADMIN_NAME:-Super Admin}

    read -p "Admin Email: " ADMIN_EMAIL
    if [ -z "$ADMIN_EMAIL" ]; then error "Admin email is required"; exit 1; fi

    read -s -p "Admin Password: " ADMIN_PASS
    echo ""
    if [ -z "$ADMIN_PASS" ]; then error "Admin password is required"; exit 1; fi

    # Optional: Mail configuration
    echo ""
    echo "Mail Configuration (optional — press Enter to skip):"
    read -p "SMTP Host [skip]: " MAIL_HOST
    if [ -n "$MAIL_HOST" ]; then
        read -p "SMTP Port [587]: " MAIL_PORT
        MAIL_PORT=${MAIL_PORT:-587}
        read -p "SMTP Username: " MAIL_USER
        read -s -p "SMTP Password: " MAIL_PASS
        echo ""
        read -p "From Email [noreply@${APP_URL#http://}]: " MAIL_FROM
        MAIL_FROM=${MAIL_FROM:-noreply@${APP_URL#http://}}
    fi
fi

# ─── VERIFY PHP & COMPOSER ───
step "Step 2: Environment Check"

# Check PHP
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -1 | awk '{print $2}')
    PHP_MAJOR=$(echo "$PHP_VERSION" | cut -d. -f1)
    PHP_MINOR=$(echo "$PHP_VERSION" | cut -d. -f2)
    if [ "$PHP_MAJOR" -ge 8 ] && [ "$PHP_MINOR" -ge 2 ] || [ "$PHP_MAJOR" -gt 8 ]; then
        success "PHP $PHP_VERSION detected"
    else
        error "PHP 8.2+ required. Found $PHP_VERSION"
        exit 1
    fi
else
    error "PHP not found. Install PHP 8.2+ first."
    exit 1
fi

# Check required extensions
REQUIRED_EXTENSIONS=("pdo" "pdo_mysql" "mbstring" "tokenizer" "xml" "ctype" "json" "bcmath" "openssl" "curl" "fileinfo" "gd" "zip")
MISSING_EXTENSIONS=()
for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if ! php -m | grep -qi "$ext"; then
        MISSING_EXTENSIONS+=("$ext")
    fi
done

if [ ${#MISSING_EXTENSIONS[@]} -gt 0 ]; then
    warn "Missing PHP extensions: ${MISSING_EXTENSIONS[*]}"
    echo "Install them with:"
    echo "  sudo apt install php8.2-{pdo-mysql,mbstring,xml,curl,gd,zip,bcmath}"
    echo ""
    echo "Continue anyway? (y/n)"
    read -r CONTINUE
    [ "$CONTINUE" != "y" ] && exit 1
else
    success "All required PHP extensions installed"
fi

# Check Composer
if ! command -v composer &> /dev/null; then
    if [ -f "composer.phar" ]; then
        COMPOSER="php composer.phar"
    else
        warn "Composer not found. Installing..."
        curl -sS https://getcomposer.org/installer | php
        COMPOSER="php composer.phar"
    fi
else
    COMPOSER="composer"
    success "Composer detected"
fi

# Check MySQL
if command -v mysql &> /dev/null; then
    success "MySQL client detected"
else
    warn "MySQL client not found. Make sure your database is accessible."
fi

# ─── COMPOSER INSTALL ───
step "Step 3: Installing Dependencies"

if [ -d "vendor" ]; then
    info "vendor/ exists. Running composer update..."
    $COMPOSER update --no-dev --optimize-autoloader 2>&1 | tail -5
else
    info "Installing composer dependencies..."
    $COMPOSER install --no-dev --optimize-autoloader 2>&1 | tail -5
fi
success "Dependencies installed"

# ─── ENVIRONMENT FILE ───
step "Step 4: Environment Configuration"

if [ "$USE_EXISTING_ENV" = false ]; then
    info "Creating .env file..."

    # Start with .env.example if it exists
    if [ -f ".env.example" ]; then
        cp .env.example .env
    else
        # Create minimal .env
        cat > .env << 'ENVEOF'
APP_NAME=CommandCode
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=public
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@localhost
MAIL_FROM_NAME="${APP_NAME}"
ENVEOF
    fi

    # Update .env with user values
    sed -i "s/^APP_NAME=.*/APP_NAME=$APP_NAME/" .env
    sed -i "s#^APP_URL=.*#APP_URL=$APP_URL#" .env
    sed -i "s/^DB_HOST=.*/DB_HOST=$DB_HOST/" .env
    sed -i "s/^DB_PORT=.*/DB_PORT=$DB_PORT/" .env
    sed -i "s/^DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
    sed -i "s/^DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
    sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env

    # Mail configuration
    if [ -n "$MAIL_HOST" ]; then
        sed -i "s/^MAIL_HOST=.*/MAIL_HOST=$MAIL_HOST/" .env
        sed -i "s/^MAIL_PORT=.*/MAIL_PORT=$MAIL_PORT/" .env
        sed -i "s/^MAIL_USERNAME=.*/MAIL_USERNAME=$MAIL_USER/" .env
        sed -i "s/^MAIL_PASSWORD=.*/MAIL_PASSWORD=$MAIL_PASS/" .env
        sed -i "s/^MAIL_FROM_ADDRESS=.*/MAIL_FROM_ADDRESS=$MAIL_FROM/" .env
        sed -i "s/^MAIL_FROM_NAME=.*/MAIL_FROM_NAME=\"$APP_NAME\"/" .env
    fi

    # Generate APP_KEY
    info "Generating application key..."
    php artisan key:generate --force
    success "Application key generated"
else
    success "Using existing .env file"
fi

# ─── DATABASE SETUP ───
step "Step 5: Database Setup"

# Test database connection
info "Testing database connection..."
if php -r "
    require 'vendor/autoload.php';
    \$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    \$dotenv->load();
    try {
        \$pdo = new PDO(
            'mysql:host=' . env('DB_HOST') . ';port=' . env('DB_PORT') . ';dbname=' . env('DB_DATABASE'),
            env('DB_USERNAME'),
            env('DB_PASSWORD')
        );
        echo 'connected';
    } catch (Exception \$e) {
        fwrite(STDERR, \$e->getMessage());
        exit(1);
    }
" 2>/dev/null; then
    success "Database connection successful"
else
    error "Cannot connect to database. Check your .env credentials."
    echo "  Host: $(grep DB_HOST .env | cut -d= -f2)"
    echo "  Port: $(grep DB_PORT .env | cut -d= -f2)"
    echo "  Database: $(grep DB_DATABASE .env | cut -d= -f2)"
    echo "  User: $(grep DB_USERNAME .env | cut -d= -f2)"
    echo ""
    echo "Fix the .env file and re-run this script."
    exit 1
fi

# Run migrations
info "Running migrations..."
php artisan migrate --force 2>&1 | tail -10
success "Migrations completed"

# Seed database
info "Seeding default data..."
php artisan db:seed --force 2>&1 | tail -5
success "Database seeded"

# ─── CREATE ADMIN USER ───
step "Step 6: Admin Account"

if [ "$USE_EXISTING_ENV" = false ]; then
    info "Creating admin account..."

    php artisan tinker --execute="
        \$existing = \App\Models\User::where('email', '$ADMIN_EMAIL')->first();
        if (\$existing) {
            echo 'Admin already exists';
        } else {
            // Generate username from email
            \$username = explode('@', '$ADMIN_EMAIL')[0];
            \$baseUsername = \$username;
            \$counter = 1;
            while (\App\Models\User::where('username', \$username)->exists()) {
                \$username = \$baseUsername . \$counter;
                \$counter++;
            }

            // Generate unique referral code
            \$referralCode = strtoupper(substr(\$username, 0, 6)) . strtoupper(\Illuminate\Support\Str::random(4));
            while (\App\Models\User::where('referral_code', \$referralCode)->exists()) {
                \$referralCode = strtoupper(substr(\$username, 0, 6)) . strtoupper(\Illuminate\Support\Str::random(4));
            }

            \$user = \App\Models\User::create([
                'name'           => '$ADMIN_NAME',
                'username'       => \$username,
                'email'          => '$ADMIN_EMAIL',
                'password'       => '$ADMIN_PASS',
                'referral_code'  => \$referralCode,
                'role'           => 'admin',
                'is_admin'       => true,
                'status'         => 'active',
                'kyc_status'     => 'verified',
            ]);

            // Create default wallets (matching RegisterController types)
            foreach (['deposit', 'interest', 'commission', 'bonus', 'withdrawal', 'trading'] as \$type) {
                \DB::table('wallets')->insert([
                    'user_id' => \$user->id,
                    'type' => \$type,
                    'balance' => 0,
                    'currency' => 'USD',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Create binary tree node for admin (root)
            \DB::table('binary_trees')->insert([
                'user_id'   => \$user->id,
                'parent_id' => null,
                'position'  => 'left',
                'level'     => 0,
                'left_count'  => 0,
                'right_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            echo 'Admin created: ' . \$user->email;
        }
    " 2>/dev/null && success "Admin account ready" || warn "Admin setup skipped (may already exist)"
else
    info "Skipping admin creation (using existing .env)"
fi

# ─── STORAGE & PERMISSIONS ───
step "Step 7: Storage & Permissions"

# Create storage symlink
info "Creating storage symlink..."
if [ -L "public/storage" ]; then
    success "Storage symlink already exists"
elif php artisan storage:link 2>/dev/null; then
    success "Storage symlink created"
else
    warn "Storage symlink failed — creating directory fallback..."
    mkdir -p public/storage
    cp -r storage/app/public/* public/storage/ 2>/dev/null || true
    success "Storage fallback created"
fi

# Create required directories
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Set permissions
info "Setting file permissions..."

# Detect web server user
if id -u www-data &> /dev/null; then
    WEB_USER="www-data"
elif id -u nginx &> /dev/null; then
    WEB_USER="nginx"
elif id -u apache &> /dev/null; then
    WEB_USER="apache"
else
    WEB_USER="$(whoami)"
fi

chmod -R 775 storage bootstrap/cache public/storage 2>/dev/null || true
chown -R "$WEB_USER":"$WEB_USER" storage bootstrap/cache 2>/dev/null || true
success "Permissions set (web user: $WEB_USER)"

# ─── OPTIMIZE ───
step "Step 8: Optimization"

info "Clearing caches..."
php artisan optimize:clear 2>/dev/null || true

info "Caching configuration..."
php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true
php artisan event:cache 2>/dev/null || true
success "Optimization complete"

# ─── CRON JOB SETUP ───
step "Step 9: Cron Jobs"

CRON_LINE="* * * * * cd $APP_DIR && php artisan schedule:run >> /dev/null 2>&1"

if crontab -l 2>/dev/null | grep -q "schedule:run"; then
    success "Cron job already configured"
else
    echo "Cron jobs are required for automated payouts, commissions, and rank promotions."
    echo ""
    echo "Add this line to your crontab:"
    echo -e "  ${YELLOW}$CRON_LINE${NC}"
    echo ""
    echo "To add it automatically now? (y/n)"
    read -r ADD_CRON
    if [ "$ADD_CRON" = "y" ]; then
        (crontab -l 2>/dev/null; echo "$CRON_LINE") | crontab -
        success "Cron job added"
    else
        info "Cron job skipped — add it manually later"
        echo "  Run: crontab -e"
        echo "  Add: $CRON_LINE"
    fi
fi

# ─── INSTALLATION MARKER ───
step "Step 10: Finalize"

# Create installation marker
mkdir -p storage/app
cat > storage/app/installed << MARKEOF
{
    "installed_at": "$(date -Iseconds)",
    "version": "1.0.0",
    "admin_email": "${ADMIN_EMAIL:-existing}",
    "deployed_via": "deploy.sh"
}
MARKEOF

# ─── SUMMARY ───
echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║   ✓  DEPLOYMENT COMPLETE                      ║${NC}"
echo -e "${Green}╚══════════════════════════════════════════════╝${NC}"
echo ""

# Get app URL from .env
DEPLOY_URL=$(grep "^APP_URL=" .env | cut -d= -f2)

echo -e "${PURPLE}Platform URLs:${NC}"
echo "  Landing:      $DEPLOY_URL"
echo "  User Login:   $DEPLOY_URL/login"
echo "  Admin Login:  $DEPLOY_URL/admin/login"
echo ""

echo -e "${PURPLE}Admin Credentials:${NC}"
if [ "$USE_EXISTING_ENV" = false ]; then
    echo "  Email:    $ADMIN_EMAIL"
    echo "  Password: (hidden for security)"
else
    echo "  (Using existing admin from database)"
fi
echo ""

echo -e "${PURPLE}Next Steps:${NC}"
echo "  1. Login to admin panel and configure settings"
echo "  2. Set up investment packages"
echo "  3. Add crypto deposit addresses"
echo "  4. Configure Tawk.to chat widget"
echo "  5. Test deposit/withdrawal flow"
echo "  6. Configure ranks and MLM settings"
echo ""

echo -e "${PURPLE}Cron Jobs:${NC}"
echo "  Status: $(crontab -l 2>/dev/null | grep -q schedule:run && echo '✓ Active' || echo '⚠ Not set up')"
echo "  Manual test: php artisan cron:run-all --dry-run"
echo ""

echo -e "${PURPLE}Useful Commands:${NC}"
echo "  php artisan optimize:clear    # Clear all caches"
echo "  php artisan cron:run-all      # Run all automated jobs"
echo "  php artisan tinker            # Interactive console"
echo "  php artisan down              # Maintenance mode on"
echo "  php artisan up                # Maintenance mode off"
echo ""

echo -e "${PURPLE}Documentation:${NC}"
echo "  Installation guide: INSTALL.md"
echo "  Full README: README.md"
echo ""

log "Deployment completed successfully!"
