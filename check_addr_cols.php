<?php
require 'config/database.php';
$stmt = $pdo->query("DESCRIBE addresses");
$cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo implode("\n", $cols);
?>
