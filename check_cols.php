<?php
require 'config/database.php';
$stmt = $pdo->query("DESCRIBE individuals");
echo json_encode($stmt->fetchAll(), JSON_PRETTY_PRINT);
?>
