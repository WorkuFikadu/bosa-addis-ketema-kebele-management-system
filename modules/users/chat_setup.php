<?php
// modules/users/chat_setup.php - Run once to create chat tables
require_once __DIR__ . '/../../config/database.php';

$sqls = [
    "CREATE TABLE IF NOT EXISTS `staff_messages` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `sender_id` INT NOT NULL,
        `receiver_id` INT DEFAULT NULL COMMENT 'NULL = group/general channel',
        `channel` VARCHAR(50) DEFAULT 'general' COMMENT 'general, updates, news, or dm_X',
        `message` TEXT NOT NULL,
        `msg_type` ENUM('text','image','file','announcement') DEFAULT 'text',
        `file_path` VARCHAR(255) DEFAULT NULL,
        `is_read` TINYINT(1) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(`channel`),
        INDEX(`sender_id`),
        INDEX(`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS `staff_announcements` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `author_id` INT NOT NULL,
        `title` VARCHAR(255) NOT NULL,
        `body` TEXT NOT NULL,
        `priority` ENUM('normal','urgent','critical') DEFAULT 'normal',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

$ok = true;
foreach ($sqls as $sql) {
    try {
        $pdo->exec($sql);
        echo "<p class='text-success'>✓ Table created/verified.</p>";
    } catch (PDOException $e) {
        echo "<p class='text-danger'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        $ok = false;
    }
}

if ($ok) {
    echo "<p><strong>✅ Chat tables ready!</strong> <a href='chat.php'>Go to Staff Chat →</a></p>";
}
?>
