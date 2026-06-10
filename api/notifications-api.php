<?php
// api/notifications-api.php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mark as read
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['mark_read'])) {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?");
        $stmt->execute([$user_id]);
        echo json_encode(['success' => true]);
        exit;
    }
}

// Fetch notifications
$stmt = $pdo->prepare("SELECT id, message, link, type, created_at, is_read FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count unread
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE");
$stmt->execute([$user_id]);
$unread_count = $stmt->fetchColumn();

echo json_encode([
    'notifications' => $notifications,
    'unread_count' => $unread_count
]);
