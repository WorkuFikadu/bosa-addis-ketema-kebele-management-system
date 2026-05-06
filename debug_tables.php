<?php
require_once 'config/database.php';
$tables = ['families', 'transactions'];
foreach ($tables as $table) {
    echo "Table: $table\n";
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $c) {
            echo " - {$c['Field']}\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
