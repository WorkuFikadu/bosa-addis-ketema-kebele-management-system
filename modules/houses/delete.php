<?php
// modules/houses/delete.php
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

$hnum = $_GET['hnum'] ?? null;

if ($hnum) {
    try {
        $stmt = $pdo->prepare("DELETE FROM houses WHERE hnum = ?");
        $stmt->execute([$hnum]);
        header('Location: index.php?msg=House record deleted');
    } catch (PDOException $e) {
        header('Location: index.php?error=Delete failed');
    }
} else {
    header('Location: index.php');
}
exit;
?>
