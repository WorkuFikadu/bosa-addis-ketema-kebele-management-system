<?php
require_once 'config/database.php';
try {
    $stmt = $pdo->query("DESCRIBE court_cases");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
