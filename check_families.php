<?php
require 'config/database.php';
$stmt = $pdo->query('DESCRIBE families');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
