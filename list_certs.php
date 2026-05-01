<?php
require 'config/database.php';
$stmt = $pdo->query('SELECT cert_number FROM vital_certificates');
echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN), JSON_PRETTY_PRINT);
?>
