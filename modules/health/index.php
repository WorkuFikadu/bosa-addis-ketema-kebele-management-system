<?php
require_once '../../includes/header.php';
require_once '../../config/database.php';

// Fetch quick stats
$stats = [
    'health' => $pdo->query("SELECT COUNT(*) FROM health_records")->fetchColumn(),
    'welfare' => $pdo->query("SELECT COUNT(*) FROM welfare_records")->fetchColumn(),
    'sanitation' => $pdo->query("SELECT COUNT(*) FROM sanitation_campaigns")->fetchColumn(),
    'safetynet' => $pdo->query("SELECT COUNT(*) FROM safetynet_records")->fetchColumn()
];
?>
<div class="container-fluid py-4 min-vh-100 bg-light">
    <div class="mb-3">
        <a href="../../dashboard.php" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold shadow-sm">
            <i class="fas fa-arrow-left me-2"></i><?php echo __('back'); ?>
        </a>
    </div>

    <!-- Header Section -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #0ea5e9, #2563eb); color: white;">
        <div class="card-body p-4 p-md-5 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4">
            <div>
                <h2 class="fw-black mb-2"><i class="fas fa-hand-holding-medical me-3 text-warning"></i><?php echo __('health_social_module_title'); ?></h2>
                <p class="mb-0 text-white-50 fw-bold fs-5" style="max-width: 600px;"><?php echo __('health_social_module_desc'); ?></p>
            </div>
            <button class="btn btn-warning text-dark rounded-pill fw-bold shadow-lg px-4 py-2" data-bs-toggle="modal" data-bs-target="#initiativeModal">
                <i class="fas fa-plus-circle me-2"></i> <?php echo __('register_initiative'); ?>
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded px-3 py-2 me-3 fs-3">
                        <i class="fas fa-notes-medical"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($stats['health']); ?></h3>
                        <span class="small text-muted fw-bold text-uppercase"><?php echo __('records'); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="bg-danger bg-opacity-10 text-danger rounded px-3 py-2 me-3 fs-3">
                        <i class="fas fa-hand-holding-heart"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($stats['welfare']); ?></h3>
                        <span class="small text-muted fw-bold text-uppercase"><?php echo __('welfare_cases'); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 text-success rounded px-3 py-2 me-3 fs-3">
                        <i class="fas fa-broom"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($stats['sanitation']); ?></h3>
                        <span class="small text-muted fw-bold text-uppercase"><?php echo __('campaigns_small'); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 text-warning rounded px-3 py-2 me-3 fs-3">
                        <i class="fas fa-people-roof"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($stats['safetynet']); ?></h3>
                        <span class="small text-muted fw-bold text-uppercase"><?php echo __('psnp_members'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Services Grid -->
    <div class="row g-4 mb-5">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift service-card" style="border-top: 4px solid #0ea5e9;">
                <div class="card-body p-4 text-center d-flex flex-column">
                    <div class="icon-wrapper bg-primary bg-opacity-10 text-primary mx-auto mb-3">
                        <i class="fas fa-syringe"></i>
                    </div>
                    <h6 class="fw-bold text-dark mt-2"><?php echo __('health_extension'); ?></h6>
                    <p class="text-muted small mb-4 flex-grow-1"><?php echo __('health_extension_desc'); ?></p>
                    <a href="health_list.php" class="btn btn-primary btn-sm rounded-pill w-100 fw-bold shadow-sm">
                        <i class="fas fa-arrow-right me-1"></i> <?php echo __('manage_drives'); ?>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift service-card" style="border-top: 4px solid #ef4444;">
                <div class="card-body p-4 text-center d-flex flex-column">
                    <div class="icon-wrapper bg-danger bg-opacity-10 text-danger mx-auto mb-3">
                        <i class="fas fa-hands-holding-child"></i>
                    </div>
                    <h6 class="fw-bold text-dark mt-2"><?php echo __('social_welfare'); ?></h6>
                    <p class="text-muted small mb-4 flex-grow-1"><?php echo __('social_welfare_desc'); ?></p>
                    <a href="welfare_list.php" class="btn btn-danger btn-sm rounded-pill w-100 fw-bold shadow-sm">
                        <i class="fas fa-arrow-right me-1"></i> <?php echo __('welfare_registry'); ?>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift service-card" style="border-top: 4px solid #10b981;">
                <div class="card-body p-4 text-center d-flex flex-column">
                    <div class="icon-wrapper bg-success bg-opacity-10 text-success mx-auto mb-3">
                        <i class="fas fa-broom"></i>
                    </div>
                    <h6 class="fw-bold text-dark mt-2"><?php echo __('sanitation'); ?></h6>
                    <p class="text-muted small mb-4 flex-grow-1"><?php echo __('sanitation_desc'); ?></p>
                    <a href="sanitation_list.php" class="btn btn-success btn-sm rounded-pill w-100 fw-bold shadow-sm">
                        <i class="fas fa-arrow-right me-1"></i> <?php echo __('campaigns'); ?>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift service-card" style="border-top: 4px solid #f59e0b;">
                <div class="card-body p-4 text-center d-flex flex-column">
                    <div class="icon-wrapper bg-warning bg-opacity-10 text-warning mx-auto mb-3">
                        <i class="fas fa-shield-heart"></i>
                    </div>
                    <h6 class="fw-bold text-dark mt-2"><?php echo __('safety_net'); ?></h6>
                    <p class="text-muted small mb-4 flex-grow-1"><?php echo __('safety_net_desc'); ?></p>
                    <a href="safetynet_list.php" class="btn btn-warning btn-sm rounded-pill w-100 fw-bold shadow-sm text-dark">
                        <i class="fas fa-arrow-right me-1"></i> <?php echo __('manage_psnp'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action Modal -->
<div class="modal fade" id="initiativeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-primary text-white border-0 py-3">
        <h5 class="modal-title fw-bold"><i class="fas fa-star me-2 mt-1"></i> <?php echo __('new_initiative'); ?></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <p class="text-muted small mb-4"><?php echo __('select_initiative_desc'); ?></p>
        <div class="d-grid gap-3">
            <a href="health_create.php" class="btn btn-outline-primary text-start p-3 rounded-3 d-flex align-items-center">
                <i class="fas fa-syringe fs-4 me-3"></i>
                <div>
                    <div class="fw-bold"><?php echo __('health_extension_drive'); ?></div>
                    <div class="small opacity-75"><?php echo __('health_extension_drive_desc'); ?></div>
                </div>
            </a>
            <a href="sanitation_list.php?action=new" class="btn btn-outline-success text-start p-3 rounded-3 d-flex align-items-center">
                <i class="fas fa-broom fs-4 me-3"></i>
                <div>
                    <div class="fw-bold"><?php echo __('sanitation_campaign'); ?></div>
                    <div class="small opacity-75"><?php echo __('sanitation_campaign_desc'); ?></div>
                </div>
            </a>
            <a href="safetynet_create.php" class="btn btn-outline-warning text-start p-3 rounded-3 d-flex align-items-center">
                <i class="fas fa-people-roof fs-4 me-3 text-warning"></i>
                <div class="text-dark">
                    <div class="fw-bold text-dark"><?php echo __('psnp_enrollment'); ?></div>
                    <div class="small opacity-75 text-dark"><?php echo __('psnp_enrollment_desc'); ?></div>
                </div>
            </a>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.icon-wrapper {
    width: 65px; height: 65px; font-size: 1.6rem;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.hover-lift { transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease; }
.hover-lift:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.08) !important; }
.service-card { background: #fff; }
</style>
<?php require_once '../../includes/footer.php'; ?>
