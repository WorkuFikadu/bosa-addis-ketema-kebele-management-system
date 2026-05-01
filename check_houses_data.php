<?php
require 'config/database.php';
$stmt = $pdo->query('SELECT * FROM houses');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
