<?php
require 'config/database.php';
$stmt = $pdo->query("DESCRIBE id_cards");
$cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo implode("\n", $cols);
?>
