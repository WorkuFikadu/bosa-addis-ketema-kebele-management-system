<?php
require_once 'config/database.php';
try {
    $stmt = $pdo->query("DESCRIBE id_cards");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($cols, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
