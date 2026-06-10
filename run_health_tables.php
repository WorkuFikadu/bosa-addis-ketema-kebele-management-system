<?php
require 'config/database.php';

try {
    $sql = file_get_contents('health_welfare_tables.sql');
    $pdo->exec($sql);
    echo "Tables in health_welfare_tables.sql created successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
