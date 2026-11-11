<?php

// Prepare writable /tmp directory structure for Vercel serverless environment
$storagePath = '/tmp/storage';
$dirs = [
    $storagePath . '/framework/views',
    $storagePath . '/framework/cache/data',
    $storagePath . '/framework/sessions',
    $storagePath . '/logs',
    $storagePath . '/app/documents',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Fix Symfony / Laravel pathInfo calculation on Vercel:
// Because this file is located at /api/index.php, Symfony detects '/api' as the base URL
// and strips '/api' from the pathInfo, causing all /api/* routes to return 404.
// Setting SCRIPT_NAME and PHP_SELF to '/index.php' preserves the full /api/* path.
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

// Forward all incoming requests to Laravel public entrypoint
require __DIR__ . '/../public/index.php';

