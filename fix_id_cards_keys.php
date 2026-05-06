<?php
// fix_id_cards_keys.inc.php
require_once __DIR__ . '/config/database.php';
try {
    // 1. Drop the foreign key first (it might be preventing index drop)
    // We need to know the name. In schema.sql it was id_cards_ibfk_1
    // But let's try multiple common names
    $fks = ['id_cards_ibfk_1', 'fk_id_resident', 'resident_id_fk'];
    foreach($fks as $fk) {
        try {
            $pdo->exec("ALTER TABLE id_cards DROP FOREIGN KEY $fk");
            echo "Dropped FK $fk\n";
        } catch (Exception $e) {}
    }

    // 2. Drop the UNIQUE index
    try {
        $pdo->exec("ALTER TABLE id_cards DROP INDEX resident_id");
        echo "Dropped Unique Index resident_id\n";
    } catch (Exception $e) {
        echo "Could not drop index resident_id: " . $e->getMessage() . "\n";
    }

    // 3. Re-add the Foreign Key (it will create a regular index automatically if none exists)
    $pdo->exec("ALTER TABLE id_cards ADD CONSTRAINT id_cards_ibfk_1 FOREIGN KEY (resident_id) REFERENCES individuals(id) ON DELETE CASCADE");
    echo "Re-added Foreign Key id_cards_ibfk_1\n";

} catch (Exception $e) {
    echo "GRAND ERROR: " . $e->getMessage() . "\n";
}
