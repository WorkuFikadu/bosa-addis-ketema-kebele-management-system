<?php
// add_created_at_to_id_cards.php
require_once __DIR__ . '/config/database.php';
try {
    $pdo->exec("ALTER TABLE id_cards ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    echo "Successfully added created_at to id_cards table.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
