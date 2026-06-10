<?php
require_once '../../includes/header.php';
require_once '../../config/database.php';

// Fetch quick stats
$stats = [
    'police' => $pdo->query("SELECT COUNT(*) FROM police_records")->fetchColumn(),
    'milisha' => $pdo->query("SELECT COUNT(*) FROM milisha_records")->fetchColumn(),
    'gachana' => $pdo->query("SELECT COUNT(*) FROM gachana_records")->fetchColumn(),
    'court' => $pdo->query("SELECT COUNT(*) FROM court_cases")->fetchColumn(),
    'active_cases' => $pdo->query("SELECT COUNT(*) FROM court_cases WHERE status='In Progress'")->fetchColumn()
];
?>
<div class="container-fluid py-4 min-vh-100 bg-light">
    <div class="mb-3">
        <a href="../../dashboard.php" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-bold shadow-sm">
            <i class="fas fa-arrow-left me-2"></i><?php echo __('back'); ?>
        </a>
    </div>

    <!-- Header Section -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 position-relative overflow-hidden" style="background: linear-gradient(135deg, #1e293b, #0f172a); color: white;">
        <div class="position-absolute top-0 end-0 opacity-10 p-5" style="transform: scale(2) translate(10%, -20%);">
            <i class="fas fa-shield-halved fa-10x"></i>
        </div>
        <div class="card-body p-4 p-md-5 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 position-relative z-1">
            <div>
                <h2 class="fw-black mb-2 text-warning"><i class="fas fa-shield-halved me-3"></i><?php echo __('peace_security_module'); ?></h2>
                <p class="mb-0 text-white-50 fw-bold fs-5" style="max-width: 600px;"><?php echo __('peace_security_desc'); ?></p>
            </div>
            <button class="btn btn-warning text-dark rounded-pill fw-bold shadow-lg px-4 py-3 text-uppercase" data-bs-toggle="modal" data-bs-target="#securityModal">
                <i class="fas fa-plus-circle me-2"></i> <?php echo __('new_case_record'); ?>
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white border-start border-primary border-4">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="small text-muted fw-bold text-uppercase"><?php echo __('total_police'); ?></span>
                        <h3 class="fw-black mb-0 text-dark"><?php echo number_format($stats['police']); ?></h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-user-shield fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white border-start border-success border-4">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="small text-muted fw-bold text-uppercase"><?php echo __('milisha_force'); ?></span>
                        <h3 class="fw-black mb-0 text-dark"><?php echo number_format($stats['milisha']); ?></h3>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-person-military-pointing fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white border-start border-info border-4">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="small text-muted fw-bold text-uppercase"><?php echo __('gachana_sirna_count'); ?></span>
                        <h3 class="fw-black mb-0 text-dark"><?php echo number_format($stats['gachana']); ?></h3>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-users-viewfinder fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white border-start border-danger border-4">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="small text-muted fw-bold text-uppercase"><?php echo __('active_cases'); ?></span>
                        <h3 class="fw-black mb-0 text-danger"><?php echo number_format($stats['active_cases']); ?> <span class="fs-6 text-muted fw-normal">/ <?php echo $stats['court']; ?></span></h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-gavel fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Services Grid -->
    <div class="row g-4 mb-5">
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 glass-light-card hover-lift">
                <div class="card-body p-4 text-center d-flex flex-column">
                    <div class="bg-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 64px; height: 64px; font-size: 1.75rem;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h5 class="fw-bold text-dark"><?php echo __('police_reg'); ?></h5>
                    <p class="text-muted small mb-4 flex-grow-1"><?php echo __('police_reg_desc'); ?></p>
                    <a href="police_list.php" class="btn btn-outline-primary btn-sm rounded-pill w-100 fw-bold">
                        <i class="fas fa-arrow-right me-1"></i> <?php echo __('open_police_module'); ?>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 glass-light-card hover-lift">
                <div class="card-body p-4 text-center d-flex flex-column">
                    <div class="bg-success text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 64px; height: 64px; font-size: 1.75rem;">
                        <i class="fas fa-person-military-pointing"></i>
                    </div>
                    <h5 class="fw-bold text-dark"><?php echo __('milisha_reg'); ?></h5>
                    <p class="text-muted small mb-4 flex-grow-1"><?php echo __('milisha_reg_desc'); ?></p>
                    <a href="milisha_list.php" class="btn btn-outline-success btn-sm rounded-pill w-100 fw-bold">
                        <i class="fas fa-arrow-right me-1"></i> <?php echo __('open_milisha_module'); ?>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 glass-light-card hover-lift">
                <div class="card-body p-4 text-center d-flex flex-column">
                    <div class="bg-info text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 64px; height: 64px; font-size: 1.75rem;">
                        <i class="fas fa-users-viewfinder"></i>
                    </div>
                    <h5 class="fw-bold text-dark"><?php echo __('gachana_reg'); ?></h5>
                    <p class="text-muted small mb-4 flex-grow-1"><?php echo __('gachana_reg_desc'); ?></p>
                    <a href="gachana_list.php" class="btn btn-outline-info btn-sm rounded-pill w-100 fw-bold">
                        <i class="fas fa-arrow-right me-1"></i> <?php echo __('open_gachana_module'); ?>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 glass-light-card hover-lift border-top border-danger border-4 bg-danger bg-opacity-10">
                <div class="card-body p-4 text-center d-flex flex-column">
                    <div class="bg-danger text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 64px; height: 64px; font-size: 1.75rem;">
                        <i class="fas fa-gavel"></i>
                    </div>
                    <h5 class="fw-bold text-dark"><?php echo __('court_justice'); ?></h5>
                    <p class="text-dark opacity-75 small mb-4 flex-grow-1"><?php echo __('court_justice_desc'); ?></p>
                    <a href="court_list.php" class="btn btn-danger btn-sm rounded-pill w-100 fw-bold shadow">
                        <i class="fas fa-arrow-right me-1"></i> <?php echo __('manage_courts'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action Modal -->
<div class="modal fade" id="securityModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-dark text-white border-0 py-3">
        <h5 class="modal-title fw-bold text-warning"><i class="fas fa-plus-circle me-2 mt-1"></i> <?php echo __('quick_record_entry'); ?></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <p class="text-muted small mb-4"><?php echo __('select_security_record_type'); ?></p>
        <div class="d-grid gap-3">
            <a href="court_create.php" class="btn btn-outline-danger text-start p-3 rounded-3 d-flex align-items-center">
                <i class="fas fa-gavel fs-3 me-3"></i>
                <div>
                    <div class="fw-bold"><?php echo __('file_court_case'); ?></div>
                    <div class="small text-muted"><?php echo __('file_court_case_desc'); ?></div>
                </div>
            </a>
            <a href="police_create.php" class="btn btn-outline-primary text-start p-3 rounded-3 d-flex align-items-center">
                <i class="fas fa-user-shield fs-3 me-3"></i>
                <div>
                    <div class="fw-bold"><?php echo __('register_police_personnel'); ?></div>
                    <div class="small text-muted"><?php echo __('register_police_desc'); ?></div>
                </div>
            </a>
            <a href="milisha_create.php" class="btn btn-outline-success text-start p-3 rounded-3 d-flex align-items-center">
                <i class="fas fa-person-military-pointing fs-3 me-3"></i>
                <div>
                    <div class="fw-bold"><?php echo __('register_milisha_btn'); ?></div>
                    <div class="small text-muted"><?php echo __('register_milisha_desc'); ?></div>
                </div>
            </a>
            <a href="gachana_create.php" class="btn btn-outline-info text-start p-3 rounded-3 d-flex align-items-center">
                <i class="fas fa-users-viewfinder fs-3 me-3"></i>
                <div>
                    <div class="fw-bold"><?php echo __('register_gachana_btn'); ?></div>
                    <div class="small text-muted"><?php echo __('register_gachana_desc'); ?></div>
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
