<?php
require 'config/database.php';
print_r($pdo->query('DESCRIBE users')->fetchAll());
?>
