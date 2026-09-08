<?php
// api/index.php - Minimal Vercel Router (no external dependencies)
ini_set('display_errors', '1');
error_reporting(E_ALL);

$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = urldecode(parse_url($request_uri, PHP_URL_PATH) ?? '/');
$trimmed = trim($path, '/');

// Project root detection
$project_root = dirname(__DIR__);

// Check if project root files are accessible
if (!is_dir($project_root) || !is_file($project_root . '/index.php')) {
    http_response_code(500);
    echo "<h2>Configuration Error</h2>";
    echo "<p>Project root not found at: <code>" . htmlspecialchars($project_root) . "</code></p>";
    echo "<p>__DIR__ is: <code>" . __DIR__ . "</code></p>";
    echo "<p>Files in __DIR__: <pre>" . implode("\n", scandir(__DIR__)) . "</pre></p>";
    exit;
}

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
echo "<p>Looked for: <code>" . htmlspecialchars($target) . "</code></p>";
