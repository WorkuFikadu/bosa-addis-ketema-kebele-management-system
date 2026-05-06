<?php
// check_id_cards_schema_v2.php
require_once __DIR__ . '/config/database.php';
$stmt = $pdo->query("SHOW CREATE TABLE id_cards");
$res = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($res);
