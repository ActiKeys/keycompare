<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InstallerController extends Controller
{
    /**
     * Detect whether the system is already installed.
     */
    public static function isInstalled(): bool
    {
        return file_exists(storage_path('app/installed.lock'));
    }

    public function __construct()
    {
        // No middleware — installer is accessible pre-install
    }

    /**
     * Show the install wizard.
     */
    public function index()
    {
        if (self::isInstalled()) {
            return redirect('/');
        }
        return redirect()->route('installer.welcome');
    }

    /**
     * Step 1: Welcome & requirements check.
     * Auto-fixes what it can (creates .env, generates APP_KEY, creates dirs).
     */
    public function welcome(Request $request)
    {
        if (self::isInstalled()) {
            return redirect('/');
        }

        // Try to auto-fix common issues
        $autoFixed = $this->autoFixSetup();

        $requirements = $this->checkRequirements();

        return view('installer.welcome', [
            'step' => 1,
            'requirements' => $requirements,
            'allPassed' => collect($requirements)->every(fn($r) => $r['ok']),
            'autoFixed' => $autoFixed,
        ]);
    }

    /**
     * Run automatic setup tasks to make the installer work without CLI.
     * Returns array of actions taken.
     */
    protected function autoFixSetup(): array
    {
        $actions = [];

        // 1. Create .env from .env.example if missing
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            if (file_exists(base_path('.env.example'))) {
                copy(base_path('.env.example'), $envPath);
                $actions[] = 'Created .env from .env.example';
            }
        }

        // 2. Generate APP_KEY if empty
        $envContent = file_exists($envPath) ? file_get_contents($envPath) : '';
        if ($envContent && !preg_match('/^APP_KEY=base64:.+/m', $envContent)) {
            $key = 'base64:' . base64_encode(random_bytes(32));
            $envContent = preg_replace('/^APP_KEY=.*$/m', "APP_KEY={$key}", $envContent);
            if (!str_contains($envContent, "APP_KEY={$key}")) {
                $envContent .= "\nAPP_KEY={$key}\n";
            }
            file_put_contents($envPath, $envContent);
            $actions[] = 'Generated APP_KEY';
        }

        // 3. Generate IMPORT_API_TOKEN if empty
        $envContent = file_exists($envPath) ? file_get_contents($envPath) : '';
        if ($envContent && !preg_match('/^IMPORT_API_TOKEN=.+/m', $envContent)) {
            $token = Str::random(32);
            $envContent = preg_replace('/^IMPORT_API_TOKEN=.*$/m', "IMPORT_API_TOKEN={$token}", $envContent);
            if (!str_contains($envContent, "IMPORT_API_TOKEN={$token}")) {
                $envContent .= "\nIMPORT_API_TOKEN={$token}\n";
            }
            file_put_contents($envPath, $envContent);
            $actions[] = 'Generated IMPORT_API_TOKEN';
        }

        // 4. Create required directories
        $dirs = [
            'storage/framework/cache/data',
            'storage/framework/sessions',
            'storage/framework/views',
            'storage/logs',
            'storage/app/public',
            'bootstrap/cache',
        ];
        foreach ($dirs as $dir) {
            $path = base_path($dir);
            if (!is_dir($path)) {
                @mkdir($path, 0755, true);
                $actions[] = "Created $dir";
            }
        }

        // 5. Create .gitignore placeholders
        foreach (['storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs'] as $dir) {
            $gitignore = base_path($dir . '/.gitignore');
            if (!file_exists($gitignore)) {
                file_put_contents($gitignore, "*\n!.gitignore\n");
            }
        }

        // 6. Try to create storage symlink (may fail on shared hosting)
        if (!file_exists(public_path('storage'))) {
            $storagePublic = base_path('storage/app/public');
            if (is_dir($storagePublic)) {
                try {
                    symlink($storagePublic, public_path('storage'));
                    $actions[] = 'Created public/storage symlink';
                } catch (\Throwable $e) {
                    // Fallback: copy
                    $this->recurseCopy($storagePublic, public_path('storage'));
                    $actions[] = 'Copied public/storage (symlink not allowed)';
                }
            }
        }

        // 7. Try to set permissions (best-effort)
        @chmod(base_path('.env'), 0644);
        @chmod(base_path('storage'), 0755);
        @chmod(base_path('bootstrap/cache'), 0755);
        @chmod(base_path('database'), 0755);

        return $actions;
    }

    /**
     * Recursive copy (used as fallback when symlinks not allowed).
     */
    protected function recurseCopy(string $src, string $dst): void
    {
        if (!is_dir($dst)) {
            @mkdir($dst, 0755, true);
        }
        $items = scandir($src);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $s = $src . '/' . $item;
            $d = $dst . '/' . $item;
            if (is_dir($s)) {
                $this->recurseCopy($s, $d);
            } else {
                copy($s, $d);
            }
        }
    }

    /**
     * Step 2: Database configuration.
     */
    public function database()
    {
        if (self::isInstalled()) {
            return redirect('/');
        }

        $envContent = file_exists(base_path('.env')) ? file_get_contents(base_path('.env')) : '';

        return view('installer.database', [
            'step' => 2,
            'defaults' => [
                'host' => $this->getEnvValue('DB_HOST', 'localhost'),
                'port' => $this->getEnvValue('DB_PORT', '3306'),
                'database' => $this->getEnvValue('DB_DATABASE', ''),
                'username' => $this->getEnvValue('DB_USERNAME', ''),
                'password' => '',
            ],
        ]);
    }

    /**
     * Test database connection (AJAX).
     */
    public function testDatabase(Request $request)
    {
        $data = $request->validate([
            'host' => 'required|string',
            'port' => 'required|integer',
            'database' => 'required|string',
            'username' => 'required|string',
            'password' => 'nullable|string',
        ]);

        try {
            config([
                'database.connections.test_mysql' => [
                    'driver' => 'mysql',
                    'host' => $data['host'],
                    'port' => $data['port'],
                    'database' => $data['database'],
                    'username' => $data['username'],
                    'password' => $data['password'],
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                ],
            ]);
            DB::connection('test_mysql')->getPdo();
            return response()->json(['ok' => true, 'message' => 'Connection successful']);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Save DB config, run migrations, then move to next step.
     */
    public function saveDatabase(Request $request)
    {
        $data = $request->validate([
            'host' => 'required|string',
            'port' => 'required|integer',
            'database' => 'required|string',
            'username' => 'required|string',
            'password' => 'nullable|string',
        ]);

        // Make sure DB_CONNECTION is mysql
        $this->writeEnv([
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $data['host'],
            'DB_PORT' => $data['port'],
            'DB_DATABASE' => $data['database'],
            'DB_USERNAME' => $data['username'],
            'DB_PASSWORD' => $data['password'] ?? '',
        ]);

        // Clear any cached config
        if (file_exists(base_path('bootstrap/cache/config.php'))) {
            @unlink(base_path('bootstrap/cache/config.php'));
        }

        // Test the connection
        try {
            DB::purge();
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            return back()->withErrors(['db' => 'Could not connect: ' . $e->getMessage()])->withInput();
        }

        // Run migrations
        try {
            Artisan::call('migrate', ['--force' => true]);
        } catch (\Throwable $e) {
            return back()->withErrors(['db' => 'Migration failed: ' . $e->getMessage()]);
        }

        return redirect()->route('installer.admin');
    }

    /**
     * Step 3: Admin account creation.
     */
    public function admin()
    {
        if (self::isInstalled()) {
            return redirect('/');
        }
        return view('installer.admin', ['step' => 3]);
    }

    /**
     * Save admin user.
     */
    public function saveAdmin(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'username' => 'required|string|max:64|unique:users,username|alpha_dash',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            \App\Models\User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
                'email_verified_at' => now(),
                'is_admin' => true,
            ]);
        } catch (\Throwable $e) {
            return back()->withErrors(['admin' => 'Could not create admin: ' . $e->getMessage()])->withInput();
        }

        return redirect()->route('installer.settings');
    }

    /**
     * Step 4: Site settings.
     */
    public function settings()
    {
        if (self::isInstalled()) {
            return redirect('/');
        }
        return view('installer.settings', [
            'step' => 4,
            'defaults' => [
                'app_name' => $this->getEnvValue('APP_NAME', 'KeyCompare'),
                'app_url' => url('/'),
            ],
        ]);
    }

    /**
     * Save site settings and finalize.
     */
    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'app_name' => 'required|string|max:255',
            'import_token' => 'nullable|string|max:255',
        ]);

        $token = $data['import_token'] ?: $this->getEnvValue('IMPORT_API_TOKEN', Str::random(32));

        $this->writeEnv([
            'APP_NAME' => '"' . $data['app_name'] . '"',
            'IMPORT_API_TOKEN' => $token,
        ]);

        // Clear config cache so new .env takes effect
        if (file_exists(base_path('bootstrap/cache/config.php'))) {
            @unlink(base_path('bootstrap/cache/config.php'));
        }

        // Mark as installed
        @file_put_contents(storage_path('app/installed.lock'), now()->toIso8601String());

        // Auto-login the admin (find first admin user)
        $admin = \App\Models\User::where('is_admin', true)->first();
        if ($admin) {
            auth()->login($admin);
        }

        return redirect()->route('installer.done');
    }

    /**
     * Step 5: Done!
     */
    public function done()
    {
        if (!self::isInstalled()) {
            return redirect()->route('installer.welcome');
        }
        return view('installer.done', [
            'step' => 5,
            'admin' => \App\Models\User::where('is_admin', true)->first(),
            'import_token' => env('IMPORT_API_TOKEN'),
            'app_url' => url('/'),
        ]);
    }

    // ===== Tools (always available, even after install) =====

    /**
     * System tools for troubleshooting (permissions, cache, etc).
     */
    public function tools()
    {
        $checks = [
            'php_version' => [
                'label' => 'PHP version',
                'ok' => version_compare(PHP_VERSION, '8.2.0', '>='),
                'value' => PHP_VERSION,
                'required' => '8.2.0+',
            ],
            'env_exists' => [
                'label' => '.env file',
                'ok' => file_exists(base_path('.env')),
            ],
            'env_writable' => [
                'label' => '.env writable',
                'ok' => is_writable(base_path('.env')),
            ],
            'storage_writable' => [
                'label' => 'storage/ writable',
                'ok' => is_writable(base_path('storage')),
            ],
            'bootstrap_cache_writable' => [
                'label' => 'bootstrap/cache writable',
                'ok' => is_writable(base_path('bootstrap/cache')),
            ],
            'public_storage_exists' => [
                'label' => 'public/storage exists',
                'ok' => file_exists(public_path('storage')),
            ],
            'installed' => [
                'label' => 'System installed',
                'ok' => self::isInstalled(),
            ],
        ];

        return view('installer.tools', [
            'step' => 'tools',
            'checks' => $checks,
            'allOk' => collect($checks)->every(fn($c) => $c['ok']),
        ]);
    }

    /**
     * Run a specific tool action via POST.
     */
    public function runTool(Request $request)
    {
        $action = $request->input('action');

        switch ($action) {
            case 'fix_permissions':
                @chmod(base_path('storage'), 0755);
                @chmod(base_path('storage/app'), 0755);
                @chmod(base_path('storage/app/public'), 0755);
                @chmod(base_path('storage/framework'), 0755);
                @chmod(base_path('storage/framework/cache'), 0755);
                @chmod(base_path('storage/framework/cache/data'), 0755);
                @chmod(base_path('storage/framework/sessions'), 0755);
                @chmod(base_path('storage/framework/views'), 0755);
                @chmod(base_path('storage/logs'), 0755);
                @chmod(base_path('bootstrap/cache'), 0755);
                @chmod(base_path('database'), 0755);
                @chmod(base_path('.env'), 0644);
                if (file_exists(public_path('storage'))) {
                    @chmod(public_path('storage'), 0755);
                }
                return back()->with('success', 'Permissions set (0755 for dirs, 0644 for files)');

            case 'create_storage_link':
                if (file_exists(public_path('storage'))) {
                    return back()->with('error', 'public/storage already exists');
                }
                try {
                    symlink(base_path('storage/app/public'), public_path('storage'));
                    return back()->with('success', 'Symlink created');
                } catch (\Throwable $e) {
                    // Fallback to copy
                    $this->recurseCopy(base_path('storage/app/public'), public_path('storage'));
                    return back()->with('success', 'Symlink not allowed, copied instead');
                }

            case 'clear_cache':
                try {
                    Artisan::call('cache:clear');
                    Artisan::call('config:clear');
                    Artisan::call('view:clear');
                    Artisan::call('route:clear');
                    return back()->with('success', 'All caches cleared');
                } catch (\Throwable $e) {
                    return back()->with('error', 'Cache clear failed: ' . $e->getMessage());
                }

            case 'storage_link':
                try {
                    Artisan::call('storage:link');
                    return back()->with('success', 'Storage link created');
                } catch (\Throwable $e) {
                    return back()->with('error', 'Storage link failed: ' . $e->getMessage());
                }

            case 'migrate':
                try {
                    Artisan::call('migrate', ['--force' => true]);
                    return back()->with('success', 'Migrations ran successfully');
                } catch (\Throwable $e) {
                    return back()->with('error', 'Migration failed: ' . $e->getMessage());
                }

            case 'reset_installation':
                $lockFile = storage_path('app/installed.lock');
                if (file_exists($lockFile)) {
                    unlink($lockFile);
                }
                return redirect()->route('installer.welcome')->with('success', 'Installation reset. Re-running installer.');

            default:
                return back()->with('error', 'Unknown action: ' . $action);
        }
    }

    // ===== Helpers =====

    protected function checkRequirements(): array
    {
        return [
            'php' => [
                'label' => 'PHP ' . PHP_VERSION,
                'ok' => version_compare(PHP_VERSION, '8.2.0', '>='),
                'required' => '8.2.0+',
            ],
            'extensions' => [
                'label' => 'Required PHP extensions',
                'ok' => $this->checkExtensions(),
                'required' => 'mbstring, pdo, pdo_mysql, openssl, ctype, json, bcmath, fileinfo, tokenizer, xml, gd, intl',
            ],
            'storage' => [
                'label' => 'Storage directory writable',
                'ok' => is_writable(base_path('storage')),
            ],
            'bootstrap' => [
                'label' => 'Bootstrap cache writable',
                'ok' => (is_dir(base_path('bootstrap/cache')) && is_writable(base_path('bootstrap/cache')))
                    || @mkdir(base_path('bootstrap/cache'), 0755, true),
            ],
            'env' => [
                'label' => '.env file writable',
                'ok' => file_exists(base_path('.env')) && is_writable(base_path('.env'))
                    || (!file_exists(base_path('.env')) && is_writable(base_path())),
            ],
        ];
    }

    protected function checkExtensions(): bool
    {
        $required = ['mbstring', 'pdo', 'pdo_mysql', 'openssl', 'ctype', 'json', 'bcmath', 'fileinfo', 'tokenizer', 'xml', 'gd', 'intl'];
        foreach ($required as $ext) {
            if (!extension_loaded($ext)) return false;
        }
        return true;
    }

    /**
     * Read a value from .env file.
     */
    protected function getEnvValue(string $key, string $default = ''): string
    {
        $path = base_path('.env');
        if (!file_exists($path)) return $default;
        $content = file_get_contents($path);
        if (preg_match("/^{$key}=(.*)$/m", $content, $m)) {
            return trim($m[1], " \t\"'");
        }
        return $default;
    }

    /**
     * Update or append to .env file.
     */
    protected function writeEnv(array $values): void
    {
        $path = base_path('.env');
        if (!file_exists($path)) {
            $example = base_path('.env.example');
            if (file_exists($example)) {
                copy($example, $path);
            } else {
                file_put_contents($path, '');
            }
        }
        $content = file_get_contents($path);
        foreach ($values as $key => $value) {
            $value = (string) $value;
            if (preg_match('/\s|"|\'|#/', $value) && !str_starts_with($value, '"')) {
                $value = '"' . str_replace('"', '\"', $value) . '"';
            }
            if (preg_match("/^{$key}=/m", $content)) {
                $content = preg_replace("/^{$key}=.*$/m", "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}\n";
            }
        }
        file_put_contents($path, $content);
    }
}
