<?php
// dashboard.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

// Fetch stats
$totalResidents = $pdo->query("SELECT COUNT(*) FROM individuals")->fetchColumn();
$totalFamilies = $pdo->query("SELECT COUNT(*) FROM families")->fetchColumn();
$totalHouses = $pdo->query("SELECT COUNT(*) FROM houses")->fetchColumn();
$totalIDs = $pdo->query("SELECT COUNT(*) FROM id_cards")->fetchColumn();

// Fetch recent residents
$recentResidents = $pdo->query("SELECT * FROM individuals ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<div class="container-fluid py-4 mesh-bg">
    <!-- Welcome Banner -->
    <div class="card mb-5 border-0 shadow-lg position-relative overflow-hidden" style="border-radius: 30px; background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); min-height: 280px;">
        <!-- Decorative Elements -->
        <div class="position-absolute top-0 end-0 p-5 opacity-10">
            <i class="fas fa-landmark text-white display-1 rotated-12"></i>
        </div>
        <div class="card-body p-md-5 position-relative z-index-1">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <img src="assets/img/ethiopia_flag.png" alt="Ethiopia" height="28" class="rounded shadow-sm">
                        <img src="assets/img/oromia_flag.png" alt="Oromia" height="28" class="rounded shadow-sm">
                        <span class="badge rounded-pill bg-white bg-opacity-10 text-white px-3 py-2 border border-white border-opacity-10">
                            <?php echo __('admin_portal'); ?>
                        </span>
                    </div>
                    <h1 class="display-4 fw-bold text-white mb-3">
                        <span class="text-white-50 fw-light"><?php echo __('welcome'); ?>,</span><br>
                        <?php echo $_SESSION['username'] ?? 'Staff Member'; ?>
                    </h1>
                    <p class="lead text-white-50 mb-0 max-width-500">
                        Managing digital records for <strong><?php echo $system_name; ?></strong>. Your activity enhances the efficiency of our community governance.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="row g-4 mb-5">
        <div class="col-sm-6 col-xl-3">
            <div class="card card-hover h-100 bg-grad-primary border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="bg-white bg-opacity-20 rounded-3 p-3">
                            <i class="fas fa-users text-white fs-4"></i>
                        </div>
                        <span class="badge bg-white bg-opacity-10 text-white">+12%</span>
                    </div>
                    <h6 class="text-white-50 fw-bold small text-uppercase mb-1"><?php echo __('total_residents'); ?></h6>
                    <h2 class="text-white mb-0 display-6 fw-bold"><?php echo number_format($totalResidents); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-hover h-100 bg-grad-secondary border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="bg-white bg-opacity-20 rounded-3 p-3">
                            <i class="fas fa-people-roof text-white fs-4"></i>
                        </div>
                        <span class="badge bg-white bg-opacity-10 text-white">Active</span>
                    </div>
                    <h6 class="text-white-50 fw-bold small text-uppercase mb-1"><?php echo __('total_families'); ?></h6>
                    <h2 class="text-white mb-0 display-6 fw-bold"><?php echo number_format($totalFamilies); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-hover h-100 bg-grad-accent border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="bg-white bg-opacity-20 rounded-3 p-3">
                            <i class="fas fa-building-user text-white fs-4"></i>
                        </div>
                        <span class="badge bg-white bg-opacity-10 text-white">Verified</span>
                    </div>
                    <h6 class="text-white-50 fw-bold small text-uppercase mb-1"><?php echo __('total_houses'); ?></h6>
                    <h2 class="text-white mb-0 display-6 fw-bold"><?php echo number_format($totalHouses); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-hover h-100 bg-grad-warning border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="bg-white bg-opacity-20 rounded-3 p-3">
                            <i class="fas fa-id-card-clip text-white fs-4"></i>
                        </div>
                        <span class="badge bg-white bg-opacity-10 text-white">System</span>
                    </div>
                    <h6 class="text-white-50 fw-bold small text-uppercase mb-1"><?php echo __('ids_issued'); ?></h6>
                    <h2 class="text-white mb-0 display-6 fw-bold"><?php echo number_format($totalIDs); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content Area -->
        <div class="col-xl-8 col-lg-12">
            <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 28px;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-chart-line me-2 text-primary"></i> <?php echo __('recent_residents'); ?>
                    </h5>
                    <a href="modules/residents/index.php" class="btn btn-sm btn-light border-0 px-3"><?php echo __('view_all'); ?></a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th class="border-0 pb-3" style="min-width: 150px;"><?php echo __('resident'); ?></th>
                                <th class="border-0 pb-3"><?php echo __('status'); ?></th>
                                <th class="border-0 pb-3"><?php echo __('reg_date'); ?></th>
                                <th class="border-0 pb-3 text-end"><?php echo __('action'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentResidents)): ?>
                                <tr><td colspan="4" class="text-center py-5"><?php echo __('no_records'); ?></td></tr>
                            <?php else: ?>
                                <?php foreach ($recentResidents as $resident): ?>
                                    <tr class="border-transparent">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light rounded-circle p-2 me-3 d-none d-sm-flex" style="width: 36px; height: 36px; align-items: center; justify-content: center;">
                                                    <i class="fas fa-user text-primary-dark small"></i>
                                                </div>
                                                <span class="fw-bold small"><?php echo "{$resident['fname']} {$resident['lname']}"; ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill bg-success-subtle text-success px-2 py-1 small"><?php echo __('active'); ?></span>
                                        </td>
                                        <td class="text-muted small">
                                            <?php echo date('M d, Y', strtotime($resident['created_at'])); ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="modules/residents/view.php?id=<?php echo $resident['id']; ?>" class="btn btn-sm btn-light border rounded-pill px-2 py-1 small"><?php echo __('details'); ?></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Actions -->
        <div class="col-xl-4 col-lg-12">
            <div class="card border-0 shadow-sm p-4 mb-4 glass-card" style="border-radius: 28px;">
                <h6 class="fw-bold mb-4 text-dark"><i class="fas fa-bolt-lightning me-2 text-warning"></i> <?php echo __('official_ops'); ?></h6>
                <div class="row g-3">
                    <div class="col-md-6 col-xl-12">
                        <a href="modules/idcards/index.php" class="btn btn-white text-start p-3 border shadow-sm hover-lift w-100" style="border-radius: 20px;">
                            <div class="d-flex align-items-center">
                                <div class="bg-grad-primary text-white rounded-4 p-2 me-3" style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-certificate fs-6"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold small"><?php echo __('issuance_control'); ?></h6>
                                    <p class="mb-0 text-muted" style="font-size: 0.7rem;"><?php echo __('process_certs'); ?></p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6 col-xl-12">
                        <a href="modules/residents/create.php" class="btn btn-white text-start p-3 border shadow-sm hover-lift w-100" style="border-radius: 20px;">
                            <div class="d-flex align-items-center">
                                <div class="bg-grad-accent text-white rounded-4 p-2 me-3" style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-user-plus fs-6"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark small"><?php echo __('citizen_entry'); ?></h6>
                                    <p class="mb-0 text-muted" style="font-size: 0.7rem;"><?php echo __('reg_records'); ?></p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- System Information / Audit Trail for Admin -->
            <div class="card border-0 shadow-sm text-white h-100 overflow-hidden" style="border-radius: 28px; background: #0f172a;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold mb-0"><i class="fas fa-history me-2 text-info"></i> <?php echo __('admin_log'); ?></h6>
                        <a href="modules/reports/audit_logs.php" class="text-white-50 small text-decoration-none hover-text-white">View All</a>
                    </div>
                    
                    <div class="activity-feed space-y-4">
                        <?php
                        $audit_stmt = $pdo->query("SELECT al.*, u.username 
                                                  FROM audit_logs al 
                                                  LEFT JOIN users u ON al.user_id = u.id 
                                                  ORDER BY al.created_at DESC LIMIT 6");
                        $recent_logs = $audit_stmt->fetchAll();
                        
                        if (empty($recent_logs)): ?>
                            <p class="text-white-50 small italic text-center py-4">No recent activity detected.</p>
                        <?php else: ?>
                            <?php foreach ($recent_logs as $log): 
                                $color = 'info';
                                if($log['action'] == 'CREATED') $color = 'success';
                                if($log['action'] == 'DELETED') $color = 'danger';
                            ?>
                            <div class="d-flex gap-3 mb-4 last-mb-0">
                                <div class="bg-<?php echo $color; ?> bg-opacity-20 rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="fas fa-circle text-<?php echo $color; ?>" style="font-size: 0.5rem;"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-white fw-semibold" style="font-size: 0.75rem;">
                                        <?php echo htmlspecialchars($log['username'] ?? 'System'); ?> 
                                        <span class="text-white-50 fw-normal">performed</span> 
                                        <?php echo $log['action']; ?>
                                    </p>
                                    <p class="mb-0 text-white-50" style="font-size: 0.65rem;">
                                        <?php echo substr(htmlspecialchars($log['details']), 0, 45) . (strlen($log['details']) > 45 ? '...' : ''); ?>
                                    </p>
                                    <small class="text-info" style="font-size: 0.55rem;"><?php echo date('h:i A', strtotime($log['created_at'])); ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
