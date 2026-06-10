<?php
// modules/health/sanitation_list.php
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../auth/login.php'); exit; }

// Handle campaign completion
if (isset($_POST['complete_campaign'])) {
    $pdo->prepare("UPDATE sanitation_campaigns SET status = 'Completed' WHERE id = ?")->execute([$_POST['campaign_id']]);
}

$campaigns = $pdo->query("SELECT * FROM sanitation_campaigns ORDER BY campaign_date DESC")->fetchAll();
?>

<div class="mb-3">
    <a href="index.php" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold shadow-sm">
        <i class="fas fa-arrow-left me-2"></i><?php echo __('back'); ?>
    </a>
</div>

<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="fw-black mb-1" style="font-size: 2.4rem;"><i class="fas fa-broom text-info me-2"></i><?php echo __('sanitation_campaigns'); ?></h2>
        <p class="text-muted mb-0"><?php echo __('sanitation_campaigns_desc'); ?></p>
    </div>
    <a href="sanitation_create.php" class="btn btn-info text-white shadow-sm rounded-pill px-4 fw-bold">
        <i class="fas fa-calendar-plus me-2"></i><?php echo __('plan_new_campaign'); ?>
    </a>
</div>

<div class="row g-4">
    <?php foreach ($campaigns as $c): 
        $status_cls = match($c['status']) {
            'Completed' => 'bg-success',
            'In Progress' => 'bg-warning text-dark',
            'Cancelled' => 'bg-danger',
            default => 'bg-info'
        };
        $display_status = match($c['status']) {
            'Completed' => __('completed'),
            'In Progress' => __('in_progress'),
            'Cancelled' => __('cancelled'),
            default => $c['status']
        };
    ?>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 glass-light-card">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge <?php echo $status_cls; ?> rounded-pill px-3 py-1" style="font-size:0.6rem;"><?php echo strtoupper($display_status); ?></span>
                    <small class="text-muted fw-bold"><i class="fas fa-calendar me-1"></i><?php echo date('M d, Y', strtotime($c['campaign_date'])); ?></small>
                </div>
                <h5 class="fw-black text-dark mb-2"><?php echo htmlspecialchars($c['campaign_name']); ?></h5>
                <p class="text-muted small mb-3"><i class="fas fa-location-dot me-1 text-info"></i><?php echo __('zone'); ?>: <?php echo htmlspecialchars($c['zone']); ?></p>
                
                <div class="p-3 bg-light rounded-3 mb-3 text-center">
                    <div class="fw-black text-info fs-4"><?php echo number_format($c['participants_est']); ?></div>
                    <div class="text-muted small fw-bold text-uppercase"><?php echo __('est_participants'); ?></div>
                </div>

                <div class="mb-3 small text-muted italic">
                    "<?php echo htmlspecialchars($c['impact_notes'] ?: __('no_impact_notes', 'No impact notes yet.')); ?>"
                </div>

                <?php if ($c['status'] !== 'Completed' && $c['status'] !== 'Cancelled'): ?>
                <form method="POST">
                    <input type="hidden" name="campaign_id" value="<?php echo $c['id']; ?>">
                    <button type="submit" name="complete_campaign" class="btn btn-sm btn-info text-white rounded-pill w-100 fw-bold shadow-sm">
                        <?php echo __('mark_completed', 'Mark as Completed'); ?>
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($campaigns)): ?>
    <div class="col-12 text-center py-5 text-muted">
        <i class="fas fa-mountain fa-3x mb-3 opacity-25"></i>
        <p><?php echo __('no_sanitation_campaigns'); ?></p>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
