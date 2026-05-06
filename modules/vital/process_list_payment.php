<?php
// modules/vital/process_list_payment.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/payment_handler.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resident_id = $_POST['resident_id'] ?? 0;
    $service_key = $_POST['service_key'] ?? '';
    $redirect    = $_POST['redirect_to'] ?? 'index.php';

    try {
        if (processPaymentSubmission($pdo, $resident_id, $service_key)) {
            header("Location: $redirect?success=Payment recorded! Waiting for admin verification if digital.");
        } else {
            header("Location: $redirect?error=Payment details missing.");
        }
        exit;
    } catch (Exception $e) {
        header("Location: $redirect?error=Payment failed: " . $e->getMessage());
        exit;
    }
}
?>
