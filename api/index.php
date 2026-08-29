<?php

// Serverless /tmp writable directory setup
$writableDirs = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
    '/tmp/bootstrap/cache',
];

foreach ($writableDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
putenv('APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php');
putenv('APP_EVENTS_CACHE=/tmp/bootstrap/cache/events.php');
putenv('APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php');
putenv('APP_ROUTES_CACHE=/tmp/bootstrap/cache/routes.php');
putenv('APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php');

// If using SQLite database, copy pre-seeded database to writable /tmp
if (!getenv('DB_CONNECTION') || getenv('DB_CONNECTION') === 'sqlite') {
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

// Forward execution to Laravel entrypoint
require __DIR__ . '/../public/index.php';
