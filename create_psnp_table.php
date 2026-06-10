<?php
require 'config/database.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS economic_psnp (
        id INT AUTO_INCREMENT PRIMARY KEY,
        resident_id INT NOT NULL,
        enrollment_date DATE NOT NULL,
        household_size INT NOT NULL,
        transfer_type VARCHAR(100) NOT NULL,
        work_requirement VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (resident_id) REFERENCES individuals(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Table economic_psnp created successfully.\n";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
?>
