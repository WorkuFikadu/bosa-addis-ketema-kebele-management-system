<?php
require 'config/database.php';
$stmt = $pdo->query('DESCRIBE houses');
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}
?>
