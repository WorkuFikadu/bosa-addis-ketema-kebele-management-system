<?php
// modules/economic/agriculture_list.php
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../auth/login.php'); exit; }

$search = $_GET['q'] ?? '';
$where = '';
$params = [];
if ($search) {
    $where = " AND (i.fname LIKE ? OR ea.plot_number LIKE ?)";
    $params = ["%$search%", "%$search%"];
}

$plots = $pdo->prepare("
    SELECT ea.*, i.fname, i.lname, i.phot
    FROM economic_agriculture ea
    JOIN individuals i ON ea.land_owner_id = i.id
    WHERE 1=1 $where
    ORDER BY ea.created_at DESC
");
$plots->execute($params);
$plots = $plots->fetchAll();

$land_total = $pdo->query("SELECT SUM(land_size_sqm) FROM economic_agriculture")->fetchColumn() ?: 0;
$fertilizer_total = $pdo->query("SELECT SUM(fertilizer_received) FROM economic_agriculture")->fetchColumn() ?: 0;
?>

<div class="mb-3">
    <a href="index.php" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold shadow-sm">
        <i class="fas fa-arrow-left me-2"></i><?php echo __('back'); ?>
    </a>
</div>

<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="fw-black mb-1" style="font-size: 2.4rem;"><i class="fas fa-mountain-sun text-danger me-2"></i><?php echo __('land_agriculture_registry'); ?></h2>
        <p class="text-muted mb-0"><?php echo __('land_agriculture_registry_desc'); ?></p>
    </div>
    <a href="agriculture_create.php" class="btn btn-danger text-white shadow-sm rounded-pill px-4 fw-bold">
        <i class="fas fa-map-location-dot me-2"></i><?php echo __('register_plot_input'); ?>
    </a>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-top border-danger border-5">
            <h4 class="fw-black mb-1 text-danger"><?php echo number_format($land_total, 0); ?> m²</h4>
            <div class="small fw-bold text-uppercase text-muted"><?php echo __('total_managed_land_area'); ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-top border-success border-5">
            <h4 class="fw-black mb-1 text-success"><?php echo number_format($fertilizer_total, 1); ?> Qtl</h4>
            <div class="small fw-bold text-uppercase text-muted"><?php echo __('fertilizer_distributed'); ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-top border-info border-5">
            <h4 class="fw-black mb-1 text-info"><?php echo count($plots); ?></h4>
            <div class="small fw-bold text-uppercase text-muted"><?php echo __('registered_plots'); ?></div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm rounded-4 p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted small text-uppercase">
                <tr>
                    <th class="px-4 py-3"><?php echo __('land_owner'); ?></th>
                    <th class="py-3"><?php echo __('plot_num'); ?></th>
                    <th class="py-3"><?php echo __('size_sqm'); ?></th>
                    <th class="py-3"><?php echo __('primary_use'); ?></th>
                    <th class="py-3"><?php echo __('inputs_recvd'); ?></th>
                    <th class="px-4 py-3 text-end"><?php echo __('action'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($plots as $p): ?>
                <tr>
                    <td class="px-4">
                        <div class="fw-bold text-dark small"><?php echo htmlspecialchars("{$p['fname']} {$p['lname']}"); ?></div>
                        <small class="text-muted italic"><?php echo __('registered'); ?>: <?php echo date('M d, Y', strtotime($p['created_at'])); ?></small>
                    </td>
                    <td><code class="fw-bold text-danger"><?php echo htmlspecialchars($p['plot_number'] ?: 'UNASSIGNED'); ?></code></td>
                    <td><div class="small fw-bold"><?php echo number_format($p['land_size_sqm'], 0); ?></div></td>
                    <td><span class="badge bg-light text-dark border px-2"><?php echo strtoupper($p['land_use']); ?></span></td>
                    <td>
                        <div class="small">
                            <span class="badge bg-success-soft text-success"><i class="fas fa-leaf me-1"></i><?php echo $p['fertilizer_received'] ?: '0'; ?> <?php echo __('qtl_fert'); ?></span>
                            <span class="badge bg-info-soft text-info"><i class="fas fa-seedling me-1"></i><?php echo $p['seed_received'] ?: '0'; ?> <?php echo __('kg_seed'); ?></span>
                        </div>
                    </td>
                    <td class="px-4 text-end">
                        <button class="btn btn-sm btn-light rounded-pill px-3 shadow-sm text-danger fw-bold"><?php echo __('manage_inputs'); ?></button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($plots)): ?>
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fas fa-map-marked-alt fa-3x mb-3 opacity-25"></i>
                        <p><?php echo __('no_land_records'); ?></p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.bg-success-soft { background: rgba(22, 163, 74, 0.1); }
.bg-info-soft { background: rgba(14, 165, 233, 0.1); }
</style>

<?php require_once '../../includes/footer.php'; ?>
