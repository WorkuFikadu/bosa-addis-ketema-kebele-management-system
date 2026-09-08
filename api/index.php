<?php
// api/index.php - Vercel Serverless PHP Router

// Fix session path for Vercel Lambda
session_save_path('/tmp');
ini_set('session.save_path', '/tmp');

// Enable error reporting to help with debugging
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = urldecode(parse_url($request_uri, PHP_URL_PATH) ?? '/');
$trimmed = trim($path, '/');

// Use the project root relative to the api directory
$project_root = dirname(__DIR__);

// Default to index.php for the root path
if ($trimmed === '' || $trimmed === 'index.php') {
    $target_file = $project_root . '/index.php';
} else {
    $target = $project_root . '/' . $trimmed;
    
    if (is_file($target) && strtolower(pathinfo($target, PATHINFO_EXTENSION)) === 'php') {
        $target_file = $target;
    } elseif (is_file($target . '.php')) {
        $target_file = $target . '.php';
    } elseif (is_dir($target) && is_file($target . '/index.php')) {
        $target_file = $target . '/index.php';
    } else {
        http_response_code(404);
        echo "<h1>404 Not Found</h1>";
        echo "<p>File or directory not found: <code>" . htmlspecialchars($trimmed) . "</code></p>";
        exit;
    }
}

// Ensure the target file exists
if (!is_file($target_file)) {
    http_response_code(500);
    echo "<h1>500 Internal Server Error</h1>";
    echo "<p>Failed to find target file: <code>" . htmlspecialchars($target_file) . "</code></p>";
    echo "<h3>Directory Contents (" . htmlspecialchars($project_root) . "):</h3>";
    echo "<pre>";
    $files = scandir($project_root);
    foreach ($files as $file) {
        echo htmlspecialchars($file) . "\n";
    }
    echo "</pre>";
    exit;
}

// Require the actual PHP file
require $target_file;
exit;
