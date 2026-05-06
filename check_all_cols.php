<?php
require_once 'config/database.php';
$tables = ['addresses', 'id_cards', 'individuals', 'residents', 'houses'];
$results = [];
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        $results[$table] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        $results[$table] = "Error: " . $e->getMessage();
    }
}
echo json_encode($results, JSON_PRETTY_PRINT);
