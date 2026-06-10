<?php
// modules/economic/youth_id_lost.php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) { header('Location: ../../auth/login.php'); exit; }

$card_id = $_GET['card_id'] ?? null;

if ($card_id) {
    try {
        $stmt = $pdo->prepare("UPDATE youth_id_cards SET status = 'Lost' WHERE id = ?");
        $stmt->execute([$card_id]);
        header('Location: youth_list.php?success=ID+Card marked as LOST. A new card can now be issued.');
        exit;
    } catch (PDOException $e) {
        header('Location: youth_list.php?error=Failed to update card status.');
        exit;
    }
} else {
    header('Location: youth_list.php');
    exit;
}
