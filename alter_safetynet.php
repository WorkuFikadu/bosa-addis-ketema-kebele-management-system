<?php
require 'config/database.php';
try {
    $sql = "ALTER TABLE safetynet_records 
        ADD COLUMN vulnerability_criteria VARCHAR(255) DEFAULT NULL,
        ADD COLUMN proxy_name VARCHAR(100) DEFAULT NULL,
        ADD COLUMN duty_station VARCHAR(100) DEFAULT NULL,
        ADD COLUMN monthly_entitlement DECIMAL(10,2) DEFAULT NULL,
        ADD COLUMN payment_method VARCHAR(50) DEFAULT 'Cash';";
    $pdo->exec($sql);
    echo "safetynet_records altered successfully.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Columns already exist.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
