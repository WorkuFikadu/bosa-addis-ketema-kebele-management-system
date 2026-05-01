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

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM individuals WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: index.php?msg=Resident deleted successfully');
    } catch (PDOException $e) {
        header('Location: index.php?error=Failed to delete resident: ' . $e->getMessage());
    }
} else {
    header('Location: index.php');
}
exit;
?>
