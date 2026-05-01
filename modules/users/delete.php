<?php
// modules/users/delete.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

// Only Administrator can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied.");
}

$id = $_GET['id'] ?? null;

if ($id) {
    // Prevent admin from deleting themselves
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $u = $stmt->fetch();

    if ($u && $u['username'] !== $_SESSION['username']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
    }
}

header('Location: index.php');
exit;
?>
