<?php
require 'config/database.php';
$stmt = $pdo->query("SHOW TABLES LIKE 'economic_%'");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
print_r($tables);
