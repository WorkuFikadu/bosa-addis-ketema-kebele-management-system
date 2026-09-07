<?php
// config/database.php - PDO Connection Settings

// Check environment variables first (for Vercel / Cloud / Docker)
$host = getenv('DB_HOST') ?: (getenv('MYSQL_HOST') ?: 'sql101.infinityfree.com');
$db_name = getenv('DB_NAME') ?: (getenv('MYSQL_DATABASE') ?: 'if0_42169059_bosa_db');
$username = getenv('DB_USER') ?: (getenv('MYSQL_USER') ?: 'if0_42169059');
$password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : (getenv('MYSQL_PASSWORD') !== false ? getenv('MYSQL_PASSWORD') : '9BVdoBSdT1bivC');
$port = getenv('DB_PORT') ?: (getenv('MYSQL_PORT') ?: '3306');

$pdo = null;
$db_error = null;

try {
    // Attempt to connect to the database with a 3 second timeout
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db_name;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 3
    ]);
} catch (PDOException $e) {
    $pdo = null;
    $db_error = $e->getMessage();
}
?>
