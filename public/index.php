<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/* -----------------------------------------------------------------------------
 | Pre-installer bootstrap
 | Runs BEFORE Laravel to ensure .env, APP_KEY, and storage dirs exist.
 | This makes the installer wizard work on cPanel shared hosting without
 | requiring shell/SSH access.
 |
 | Each task is wrapped in try/catch so a failure here doesn't block the
 | installer page from loading — the user can still fix things via the
 | web interface.
 */

$basePath = __DIR__ . '/..';

try {
    // 1. Create required directories if missing
    foreach ([
        'storage/framework/cache/data',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/logs',
        'storage/app/public',
        'bootstrap/cache',
    ] as $dir) {
        $path = $basePath . '/' . $dir;
        if (!is_dir($path)) {
            @mkdir($path, 0755, true);
        }
    }

    // 2. .gitignore placeholders (so dirs aren't tracked empty)
    foreach (['storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs'] as $dir) {
        $gitignore = $basePath . '/' . $dir . '/.gitignore';
        if (!file_exists($gitignore)) {
            @file_put_contents($gitignore, "*\n!.gitignore\n");
        }
    }

    // 3. Create .env from .env.example if missing
    $envPath = $basePath . '/.env';
    if (!file_exists($envPath) && file_exists($basePath . '/.env.example')) {
        @copy($basePath . '/.env.example', $envPath);
    }

    // 4. Ensure APP_KEY is set
    if (file_exists($envPath)) {
        $envContent = file_get_contents($envPath);
        if (!preg_match('/^APP_KEY=base64:.+$/m', $envContent)) {
            $key = 'base64:' . base64_encode(random_bytes(32));
            if (preg_match('/^APP_KEY=.*$/m', $envContent)) {
                $envContent = preg_replace('/^APP_KEY=.*$/m', "APP_KEY={$key}", $envContent);
            } else {
                $envContent = rtrim($envContent) . "\nAPP_KEY={$key}\n";
            }
            @file_put_contents($envPath, $envContent);
        }

        // 5. Ensure IMPORT_API_TOKEN is set
        $envContent = file_get_contents($envPath);
        if (!preg_match('/^IMPORT_API_TOKEN=.+$/m', $envContent)) {
            $token = bin2hex(random_bytes(16));
            if (preg_match('/^IMPORT_API_TOKEN=.*$/m', $envContent)) {
                $envContent = preg_replace('/^IMPORT_API_TOKEN=.*$/m', "IMPORT_API_TOKEN={$token}", $envContent);
            } else {
                $envContent = rtrim($envContent) . "\nIMPORT_API_TOKEN={$token}\n";
            }
            @file_put_contents($envPath, $envContent);
        }
    }

    // 6. Create storage symlink (best-effort, falls back to copy)
    if (!file_exists($basePath . '/public/storage')) {
        $storagePublic = $basePath . '/storage/app/public';
        if (is_dir($storagePublic)) {
            if (!@symlink($storagePublic, $basePath . '/public/storage')) {
                // Recursive copy fallback
                $copy = function ($src, $dst) use (&$copy) {
                    if (!is_dir($dst)) @mkdir($dst, 0755, true);
                    foreach (scandir($src) as $item) {
                        if ($item === '.' || $item === '..') continue;
                        $s = $src . '/' . $item;
                        $d = $dst . '/' . $item;
                        if (is_dir($s)) $copy($s, $d);
                        else @copy($s, $d);
                    }
                };
                $copy($storagePublic, $basePath . '/public/storage');
            }
        }
    }
} catch (\Throwable $e) {
    // Silently fail — installer page will show errors and the user can fix manually
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
