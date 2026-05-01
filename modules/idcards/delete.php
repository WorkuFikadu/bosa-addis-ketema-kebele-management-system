<?php
// modules/idcards/delete.php
require_once __DIR__ . '/../../config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php?error=Access Denied');
    exit;
}

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM id_cards WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: index.php?msg=ID Card revoked');
    } catch (PDOException $e) {
        header('Location: index.php?error=Action failed');
    }
} else {
    header('Location: index.php');
}
exit;
?>
