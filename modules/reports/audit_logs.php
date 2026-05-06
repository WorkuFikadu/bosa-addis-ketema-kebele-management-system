<?php
// modules/reports/audit_logs.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    die("<div class='alert alert-danger m-5'>Access Denied: Admin role required to view audit logs.</div>");
}

// Fetch Logs
$query = "SELECT al.*, u.username 
          FROM audit_logs al 
          LEFT JOIN users u ON al.user_id = u.id 
          ORDER BY al.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$logs = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-history me-2 text-primary"></i> Administrative Audit Logs</h2>
    <div>
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="fas fa-print me-2"></i> Print Log
        </button>
    </div>
</div>

<div class="card p-0 shadow-sm border-0" style="border-radius: 20px; overflow: hidden;">
    <div class="card-header bg-dark text-white p-3">
        <h6 class="mb-0 text-uppercase small tracking-widest"><i class="fas fa-list me-2"></i> System Activity Trail</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">No activities recorded yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="ps-4 text-muted small">
                                <?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?>
                            </td>
                            <td class="fw-bold text-dark">
                                <i class="fas fa-user-shield me-2 text-primary-dark opacity-50"></i>
                                <?php echo $log['username'] ?? 'System'; ?>
                            </td>
                            <td>
                                <?php 
                                $badge_class = 'bg-secondary';
                                if ($log['action'] === 'CREATED') $badge_class = 'bg-success';
                                if ($log['action'] === 'UPDATED') $badge_class = 'bg-info';
                                if ($log['action'] === 'DELETED') $badge_class = 'bg-danger';
                                ?>
                                <span class="badge <?php echo $badge_class; ?> rounded-pill px-3"><?php echo $log['action']; ?></span>
                            </td>
                            <td>
                                <span class="text-uppercase small fw-bold text-muted"><?php echo $log['module']; ?></span>
                            </td>
                            <td class="text-muted pe-4">
                                <?php echo htmlspecialchars($log['details']); ?>
                                <?php if ($log['target_id']): ?>
                                    <span class="badge bg-light text-dark border ms-2">ID: #<?php echo $log['target_id']; ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
