<?php
// includes/functions.php

/**
 * Logs an administrative activity to the database
 * 
 * @param PDO $pdo The database connection
 * @param string $action The action performed (e.g., 'CREATED', 'UPDATED', 'DELETED')
 * @param string $module The module affected (e.g., 'residents', 'houses')
 * @param int|null $target_id The ID of the affected record
 * @param string|null $details Additional details for the log
 * @return bool True on success
 */
function log_activity($pdo, $action, $module, $target_id = null, $details = null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $user_id = $_SESSION['user_id'] ?? null;
    
    $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, module, target_id, details) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$user_id, $action, $module, $target_id, $details]);
}
