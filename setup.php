<?php
// setup.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';

$sql_file = __DIR__ . '/schema.sql';
if (!file_exists($sql_file)) {
    die("Error: schema.sql not found at " . $sql_file);
}

$sql = file_get_contents($sql_file);

try {
    echo "<h2>Starting Kebele System Setup...</h2>";
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS kebele_system");
    echo "<p>Checking database 'kebele_system'... Done.</p>";
    
    $pdo->exec("USE kebele_system");
    
    // Execute schema
    // Split by semicolon to run multiple queries if needed, 
    // but pdo->exec can handle multiple queries depending on driver settings.
    $pdo->exec($sql);
    echo "<p>Running database schema... Done.</p>";
    
    echo "<h1>Database Setup Successful!</h1>";
    echo "<div style='background: #e8f5e9; padding: 20px; border-radius: 8px; border: 1px solid #c8e6c9;'>";
    echo "<p>The Kebele System database has been initialized with an admin user.</p>";
    echo "<ul>";
    echo "<li><strong>Admin Username:</strong> admin</li>";
    echo "<li><strong>Admin Password:</strong> admin123</li>";
    echo "</ul>";
    echo "<hr>";
    echo "<p><strong>Optional:</strong> <a href='seed_data.php' style='display: inline-block; padding: 10px 20px; background: #2196f3; color: white; text-decoration: none; border-radius: 5px;'>Populate Sample Data</a> (Recommended for demo)</p>";
    echo "<br><a href='index.php' style='display: inline-block; padding: 10px 20px; background: #4caf50; color: white; text-decoration: none; border-radius: 5px;'>Go to Login Page</a>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<h1 style='color: red;'>Setup Failed</h1>";
    echo "<p><strong>Error Message:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Error Code:</strong> " . $e->getCode() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
