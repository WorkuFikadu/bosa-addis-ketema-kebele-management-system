<?php
// modules/residents/cleanup.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access.");
}

// Define the cleanup logic
// Step 1: Identify incomplete residents
$incompleteQuery = "
    SELECT i.id 
    FROM individuals i 
    LEFT JOIN addresses a ON i.id = a.id 
    LEFT JOIN ages ag ON i.id = ag.id 
    WHERE (i.fname = '' OR i.lname = '' OR a.id IS NULL OR ag.id IS NULL)
";

$stmt = $pdo->query($incompleteQuery);
$idsToDelete = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($idsToDelete)) {
    $_SESSION['flash_message'] = "No incomplete records found.";
    $_SESSION['flash_type'] = "info";
    header("Location: index.php");
    exit;
}

try {
    $pdo->beginTransaction();

    $idsString = implode(',', $idsToDelete);

    // Step 2: Delete from dependent tables that don't have CASCADE
    // Marriage Details
    $pdo->exec("DELETE FROM marriage_details WHERE groom_id IN ($idsString) OR bride_id IN ($idsString)");
    
    // Divorce Details (if it exists and has FKs)
    $pdo->exec("DELETE FROM divorce_details WHERE individual_id IN ($idsString)");

    // Step 3: Delete from individuals (this will trigger CASCADE for addresses, ages, families, id_cards, vital_certificates)
    $count = $pdo->exec("DELETE FROM individuals WHERE id IN ($idsString)");

    // Log the action
    $log_stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details) VALUES (?, 'CLEANUP', ?)");
    $log_stmt->execute([$_SESSION['user_id'], "Deleted $count incomplete resident records (IDs: $idsString)"]);

    $pdo->commit();

    $_SESSION['flash_message'] = "Successfully deleted $count incomplete resident records.";
    $_SESSION['flash_type'] = "success";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['flash_message'] = "Error during cleanup: " . $e->getMessage();
    $_SESSION['flash_type'] = "danger";
}

header("Location: index.php");
exit;
