<?php

declare(strict_types=1);

// Dukungan PHP Built-in Server untuk Clean URL dan Static Assets
if (php_sapi_name() === 'cli-server') {
    $urlPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $filePath = __DIR__ . $urlPath;
    if ($urlPath !== '/' && file_exists($filePath) && !is_dir($filePath)) {
        return false;
    }
}

// Set zona waktu Indonesia
date_default_timezone_set('Asia/Jakarta');

// Tentukan direktori dasar aplikasi
define('EFOOD_ROOT', dirname(__DIR__));
define('EFOOD_PUBLIC', __DIR__);

// Autoloader sederhana standar PSR-4
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = EFOOD_ROOT . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Load file konfigurasi .env
\App\Helpers\Env::load(EFOOD_ROOT . '/.env');

// Inisialisasi Sesi Aman
\App\Helpers\Session::start();

// Handle Download ZIP Source Code langsung dari browser
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$parsedPath = parse_url($requestUri, PHP_URL_PATH) ?? '/';

if (str_ends_with($parsedPath, '/download-zip') || (isset($_GET['url']) && $_GET['url'] === 'download-zip')) {
    $zipFile = sys_get_temp_dir() . '/efood-release-' . time() . '.zip';
    $cmd = "cd " . escapeshellarg(dirname(EFOOD_ROOT)) . " && zip -r " . escapeshellarg($zipFile) . " efood -x '*/.git*' '*/storage/database.sqlite*'";
    exec($cmd, $out, $ret);

    if (file_exists($zipFile)) {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="efood-php-mysql-ready.zip"');
        header('Content-Length: ' . filesize($zipFile));
        readfile($zipFile);
        unlink($zipFile);
        exit;
    }
}

// Deteksi Clean URL
$url = $_GET['url'] ?? null;
if ($url === null) {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $scriptDir = dirname($scriptName);
    
    // Hilangkan script dir dari request uri jika di subfolder
    if ($scriptDir !== '/' && $scriptDir !== '\\' && str_starts_with($parsedPath, $scriptDir)) {
        $url = substr($parsedPath, strlen($scriptDir));
    } else {
        $url = $parsedPath;
    }
    
    // Hilangkan 'index.php' jika ada di URL
    if (str_starts_with($url, '/index.php')) {
        $url = substr($url, 10);
    }
}

$url = trim($url, '/');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Load routes & dispatch
require_once EFOOD_ROOT . '/routes/web.php';
\App\Routes\Router::dispatch($method, $url);
