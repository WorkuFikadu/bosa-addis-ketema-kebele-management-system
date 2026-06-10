<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $title = $_POST['title'];
        $body = $_POST['body'];
        $category = $_POST['category'];
        $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;
        
        $stmt = $pdo->prepare("INSERT INTO announcements (title, body, category, is_pinned, created_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $body, $category, $is_pinned, $_SESSION['user_id'] ?? null]);
        $success = "Announcement published successfully!";
        
        log_activity($pdo, $_SESSION['user_id'] ?? null, "Published Announcement: $title");

        // Create notification for staff
        $stmt = $pdo->query("SELECT id FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, type, message, link) VALUES (?, 'announcement', ?, '/Bosa Addis/modules/announcements/')");
        foreach ($users as $uid) {
            $notifStmt->execute([$uid, "New Announcement: $title"]);
        }

    } elseif ($_POST['action'] === 'delete') {
        $id = $_POST['announcement_id'];
        $pdo->prepare("DELETE FROM announcements WHERE id = ?")->execute([$id]);
        $success = "Announcement removed.";
        log_activity($pdo, $_SESSION['user_id'] ?? null, "Deleted an Announcement");
    }
}

// Fetch announcements
$stmt = $pdo->query("
    SELECT a.*, u.username 
    FROM announcements a 
    LEFT JOIN users u ON a.created_by = u.id 
    ORDER BY a.is_pinned DESC, a.created_at DESC
");
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getCategoryBadge($category) {
    $map = [
        'Meeting' => 'bg-primary',
        'Holiday' => 'bg-success',
        'Emergency' => 'bg-danger',
        'Community Event' => 'bg-info',
        'General' => 'bg-secondary'
    ];
    $color = $map[$category] ?? 'bg-secondary';
    return "<span class='badge $color rounded-pill'>$category</span>";
}
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8 d-flex align-items-center">
            <div class="icon-circle bg-warning text-white me-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 50px; height: 50px; border-radius: 15px;">
                <i class="fas fa-bullhorn fa-lg"></i>
            </div>
            <div>
                <h2 class="h3 mb-0 text-gray-800 fw-bold"><?php echo __('live_notice_board'); ?></h2>
                <p class="text-muted mb-0"><?php echo __('broadcast_events_desc'); ?></p>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <button class="btn btn-warning shadow-sm rounded-pill px-4 text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal">
                <i class="fas fa-plus me-2"></i><?php echo __('publish_notice'); ?>
        </div>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <?php if (empty($announcements)): ?>
            <div class="col-12 py-5 text-center text-muted">
                <i class="fas fa-comment-slash fa-3x mb-3 text-light"></i>
                <h5><?php echo __('notice_empty'); ?></h5>
            </div>
        <?php else: ?>
            <?php foreach ($announcements as $a): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm transition-all hover-lift" style="border-radius: 20px; <?php echo $a['is_pinned'] ? 'border-left: 5px solid #eab308 !important;' : ''; ?>">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <?php echo getCategoryBadge($a['category']); ?>
                                
                                <div class="d-flex align-items-center">
                                    <?php if ($a['is_pinned']): ?>
                                        <i class="fas fa-thumbtack text-warning fa-lg me-2" title="Pinned"></i>
                                    <?php endif; ?>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this announcement?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="announcement_id" value="<?php echo $a['id']; ?>">
                                        <button type="submit" class="btn btn-link text-danger p-0 m-0 text-decoration-none shadow-none"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </div>
                            </div>
                            
                            <h5 class="fw-bold text-dark mb-3"><?php echo htmlspecialchars($a['title']); ?></h5>
                            <p class="text-muted" style="font-size: 0.95rem; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($a['body'])); ?></p>
                            
                            <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                                <small class="text-muted fw-bold"><i class="fas fa-user-edit me-1"></i><?php echo htmlspecialchars($a['username'] ?? 'System'); ?></small>
                                <small class="text-muted"><i class="far fa-calendar-alt me-1"></i><?php echo date('M d, Y', strtotime($a['created_at'])); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addAnnouncementModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-warning border-0">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-bullhorn me-2"></i><?php echo __('publish_notice'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label fw-bold"><?php echo __('notice_title'); ?></label>
                        <input type="text" name="title" class="form-control form-control-lg rounded-3 bg-light border-0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold"><?php echo __('category'); ?></label>
                        <select name="category" class="form-select rounded-3 bg-light border-0">
                            <option value="General"><?php echo __('general_info'); ?></option>
                            <option value="Meeting"><?php echo __('official_meeting'); ?></option>
                            <option value="Holiday"><?php echo __('public_holiday'); ?></option>
                            <option value="Community Event"><?php echo __('community_event'); ?></option>
                            <option value="Emergency"><?php echo __('emergency_alert'); ?></option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold"><?php echo __('message_layout_content'); ?></label>
                        <textarea name="body" class="form-control rounded-3 bg-light border-0" rows="5" required></textarea>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_pinned" id="pinnedCheck">
                        <label class="form-check-label fw-bold" for="pinnedCheck"><?php echo __('pin_to_top'); ?></label>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark"><?php echo __('publish_now'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
