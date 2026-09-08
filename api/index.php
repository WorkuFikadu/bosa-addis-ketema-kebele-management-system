<?php
// api/index.php - Vercel Serverless PHP Router

// CRITICAL FIX FOR VERCEL: Lambda has a read-only filesystem except for /tmp
// If we don't set this, session_start() will crash the entire function
session_save_path('/tmp');
ini_set('session.save_path', '/tmp');

ini_set('display_errors', '1');
error_reporting(E_ALL);

$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = urldecode(parse_url($request_uri, PHP_URL_PATH) ?? '/');
$trimmed = trim($path, '/');

$project_root = dirname(__DIR__);

// Root request
if ($trimmed === '' || $trimmed === 'index.php') {
    require $project_root . '/index.php';
    exit;
}

$target = $project_root . '/' . $trimmed;

// Direct .php file
if (is_file($target) && strtolower(pathinfo($target, PATHINFO_EXTENSION)) === 'php') {
    require $target;
    exit;
}

// Extensionless: /about -> /about.php
if (is_file($target . '.php')) {
    require $target . '.php';
    exit;
}

// Directory index
if (is_dir($target) && is_file($target . '/index.php')) {
    require $target . '/index.php';
    exit;
}

// 404
http_response_code(404);
echo "<h1>404 Not Found</h1><p>Path: <code>" . htmlspecialchars($path) . "</code></p>";
