<?php
// check_id_cards_schema.php
require_once __DIR__ . '/config/database.php';
$stmt = $pdo->query("SHOW CREATE TABLE id_cards");
$res = $stmt->fetch();
echo $res[1];
