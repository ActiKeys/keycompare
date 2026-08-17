<?php
/**
 * Diagnostic script — upload this to /public_html/public/ and visit it
 * to see what's wrong with your setup.
 *
 * Visit: https://yourdomain.com/diagnose.php
 */

header('Content-Type: text/plain; charset=utf-8');
echo "=== KeyCompare Diagnostic ===\n\n";

echo "PHP version: " . PHP_VERSION . "\n";
echo "PHP SAPI:    " . PHP_SAPI . "\n";
echo "Server:      " . ($_SERVER['SERVER_SOFTWARE'] ?? 'unknown') . "\n";
echo "Document root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'unknown') . "\n";
echo "Script:      " . __FILE__ . "\n\n";

echo "=== Required extensions ===\n";
$required = ['mbstring', 'pdo', 'pdo_mysql', 'openssl', 'ctype', 'json', 'bcmath', 'fileinfo', 'tokenizer', 'xml', 'gd', 'intl'];
foreach ($required as $ext) {
    $loaded = extension_loaded($ext);
    echo ($loaded ? "✓" : "✗") . " $ext" . ($loaded ? "" : " (MISSING)") . "\n";
}

echo "\n=== File checks ===\n";
$files = [
    '.env' => __DIR__ . '/../.env',
    'vendor/autoload.php' => __DIR__ . '/../vendor/autoload.php',
    'bootstrap/app.php' => __DIR__ . '/../bootstrap/app.php',
    'storage/' => __DIR__ . '/../storage',
    'storage/framework/' => __DIR__ . '/../storage/framework',
    'storage/logs/' => __DIR__ . '/../storage/logs',
    'bootstrap/cache/' => __DIR__ . '/../bootstrap/cache',
];
foreach ($files as $name => $path) {
    if (file_exists($path)) {
        $perms = is_dir($path) ? 'dir' . substr(sprintf('%o', fileperms($path)), -3) : substr(sprintf('%o', fileperms($path)), -3);
        $writable = is_writable($path) ? 'writable' : 'not writable';
        echo "✓ $name: exists, $perms, $writable\n";
    } else {
        echo "✗ $name: MISSING\n";
    }
}

echo "\n=== .env contents ===\n";
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $content = file_get_contents($envFile);
    $masked = preg_replace('/(PASSWORD|SECRET|KEY|TOKEN)=(.+)/', '$1=***', $content);
    echo $masked;
} else {
    echo "MISSING — please copy .env.example to .env\n";
}

echo "\n=== Server info ===\n";
foreach (['HTTP_HOST', 'REQUEST_URI', 'SCRIPT_NAME', 'PHP_SELF', 'DOCUMENT_ROOT', 'SERVER_ADMIN'] as $k) {
    echo "$k: " . ($_SERVER[$k] ?? 'not set') . "\n";
}

echo "\n=== Done ===\n";
echo "If you see this output, PHP is working!\n";
echo "If you see raw PHP code or 404, PHP is NOT enabled for this folder.\n";
