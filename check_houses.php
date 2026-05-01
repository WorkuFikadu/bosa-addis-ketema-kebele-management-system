<?php
require 'config/database.php';
$stmt = $pdo->query('DESCRIBE houses');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
