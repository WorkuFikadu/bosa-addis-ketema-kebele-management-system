<?php
// api/index.php - Serverless Entrypoint & Router for Vercel
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Set working directory to the project root
chdir(dirname(__DIR__));

$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$parsed_url = parse_url($request_uri);
$path = urldecode($parsed_url['path'] ?? '/');

// Clean leading/trailing slashes
$trimmed = trim($path, '/');

// Root request defaults to index.php
if ($trimmed === '' || $trimmed === 'index.php') {
    require __DIR__ . '/../index.php';
    exit;
}

$target_file = __DIR__ . '/../' . $trimmed;

// 1. Direct file match
if (is_file($target_file)) {
    $ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // If it's a PHP file, execute it
    if ($ext === 'php') {
        require $target_file;
        exit;
    }
    
    // Fallback static file serving
    $mimes = [
        'css'   => 'text/css',
        'js'    => 'application/javascript',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'svg'   => 'image/svg+xml',
        'ico'   => 'image/x-icon',
        'webp'  => 'image/webp',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'json'  => 'application/json',
        'pdf'   => 'application/pdf',
    ];
    if (isset($mimes[$ext])) {
        header('Content-Type: ' . $mimes[$ext]);
        readfile($target_file);
        exit;
    }
}

// 2. Extensionless PHP route (e.g., /about -> /about.php, /dashboard -> /dashboard.php)
if (is_file($target_file . '.php')) {
    require $target_file . '.php';
    exit;
}

// 3. Directory index (e.g., /auth/ -> /auth/index.php)
if (is_dir($target_file) && is_file($target_file . DIRECTORY_SEPARATOR . 'index.php')) {
    require $target_file . DIRECTORY_SEPARATOR . 'index.php';
    exit;
}

// 4. If not found, return 404
http_response_code(404);
echo "<h1>404 Not Found</h1><p>The requested page was not found.</p>";
