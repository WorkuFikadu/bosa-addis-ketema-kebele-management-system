<?php
require_once '../config/database.php';

echo "<pre>";
echo "=== Name Replacement: አህመድ መሀመድ → Nezif Teleha ===\n\n";

$oldName  = 'አህመድ መሀመድ';
$newFirst = 'Nezif';
$newLast  = 'Teleha';
$newFull  = 'Nezif Teleha';

try {

    // --- 1. users table ---
    echo "--- Checking 'users' table ---\n";
    $stmt = $pdo->prepare("SELECT id, username, full_name FROM users WHERE full_name LIKE ?");
    $stmt->execute(["%$oldName%"]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($rows) {
        foreach ($rows as $row) {
            echo "Found user id={$row['id']}, username={$row['username']}, full_name={$row['full_name']}\n";
        }
        $upd = $pdo->prepare("UPDATE users SET full_name = ? WHERE full_name LIKE ?");
        $upd->execute([$newFull, "%$oldName%"]);
        echo "Updated " . $upd->rowCount() . " row(s) in 'users'.\n";
    } else {
        echo "Not found in 'users'.\n";
    }

    echo "\n";

    // --- 2. individuals table (first_name / last_name / full_name) ---
    echo "--- Checking 'individuals' table ---\n";

    // Try full_name column first
    try {
        $stmt = $pdo->prepare("SELECT id, first_name, last_name FROM individuals WHERE CONCAT(first_name,' ',last_name) LIKE ?");
        $stmt->execute(["%$oldName%"]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($rows) {
            foreach ($rows as $row) {
                echo "Found individual id={$row['id']}: {$row['first_name']} {$row['last_name']}\n";
            }
            $upd = $pdo->prepare("UPDATE individuals SET first_name = ?, last_name = ? WHERE CONCAT(first_name,' ',last_name) LIKE ?");
            $upd->execute([$newFirst, $newLast, "%$oldName%"]);
            echo "Updated " . $upd->rowCount() . " row(s) in 'individuals'.\n";
        } else {
            echo "Not found in 'individuals' (first_name + last_name).\n";
        }
    } catch (Exception $e) {
        echo "individuals check error: " . $e->getMessage() . "\n";
    }

    echo "\n";

    // --- 3. residents table ---
    echo "--- Checking 'residents' table ---\n";
    try {
        $stmt = $pdo->prepare("SELECT id, first_name, last_name FROM residents WHERE CONCAT(first_name,' ',last_name) LIKE ?");
        $stmt->execute(["%$oldName%"]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($rows) {
            foreach ($rows as $row) {
                echo "Found resident id={$row['id']}: {$row['first_name']} {$row['last_name']}\n";
            }
            $upd = $pdo->prepare("UPDATE residents SET first_name = ?, last_name = ? WHERE CONCAT(first_name,' ',last_name) LIKE ?");
            $upd->execute([$newFirst, $newLast, "%$oldName%"]);
            echo "Updated " . $upd->rowCount() . " row(s) in 'residents'.\n";
        } else {
            echo "Not found in 'residents'.\n";
        }
    } catch (Exception $e) {
        echo "residents table not found or error: " . $e->getMessage() . "\n";
    }

    echo "\n";

    // --- 4. families head_name ---
    echo "--- Checking 'families' table ---\n";
    try {
        $stmt = $pdo->prepare("SELECT id, head_name FROM families WHERE head_name LIKE ?");
        $stmt->execute(["%$oldName%"]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($rows) {
            foreach ($rows as $row) {
                echo "Found family id={$row['id']}, head_name={$row['head_name']}\n";
            }
            $upd = $pdo->prepare("UPDATE families SET head_name = ? WHERE head_name LIKE ?");
            $upd->execute([$newFull, "%$oldName%"]);
            echo "Updated " . $upd->rowCount() . " row(s) in 'families'.\n";
        } else {
            echo "Not found in 'families'.\n";
        }
    } catch (Exception $e) {
        echo "families table not found or error: " . $e->getMessage() . "\n";
    }

    echo "\n=== Done! ===\n";

} catch (Exception $e) {
    echo "Critical Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
