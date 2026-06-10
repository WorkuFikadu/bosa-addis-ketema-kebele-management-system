<?php
// modules/users/chat_api.php - Advanced Real-time AJAX endpoint for the staff chat
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']); exit;
}

header('Content-Type: application/json');

$action  = $_POST['action'] ?? $_GET['action'] ?? '';
$channel = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['channel'] ?? $_POST['channel'] ?? 'general');
$user_id = (int)$_SESSION['user_id'];
$is_admin = ($_SESSION['role'] ?? '') === 'admin';

// ── Ensure advanced tables exist ────────────────────────────────
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `staff_messages` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `sender_id` INT NOT NULL,
        `receiver_id` INT DEFAULT NULL,
        `channel` VARCHAR(50) DEFAULT 'general',
        `message` TEXT NOT NULL,
        `msg_type` ENUM('text','image','file','announcement') DEFAULT 'text',
        `file_path` VARCHAR(255) DEFAULT NULL,
        `is_read` TINYINT(1) DEFAULT 0,
        `reply_to` INT DEFAULT NULL,
        `is_edited` TINYINT(1) DEFAULT 0,
        `is_deleted` TINYINT(1) DEFAULT 0,
        `is_pinned` TINYINT(1) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX(`channel`), INDEX(`sender_id`), INDEX(`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Add missing columns if table already existed
    try { $pdo->exec("ALTER TABLE staff_messages ADD COLUMN `reply_to` INT DEFAULT NULL"); } catch(Exception $e){}
    try { $pdo->exec("ALTER TABLE staff_messages ADD COLUMN `is_edited` TINYINT(1) DEFAULT 0"); } catch(Exception $e){}
    try { $pdo->exec("ALTER TABLE staff_messages ADD COLUMN `is_deleted` TINYINT(1) DEFAULT 0"); } catch(Exception $e){}
    try { $pdo->exec("ALTER TABLE staff_messages ADD COLUMN `is_pinned` TINYINT(1) DEFAULT 0"); } catch(Exception $e){}
    try { $pdo->exec("ALTER TABLE staff_messages ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"); } catch(Exception $e){}

    $pdo->exec("CREATE TABLE IF NOT EXISTS `staff_announcements` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `author_id` INT NOT NULL,
        `title` VARCHAR(255) NOT NULL,
        `body` TEXT NOT NULL,
        `priority` ENUM('normal','urgent','critical') DEFAULT 'normal',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `staff_reactions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `message_id` INT NOT NULL,
        `user_id` INT NOT NULL,
        `emoji` VARCHAR(10) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `uniq_reaction` (`message_id`, `user_id`, `emoji`),
        INDEX(`message_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `staff_typing` (
        `user_id` INT NOT NULL,
        `channel` VARCHAR(50) NOT NULL,
        `last_typed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY(`user_id`, `channel`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `staff_online` (
        `user_id` INT NOT NULL PRIMARY KEY,
        `last_seen` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `staff_read_receipts` (
        `message_id` INT NOT NULL,
        `user_id` INT NOT NULL,
        `read_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY(`message_id`, `user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

} catch (Exception $e) {}

// Update online status (heartbeat)
try {
    $pdo->prepare("INSERT INTO staff_online (user_id, last_seen) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE last_seen = NOW()")->execute([$user_id]);
} catch(Exception $e){}

// ── FETCH MESSAGES ──────────────────────────────────────────────
if ($action === 'fetch') {
    $since = (int)($_GET['since'] ?? 0);
    $limit = (int)($_GET['limit'] ?? 60);

    // Fetch messages with reply-to context
    $stmt = $pdo->prepare("
        SELECT m.id, m.message, m.msg_type, m.file_path, m.created_at, m.updated_at,
               m.reply_to, m.is_edited, m.is_deleted, m.is_pinned,
               u.username, u.full_name, u.id as uid,
               (u.id = ?) as is_me,
               rm.message as reply_msg,
               ru.full_name as reply_author, ru.username as reply_username
        FROM staff_messages m
        JOIN users u ON m.sender_id = u.id
        LEFT JOIN staff_messages rm ON m.reply_to = rm.id
        LEFT JOIN users ru ON rm.sender_id = ru.id
        WHERE m.channel = ? AND m.id > ?
        ORDER BY m.id ASC
        LIMIT $limit
    ");
    $stmt->execute([$user_id, $channel, $since]);
    $msgs = $stmt->fetchAll();

    // Fetch reactions grouped by message
    $msgIds = array_column($msgs, 'id');
    $reactions = [];
    if ($msgIds) {
        $placeholders = implode(',', array_fill(0, count($msgIds), '?'));
        $rstmt = $pdo->prepare("
            SELECT r.message_id, r.emoji, COUNT(*) as cnt,
                   MAX(r.user_id = ?) as i_reacted
            FROM staff_reactions r
            WHERE r.message_id IN ($placeholders)
            GROUP BY r.message_id, r.emoji
            ORDER BY r.message_id, cnt DESC
        ");
        $rstmt->execute(array_merge([$user_id], $msgIds));
        foreach ($rstmt->fetchAll() as $r) {
            $reactions[$r['message_id']][] = $r;
        }
    }

    // Attach reactions to messages
    foreach ($msgs as &$m) {
        $m['reactions'] = $reactions[$m['id']] ?? [];
        // Mark as read if DM
        if (str_starts_with($channel, 'dm_') && !$m['is_me']) {
            try {
                $pdo->prepare("INSERT IGNORE INTO staff_read_receipts (message_id, user_id) VALUES (?,?)")->execute([$m['id'], $user_id]);
                $pdo->prepare("UPDATE staff_messages SET is_read=1 WHERE id=? AND receiver_id=?")->execute([$m['id'], $user_id]);
            } catch(Exception $e){}
        }
    }
    unset($m);

    // Pinned messages for this channel
    $pinstmt = $pdo->prepare("
        SELECT m.id, m.message, m.msg_type, m.file_path, m.created_at,
               u.full_name, u.username
        FROM staff_messages m JOIN users u ON m.sender_id = u.id
        WHERE m.channel = ? AND m.is_pinned = 1 AND m.is_deleted = 0
        ORDER BY m.id DESC LIMIT 3
    ");
    $pinstmt->execute([$channel]);
    $pinned = $pinstmt->fetchAll();

    // Announcements
    $ann = [];
    if ($channel === 'general') {
        $astmt = $pdo->query("SELECT a.*, u.username FROM staff_announcements a JOIN users u ON a.author_id = u.id ORDER BY a.id DESC LIMIT 5");
        $ann = $astmt ? $astmt->fetchAll() : [];
    }

    // Typing users (active in last 4 seconds)
    $typstmt = $pdo->prepare("
        SELECT t.user_id, u.full_name, u.username
        FROM staff_typing t JOIN users u ON t.user_id = u.id
        WHERE t.channel = ? AND t.last_typed > DATE_SUB(NOW(), INTERVAL 4 SECOND) AND t.user_id != ?
    ");
    $typstmt->execute([$channel, $user_id]);
    $typing = $typstmt->fetchAll();

    // Online users (active in last 2 minutes)
    $onstmt = $pdo->query("SELECT user_id FROM staff_online WHERE last_seen > DATE_SUB(NOW(), INTERVAL 2 MINUTE)");
    $online_ids = $onstmt ? array_column($onstmt->fetchAll(), 'user_id') : [];

    // Unread channel badges
    $badges = [];
    try {
        $bstmt = $pdo->prepare("
            SELECT channel, COUNT(*) as cnt
            FROM staff_messages
            WHERE is_read = 0 AND sender_id != ? AND channel != ?
            GROUP BY channel
        ");
        $bstmt->execute([$user_id, $channel]);
        foreach ($bstmt->fetchAll() as $b) {
            $badges[$b['channel']] = $b['cnt'];
        }
    } catch(Exception $e){}

    echo json_encode([
        'messages'     => $msgs,
        'announcements'=> $ann,
        'pinned'       => $pinned,
        'typing'       => $typing,
        'online_ids'   => $online_ids,
        'badges'       => $badges,
    ]);
    exit;
}

// ── SEND MESSAGE ────────────────────────────────────────────────
if ($action === 'send') {
    $msg      = trim($_POST['message'] ?? '');
    $reply_to = (int)($_POST['reply_to'] ?? 0) ?: null;
    $type     = 'text';
    $file_path = null;

    if (!empty($_FILES['attachment']['name'])) {
        $ext  = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp','pdf','doc','docx','xlsx','txt','csv'];
        if (!in_array($ext, $allowed)) {
            echo json_encode(['error' => 'File type not allowed']); exit;
        }
        $dir = __DIR__ . '/../../uploads/chat/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $fname = uniqid('chat_') . '.' . $ext;
        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $dir . $fname)) {
            $file_path = $fname;
            $type = in_array($ext, ['jpg','jpeg','png','gif','webp']) ? 'image' : 'file';
            if (empty($msg)) $msg = $_FILES['attachment']['name'];
        }
    }

    if (empty($msg)) { echo json_encode(['error' => 'Empty message']); exit; }

    // Determine receiver_id for DMs
    $receiver_id = null;
    if (str_starts_with($channel, 'dm_')) {
        $receiver_id = (int)str_replace('dm_', '', $channel);
    }

    $stmt = $pdo->prepare("INSERT INTO staff_messages (sender_id, receiver_id, channel, message, msg_type, file_path, reply_to) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$user_id, $receiver_id, $channel, $msg, $type, $file_path, $reply_to]);

    // Clear typing
    try { $pdo->prepare("DELETE FROM staff_typing WHERE user_id=? AND channel=?")->execute([$user_id, $channel]); } catch(Exception $e){}

    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    exit;
}

// ── EDIT MESSAGE ────────────────────────────────────────────────
if ($action === 'edit') {
    $msg_id  = (int)($_POST['msg_id'] ?? 0);
    $new_msg = trim($_POST['message'] ?? '');
    if (!$msg_id || !$new_msg) { echo json_encode(['error' => 'Invalid']); exit; }

    // Only sender can edit (unless admin)
    $check = $pdo->prepare("SELECT sender_id FROM staff_messages WHERE id=?");
    $check->execute([$msg_id]);
    $row = $check->fetch();
    if (!$row || (!$is_admin && $row['sender_id'] != $user_id)) {
        echo json_encode(['error' => 'Permission denied']); exit;
    }

    $pdo->prepare("UPDATE staff_messages SET message=?, is_edited=1 WHERE id=?")->execute([$new_msg, $msg_id]);
    echo json_encode(['success' => true]);
    exit;
}

// ── DELETE MESSAGE ──────────────────────────────────────────────
if ($action === 'delete') {
    $msg_id = (int)($_POST['msg_id'] ?? 0);
    if (!$msg_id) { echo json_encode(['error' => 'Invalid']); exit; }

    $check = $pdo->prepare("SELECT sender_id FROM staff_messages WHERE id=?");
    $check->execute([$msg_id]);
    $row = $check->fetch();
    if (!$row || (!$is_admin && $row['sender_id'] != $user_id)) {
        echo json_encode(['error' => 'Permission denied']); exit;
    }

    $pdo->prepare("UPDATE staff_messages SET is_deleted=1, message='[Message deleted]' WHERE id=?")->execute([$msg_id]);
    echo json_encode(['success' => true]);
    exit;
}

// ── REACT TO MESSAGE ────────────────────────────────────────────
if ($action === 'react') {
    $msg_id = (int)($_POST['msg_id'] ?? 0);
    $emoji  = mb_substr(trim($_POST['emoji'] ?? ''), 0, 10);
    if (!$msg_id || !$emoji) { echo json_encode(['error' => 'Invalid']); exit; }

    // Toggle: if already reacted with same emoji, remove; else add
    $check = $pdo->prepare("SELECT id FROM staff_reactions WHERE message_id=? AND user_id=? AND emoji=?");
    $check->execute([$msg_id, $user_id, $emoji]);
    if ($check->fetch()) {
        $pdo->prepare("DELETE FROM staff_reactions WHERE message_id=? AND user_id=? AND emoji=?")->execute([$msg_id, $user_id, $emoji]);
        $removed = true;
    } else {
        $pdo->prepare("INSERT IGNORE INTO staff_reactions (message_id, user_id, emoji) VALUES (?,?,?)")->execute([$msg_id, $user_id, $emoji]);
        $removed = false;
    }

    // Return updated counts
    $rstmt = $pdo->prepare("SELECT emoji, COUNT(*) as cnt, MAX(user_id=?) as i_reacted FROM staff_reactions WHERE message_id=? GROUP BY emoji ORDER BY cnt DESC");
    $rstmt->execute([$user_id, $msg_id]);
    echo json_encode(['success' => true, 'reactions' => $rstmt->fetchAll(), 'removed' => $removed]);
    exit;
}

// ── PIN / UNPIN MESSAGE ─────────────────────────────────────────
if ($action === 'pin') {
    if (!$is_admin) { echo json_encode(['error' => 'Admins only']); exit; }
    $msg_id = (int)($_POST['msg_id'] ?? 0);
    $pin    = (int)($_POST['pin'] ?? 1); // 1 = pin, 0 = unpin
    $pdo->prepare("UPDATE staff_messages SET is_pinned=? WHERE id=?")->execute([$pin, $msg_id]);
    echo json_encode(['success' => true]);
    exit;
}

// ── TYPING INDICATOR ────────────────────────────────────────────
if ($action === 'typing') {
    try {
        $pdo->prepare("INSERT INTO staff_typing (user_id, channel, last_typed) VALUES (?,?,NOW()) ON DUPLICATE KEY UPDATE last_typed=NOW()")->execute([$user_id, $channel]);
    } catch(Exception $e){}
    echo json_encode(['success' => true]);
    exit;
}

// ── POST ANNOUNCEMENT ───────────────────────────────────────────
if ($action === 'announce') {
    if (!$is_admin) { echo json_encode(['error' => 'Admins only']); exit; }
    $title    = trim($_POST['title'] ?? '');
    $body     = trim($_POST['body'] ?? '');
    $priority = in_array($_POST['priority'] ?? '', ['normal','urgent','critical']) ? $_POST['priority'] : 'normal';
    if (empty($title) || empty($body)) { echo json_encode(['error' => 'Missing fields']); exit; }

    $stmt = $pdo->prepare("INSERT INTO staff_announcements (author_id, title, body, priority) VALUES (?,?,?,?)");
    $stmt->execute([$user_id, $title, $body, $priority]);

    $sysMsg = "📢 [{$priority}] {$title}: {$body}";
    $pdo->prepare("INSERT INTO staff_messages (sender_id, channel, message, msg_type) VALUES (?,?,?,'announcement')")->execute([$user_id, 'general', $sysMsg]);
    echo json_encode(['success' => true]);
    exit;
}

// ── FETCH ONLINE USERS ──────────────────────────────────────────
if ($action === 'users') {
    $stmt = $pdo->query("SELECT id, username, full_name, role FROM users ORDER BY username ASC");
    echo json_encode(['users' => $stmt ? $stmt->fetchAll() : []]);
    exit;
}

echo json_encode(['error' => 'Unknown action']);
?>
