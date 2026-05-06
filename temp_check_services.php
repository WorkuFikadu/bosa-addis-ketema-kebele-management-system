<?php
require 'config/database.php';
$stmt = $pdo->query('SELECT * FROM service_prices');
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($results, JSON_PRETTY_PRINT);
?>
