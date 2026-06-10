<?php
// modules/users/chat.php — Staff Communication Hub (Telegram-style)
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /Bosa Addis/auth/login.php'); exit;
}

// Ensure all chat tables exist (including advanced features)
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

    // Add advanced columns if missing
    foreach(['reply_to INT DEFAULT NULL','is_edited TINYINT(1) DEFAULT 0','is_deleted TINYINT(1) DEFAULT 0','is_pinned TINYINT(1) DEFAULT 0'] as $_col) {
        try { $pdo->exec("ALTER TABLE staff_messages ADD COLUMN $_col"); } catch(Exception $e){}
    }

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
        UNIQUE KEY `uniq_reaction` (`message_id`,`user_id`,`emoji`),
        INDEX(`message_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `staff_typing` (
        `user_id` INT NOT NULL,
        `channel` VARCHAR(50) NOT NULL,
        `last_typed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY(`user_id`,`channel`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `staff_online` (
        `user_id` INT NOT NULL PRIMARY KEY,
        `last_seen` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

} catch (Exception $e) {}

$current_user_id   = $_SESSION['user_id'];
$current_username  = $_SESSION['username'] ?? 'User';
$current_role      = $_SESSION['role'] ?? 'staff';
$is_admin          = $current_role === 'admin';

// Get all staff users
$usersStmt = $pdo->query("SELECT id, username, full_name, role FROM users ORDER BY username ASC");
$all_users = $usersStmt ? $usersStmt->fetchAll() : [];

$channels = [
    'general'  => ['icon' => 'fas fa-hashtag', 'label' => 'General',    'color' => '#2563eb'],
    'updates'  => ['icon' => 'fas fa-bell',    'label' => 'Updates',    'color' => '#16a34a'],
    'news'     => ['icon' => 'fas fa-newspaper','label' => 'News',      'color' => '#d97706'],
    'tasks'    => ['icon' => 'fas fa-list-check','label' => 'Tasks',     'color' => '#7c3aed'],
    'random'   => ['icon' => 'fas fa-shuffle', 'label' => 'Random',     'color' => '#db2777'],
];
?>
<!-- ============================================================
     Staff Communication Hub — Telegram-style
============================================================ -->
<style>
/* ── Reset & Fonts ───────────────────────────────────────────── */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
.chat-root * { box-sizing: border-box; font-family: 'Inter', sans-serif; }

/* ── Layout Shell ────────────────────────────────────────────── */
.chat-root {
    display: flex;
    height: calc(100vh - 100px);
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 32px 64px rgba(0,0,0,0.12);
    background: #fff;
    border: 1px solid rgba(0,0,0,0.06);
}

/* ── Left Sidebar ────────────────────────────────────────────── */
.chat-sidebar {
    width: 280px;
    min-width: 280px;
    background: linear-gradient(180deg, #17212b 0%, #0e1621 100%);
    display: flex;
    flex-direction: column;
    border-right: 1px solid rgba(255,255,255,0.04);
}
.chat-sidebar-header {
    padding: 20px 16px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}
.chat-sidebar-header h6 {
    color: #fff;
    font-weight: 800;
    font-size: 1.05rem;
    margin: 0;
    letter-spacing: 0.3px;
}
.chat-search {
    margin: 12px 16px;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 12px;
    display: flex;
    align-items: center;
    padding: 8px 12px;
    gap: 8px;
}
.chat-search input {
    background: transparent; border: none; outline: none;
    color: #fff; font-size: 0.85rem; width: 100%;
}
.chat-search input::placeholder { color: rgba(255,255,255,0.35); }
.chat-search i { color: rgba(255,255,255,0.4); font-size: 0.8rem; }

.sidebar-section-label {
    color: rgba(255,255,255,0.35);
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    padding: 12px 20px 6px;
}
.chat-channel-list { overflow-y: auto; flex: 1; padding-bottom: 10px; }
.chat-channel-list::-webkit-scrollbar { width: 4px; }
.chat-channel-list::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 2px; }

.channel-item {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 20px;
    cursor: pointer;
    border-radius: 10px;
    margin: 2px 8px;
    transition: all 0.2s ease;
    color: rgba(255,255,255,0.55);
    font-size: 0.875rem; font-weight: 500;
    text-decoration: none;
}
.channel-item:hover { background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.9); }
.channel-item.active { background: rgba(37,99,235,0.18); color: #fff; font-weight: 600; }
.channel-item .ch-icon { width: 32px; height: 32px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; flex-shrink: 0; }
.channel-item .ch-badge { margin-left: auto; background: #ef4444; color: #fff; font-size: 0.65rem; font-weight: 700; border-radius: 20px; padding: 1px 7px; min-width: 18px; text-align: center; }

.user-item {
    display: flex; align-items: center; gap: 10px;
    padding: 7px 20px;
    cursor: pointer;
    border-radius: 10px;
    margin: 2px 8px;
    transition: all 0.2s ease;
    color: rgba(255,255,255,0.5);
    font-size: 0.82rem;
    text-decoration: none;
}
.user-item:hover { background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.85); }
.user-avatar-sm {
    width: 28px; height: 28px; border-radius: 50%;
    background: linear-gradient(135deg, #2563eb, #7c3aed);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.7rem; font-weight: 700; color: #fff; flex-shrink: 0;
    text-transform: uppercase;
}
.online-dot { width: 8px; height: 8px; border-radius: 50%; background: #22c55e; margin-left: auto; flex-shrink: 0; box-shadow: 0 0 6px #22c55e; animation: pulse-green 2s infinite; }
@keyframes pulse-green { 0%,100%{opacity:1;} 50%{opacity:0.5;} }

/* ── Chat Main Area ──────────────────────────────────────────── */
.chat-main {
    flex: 1; display: flex; flex-direction: column;
    background: #f0f2f5;
    min-width: 0;
}
.chat-topbar {
    background: #fff;
    padding: 14px 24px;
    border-bottom: 1px solid #f0f2f5;
    display: flex; align-items: center; gap: 14px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.chat-topbar .ch-title { font-weight: 800; font-size: 1rem; color: #0f172a; }
.chat-topbar .ch-subtitle { font-size: 0.75rem; color: #64748b; font-weight: 500; }
.chat-topbar .topbar-icon { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
.topbar-actions { margin-left: auto; display: flex; gap: 10px; }
.topbar-btn { background: none; border: none; width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #64748b; cursor: pointer; transition: all 0.2s; font-size: 1rem; }
.topbar-btn:hover { background: #f1f5f9; color: #2563eb; }

/* ── Messages Area ───────────────────────────────────────────── */
.chat-messages {
    flex: 1; overflow-y: auto; padding: 20px 24px;
    display: flex; flex-direction: column; gap: 4px;
}
.chat-messages::-webkit-scrollbar { width: 5px; }
.chat-messages::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

.msg-date-divider {
    text-align: center; margin: 12px 0;
    font-size: 0.72rem; color: #94a3b8; font-weight: 600;
    position: relative;
}
.msg-date-divider::before, .msg-date-divider::after {
    content: ''; position: absolute; top: 50%; width: calc(50% - 60px);
    height: 1px; background: #e2e8f0;
}
.msg-date-divider::before { left: 0; }
.msg-date-divider::after { right: 0; }

.msg-group { display: flex; flex-direction: column; gap: 2px; margin-bottom: 6px; }
.msg-row { display: flex; align-items: flex-end; gap: 8px; }
.msg-row.mine { flex-direction: row-reverse; }

.msg-avatar { width: 34px; height: 34px; border-radius: 10px; background: linear-gradient(135deg,#2563eb,#7c3aed); display:flex;align-items:center;justify-content:center; font-size:0.75rem;font-weight:700;color:#fff; flex-shrink:0; text-transform:uppercase; }

.msg-bubble {
    max-width: 68%; padding: 10px 14px;
    border-radius: 18px;
    font-size: 0.875rem; line-height: 1.5;
    position: relative; word-break: break-word;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    transition: all 0.2s;
}
.msg-bubble.theirs {
    background: #fff; color: #1e293b;
    border-top-left-radius: 4px;
}
.msg-bubble.mine {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff; border-top-right-radius: 4px;
}
.msg-bubble.announcement-bubble {
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    color: #92400e; border: 1px solid #fbbf24;
    border-radius: 16px; max-width: 85%;
}
.msg-bubble:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }

.msg-name { font-size: 0.72rem; font-weight: 700; color: #2563eb; margin-bottom: 3px; }
.msg-time { font-size: 0.65rem; opacity: 0.6; margin-top: 4px; text-align: right; }
.msg-row.mine .msg-time { color: rgba(255,255,255,0.7); }

.msg-image { max-width: 240px; border-radius: 12px; cursor: pointer; transition: opacity 0.2s; }
.msg-image:hover { opacity: 0.9; }
.msg-file-link { display:flex;align-items:center;gap:8px;padding:8px 12px;background:rgba(0,0,0,0.06);border-radius:10px;text-decoration:none;color:inherit;font-size:0.8rem;font-weight:600;margin-top:4px; }
.msg-file-link:hover { background:rgba(0,0,0,0.1); }

/* Typing indicator */
.typing-indicator { display:none; padding:4px 0; }
.typing-indicator.visible { display:flex; align-items:center; gap:6px; font-size:0.78rem; color:#94a3b8; }
.typing-dots span { display:inline-block; width:6px;height:6px;border-radius:50%;background:#94a3b8; animation:typing-dot 1.4s infinite; }
.typing-dots span:nth-child(2){animation-delay:0.2s;}
.typing-dots span:nth-child(3){animation-delay:0.4s;}
@keyframes typing-dot{0%,80%,100%{transform:scale(0.8);opacity:0.5}40%{transform:scale(1.2);opacity:1}}

/* ── Input Bar ───────────────────────────────────────────────── */
.chat-input-bar {
    background: #fff;
    padding: 14px 20px;
    border-top: 1px solid #f0f2f5;
    display: flex; align-items: flex-end; gap: 10px;
}
.chat-input-wrap {
    flex: 1; background: #f1f5f9; border-radius: 16px;
    display: flex; align-items: flex-end; gap: 8px;
    padding: 8px 14px; border: 2px solid transparent;
    transition: border-color 0.2s;
}
.chat-input-wrap:focus-within { border-color: #2563eb; background: #fff; }
.chat-textarea {
    flex: 1; background: transparent; border: none; outline: none;
    font-size: 0.9rem; resize: none; max-height: 120px; min-height: 24px;
    font-family: 'Inter', sans-serif; color: #1e293b; line-height: 1.5;
    padding: 0;
}
.chat-textarea::placeholder { color: #94a3b8; }
.attach-btn { background: none; border: none; color: #94a3b8; cursor: pointer; padding: 2px; font-size: 1.1rem; transition: color 0.2s; flex-shrink: 0; }
.attach-btn:hover { color: #2563eb; }
#file-input { display: none; }
.chat-send-btn {
    width: 48px; height: 48px; border-radius: 50%; border: none;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff; font-size: 1rem; cursor: pointer; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.3s cubic-bezier(0.175,0.885,0.32,1.275);
    box-shadow: 0 4px 14px rgba(37,99,235,0.35);
}
.chat-send-btn:hover { transform: scale(1.08); box-shadow: 0 6px 20px rgba(37,99,235,0.5); }
.chat-send-btn:active { transform: scale(0.95); }

/* File preview */
.file-preview-bar {
    display: none; padding: 8px 20px;
    background: #eff6ff; border-top: 1px solid #bfdbfe;
    align-items: center; gap: 10px; font-size: 0.82rem;
}
.file-preview-bar.visible { display: flex; }
.file-preview-bar .remove-file { background: none; border: none; color: #ef4444; cursor: pointer; font-size: 1rem; }

/* ── Right Panel (Members) ───────────────────────────────────── */
.chat-members-panel {
    width: 240px; min-width: 240px;
    background: #fff;
    border-left: 1px solid #f0f2f5;
    display: flex; flex-direction: column;
    overflow: hidden;
}
.members-header {
    padding: 18px 20px 14px;
    border-bottom: 1px solid #f0f2f5;
    font-weight: 800; font-size: 0.9rem; color: #1e293b;
    display: flex; align-items: center; gap: 8px;
}
.members-header i { color: #2563eb; }
.members-list { overflow-y: auto; flex: 1; padding: 12px 0; }
.member-row {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 20px; cursor: pointer; transition: background 0.15s;
    font-size: 0.82rem; color: #374151;
}
.member-row:hover { background: #f8fafc; }
.member-row .member-avatar {
    width: 34px; height: 34px; border-radius: 10px;
    background: linear-gradient(135deg,#2563eb,#7c3aed);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.75rem; font-weight: 700; color: #fff; flex-shrink: 0;
    text-transform: uppercase;
}
.member-row .member-name { font-weight: 600; }
.member-row .member-role { font-size: 0.68rem; color: #94a3b8; }
.member-status { width: 9px; height: 9px; border-radius: 50%; background: #22c55e; margin-left: auto; box-shadow: 0 0 4px #22c55e; }

/* ── Announcement Panel ──────────────────────────────────────── */
.announcement-bar {
    display: none; padding: 0 24px 10px;
    flex-direction: column; gap: 8px;
}
.announcement-bar.visible { display: flex; }
.ann-card {
    background: linear-gradient(135deg, #fff7ed, #fef3c7);
    border: 1px solid #fde68a; border-radius: 14px; padding: 12px 16px;
    display: flex; gap: 12px; align-items: flex-start;
    box-shadow: 0 2px 8px rgba(217,119,6,0.08);
}
.ann-card .ann-icon { font-size: 1.3rem; flex-shrink: 0; margin-top: 2px; }
.ann-card .ann-title { font-weight: 700; font-size: 0.85rem; color: #92400e; }
.ann-card .ann-body { font-size: 0.78rem; color: #b45309; margin-top: 2px; }
.ann-card .ann-priority { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; border-radius: 20px; padding: 2px 8px; background: #92400e; color: #fff; display: inline-block; margin-top: 4px; }
.ann-card .ann-priority.urgent { background: #ef4444; }
.ann-card .ann-priority.critical { background: #7f1d1d; }

/* ── Modal ───────────────────────────────────────────────────── */
.chat-modal-backdrop {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.5); z-index: 9999;
    align-items: center; justify-content: center;
}
.chat-modal-backdrop.open { display: flex; }
.chat-modal {
    background: #fff; border-radius: 20px; padding: 30px;
    width: 95%; max-width: 420px; box-shadow: 0 32px 64px rgba(0,0,0,0.2);
    animation: modal-in 0.3s cubic-bezier(0.175,0.885,0.32,1.275);
}
@keyframes modal-in { from{transform:scale(0.85);opacity:0} to{transform:scale(1);opacity:1} }
.chat-modal h5 { font-weight: 800; color: #1e293b; margin-bottom: 18px; }
.chat-modal .form-control, .chat-modal .form-select { border-radius: 10px; border: 1.5px solid #e2e8f0; padding: 10px 14px; font-size: 0.875rem; }
.chat-modal .form-control:focus, .chat-modal .form-select:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.12); }
.chat-modal .btn-primary { background: linear-gradient(135deg,#2563eb,#1d4ed8); border: none; border-radius: 10px; font-weight: 700; padding: 10px 20px; }
.chat-modal .modal-close { background: none; border: none; font-size: 1.2rem; color: #94a3b8; cursor: pointer; float: right; margin-top: -8px; }

/* ── Responsive ──────────────────────────────────────────────── */
@media (max-width: 1024px) {
    .chat-members-panel { display: none; }
}
@media (max-width: 768px) {
    .chat-sidebar { width: 220px; min-width: 220px; }
}
@media (max-width: 576px) {
    .chat-sidebar { display: none; }
    .chat-root { height: calc(100vh - 80px); }
}

/* Empty state */
.chat-empty {
    flex: 1; display: flex; flex-direction: column;
    align-items: center; justify-content: center; gap: 12px;
    color: #94a3b8;
}
.chat-empty i { font-size: 3rem; opacity: 0.4; }
.chat-empty p { font-size: 0.9rem; font-weight: 600; }

/* Page header */
.page-chat-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 16px;
}
.page-chat-header h2 { font-weight: 900; font-size: 1.5rem; color: #0f172a; display: flex; align-items: center; gap: 10px; }
.page-chat-header p { color: #64748b; font-size:0.85rem; margin:0; }
</style>

<!-- Page Header -->
<div class="page-chat-header">
    <div>
        <h2><i class="fas fa-comments" style="color:#2563eb;"></i> <?php echo __('staff_communication_hub'); ?></h2>
        <p><?php echo __('chat_desc'); ?></p>
    </div>
    <?php if ($is_admin): ?>
    <button class="btn btn-warning fw-bold shadow-sm" onclick="openAnnounceModal()">
        <i class="fas fa-bullhorn me-2"></i><?php echo __('post_announcement'); ?>
    </button>
    <?php endif; ?>
</div>

<!-- ════════════════════════════════════════════════════════════ -->
<div class="chat-root" id="chatRoot">

    <!-- ── Left Sidebar ─────────────────────────────────────── -->
    <div class="chat-sidebar">
        <div class="chat-sidebar-header">
            <h6><i class="fas fa-tower-broadcast me-2" style="color:#2563eb;"></i><?php echo __('staff_channels'); ?></h6>
        </div>

        <div class="chat-search">
            <i class="fas fa-search"></i>
            <input type="text" id="sidebarSearch" placeholder="<?php echo __('search_channels'); ?>" onkeyup="filterSidebar(this.value)">
        </div>

        <div class="chat-channel-list" id="channelList">
            <div class="sidebar-section-label"><?php echo __('channels'); ?></div>
            <?php foreach ($channels as $ch_key => $ch): ?>
            <div class="channel-item <?= $ch_key === 'general' ? 'active' : '' ?>"
                 onclick="switchChannel('<?= $ch_key ?>', this)"
                 data-channel="<?= $ch_key ?>">
                <div class="ch-icon" style="background: <?= $ch['color'] ?>22; color: <?= $ch['color'] ?>;">
                    <i class="<?= $ch['icon'] ?>"></i>
                </div>
                <span><?= $ch['label'] ?></span>
            </div>
            <?php endforeach; ?>

            <div class="sidebar-section-label mt-3"><?php echo __('direct_messages'); ?></div>
            <?php foreach ($all_users as $u): ?>
                <?php if ($u['id'] == $current_user_id) continue; ?>
                <div class="user-item"
                     onclick="switchChannel('dm_<?= $u['id'] ?>', this)"
                     data-channel="dm_<?= $u['id'] ?>">
                    <div class="user-avatar-sm"><?= mb_substr($u['username'], 0, 2) ?></div>
                    <span><?= htmlspecialchars($u['full_name'] ?: $u['username']) ?></span>
                    <span class="online-dot"></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ── Main Chat Area ───────────────────────────────────── -->
    <div class="chat-main">
        <!-- Top Bar -->
        <div class="chat-topbar">
            <div class="topbar-icon" id="topbarIcon" style="background:#2563eb22; color:#2563eb;">
                <i class="fas fa-hashtag"></i>
            </div>
            <div>
                <div class="ch-title" id="topbarTitle">General</div>
                <div class="ch-subtitle" id="topbarSubtitle">Public channel for all staff</div>
            </div>
            <div class="topbar-actions">
                <button class="topbar-btn" title="Search messages" onclick="toggleSearch()"><i class="fas fa-search"></i></button>
                <button class="topbar-btn" title="Pin / Info"><i class="fas fa-thumbtack"></i></button>
                <button class="topbar-btn" title="Members"><i class="fas fa-users"></i></button>
            </div>
        </div>

        <!-- Search bar (hidden by default) -->
        <div id="msgSearchBar" style="display:none; padding:8px 24px; background:#f8fafc; border-bottom:1px solid #e2e8f0;">
            <input type="text" class="form-control form-control-sm" placeholder="Search in this channel..." id="msgSearchInput" oninput="searchMessages(this.value)">
        </div>

        <!-- Announcements -->
        <div class="announcement-bar" id="announcementBar"></div>

        <!-- Messages -->
        <div class="chat-messages" id="chatMessages">
            <div class="chat-empty" id="emptyState">
                <i class="fas fa-comments"></i>
                <p><?php echo __('no_messages_yet'); ?></p>
            </div>
        </div>

        <!-- Typing indicator -->
        <div style="padding:0 24px 4px;">
            <div class="typing-indicator" id="typingIndicator">
                <div class="typing-dots"><span></span><span></span><span></span></div>
                <span id="typingText"><?php echo __('someone_typing'); ?></span>
            </div>
        </div>

        <!-- File preview bar -->
        <div class="file-preview-bar" id="filePreviewBar">
            <i class="fas fa-paperclip" style="color:#2563eb;"></i>
            <span id="filePreviewName"></span>
            <button class="remove-file" onclick="removeFile()"><i class="fas fa-times"></i></button>
        </div>

        <!-- Input Bar -->
        <div class="chat-input-bar">
            <div class="chat-input-wrap">
                <button class="attach-btn" onclick="document.getElementById('file-input').click()" title="Attach file">
                    <i class="fas fa-paperclip"></i>
                </button>
                <input type="file" id="file-input" onchange="handleFileSelect(this)">
                <textarea class="chat-textarea" id="msgInput" rows="1"
                    placeholder="<?php echo __('write_message'); ?>"
                    onkeydown="handleInputKey(event)"
                    oninput="autoGrow(this)"></textarea>
                <button class="attach-btn" onclick="insertEmoji()" title="Emoji">
                    <i class="fas fa-face-smile"></i>
                </button>
            </div>
            <button class="chat-send-btn" onclick="sendMessage()" title="Send">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>

    <!-- ── Right Members Panel ──────────────────────────────── -->
    <div class="chat-members-panel">
        <div class="members-header"><i class="fas fa-users"></i> <?php echo __('members'); ?> (<?= count($all_users) ?>)</div>
        <div class="members-list">
            <?php foreach ($all_users as $u): ?>
            <div class="member-row" onclick="switchChannel('dm_<?= $u['id'] ?>', null)" title="Send DM">
                <div class="member-avatar"><?= mb_substr($u['username'], 0, 2) ?></div>
                <div>
                    <div class="member-name"><?= htmlspecialchars($u['full_name'] ?: $u['username']) ?></div>
                    <div class="member-role"><?= htmlspecialchars($u['role']) ?></div>
                </div>
                <div class="member-status"></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ── Announce Modal ──────────────────────────────────────── -->
<?php if ($is_admin): ?>
<div class="chat-modal-backdrop" id="announceModal">
    <div class="chat-modal">
        <button class="modal-close" onclick="closeAnnounceModal()"><i class="fas fa-times"></i></button>
        <h5><i class="fas fa-bullhorn me-2 text-warning"></i><?php echo __('post_announcement'); ?></h5>
        <div class="d-flex flex-column gap-3">
            <div>
                <label class="form-label fw-bold small"><?php echo __('title'); ?></label>
                <input type="text" class="form-control" id="annTitle" placeholder="Announcement title...">
            </div>
            <div>
                <label class="form-label fw-bold small"><?php echo __('message_label'); ?></label>
                <textarea class="form-control" id="annBody" rows="4" placeholder="Write your announcement..."></textarea>
            </div>
            <div>
                <label class="form-label fw-bold small"><?php echo __('priority'); ?></label>
                <select class="form-select" id="annPriority">
                    <option value="normal">📢 <?php echo __('priority_normal'); ?></option>
                    <option value="urgent">🔴 <?php echo __('priority_urgent'); ?></option>
                    <option value="critical">🚨 <?php echo __('priority_critical'); ?></option>
                </select>
            </div>
            <div class="d-flex gap-2 justify-content-end">
                <button class="btn btn-light rounded-pill px-4" onclick="closeAnnounceModal()"><?php echo __('cancel'); ?></button>
                <button class="btn btn-primary rounded-pill px-4" onclick="postAnnouncement()">
                    <i class="fas fa-paper-plane me-2"></i><?php echo __('publish'); ?>
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Image lightbox -->
<div class="chat-modal-backdrop" id="imageLightbox" onclick="this.classList.remove('open')">
    <img src="" id="lightboxImg" style="max-width:90vw;max-height:90vh;border-radius:16px;box-shadow:0 32px 64px rgba(0,0,0,0.4);">
</div>

<!-- Emoji Picker Panel -->
<div id="emojiPickerPanel" style="display:none;position:absolute;bottom:80px;right:80px;z-index:9990;background:#fff;border-radius:16px;box-shadow:0 10px 40px rgba(0,0,0,0.15);padding:12px;width:300px;border:1px solid #e2e8f0;">
    <div style="font-size:0.7rem;font-weight:700;color:#94a3b8;margin-bottom:8px;text-transform:uppercase;letter-spacing:1px;">Quick Reactions</div>
    <div id="emojiGrid" style="display:flex;flex-wrap:wrap;gap:4px;"></div>
</div>

<!-- Context Menu -->
<div id="msgContextMenu" style="display:none;position:fixed;z-index:9995;background:#fff;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.15);border:1px solid #e2e8f0;padding:6px;min-width:160px;">
    <div id="ctxReply"   onclick="ctxAction('reply')"  style="padding:8px 14px;cursor:pointer;border-radius:8px;font-size:0.85rem;display:flex;align-items:center;gap:8px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background=''"><i class="fas fa-reply" style="color:#2563eb;width:16px;"></i><?php echo __('reply'); ?></div>
    <div id="ctxEdit"    onclick="ctxAction('edit')"   style="padding:8px 14px;cursor:pointer;border-radius:8px;font-size:0.85rem;display:flex;align-items:center;gap:8px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background=''"><i class="fas fa-pen" style="color:#d97706;width:16px;"></i><?php echo __('edit'); ?></div>
    <div id="ctxPin"     onclick="ctxAction('pin')"    style="padding:8px 14px;cursor:pointer;border-radius:8px;font-size:0.85rem;display:flex;align-items:center;gap:8px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background=''"><i class="fas fa-thumbtack" style="color:#7c3aed;width:16px;"></i><?php echo __('pin'); ?></div>
    <hr style="margin:4px 0;border-color:#f1f5f9;">
    <div id="ctxDelete"  onclick="ctxAction('delete')" style="padding:8px 14px;cursor:pointer;border-radius:8px;font-size:0.85rem;display:flex;align-items:center;gap:8px;color:#ef4444;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background=''"><i class="fas fa-trash" style="width:16px;"></i><?php echo __('delete'); ?></div>
</div>

<!-- Edit bar (shown above input when editing) -->
<div id="editBar" style="display:none;padding:6px 24px;background:#eff6ff;border-top:1px solid #bfdbfe;align-items:center;gap:10px;font-size:0.82rem;color:#1d4ed8;">
    <i class="fas fa-pen"></i><span id="editBarText" style="flex:1;font-weight:600;"></span>
    <button onclick="cancelEdit()" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:1rem;"><i class="fas fa-times"></i></button>
</div>

<!-- Reply bar -->
<div id="replyBar" style="display:none;padding:6px 24px;background:#f0fdf4;border-top:1px solid #bbf7d0;align-items:center;gap:10px;font-size:0.82rem;color:#15803d;">
    <i class="fas fa-reply"></i><span id="replyBarText" style="flex:1;font-style:italic;"></span>
    <button onclick="cancelReply()" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:1rem;"><i class="fas fa-times"></i></button>
</div>

<!-- Pinned messages bar -->
<div id="pinnedBar" style="display:none;padding:8px 24px;background:linear-gradient(90deg,#f5f3ff,#ede9fe);border-bottom:1px solid #ddd6fe;align-items:center;gap:10px;font-size:0.8rem;cursor:pointer;" onclick="togglePinnedPanel()">
    <i class="fas fa-thumbtack" style="color:#7c3aed;"></i>
    <span id="pinnedBarText" style="font-weight:600;color:#5b21b6;flex:1;"></span>
    <i class="fas fa-chevron-down" style="color:#7c3aed;font-size:0.7rem;"></i>
</div>

<script>
const ME       = <?= json_encode($current_user_id) ?>;
const ME_NAME  = <?= json_encode($current_username) ?>;
const IS_ADMIN = <?= json_encode($is_admin) ?>;
const API_URL  = '/Bosa Addis/modules/users/chat_api.php';

let currentChannel = 'general';
let lastMsgId      = 0;
let pollTimer      = null;
let allMessages    = [];
let replyToId      = null;
let replyToText    = '';
let editMsgId      = null;
let ctxMsgId       = null;
let ctxIsMe        = false;
let typingTimer    = null;

const channelMeta = <?= json_encode($channels) ?>;
const ALL_EMOJIS  = ['😊','😂','👍','❤️','🔥','✅','⚡','📌','🎉','🙏','👏','💪','😎','🤔','😅','🥳','🙌','👀','💡','🚀','📣','⚠️','🛑','✔️','🔔','📅'];

// Build emoji picker grid
(function(){
    const grid = document.getElementById('emojiGrid');
    ALL_EMOJIS.forEach(e => {
        const span = document.createElement('span');
        span.textContent = e;
        span.style.cssText = 'font-size:1.4rem;cursor:pointer;padding:4px;border-radius:6px;transition:transform 0.15s;';
        span.onmouseover = () => span.style.transform = 'scale(1.3)';
        span.onmouseout  = () => span.style.transform = '';
        span.onclick = () => { insertEmojiChar(e); document.getElementById('emojiPickerPanel').style.display='none'; };
        grid.appendChild(span);
    });
})();

// ── Utilities ──────────────────────────────────────────────────
function timeAgo(dateStr) {
    const d = new Date(dateStr.replace(' ','T'));
    const diff = Math.floor((new Date()-d)/1000);
    if (diff < 60)    return 'Just now';
    if (diff < 3600)  return Math.floor(diff/60) + 'm ago';
    if (diff < 86400) return d.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});
    return d.toLocaleDateString([],{month:'short',day:'numeric'})+' '+d.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});
}
function nameInitials(n){ return (n||'?').substring(0,2).toUpperCase(); }
function avatarColor(uid){ const c=['#2563eb','#7c3aed','#db2777','#d97706','#16a34a','#0891b2','#dc2626']; return c[parseInt(uid)%c.length]; }
function scrollBottom(smooth=true){ const b=document.getElementById('chatMessages'); b.scrollTo({top:b.scrollHeight,behavior:smooth?'smooth':'instant'}); }
function escapeHtml(s){ return (s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

// ── Render Messages ───────────────────────────────────────────
function renderMessages(msgs, append=false) {
    const box = document.getElementById('chatMessages');
    if (!append) { allMessages=[]; box.innerHTML=''; }
    if (!msgs.length && !allMessages.length) {
        box.innerHTML='<div class="chat-empty" id="emptyState"><i class="fas fa-comments"></i><p>No messages yet. Say something!</p></div>';
        return;
    }
    const emptyEl = box.querySelector('.chat-empty');
    if (emptyEl) emptyEl.remove();

    let lastDate='';
    msgs.forEach(msg => {
        allMessages.push(msg);
        const msgDate = new Date(msg.created_at.replace(' ','T')).toDateString();
        if (msgDate !== lastDate) {
            lastDate = msgDate;
            const div = document.createElement('div');
            div.className='msg-date-divider';
            div.textContent = msgDate===new Date().toDateString()?'Today':msgDate;
            box.appendChild(div);
        }
        box.appendChild(buildMsgRow(msg));
    });
    scrollBottom(!append);
}

function buildMsgRow(msg) {
    const isMe  = parseInt(msg.is_me)===1;
    const isAnn = msg.msg_type==='announcement';
    const isDel = parseInt(msg.is_deleted||0)===1;

    const row = document.createElement('div');
    row.className = 'msg-row'+(isMe?' mine':'')+(isAnn?' announcement':'');
    row.dataset.id = msg.id;
    row.addEventListener('contextmenu', e => showContextMenu(e, msg.id, isMe));

    const avatarHtml = (!isMe && !isAnn)
        ? `<div class="msg-avatar" style="background:linear-gradient(135deg,${avatarColor(msg.uid)},${avatarColor(msg.uid+'1')})">${nameInitials(msg.full_name||msg.username)}</div>`
        : '';

    // Reply preview
    let replyHtml = '';
    if (msg.reply_to && msg.reply_msg) {
        const rAuthor = msg.reply_author || msg.reply_username || 'Someone';
        replyHtml = `<div style="background:rgba(0,0,0,0.06);border-left:3px solid #2563eb;border-radius:8px;padding:4px 8px;margin-bottom:5px;font-size:0.75rem;opacity:0.85;"><span style="font-weight:700;color:#2563eb;">${escapeHtml(rAuthor)}</span><br>${escapeHtml((msg.reply_msg||'').substring(0,80))}${msg.reply_msg&&msg.reply_msg.length>80?'…':''}</div>`;
    }

    // Bubble content
    let bubbleContent = '';
    if (isDel) {
        bubbleContent = `<span style="opacity:0.5;font-style:italic;">🗑 Message deleted</span>`;
    } else if (msg.msg_type==='image') {
        bubbleContent = `<img src="/Bosa Addis/uploads/chat/${msg.file_path}" class="msg-image" onclick="openLightbox(this.src)" alt="image">`;
    } else if (msg.msg_type==='file') {
        bubbleContent = `<a class="msg-file-link" href="/Bosa Addis/uploads/chat/${msg.file_path}" target="_blank"><i class="fas fa-file"></i>${escapeHtml(msg.message)}</a>`;
    } else {
        bubbleContent = escapeHtml(msg.message);
    }

    // Reactions
    let reactHtml = '';
    if (msg.reactions && msg.reactions.length) {
        reactHtml = `<div class="msg-reactions">${msg.reactions.map(r=>`<span class="react-chip${parseInt(r.i_reacted)?'  react-mine':''}" onclick="toggleReact(${msg.id},'${r.emoji}')" title="React">${r.emoji} <b>${r.cnt}</b></span>`).join('')}</div>`;
    }

    // Quick react button (hover)
    const quickReact = !isDel ? `<div class="quick-react-wrap"><button class="quick-react-btn" onclick="showQuickReact(event,${msg.id})" title="React">😊</button></div>` : '';

    const nameLabel = !isMe && !isAnn ? `<div class="msg-name">${escapeHtml(msg.full_name||msg.username)}</div>` : '';
    const editedLabel = parseInt(msg.is_edited||0) ? `<span style="font-size:0.62rem;opacity:0.55;margin-left:4px;">(edited)</span>` : '';
    const pinBadge = parseInt(msg.is_pinned||0) ? `<i class="fas fa-thumbtack" style="color:#7c3aed;font-size:0.65rem;margin-left:4px;" title="Pinned"></i>` : '';

    row.innerHTML = `
        ${avatarHtml}
        <div style="max-width:70%;position:relative;">
            ${nameLabel}
            ${quickReact}
            <div class="msg-bubble ${isMe?'mine':(isAnn?'announcement-bubble':'theirs')}">
                ${replyHtml}
                ${bubbleContent}
                <div class="msg-time">${timeAgo(msg.created_at)}${editedLabel}${pinBadge}</div>
            </div>
            ${reactHtml}
        </div>`;
    return row;
}

// ── Quick react inline picker ─────────────────────────────────
let quickReactMsgId = null;
const QUICK_EMOJIS = ['👍','❤️','😂','😮','😢','🔥','✅','👏'];

function showQuickReact(e, msgId) {
    e.stopPropagation();
    quickReactMsgId = msgId;
    // Build a tiny floating picker near the button
    let picker = document.getElementById('quickReactPicker');
    if (!picker) {
        picker = document.createElement('div');
        picker.id = 'quickReactPicker';
        picker.style.cssText = 'position:fixed;z-index:9999;background:#fff;border-radius:30px;box-shadow:0 8px 24px rgba(0,0,0,0.15);padding:6px 10px;display:flex;gap:4px;border:1px solid #e2e8f0;';
        QUICK_EMOJIS.forEach(em => {
            const s = document.createElement('span');
            s.textContent = em;
            s.style.cssText='font-size:1.2rem;cursor:pointer;transition:transform 0.1s;';
            s.onmouseover=()=>s.style.transform='scale(1.3)';
            s.onmouseout=()=>s.style.transform='';
            s.onclick=()=>{ toggleReact(quickReactMsgId, em); picker.remove(); };
            picker.appendChild(s);
        });
        document.body.appendChild(picker);
    }
    const rect = e.target.getBoundingClientRect();
    picker.style.top  = (rect.top - 50) + 'px';
    picker.style.left = (rect.left - 60) + 'px';
    picker.style.display='flex';
    setTimeout(()=>document.addEventListener('click',()=>picker.remove(),{once:true}),50);
}

async function toggleReact(msgId, emoji) {
    const fd = new FormData();
    fd.append('action','react'); fd.append('msg_id',msgId); fd.append('emoji',emoji);
    try {
        const r = await fetch(API_URL,{method:'POST',body:fd});
        const data = await r.json();
        if (data.success) {
            // Refresh reactions on the row
            const row = document.querySelector(`.msg-row[data-id="${msgId}"]`);
            if (row) {
                let rDiv = row.querySelector('.msg-reactions');
                if (!rDiv) { rDiv=document.createElement('div'); rDiv.className='msg-reactions'; row.querySelector('div').appendChild(rDiv); }
                rDiv.innerHTML = data.reactions.map(r=>`<span class="react-chip${parseInt(r.i_reacted)?' react-mine':''}" onclick="toggleReact(${msgId},'${r.emoji}')">${r.emoji} <b>${r.cnt}</b></span>`).join('');
            }
        }
    } catch(e){}
}

// ── Context Menu ──────────────────────────────────────────────
function showContextMenu(e, msgId, isMe) {
    e.preventDefault();
    ctxMsgId = msgId; ctxIsMe = isMe;
    const menu = document.getElementById('msgContextMenu');
    document.getElementById('ctxEdit').style.display   = isMe ? '' : 'none';
    document.getElementById('ctxDelete').style.display = (isMe || IS_ADMIN) ? '' : 'none';
    document.getElementById('ctxPin').style.display    = IS_ADMIN ? '' : 'none';
    menu.style.display = 'block';
    menu.style.top  = Math.min(e.clientY, window.innerHeight-180)+'px';
    menu.style.left = Math.min(e.clientX, window.innerWidth-180)+'px';
    setTimeout(()=>document.addEventListener('click',()=>menu.style.display='none',{once:true}),50);
}

function ctxAction(act) {
    document.getElementById('msgContextMenu').style.display='none';
    if (!ctxMsgId) return;
    if (act==='reply') {
        const row = document.querySelector(`.msg-row[data-id="${ctxMsgId}"]`);
        const txt = row ? row.querySelector('.msg-bubble')?.textContent?.trim() : '';
        setReply(ctxMsgId, txt);
    } else if (act==='edit') {
        const row = document.querySelector(`.msg-row[data-id="${ctxMsgId}"]`);
        const bubble = row?.querySelector('.msg-bubble');
        const txt = bubble? bubble.childNodes[bubble.childNodes.length-2]?.textContent?.trim() || '' : '';
        startEdit(ctxMsgId, txt);
    } else if (act==='delete') {
        if (confirm('Delete this message?')) doDelete(ctxMsgId);
    } else if (act==='pin') {
        doPin(ctxMsgId);
    }
}

// ── Reply ─────────────────────────────────────────────────────
function setReply(id, text) {
    replyToId = id; replyToText = text;
    const bar = document.getElementById('replyBar');
    document.getElementById('replyBarText').textContent = text.substring(0,80)+(text.length>80?'…':'');
    bar.style.display='flex';
    document.getElementById('msgInput').focus();
}
function cancelReply() { replyToId=null; replyToText=''; document.getElementById('replyBar').style.display='none'; }

// ── Edit ──────────────────────────────────────────────────────
function startEdit(id, text) {
    editMsgId = id;
    const bar = document.getElementById('editBar');
    document.getElementById('editBarText').textContent = 'Editing: '+text.substring(0,60);
    bar.style.display='flex';
    const input = document.getElementById('msgInput');
    input.value = text; input.focus(); autoGrow(input);
}
function cancelEdit() { editMsgId=null; document.getElementById('editBar').style.display='none'; document.getElementById('msgInput').value=''; }

async function doDelete(msgId) {
    const fd=new FormData(); fd.append('action','delete'); fd.append('msg_id',msgId);
    try { await fetch(API_URL,{method:'POST',body:fd}); fetchMessages(false); } catch(e){}
}

async function doPin(msgId) {
    const fd=new FormData(); fd.append('action','pin'); fd.append('msg_id',msgId); fd.append('pin',1);
    try { await fetch(API_URL,{method:'POST',body:fd}); fetchMessages(true); } catch(e){}
}

// ── Pinned Messages ───────────────────────────────────────────
function renderPinned(pinned) {
    const bar = document.getElementById('pinnedBar');
    if (!pinned || !pinned.length) { bar.style.display='none'; return; }
    bar.style.display='flex';
    document.getElementById('pinnedBarText').textContent = `📌 Pinned: ${pinned[0].message.substring(0,60)}${pinned[0].message.length>60?'…':''}`;
}

// ── Announcements ─────────────────────────────────────────────
function renderAnnouncements(anns) {
    const bar = document.getElementById('announcementBar');
    if (!anns||!anns.length) { bar.classList.remove('visible'); return; }
    bar.classList.add('visible');
    const icons={normal:'📢',urgent:'🔴',critical:'🚨'};
    bar.innerHTML = anns.slice(0,2).map(a=>`
        <div class="ann-card">
            <div class="ann-icon">${icons[a.priority]||'📢'}</div>
            <div>
                <div class="ann-title">${escapeHtml(a.title)}</div>
                <div class="ann-body">${escapeHtml(a.body)}</div>
                <span class="ann-priority ${a.priority}">${a.priority.toUpperCase()}</span>
                <span style="font-size:0.7rem;color:#92400e;margin-left:8px;">by ${escapeHtml(a.username)} · ${timeAgo(a.created_at)}</span>
            </div>
        </div>`).join('');
}

// ── Typing indicator ──────────────────────────────────────────
function renderTyping(typing) {
    const el = document.getElementById('typingIndicator');
    const txt = document.getElementById('typingText');
    if (!typing||!typing.length) { el.classList.remove('visible'); return; }
    const names = typing.map(t=>t.full_name||t.username).join(', ');
    txt.textContent = names + (typing.length>1?' are':' is') + ' typing...';
    el.classList.add('visible');
}

// ── Online status ─────────────────────────────────────────────
function renderOnline(ids) {
    document.querySelectorAll('.online-dot,.member-status').forEach(dot=>{
        dot.style.background='#94a3b8'; dot.style.boxShadow='none';
    });
    ids.forEach(id=>{
        document.querySelectorAll(`[data-uid="${id}"] .online-dot, [data-uid="${id}"] .member-status`).forEach(dot=>{
            dot.style.background='#22c55e'; dot.style.boxShadow='0 0 6px #22c55e';
        });
    });
}

// ── Channel badges ────────────────────────────────────────────
function renderBadges(badges) {
    document.querySelectorAll('.channel-item[data-channel]').forEach(el=>{
        const ch = el.dataset.channel;
        el.querySelector('.ch-badge')?.remove();
        if (badges[ch] && ch !== currentChannel) {
            const b=document.createElement('span'); b.className='ch-badge'; b.textContent=badges[ch]>99?'99+':badges[ch];
            el.appendChild(b);
        }
    });
}

// ── Fetch (polling) ───────────────────────────────────────────
async function fetchMessages(initial=false) {
    try {
        const r = await fetch(`${API_URL}?action=fetch&channel=${currentChannel}&since=${initial?0:lastMsgId}`);
        const data = await r.json();
        if (data.messages && data.messages.length) {
            lastMsgId = data.messages[data.messages.length-1].id;
            renderMessages(data.messages, !initial);
        } else if (initial) { renderMessages([],false); }
        if (data.announcements) renderAnnouncements(data.announcements);
        if (data.pinned)        renderPinned(data.pinned);
        if (data.typing)        renderTyping(data.typing);
        if (data.online_ids)    renderOnline(data.online_ids);
        if (data.badges)        renderBadges(data.badges);
    } catch(e){}
}

function startPolling() {
    if (pollTimer) clearInterval(pollTimer);
    pollTimer = setInterval(()=>fetchMessages(false), 3000);
}

// ── Switch Channel ────────────────────────────────────────────
function switchChannel(channel, el) {
    currentChannel=channel; lastMsgId=0; allMessages=[];
    cancelReply(); cancelEdit();
    document.querySelectorAll('.channel-item,.user-item').forEach(i=>i.classList.remove('active'));
    if (el) el.classList.add('active');

    let title, subtitle, icon, color;
    if (channel.startsWith('dm_')) {
        const uid = channel.replace('dm_','');
        const nameEl = document.querySelector(`[data-channel="${channel}"] span`);
        title = nameEl ? nameEl.textContent : 'Direct Message';
        subtitle='Private conversation'; icon='fas fa-user'; color=avatarColor(uid);
        document.getElementById('announcementBar').classList.remove('visible');
    } else {
        const meta = channelMeta[channel]||{label:channel,icon:'fas fa-hashtag',color:'#2563eb'};
        title=meta.label; subtitle=`#${channel} — Public staff channel`; icon=meta.icon; color=meta.color;
    }
    document.getElementById('topbarTitle').textContent=title;
    document.getElementById('topbarSubtitle').textContent=subtitle;
    document.getElementById('topbarIcon').innerHTML=`<i class="${icon}"></i>`;
    document.getElementById('topbarIcon').style.cssText=`background:${color}22;color:${color};width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;`;
    fetchMessages(true);
}

// ── Send Message ──────────────────────────────────────────────
async function sendMessage() {
    const input = document.getElementById('msgInput');
    const msg   = input.value.trim();
    const fileInput = document.getElementById('file-input');
    if (!msg && !fileInput.files.length) return;

    // If editing
    if (editMsgId) {
        const fd=new FormData(); fd.append('action','edit'); fd.append('msg_id',editMsgId); fd.append('message',msg);
        try { const r=await fetch(API_URL,{method:'POST',body:fd}); const d=await r.json(); if(d.success){ cancelEdit(); fetchMessages(false); } } catch(e){}
        return;
    }

    const fd=new FormData();
    fd.append('action','send'); fd.append('channel',currentChannel); fd.append('message',msg);
    if (replyToId) fd.append('reply_to',replyToId);
    if (fileInput.files.length) fd.append('attachment',fileInput.files[0]);

    input.value=''; input.style.height='auto';
    cancelReply(); removeFile();

    try { const r=await fetch(API_URL,{method:'POST',body:fd}); const d=await r.json(); if(d.success) fetchMessages(false); } catch(e){}
}

function handleInputKey(e) {
    if (e.key==='Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); return; }
    // Typing indicator
    if (typingTimer) clearTimeout(typingTimer);
    const fd=new FormData(); fd.append('action','typing'); fd.append('channel',currentChannel);
    fetch(API_URL,{method:'POST',body:fd}).catch(()=>{});
    typingTimer = setTimeout(()=>typingTimer=null, 3000);
}

function autoGrow(el) { el.style.height='auto'; el.style.height=Math.min(el.scrollHeight,120)+'px'; }

// ── File handling ─────────────────────────────────────────────
function handleFileSelect(input) {
    if (input.files.length) {
        const f=input.files[0];
        document.getElementById('filePreviewName').textContent=f.name+' ('+(f.size/1024).toFixed(1)+' KB)';
        document.getElementById('filePreviewBar').classList.add('visible');
    }
}
function removeFile() { document.getElementById('file-input').value=''; document.getElementById('filePreviewBar').classList.remove('visible'); }

// ── Sidebar search ────────────────────────────────────────────
function filterSidebar(q) {
    q=q.toLowerCase();
    document.querySelectorAll('.channel-item,.user-item').forEach(el=>{
        el.style.display = (!q||el.textContent.toLowerCase().includes(q)) ? '' : 'none';
    });
}

// ── Message search ────────────────────────────────────────────
let msgSearchVisible=false;
function toggleSearch() {
    msgSearchVisible=!msgSearchVisible;
    document.getElementById('msgSearchBar').style.display=msgSearchVisible?'':'none';
    if (msgSearchVisible) document.getElementById('msgSearchInput').focus();
}
function searchMessages(q) {
    q=q.toLowerCase();
    document.querySelectorAll('.msg-row').forEach(row=>{ row.style.display=(!q||row.textContent.toLowerCase().includes(q))?'':'none'; });
}

// ── Emoji picker ──────────────────────────────────────────────
function toggleEmojiPicker() {
    const p=document.getElementById('emojiPickerPanel');
    p.style.display = p.style.display==='none' ? 'block' : 'none';
    if (p.style.display==='block') {
        setTimeout(()=>document.addEventListener('click',()=>p.style.display='none',{once:true}),50);
    }
}
function insertEmojiChar(e) { const i=document.getElementById('msgInput'); i.value+=e; i.focus(); autoGrow(i); }
// Legacy cycle function for compatibility
function insertEmoji() { toggleEmojiPicker(); }

// ── Pinned panel toggle ───────────────────────────────────────
function togglePinnedPanel() { /* Could expand a full pinned list — basic for now */ }

// ── Announcement Modal ────────────────────────────────────────
function openAnnounceModal()  { document.getElementById('announceModal').classList.add('open'); }
function closeAnnounceModal() { document.getElementById('announceModal').classList.remove('open'); }
async function postAnnouncement() {
    const title=document.getElementById('annTitle').value.trim();
    const body=document.getElementById('annBody').value.trim();
    const priority=document.getElementById('annPriority').value;
    if (!title||!body){ alert('Please fill in all fields.'); return; }
    const fd=new FormData(); fd.append('action','announce'); fd.append('title',title); fd.append('body',body); fd.append('priority',priority);
    const r=await fetch(API_URL,{method:'POST',body:fd});
    const data=await r.json();
    if (data.success) { closeAnnounceModal(); document.getElementById('annTitle').value=''; document.getElementById('annBody').value=''; fetchMessages(true); }
}

// ── Lightbox ──────────────────────────────────────────────────
function openLightbox(src) { document.getElementById('lightboxImg').src=src; document.getElementById('imageLightbox').classList.add('open'); }

// ── Init ──────────────────────────────────────────────────────
fetchMessages(true);
startPolling();
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>


let currentChannel  = 'general';
let lastMsgId       = 0;
let pollTimer       = null;
let allMessages     = [];

const channelMeta = <?= json_encode($channels) ?>;

// ── Utilities ─────────────────────────────────────────────────
function timeAgo(dateStr) {
    const d = new Date(dateStr.replace(' ', 'T'));
    const now = new Date();
    const diff = Math.floor((now - d) / 1000);
    if (diff < 60)  return 'Just now';
    if (diff < 3600) return Math.floor(diff/60) + 'm ago';
    if (diff < 86400) return d.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});
    return d.toLocaleDateString([], {month:'short', day:'numeric'}) + ' ' + d.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});
}

function nameInitials(name) {
    return (name||'?').substring(0,2).toUpperCase();
}

function avatarColor(uid) {
    const colors = ['#2563eb','#7c3aed','#db2777','#d97706','#16a34a','#0891b2','#dc2626'];
    return colors[parseInt(uid) % colors.length];
}

function scrollBottom(smooth = true) {
    const box = document.getElementById('chatMessages');
    box.scrollTo({ top: box.scrollHeight, behavior: smooth ? 'smooth' : 'instant' });
}

// ── Render Messages ───────────────────────────────────────────
function renderMessages(msgs, append = false) {
    const box = document.getElementById('chatMessages');
    if (!append) {
        allMessages = [];
        box.innerHTML = '';
    }
    if (!msgs.length && !allMessages.length) {
        box.innerHTML = '<div class="chat-empty" id="emptyState"><i class="fas fa-comments"></i><p>No messages yet. Say something!</p></div>';
        return;
    }

    const emptyEl = box.querySelector('.chat-empty');
    if (emptyEl) emptyEl.remove();

    let lastDate = '';
    msgs.forEach(msg => {
        allMessages.push(msg);
        const msgDate = new Date(msg.created_at.replace(' ','T')).toDateString();
        if (msgDate !== lastDate) {
            lastDate = msgDate;
            const div = document.createElement('div');
            div.className = 'msg-date-divider';
            div.textContent = msgDate === new Date().toDateString() ? 'Today' : msgDate;
            box.appendChild(div);
        }
        const isMe = parseInt(msg.is_me) === 1;
        const isAnn = msg.msg_type === 'announcement';
        const row = document.createElement('div');
        row.className = 'msg-row' + (isMe ? ' mine' : '') + (isAnn ? ' announcement' : '');
        row.dataset.id = msg.id;

        let avatarHtml = '';
        if (!isMe && !isAnn) {
            avatarHtml = `<div class="msg-avatar" style="background:linear-gradient(135deg,${avatarColor(msg.uid)},${avatarColor(msg.uid+'1')})">${nameInitials(msg.full_name||msg.username)}</div>`;
        }

        let bubbleContent = '';
        if (msg.msg_type === 'image') {
            bubbleContent = `<img src="/Bosa Addis/uploads/chat/${msg.file_path}" class="msg-image" onclick="openLightbox(this.src)" alt="image">`;
        } else if (msg.msg_type === 'file') {
            bubbleContent = `${escapeHtml(msg.message)}<a class="msg-file-link" href="/Bosa Addis/uploads/chat/${msg.file_path}" target="_blank"><i class="fas fa-file"></i>${escapeHtml(msg.message)}</a>`;
        } else {
            bubbleContent = escapeHtml(msg.message);
        }

        const nameLabel = !isMe && !isAnn ? `<div class="msg-name">${escapeHtml(msg.full_name||msg.username)}</div>` : '';

        row.innerHTML = `
            ${avatarHtml}
            <div>
                ${nameLabel}
                <div class="msg-bubble ${isMe ? 'mine' : (isAnn ? 'announcement-bubble' : 'theirs')}">
                    ${bubbleContent}
                    <div class="msg-time">${timeAgo(msg.created_at)}</div>
                </div>
            </div>`;
        box.appendChild(row);
    });

    scrollBottom(!append);
}

// ── Announcements ─────────────────────────────────────────────
function renderAnnouncements(anns) {
    const bar = document.getElementById('announcementBar');
    if (!anns.length) { bar.classList.remove('visible'); return; }
    bar.classList.add('visible');
    const icons = { normal:'📢', urgent:'🔴', critical:'🚨' };
    bar.innerHTML = anns.slice(0,2).map(a => `
        <div class="ann-card">
            <div class="ann-icon">${icons[a.priority]||'📢'}</div>
            <div>
                <div class="ann-title">${escapeHtml(a.title)}</div>
                <div class="ann-body">${escapeHtml(a.body)}</div>
                <span class="ann-priority ${a.priority}">${a.priority.toUpperCase()}</span>
                <span style="font-size:0.7rem;color:#92400e;margin-left:8px;">by ${escapeHtml(a.username)} · ${timeAgo(a.created_at)}</span>
            </div>
        </div>`).join('');
}

// ── Fetch (polling) ───────────────────────────────────────────
async function fetchMessages(initial = false) {
    try {
        const r = await fetch(`${API_URL}?action=fetch&channel=${currentChannel}&since=${initial ? 0 : lastMsgId}`);
        const data = await r.json();
        if (data.messages && data.messages.length) {
            const newMsgs = data.messages;
            lastMsgId = newMsgs[newMsgs.length-1].id;
            renderMessages(newMsgs, !initial);
        } else if (initial) {
            renderMessages([], false);
        }
        if (data.announcements) renderAnnouncements(data.announcements);
    } catch(e) { /* silent */ }
}

function startPolling() {
    if (pollTimer) clearInterval(pollTimer);
    pollTimer = setInterval(() => fetchMessages(false), 3000);
}

// ── Switch Channel ────────────────────────────────────────────
function switchChannel(channel, el) {
    currentChannel = channel;
    lastMsgId = 0;
    allMessages = [];

    // Update active state in sidebar
    document.querySelectorAll('.channel-item, .user-item').forEach(i => i.classList.remove('active'));
    if (el) el.classList.add('active');

    // Update topbar
    let title, subtitle, icon, color;
    if (channel.startsWith('dm_')) {
        const uid = channel.replace('dm_','');
        const memberRow = document.querySelector(`[data-channel="${channel}"] span`);
        title = memberRow ? memberRow.textContent : 'Direct Message';
        subtitle = 'Private conversation';
        icon = 'fas fa-user';
        color = avatarColor(uid);

        // Hide announcements in DM
        document.getElementById('announcementBar').classList.remove('visible');
    } else {
        const meta = channelMeta[channel] || { label: channel, icon: 'fas fa-hashtag', color: '#2563eb' };
        title = meta.label;
        subtitle = `#${channel} — Public staff channel`;
        icon = meta.icon;
        color = meta.color;
    }

    document.getElementById('topbarTitle').textContent = title;
    document.getElementById('topbarSubtitle').textContent = subtitle;
    document.getElementById('topbarIcon').innerHTML = `<i class="${icon}"></i>`;
    document.getElementById('topbarIcon').style.cssText = `background:${color}22;color:${color};width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;`;

    fetchMessages(true);
}

// ── Send Message ──────────────────────────────────────────────
async function sendMessage() {
    const input = document.getElementById('msgInput');
    const msg = input.value.trim();
    const fileInput = document.getElementById('file-input');
    if (!msg && !fileInput.files.length) return;

    const fd = new FormData();
    fd.append('action', 'send');
    fd.append('channel', currentChannel);
    fd.append('message', msg);
    if (fileInput.files.length) fd.append('attachment', fileInput.files[0]);

    input.value = '';
    input.style.height = 'auto';
    removeFile();

    try {
        const r = await fetch(API_URL, { method:'POST', body: fd });
        const data = await r.json();
        if (data.success) fetchMessages(false);
    } catch(e) {}
}

function handleInputKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
}

function autoGrow(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

// ── File handling ─────────────────────────────────────────────
function handleFileSelect(input) {
    if (input.files.length) {
        const f = input.files[0];
        document.getElementById('filePreviewName').textContent = f.name + ' (' + (f.size/1024).toFixed(1) + ' KB)';
        document.getElementById('filePreviewBar').classList.add('visible');
    }
}
function removeFile() {
    document.getElementById('file-input').value = '';
    document.getElementById('filePreviewBar').classList.remove('visible');
}

// ── Sidebar search ────────────────────────────────────────────
function filterSidebar(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.channel-item, .user-item').forEach(el => {
        const text = el.textContent.toLowerCase();
        el.style.display = (!q || text.includes(q)) ? '' : 'none';
    });
}

// ── Message search ────────────────────────────────────────────
let msgSearchVisible = false;
function toggleSearch() {
    msgSearchVisible = !msgSearchVisible;
    document.getElementById('msgSearchBar').style.display = msgSearchVisible ? '' : 'none';
    if (msgSearchVisible) document.getElementById('msgSearchInput').focus();
}
function searchMessages(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.msg-row').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = (!q || text.includes(q)) ? '' : 'none';
    });
}

// ── Emoji (simple) ────────────────────────────────────────────
const emojis = ['😊','😂','👍','❤️','🔥','✅','⚡','📌','🎉','🙏','👏','💪'];
let emojiIdx = 0;
function insertEmoji() {
    const input = document.getElementById('msgInput');
    input.value += emojis[emojiIdx++ % emojis.length];
    input.focus();
}

// ── Announcement Modal ────────────────────────────────────────
function openAnnounceModal() { document.getElementById('announceModal').classList.add('open'); }
function closeAnnounceModal() { document.getElementById('announceModal').classList.remove('open'); }
async function postAnnouncement() {
    const title = document.getElementById('annTitle').value.trim();
    const body  = document.getElementById('annBody').value.trim();
    const priority = document.getElementById('annPriority').value;
    if (!title || !body) { alert('Please fill in all fields.'); return; }
    const fd = new FormData();
    fd.append('action','announce'); fd.append('title',title);
    fd.append('body',body); fd.append('priority',priority);
    const r = await fetch(API_URL, {method:'POST',body:fd});
    const data = await r.json();
    if (data.success) {
        closeAnnounceModal();
        document.getElementById('annTitle').value='';
        document.getElementById('annBody').value='';
        fetchMessages(true);
    }
}

// ── Lightbox ──────────────────────────────────────────────────
function openLightbox(src) {
    document.getElementById('lightboxImg').src = src;
    document.getElementById('imageLightbox').classList.add('open');
}

// ── XSS safe ─────────────────────────────────────────────────
function escapeHtml(str) {
    return (str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Init ──────────────────────────────────────────────────────
fetchMessages(true);
startPolling();
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
