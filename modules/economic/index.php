<?php
require_once '../../includes/header.php';
require_once '../../config/database.php';

// Fetch quick stats
$stats = [
    'enterprises' => $pdo->query("SELECT COUNT(*) FROM economic_enterprises")->fetchColumn(),
    'youth' => $pdo->query("SELECT COUNT(*) FROM economic_youth_registry")->fetchColumn(),
    'subsidies' => $pdo->query("SELECT COUNT(*) FROM economic_subsidies")->fetchColumn(),
    'agriculture' => $pdo->query("SELECT COUNT(*) FROM economic_agriculture")->fetchColumn()
];
?>
<div class="container-fluid py-4 min-vh-100 bg-light">
    <div class="mb-3">
        <a href="../../dashboard.php" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-bold shadow-sm">
            <i class="fas fa-arrow-left me-2"></i><?php echo __('back'); ?>
        </a>
    </div>

    <!-- Header Section -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 position-relative overflow-hidden" style="background: linear-gradient(135deg, #0f766e, #047857); color: white;">
        <div class="position-absolute top-0 end-0 opacity-10 p-5" style="transform: scale(2) translate(10%, -20%);">
            <i class="fas fa-briefcase fa-10x"></i>
        </div>
        <div class="card-body p-4 p-md-5 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 position-relative z-1">
            <div>
                <h2 class="fw-black mb-2 text-warning"><i class="fas fa-chart-line me-3"></i><?php echo __('economic_youth_module_title'); ?></h2>
                <p class="mb-0 text-white-50 fw-bold fs-5" style="max-width: 600px;"><?php echo __('economic_youth_module_desc'); ?></p>
            </div>
            <button class="btn btn-warning text-dark rounded-pill fw-bold shadow-lg px-4 py-3 text-uppercase" data-bs-toggle="modal" data-bs-target="#economicModal">
                <i class="fas fa-plus-circle me-2"></i> <?php echo __('quick_action'); ?>
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white border-start border-primary border-4">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="small text-muted fw-bold text-uppercase"><?php echo __('enterprises_small'); ?></span>
                        <h3 class="fw-black mb-0 text-dark"><?php echo number_format($stats['enterprises']); ?></h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-store fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white border-start border-success border-4">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="small text-muted fw-bold text-uppercase"><?php echo __('youth_registry_small'); ?></span>
                        <h3 class="fw-black mb-0 text-dark"><?php echo number_format($stats['youth']); ?></h3>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-users-gear fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white border-start border-warning border-4">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="small text-muted fw-bold text-uppercase"><?php echo __('subsidies_small'); ?></span>
                        <h3 class="fw-black mb-0 text-dark"><?php echo number_format($stats['subsidies']); ?></h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-wheat-awn fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white border-start border-info border-4">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="small text-muted fw-bold text-uppercase"><?php echo __('agriculture_small'); ?></span>
                        <h3 class="fw-black mb-0 text-dark"><?php echo number_format($stats['agriculture']); ?></h3>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-mountain-sun fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Services Grid -->
    <div class="row g-4 mb-5">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift service-card" style="border-top: 4px solid #3b82f6;">
                <div class="card-body p-4 text-center d-flex flex-column">
                    <div class="bg-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 64px; height: 64px; font-size: 1.75rem; flex-shrink:0;">
                        <i class="fas fa-store"></i>
                    </div>
                    <h6 class="fw-bold text-dark mt-2"><?php echo __('micro_enterprises'); ?></h6>
                    <p class="text-muted small mb-4 flex-grow-1"><?php echo __('micro_enterprises_desc'); ?></p>
                    <a href="enterprise_list.php" class="btn btn-outline-primary btn-sm rounded-pill w-100 fw-bold shadow-sm">
                        <i class="fas fa-arrow-right me-1"></i> <?php echo __('manage_smes'); ?>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift service-card" style="border-top: 4px solid #10b981;">
                <div class="card-body p-4 text-center d-flex flex-column">
                    <div class="bg-success text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 64px; height: 64px; font-size: 1.75rem; flex-shrink:0;">
                        <i class="fas fa-users-gear"></i>
                    </div>
                    <h6 class="fw-bold text-dark mt-2"><?php echo __('youth_jobs'); ?></h6>
                    <p class="text-muted small mb-4 flex-grow-1"><?php echo __('youth_jobs_desc'); ?></p>
                    <a href="youth_list.php" class="btn btn-outline-success btn-sm rounded-pill w-100 fw-bold shadow-sm">
                        <i class="fas fa-arrow-right me-1"></i> <?php echo __('youth_registry'); ?>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift service-card" style="border-top: 4px solid #f59e0b;">
                <div class="card-body p-4 text-center d-flex flex-column">
                    <div class="bg-warning text-dark rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 64px; height: 64px; font-size: 1.75rem; flex-shrink:0;">
                        <i class="fas fa-wheat-awn"></i>
                    </div>
                    <h6 class="fw-bold text-dark mt-2"><?php echo __('subsidized_goods'); ?></h6>
                    <p class="text-muted small mb-4 flex-grow-1"><?php echo __('subsidized_goods_desc'); ?></p>
                    <a href="subsidy_list.php" class="btn btn-outline-warning text-dark btn-sm rounded-pill w-100 fw-bold shadow-sm">
                        <i class="fas fa-arrow-right me-1"></i> <?php echo __('distribution_logs'); ?>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift service-card" style="border-top: 4px solid #0ea5e9;">
                <div class="card-body p-4 text-center d-flex flex-column">
                    <div class="bg-info text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 64px; height: 64px; font-size: 1.75rem; flex-shrink:0;">
                        <i class="fas fa-mountain-sun"></i>
                    </div>
                    <h6 class="fw-bold text-dark mt-2"><?php echo __('agriculture_land'); ?></h6>
                    <p class="text-muted small mb-4 flex-grow-1"><?php echo __('agriculture_land_desc'); ?></p>
                    <a href="agriculture_list.php" class="btn btn-outline-info btn-sm rounded-pill w-100 fw-bold shadow-sm">
                        <i class="fas fa-arrow-right me-1"></i> <?php echo __('land_registry'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action Modal -->
<div class="modal fade" id="economicModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-dark text-white border-0 py-3">
        <h5 class="modal-title fw-bold text-warning"><i class="fas fa-plus-circle me-2 mt-1"></i> <?php echo __('quick_record_entry'); ?></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <p class="text-muted small mb-4"><?php echo __('select_economic_initiative_desc'); ?></p>
        <div class="d-grid gap-3">
            <a href="enterprise_create.php" class="btn btn-outline-primary text-start p-3 rounded-3 d-flex align-items-center">
                <i class="fas fa-store fs-3 me-3"></i>
                <div>
                    <div class="fw-bold"><?php echo __('register_micro_enterprise'); ?></div>
                    <div class="small text-muted"><?php echo __('register_micro_enterprise_desc'); ?></div>
                </div>
            </a>
            <a href="youth_create.php" class="btn btn-outline-success text-start p-3 rounded-3 d-flex align-items-center">
                <i class="fas fa-users-gear fs-3 me-3"></i>
                <div>
                    <div class="fw-bold"><?php echo __('register_youth'); ?></div>
                    <div class="small text-muted"><?php echo __('register_youth_desc'); ?></div>
                </div>
            </a>
            <a href="subsidy_create.php" class="btn btn-outline-warning text-start p-3 rounded-3 d-flex align-items-center">
                <i class="fas fa-wheat-awn fs-3 me-3 text-warning"></i>
                <div class="text-dark">
                    <div class="fw-bold"><?php echo __('log_subsidy_distribution'); ?></div>
                    <div class="small text-dark opacity-75"><?php echo __('log_subsidy_dist_desc'); ?></div>
                </div>
            </a>
            <a href="agriculture_create.php" class="btn btn-outline-info text-start p-3 rounded-3 d-flex align-items-center">
                <i class="fas fa-mountain-sun fs-3 me-3"></i>
                <div>
                    <div class="fw-bold"><?php echo __('register_agricultural_resource'); ?></div>
                    <div class="small text-muted"><?php echo __('register_agri_resource_desc'); ?></div>
                </div>
            </a>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.hover-lift { transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease; background: #fff; }
.hover-lift:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.08) !important; }
</style>
<?php require_once '../../includes/footer.php'; ?>

