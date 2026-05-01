<?php
require 'config/database.php';
$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll();

echo "Users in DB:\n";
foreach ($users as $user) {
    echo "ID: {$user['id']}, Username: {$user['username']}, Hash: {$user['password']}\n";
    if (password_verify('admin123', $user['password'])) {
        echo " -> Password 'admin123' VERIFIED for {$user['username']}\n";
    } else {
        echo " -> Password 'admin123' FAILED for {$user['username']}\n";
    }
}
?>
