<?php
// api/index.php - Vercel PHP Serverless Router
// vercel-php executes this file with the PROJECT ROOT as the working directory
// All project files are accessible relative to the project root

$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$parsed_url = parse_url($request_uri);
$path = urldecode($parsed_url['path'] ?? '/');
$trimmed = trim($path, '/');

// Resolve the project root - vercel-php sets __DIR__ to the api/ folder
// but the project root files are accessible via the working directory
$project_root = dirname(__DIR__);

// Root request
if ($trimmed === '' || $trimmed === 'index.php') {
    $file = $project_root . '/index.php';
    if (is_file($file)) {
        require $file;
        exit;
    }
}

// Static asset - let Vercel handle it via the routes config
// (assets/ and uploads/ are already handled by the routes)

$target = $project_root . '/' . $trimmed;

// Direct .php file match
if (is_file($target) && strtolower(pathinfo($target, PATHINFO_EXTENSION)) === 'php') {
    require $target;
    exit;
}

// Extensionless route: /about -> /about.php
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
echo '<h1>404 Not Found</h1><p>The page <code>' . htmlspecialchars($path) . '</code> was not found.</p>';
