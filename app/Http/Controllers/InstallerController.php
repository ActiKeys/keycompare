<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

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
     */
    public function welcome()
    {
        if (self::isInstalled()) {
            return redirect('/');
        }

        $requirements = $this->checkRequirements();

        return view('installer.welcome', [
            'step' => 1,
            'requirements' => $requirements,
            'allPassed' => collect($requirements)->every(fn($r) => $r['ok']),
        ]);
    }

    /**
     * Step 2: Database configuration.
     */
    public function database()
    {
        if (self::isInstalled()) {
            return redirect('/');
        }
        return view('installer.database', [
            'step' => 2,
            'defaults' => [
                'host' => 'localhost',
                'port' => '3306',
                'database' => '',
                'username' => '',
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

        // Write to .env (or create from .env.example)
        $this->writeEnv([
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $data['host'],
            'DB_PORT' => $data['port'],
            'DB_DATABASE' => $data['database'],
            'DB_USERNAME' => $data['username'],
            'DB_PASSWORD' => $data['password'] ?? '',
        ]);

        // Test the connection with the new env
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
                'app_name' => 'KeyCompare',
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

        $this->writeEnv([
            'APP_NAME' => '"' . $data['app_name'] . '"',
            'IMPORT_API_TOKEN' => $data['import_token'] ?: \Illuminate\Support\Str::random(32),
        ]);

        // Storage symlink
        if (!file_exists(public_path('storage'))) {
            try {
                Artisan::call('storage:link');
            } catch (\Throwable $e) {
                // ignore — symlink might not be supported
            }
        }

        // Mark as installed
        file_put_contents(storage_path('app/installed.lock'), now()->toIso8601String());

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
                'ok' => is_writable(storage_path()),
            ],
            'bootstrap' => [
                'label' => 'Bootstrap cache writable',
                'ok' => (is_dir(base_path('bootstrap/cache')) && is_writable(base_path('bootstrap/cache')))
                    || @mkdir(base_path('bootstrap/cache'), 0755, true),
            ],
            'env' => [
                'label' => '.env file writable',
                'ok' => is_writable(base_path('.env')) || !file_exists(base_path('.env')) && is_writable(base_path()),
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
            // Quote if contains spaces or special chars
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
