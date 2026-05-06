<?php
require_once 'config/database.php';
$tables = ['transactions', 'service_prices'];
foreach ($tables as $table) {
    echo "Table: $table\n";
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
