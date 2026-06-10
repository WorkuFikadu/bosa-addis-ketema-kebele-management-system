<?php
require_once 'config/database.php';
try {
    $pdo->exec("ALTER TABLE court_cases ADD COLUMN presiding_judge VARCHAR(100) DEFAULT NULL AFTER description");
    echo "Added presiding_judge column.\n";
} catch (Exception $e) {
    echo "Error adding presiding_judge: " . $e->getMessage() . "\n";
}

try {
    $pdo->exec("ALTER TABLE court_cases CHANGE COLUMN case_type case_category VARCHAR(50) NOT NULL");
    echo "Changed case_type to case_category.\n";
} catch (Exception $e) {
    echo "Error changing case_type: " . $e->getMessage() . "\n";
}
?>
