<?php
require 'config/database.php';
$stmt = $pdo->query("SELECT * FROM service_prices");
echo json_encode($stmt->fetchAll(), JSON_PRETTY_PRINT);
?>
