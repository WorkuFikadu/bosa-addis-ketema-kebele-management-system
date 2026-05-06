<?php
require 'config/database.php';

try {
    $columns = [
        "house_type VARCHAR(50) DEFAULT 'Residential'",
        "construction_type VARCHAR(100) DEFAULT 'Wood and Mud'",
        "rooms_count INT DEFAULT 1",
        "floor_type VARCHAR(50) DEFAULT 'Earth'",
        "roof_type VARCHAR(50) DEFAULT 'CIS'",
        "has_water ENUM('Yes', 'No') DEFAULT 'No'",
        "has_electricity ENUM('Yes', 'No') DEFAULT 'No'",
        "toilet_type VARCHAR(50) DEFAULT 'None'",
        "constructed_year INT DEFAULT NULL",
        "block_no VARCHAR(50) DEFAULT NULL"
    ];

    foreach ($columns as $column) {
        $name = explode(' ', $column)[0];
        // Check if column exists
        $check = $pdo->query("SHOW COLUMNS FROM houses LIKE '$name'");
        if ($check->rowCount() == 0) {
            $pdo->exec("ALTER TABLE houses ADD COLUMN $column");
            echo "Added column: $name\n";
        } else {
            echo "Column already exists: $name\n";
        }
    }
    echo "Database update completed successfully.";
} catch (PDOException $e) {
    die("Database update failed: " . $e->getMessage());
}
?>
