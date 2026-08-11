<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Setting;

class InstallerController extends Controller
{
    /**
     * Ensure .env exists and has a valid APP_KEY for sessions/CSRF.
     * Called before every installer step.
     */
    private function ensureEnv()
    {
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            // Copy .env.example if it exists, otherwise create from template
            if (File::exists(base_path('.env.example'))) {
                File::copy(base_path('.env.example'), $envPath);
            } else {
                File::put($envPath, $this->getEnvTemplate());
            }
        }

        // Generate APP_KEY if empty — required for sessions/CSRF to work
        $env = file_get_contents($envPath);
        if (!preg_match('/APP_KEY=(.+)/', $env, $m) || empty(trim($m[1]))) {
            $key = 'base64:' . base64_encode(random_bytes(32));
            if (preg_match('/^APP_KEY=.*/m', $env)) {
                $env = preg_replace('/^APP_KEY=.*/m', "APP_KEY={$key}", $env);
            } else {
                $env .= "\nAPP_KEY={$key}";
            }
            File::put($envPath, $env);
        }

        // Ensure storage directories exist
        $this->setPermissions();
    }

    /**
     * Check if installer should be available.
     */
    private function isInstalled()
    {
        $envPath = base_path('.env');
        if (!File::exists($envPath)) {
            return false;
        }
        $env = file_get_contents($envPath);
        // If APP_KEY is set AND DB connection works AND users table exists -> installed
        if (!preg_match('/APP_KEY=(.+)/', $env, $m) || empty(trim($m[1]))) {
            return false;
        }
        try {
            return DB::table('users')->exists();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Read .env file into key-value array.
     */
    private function readEnvFile()
    {
        $envPath = base_path('.env');
        if (!File::exists($envPath)) {
            return [];
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $values = [];

        foreach ($lines as $line) {
            // Skip comments
            if (str_starts_with($line, '#')) {
                continue;
            }
            // Parse KEY=VALUE
            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                // Remove quotes
                $value = trim($value, "\"'");
                $values[trim($key)] = $value;
            }
        }

        return $values;
    }

    /**
     * Step 1: Welcome & Requirements Check
     */
    public function index()
    {
        $this->ensureEnv();

        if ($this->isInstalled()) {
            return redirect('/')->with('error', 'Platform is already installed.');
        }

        $requirements = $this->checkRequirements();
        $allPassed = !in_array(false, array_column($requirements, 'passed'));

        // Detect hosting environment
        $hostingHints = $this->detectHosting();

        return view('installer.welcome', compact('requirements', 'allPassed', 'hostingHints'));
    }

    /**
     * Step 2: Database Configuration
     */
    public function database(Request $request)
    {
        $this->ensureEnv();

        if ($this->isInstalled()) {
            return redirect('/')->with('error', 'Platform is already installed.');
        }

        $requirements = $this->checkRequirements();
        if (in_array(false, array_column($requirements, 'passed'))) {
            return redirect()->route('install.index')->with('error', 'Please fix server requirements first.');
        }

        // Pre-fill from current .env if exists
        $envValues = $this->readEnvFile();
        $current = [
            'APP_URL'   => $envValues['APP_URL'] ?? $this->guessUrl(),
            'APP_NAME'  => $envValues['APP_NAME'] ?? 'APTrades',
            'DB_HOST'   => $envValues['DB_HOST'] ?? 'localhost',
            'DB_PORT'   => $envValues['DB_PORT'] ?? '3306',
            'DB_NAME'   => $envValues['DB_DATABASE'] ?? '',
            'DB_USER'   => $envValues['DB_USERNAME'] ?? '',
            'DB_PASS'   => '',
        ];

        $hostingHints = $this->detectHosting();

        return view('installer.database', compact('current', 'hostingHints'));
    }

    /**
     * Step 3: Test DB connection & write .env
     */
    public function testDatabase(Request $request)
    {
        $this->ensureEnv();

        $request->validate([
            'app_name'  => 'required|string|max:50',
            'app_url'   => 'required|url',
            'db_host'   => 'required|string',
            'db_port'   => 'required|numeric',
            'db_name'   => 'required|string',
            'db_user'   => 'required|string',
            'db_pass'   => 'nullable|string',
        ]);

        // Test connection
        try {
            $this->testDbConnection(
                $request->db_host,
                $request->db_port,
                $request->db_name,
                $request->db_user,
                $request->db_pass
            );
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('db_error', 'Connection failed: ' . $e->getMessage());
        }

        // Write .env
        $this->writeEnv($request);

        return redirect()->route('install.admin');
    }

    /**
     * Step 4: Admin Account Setup
     */
    public function admin(Request $request)
    {
        $this->ensureEnv();

        if ($this->isInstalled()) {
            return redirect('/')->with('error', 'Platform is already installed.');
        }

        return view('installer.admin');
    }

    /**
     * Step 5: Run Installation
     */
    public function run(Request $request)
    {
        $this->ensureEnv();

        $request->validate([
            'admin_name'     => 'required|string|max:100',
            'admin_email'    => 'required|email|max:150',
            'admin_password'  => 'required|string|min:8|confirmed',
        ]);

        // Double check DB is configured
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            return redirect()->route('install.database')->with('db_error', 'Database not configured. Please go back and set up your database.');
        }

        $steps = [];

        // Step A: Generate APP_KEY (overwrite the temporary one from ensureEnv)
        try {
            Artisan::call('key:generate', ['--force' => true]);
            $steps[] = ['label' => 'Application Key Generated', 'status' => 'success'];
        } catch (\Exception $e) {
            $steps[] = ['label' => 'Application Key Generation', 'status' => 'error', 'message' => $e->getMessage()];
        }

        // Step B: Run migrations
        try {
            Artisan::call('migrate', ['--force' => true]);
            $steps[] = ['label' => 'Database Tables Created', 'status' => 'success', 'detail' => 'All migrations executed'];
        } catch (\Exception $e) {
            $steps[] = ['label' => 'Database Migration', 'status' => 'error', 'message' => $e->getMessage()];
            return view('installer.complete', compact('steps'))->with('install_error', 'Migration failed: ' . $e->getMessage());
        }

        // Step C: Create storage symlink
        try {
            if (!file_exists(public_path('storage'))) {
                Artisan::call('storage:link');
            }
            $steps[] = ['label' => 'Storage Symlink Created', 'status' => 'success'];
        } catch (\Exception $e) {
            // Symlink might fail on some shared hosts -- create .htaccess fallback
            $this->createStorageFallback();
            $steps[] = ['label' => 'Storage Fallback Created (symlink unavailable)', 'status' => 'warning'];
        }

        // Step D: Set folder permissions
        try {
            $this->setPermissions();
            $steps[] = ['label' => 'Folder Permissions Set', 'status' => 'success'];
        } catch (\Exception $e) {
            $steps[] = ['label' => 'Folder Permissions', 'status' => 'warning', 'message' => 'Manual chmod may be needed'];
        }

        // Step E: Create admin user
        try {
            $admin = User::create([
                'name'       => $request->admin_name,
                'username'   => 'admin',
                'email'      => $request->admin_email,
                'password'   => $request->admin_password,
                'role'       => 'admin',
                'is_admin'   => true,
                'status'     => 'active',
            ]);

            // Create default wallets
            $walletTypes = ['deposit', 'interest', 'commission', 'bonus', 'withdrawal'];
            foreach ($walletTypes as $type) {
                DB::table('wallets')->insert([
                    'user_id'   => $admin->id,
                    'type'      => $type,
                    'balance'   => 0,
                    'currency'  => 'USD',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $steps[] = ['label' => 'Admin Account Created', 'status' => 'success', 'detail' => $admin->email];
        } catch (\Exception $e) {
            $steps[] = ['label' => 'Admin Account Creation', 'status' => 'error', 'message' => $e->getMessage()];
            return view('installer.complete', compact('steps'))->with('install_error', 'Admin creation failed: ' . $e->getMessage());
        }

        // Step F: Seed default settings
        try {
            $this->seedDefaultSettings();
            $steps[] = ['label' => 'Default Settings Configured', 'status' => 'success'];
        } catch (\Exception $e) {
            $steps[] = ['label' => 'Default Settings', 'status' => 'warning', 'message' => $e->getMessage()];
        }

        // Step G: Seed default investment packages
        try {
            $this->seedDefaultPackages();
            $steps[] = ['label' => 'Investment Packages Seeded', 'status' => 'success'];
        } catch (\Exception $e) {
            $steps[] = ['label' => 'Investment Packages', 'status' => 'warning', 'message' => $e->getMessage()];
        }

        // Step H: Seed default deposit addresses
        try {
            $this->seedDefaultAddresses();
            $steps[] = ['label' => 'Deposit Addresses Seeded', 'status' => 'success'];
        } catch (\Exception $e) {
            $steps[] = ['label' => 'Deposit Addresses', 'status' => 'warning', 'message' => $e->getMessage()];
        }

        // Step I: Create .htaccess for shared hosting if needed
        try {
            $this->createHtaccess();
            $steps[] = ['label' => 'Shared Hosting .htaccess Created', 'status' => 'success'];
        } catch (\Exception $e) {
            $steps[] = ['label' => 'Shared Hosting .htaccess', 'status' => 'warning', 'message' => $e->getMessage()];
        }

        // Step J: Clear caches
        try {
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            $steps[] = ['label' => 'Caches Cleared', 'status' => 'success'];
        } catch (\Exception $e) {
            // Non-critical
        }

        // Mark installation complete
        file_put_contents(storage_path('app/installed'), json_encode([
            'installed_at' => now()->toISOString(),
            'version'      => '1.0.0',
            'admin_email'  => $request->admin_email,
        ]));

        return view('installer.complete', compact('steps'));
    }

    // ====== Private helper methods ======

    private function checkRequirements()
    {
        $reqs = [];

        // PHP version
        $reqs[] = [
            'label'   => 'PHP Version (8.2+)',
            'passed'  => version_compare(PHP_VERSION, '8.2.0', '>='),
            'current' => PHP_VERSION,
        ];

        // Extensions
        $extensions = [
            'pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer',
            'xml', 'ctype', 'json', 'bcmath', 'fileinfo', 'curl', 'gd',
        ];

        foreach ($extensions as $ext) {
            $loaded = extension_loaded($ext);
            $reqs[] = [
                'label'   => "Extension: {$ext}",
                'passed'  => $loaded,
                'current' => $loaded ? 'Loaded' : 'Missing',
            ];
        }

        // Writable directories
        $dirs = [
            'storage'           => storage_path(),
            'storage/app'       => storage_path('app'),
            'storage/framework' => storage_path('framework'),
            'storage/logs'      => storage_path('logs'),
            'bootstrap/cache'    => base_path('bootstrap/cache'),
        ];

        foreach ($dirs as $name => $path) {
            // Create dir if missing
            if (!is_dir($path)) {
                @mkdir($path, 0755, true);
            }
            $writable = is_writable($path) || @chmod($path, 0755) && is_writable($path);
            $reqs[] = [
                'label'   => "Writable: {$name}",
                'passed'  => $writable,
                'current' => $writable ? 'OK' : 'Not writable',
            ];
        }

        // Functions
        $functions = ['proc_open', 'symlink'];
        foreach ($functions as $func) {
            $enabled = function_exists($func);
            $reqs[] = [
                'label'   => "Function: {$func}()",
                'passed'  => $enabled,
                'current' => $enabled ? 'Available' : 'Disabled',
            ];
        }

        return $reqs;
    }

    private function detectHosting()
    {
        $hints = [];
        $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
        $host = $_SERVER['HTTP_HOST'] ?? '';

        // Namecheap detection
        if (str_contains($docRoot, 'namecheap') || str_contains($host, 'namecheap')) {
            $hints[] = [
                'provider' => 'Namecheap',
                'tip' => 'Your database name and user likely start with your cPanel username prefix (e.g., username_dbname). Check cPanel -> MySQL Databases.',
            ];
        }

        // Ultahost detection
        if (str_contains($docRoot, 'ultahost') || str_contains($host, 'ultahost')) {
            $hints[] = [
                'provider' => 'Ultahost',
                'tip' => 'Create a MySQL database and user in your hPanel/DirectAdmin. Assign the user ALL PRIVILEGES to the database.',
            ];
        }

        // Generic cPanel
        if (str_contains($docRoot, 'public_html')) {
            if (empty($hints)) {
                $hints[] = [
                    'provider' => 'cPanel (Generic)',
                    'tip' => 'Your files should be in public_html/. Point your domain document root to the public/ subfolder, or use the .htaccess rewrite (auto-created by this installer).',
                ];
            }
            $hints[] = [
                'provider' => 'Document Root',
                'tip' => 'Detected public_html/ directory. The installer will create an .htaccess that routes traffic to public/ automatically.',
            ];
        }

        // PHP limit
        $hints[] = [
            'provider' => 'PHP Limits',
            'tip' => 'Max upload: ' . ini_get('upload_max_filesize') . ' | Memory limit: ' . ini_get('memory_limit') . ' | Max execution: ' . ini_get('max_execution_time') . 's',
        ];

        return $hints;
    }

    private function guessUrl()
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');

        // Remove /public from URI if present
        $uri = str_replace('/public', '', $uri);

        return "{$scheme}://{$host}{$uri}";
    }

    private function testDbConnection($host, $port, $name, $user, $pass)
    {
        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
        $pdo = new \PDO($dsn, $user, $pass, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_TIMEOUT => 5,
        ]);
        $pdo = null;
    }

    private function writeEnv(Request $request)
    {
        $envPath = base_path('.env');

        // Read existing .env or create from template
        if (File::exists($envPath)) {
            $env = file_get_contents($envPath);
        } else {
            $env = $this->getEnvTemplate();
        }

        // Update values
        $replacements = [
            'APP_NAME'  => $request->app_name,
            'APP_URL'   => rtrim($request->app_url, '/'),
            'DB_HOST'   => $request->db_host,
            'DB_PORT'   => $request->db_port,
            'DB_DATABASE' => $request->db_name,
            'DB_USERNAME' => $request->db_user,
            'DB_PASSWORD' => $request->db_pass,
        ];

        foreach ($replacements as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}={$value}";
            if (preg_match($pattern, $env)) {
                $env = preg_replace($pattern, $replacement, $env);
            } else {
                $env .= "\n{$replacement}";
            }
        }

        // Ensure APP_ENV=production
        if (preg_match('/^APP_ENV=.*/m', $env)) {
            $env = preg_replace('/^APP_ENV=.*/m', 'APP_ENV=production', $env);
        }

        // Ensure DB_CONNECTION=mysql
        if (preg_match('/^DB_CONNECTION=.*/m', $env)) {
            $env = preg_replace('/^DB_CONNECTION=.*/m', 'DB_CONNECTION=mysql', $env);
        }

        File::put($envPath, $env);

        // Clear config cache so next request picks up new DB settings
        Artisan::call('config:clear');
    }

    private function getEnvTemplate()
    {
        return <<<'ENV'
APP_NAME=APTrades
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_STORE=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@aptrades.io"
MAIL_FROM_NAME="${APP_NAME}"
ENV;
    }

    private function setPermissions()
    {
        $dirs = [
            storage_path('app'),
            storage_path('framework'),
            storage_path('framework/cache'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            @chmod($dir, 0755);
        }
    }

    private function createStorageFallback()
    {
        // If symlink fails, create a .htaccess redirect in public/storage
        $storagePublic = public_path('storage');
        if (!file_exists($storagePublic)) {
            @mkdir($storagePublic, 0755, true);
        }

        // Create an .htaccess for the storage directory
        $htaccess = "Options +FollowSymLinks\n";
        file_put_contents($storagePublic . '/.htaccess', $htaccess);
    }

    private function seedDefaultSettings()
    {
        $defaults = [
            'kyc_module'             => true,
            'deposit_module'         => true,
            'withdrawal_module'      => true,
            'referral_module'        => true,
            'binary_module'         => true,
            'profit_share_module'    => true,
            'fund_module'           => true,
            'trading_module'         => true,
            'investment_module'      => true,
            'wallet_module'          => true,
            'mlm_module'             => true,
            'auto_trade_module'      => true,
            'support_module'         => true,
            'notification_module'    => true,
            'tawk_enabled'           => false,
            'tawk_property_id'      => '',
            'tawk_widget_id'         => 'default',
            'kyc_max_file_size'      => '2048',
            'min_withdrawal'         => '0',
            'max_withdrawal'         => '0',
            'withdrawal_fee'         => '0',
            'withdrawal_fee_type'    => 'fixed',
            'deposit_fee'            => '0',
            'deposit_fee_type'       => 'fixed',
            'referral_commission'    => '0',
            'matching_bonus_rate'    => '0',
            'matching_cap'           => '0',
            'binary_placement_auto'  => 'true',
            'platform_name'          => 'APTrades',
            'platform_email'          => 'support@aptrades.io',
            'maintenance_mode'       => false,
            'registration_open'      => true,

            // Branding & Site
            'platform_tagline'        => 'Trade Smarter. Earn Bigger.',
            'platform_phone'          => '',
            'platform_address'         => '',

            // Logo & Favicon (paths, populated on upload)
            'logo'                     => '',
            'logo_dark'                => '',
            'favicon'                  => '',

            // Social Links
            'social_twitter'           => '',
            'social_facebook'          => '',
            'social_telegram'          => '',
            'social_instagram'          => '',
            'social_youtube'           => '',
            'social_linkedin'          => '',
            'social_discord'           => '',

            // SEO -- Meta
            'seo_meta_title'           => 'APTrades -- Crypto, Forex & Investment Platform',
            'seo_meta_description'     => 'Next-generation investment platform for crypto, forex, stocks, and bonds. AI-driven analytics, secure wallets, and daily profit sharing.',
            'seo_meta_keywords'        => 'crypto investment, forex trading, bitcoin, ethereum, USDT, investment platform, daily profits, MLM, referral program',
            'seo_og_title'             => '',
            'seo_og_description'       => '',
            'seo_twitter_card'         => 'summary_large_image',
            'seo_canonical_url'        => '',
            'seo_robots_index'         => '1',
            'seo_robots_follow'        => '1',

            // Analytics
            'google_analytics_id'      => '',
            'google_search_console'    => '',
            'facebook_pixel_id'        => '',

            // Schema.org
            'seo_schema_type'          => 'FinancialService',
            'seo_schema_name'          => 'APTrades',
            'seo_schema_description'   => 'Next-generation crypto and forex investment platform with AI-driven analytics, secure wallets, and daily profit sharing.',
        ];

        foreach ($defaults as $key => $value) {
            Setting::set($key, $value);
        }
    }

    private function seedDefaultPackages()
    {
        // Check if packages already exist
        if (DB::table('investment_packages')->exists()) {
            return;
        }

        $packages = [
            [
                'name' => 'Starter', 'slug' => 'starter', 'category' => 'crypto',
                'description' => 'Perfect for beginners. Start small and learn the ropes.',
                'min_amount' => 100, 'max_amount' => 5000,
                'return_rate' => 1.5, 'duration_days' => 30,
                'is_active' => true, 'featured' => false,
                'sort_order' => 1,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'name' => 'Silver', 'slug' => 'silver', 'category' => 'crypto',
                'description' => 'Balanced risk and reward for steady growth.',
                'min_amount' => 5000, 'max_amount' => 25000,
                'return_rate' => 2.5, 'duration_days' => 60,
                'is_active' => true, 'featured' => false,
                'sort_order' => 2,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'name' => 'Gold', 'slug' => 'gold', 'category' => 'mixed',
                'description' => 'Our most popular package. Diversified across multiple assets.',
                'min_amount' => 25000, 'max_amount' => 100000,
                'return_rate' => 3.5, 'duration_days' => 90,
                'is_active' => true, 'featured' => true,
                'sort_order' => 3,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'name' => 'Platinum', 'slug' => 'platinum', 'category' => 'forex',
                'description' => 'Premium forex-focused portfolio for experienced investors.',
                'min_amount' => 100000, 'max_amount' => 500000,
                'return_rate' => 5.0, 'duration_days' => 120,
                'is_active' => true, 'featured' => false,
                'sort_order' => 4,
                'created_at' => now(), 'updated_at' => now(),
            ],
        ];

        DB::table('investment_packages')->insert($packages);
    }

    private function seedDefaultAddresses()
    {
        if (DB::table('deposit_addresses')->exists()) {
            return;
        }

        $addresses = [
            ['coin' => 'BTC', 'network' => 'Bitcoin',   'address' => 'bc1qexample0000000000000000000000', 'is_active' => true],
            ['coin' => 'ETH', 'network' => 'ERC-20',    'address' => '0xExample0000000000000000000000000000000', 'is_active' => true],
            ['coin' => 'USDT', 'network' => 'TRC-20',    'address' => 'TExample0000000000000000000000000', 'is_active' => true],
            ['coin' => 'USDT', 'network' => 'ERC-20',    'address' => '0xExample2222222222222222222222222222', 'is_active' => true],
        ];

        foreach ($addresses as $addr) {
            DB::table('deposit_addresses')->insert(array_merge($addr, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    private function createHtaccess()
    {
        // Root .htaccess -- routes everything to public/
        $rootHtaccess = base_path('.htaccess');
        $content = <<<'HTACCESS'
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Route everything to the public/ directory
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>

# Protect sensitive files
<FilesMatch "\.(env|json|lock|md|sql|yaml|yml)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Disable directory browsing
Options -Indexes

# Set default page
DirectoryIndex index.php index.html

# Increase upload limits
php_value upload_max_filesize 50M
php_value post_max_size 50M
php_value max_execution_time 300
php_value memory_limit 256M
HTACCESS;

        File::put($rootHtaccess, $content);

        // Ensure public/.htaccess is correct for Laravel
        $publicHtaccess = public_path('.htaccess');
        if (!File::exists($publicHtaccess)) {
            File::put($publicHtaccess, <<<'PUBHTACCESS'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>
    RewriteEngine On
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
PUBHTACCESS);
        }
    }
}
