<?php
// modules/residents/delete.php
require_once __DIR__ . '/../../config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php?error=Access Denied: Admin role required');
    exit;
}

$id = $_GET['id'] ?? null;

require_once __DIR__ . '/../../includes/functions.php';

if ($id) {
    try {
        // Fetch name before deleting for log
        $name_stmt = $pdo->prepare("SELECT fname, lname FROM individuals WHERE id = ?");
        $name_stmt->execute([$id]);
        $resident = $name_stmt->fetch();
        $name = $resident ? "{$resident['fname']} {$resident['lname']}" : "Unknown";

        $stmt = $pdo->prepare("DELETE FROM individuals WHERE id = ?");
        $stmt->execute([$id]);
        
        log_activity($pdo, 'DELETED', 'residents', $id, "Deleted resident: $name");
        
        header('Location: index.php?msg=Resident deleted successfully');
    } catch (PDOException $e) {
        header('Location: index.php?error=Failed to delete resident: ' . $e->getMessage());
    }
} else {
    header('Location: index.php');
}
exit;
?>
