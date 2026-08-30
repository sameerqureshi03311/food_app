<?php

// Enable explicit runtime error reporting for debugging on serverless
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Force HTTPS scheme on Vercel serverless functions
$_SERVER['HTTPS'] = 'on';
$_SERVER['SERVER_PORT'] = 443;
$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';

$storagePath = '/tmp/storage';
$bootstrapCachePath = '/tmp/bootstrap/cache';

// Serverless /tmp writable directory setup
$writableDirs = [
    $storagePath . '/framework/views',
    $storagePath . '/framework/cache/data',
    $storagePath . '/framework/sessions',
    $storagePath . '/logs',
    $bootstrapCachePath,
];

foreach ($writableDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// Redirect storage and bootstrap cache paths to writable /tmp
putenv("LARAVEL_STORAGE_PATH={$storagePath}");
$_ENV['LARAVEL_STORAGE_PATH'] = $storagePath;
$_SERVER['LARAVEL_STORAGE_PATH'] = $storagePath;

putenv("VIEW_COMPILED_PATH={$storagePath}/framework/views");
$_ENV['VIEW_COMPILED_PATH'] = "{$storagePath}/framework/views";
$_SERVER['VIEW_COMPILED_PATH'] = "{$storagePath}/framework/views";

putenv("APP_CONFIG_CACHE={$bootstrapCachePath}/config.php");
putenv("APP_EVENTS_CACHE={$bootstrapCachePath}/events.php");
putenv("APP_PACKAGES_CACHE={$bootstrapCachePath}/packages.php");
putenv("APP_ROUTES_CACHE={$bootstrapCachePath}/routes.php");
putenv("APP_SERVICES_CACHE={$bootstrapCachePath}/services.php");

// SQLite database handling (fallback for zero-config client previews)
$dbConnection = getenv('DB_CONNECTION') ?: ($_ENV['DB_CONNECTION'] ?? 'sqlite');
if ($dbConnection === 'sqlite') {
    $dbFile = '/tmp/database.sqlite';
    if (!file_exists($dbFile)) {
        $sourceDb = __DIR__ . '/../database/database.sqlite';
        if (file_exists($sourceDb)) {
            @copy($sourceDb, $dbFile);
        } else {
            @touch($dbFile);
        }
    }
    putenv("DB_DATABASE={$dbFile}");
    $_ENV['DB_DATABASE'] = $dbFile;
    $_SERVER['DB_DATABASE'] = $dbFile;
}

// Forward execution to Laravel public entrypoint
require __DIR__ . '/../public/index.php';
