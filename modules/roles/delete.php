<?php
require_once __DIR__ . '/../../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}

$id = $_GET['id'] ?? null;
if ($id) {
    // Prevent deleting admin or staff roles
    $stmt = $pdo->prepare("SELECT role_key FROM system_roles WHERE id = ?");
    $stmt->execute([$id]);
    $role = $stmt->fetch();
    
    if ($role && !in_array($role['role_key'], ['admin', 'staff'])) {
        $stmt = $pdo->prepare("DELETE FROM system_roles WHERE id = ?");
        $stmt->execute([$id]);
    }
}

header('Location: index.php');
exit;
