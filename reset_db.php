<?php
require 'config/database.php';

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $tables = ['id_cards', 'residents', 'individuals', 'families', 'houses', 'addresses', 'ages', 'users'];
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS $table");
        echo "Dropped $table\n";
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "All tables dropped successfully.\n";
} catch (PDOException $e) {
    echo "Error dropping tables: " . $e->getMessage() . "\n";
}
?>
