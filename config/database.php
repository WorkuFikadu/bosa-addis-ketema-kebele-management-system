<?php
// config/database.php - PDO Connection Settings

// Check environment variables first (for Vercel / Cloud / Docker)
$host = getenv('DB_HOST') ?: (getenv('MYSQL_HOST') ?: 'sql101.infinityfree.com');
$db_name = getenv('DB_NAME') ?: (getenv('MYSQL_DATABASE') ?: 'if0_42169059_bosa_db');
$username = getenv('DB_USER') ?: (getenv('MYSQL_USER') ?: 'if0_42169059');
$password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : (getenv('MYSQL_PASSWORD') !== false ? getenv('MYSQL_PASSWORD') : '9BVdoBSdT1bivC');
$port = getenv('DB_PORT') ?: (getenv('MYSQL_PORT') ?: '3306');

try {
    // Attempt to connect to the database
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db_name;charset=utf8mb4", $username, $password);
    
    // Set PDO Error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

} catch (PDOException $e) {
    $is_vercel = isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']);
    if ($is_vercel) {
        die("<div style='font-family:Segoe UI,sans-serif;padding:2rem;background:#fee2e2;border:1px solid #ef4444;border-radius:12px;max-width:650px;margin:3rem auto;box-shadow:0 10px 25px rgba(0,0,0,0.1);'>"
            . "<h3 style='color:#b91c1c;margin-top:0;'>⚠️ Database Connection Required on Vercel</h3>"
            . "<p style='color:#374151;line-height:1.6;'>The PHP application is running successfully on Vercel, but cannot reach a MySQL database. InfinityFree/Localhost databases cannot be reached from Vercel's cloud servers.</p>"
            . "<p style='color:#374151;'><strong>Error:</strong> <code>" . htmlspecialchars($e->getMessage()) . "</code></p>"
            . "<div style='background:#fff;padding:1rem;border-radius:8px;border:1px solid #fca5a5;margin-top:1rem;'>"
            . "<strong>How to fix:</strong> In your <a href='https://vercel.com' target='_blank' style='color:#2563eb;'>Vercel Dashboard</a> &rarr; <em>Project Settings</em> &rarr; <em>Environment Variables</em>, configure:"
            . "<ul style='margin-bottom:0;color:#1e293b;'>"
            . "<li><code>DB_HOST</code> (e.g., from Aiven, TiDB, Clever Cloud, PlanetScale)</li>"
            . "<li><code>DB_NAME</code></li>"
            . "<li><code>DB_USER</code></li>"
            . "<li><code>DB_PASS</code></li>"
            . "<li><code>DB_PORT</code> (default: 3306)</li>"
            . "</ul></div>"
            . "</div>");
    } else {
        die("Critical Error: Could not connect to the database. " . $e->getMessage());
    }
}
?>
