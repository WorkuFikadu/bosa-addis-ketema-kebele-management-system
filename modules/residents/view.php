<?php
// modules/residents/view.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

$id = $_GET['id'] ?? null;
$stmt = $pdo->prepare("SELECT i.*, a.*, ag.bdate, ag.age FROM individuals i 
                       LEFT JOIN addresses a ON i.id = a.id 
                       LEFT JOIN ages ag ON i.id = ag.id 
                       WHERE i.id = ?");
$stmt->execute([$id]);
$r = $stmt->fetch();

if (!$r) {
    header('Location: index.php');
    exit;
}

// Fetch active ID card if any
$id_stmt = $pdo->prepare("SELECT * FROM id_cards WHERE resident_id = ? AND status = 'Active'");
$id_stmt->execute([$id]);
$active_id = $id_stmt->fetch();
?>

<div class="container-fluid py-4">
    <!-- Header with Breadcrumbs & Actions -->
    <div class="row mb-5 align-items-center">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-decoration-none text-muted"><?php echo __('dashboard'); ?></a></li>
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted"><?php echo __('residents'); ?></a></li>
                    <li class="breadcrumb-item active fw-bold text-primary" aria-current="page"><?php echo __('profile_detail'); ?></li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold text-dark mb-0"><?php echo __('resident'); ?>: <?php echo "{$r['fname']} {$r['lname']}"; ?></h1>
        </div>
        <div class="col-auto d-flex gap-2">
            <a href="index.php" class="btn btn-light border rounded-pill px-4">
                <i class="fas fa-arrow-left me-2"></i><?php echo __('back'); ?>
            </a>
            <?php if ($_SESSION['role'] !== 'security'): ?>
                <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-primary rounded-pill px-4 shadow-sm">
                    <i class="fas fa-edit me-2"></i><?php echo __('edit'); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column: Profile Summary & Quick Stats -->
        <div class="col-xl-4 col-lg-5">
            <!-- Profile Card -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 30px; overflow: hidden;">
                <div class="card-header border-0 p-0 position-relative" style="height: 120px; background: linear-gradient(45deg, #1e3a8a, #3b82f6);">
                    <!-- Decorative patterns would go here -->
                </div>
                <div class="card-body text-center p-4" style="margin-top: -60px;">
                    <div class="mb-3 position-relative d-inline-block">
                        <img src="../../assets/images/<?php echo $r['phot']; ?>" 
                             class="rounded-circle border border-5 border-white shadow-lg mx-auto" 
                             style="width: 140px; height: 140px; object-fit: cover;" 
                             onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($r['fname']); ?>&size=140&background=4f46e5&color=fff'">
                        <?php if ($r['status'] === 'deceased'): ?>
                            <span class="position-absolute bottom-0 end-0 bg-danger text-white rounded-pill px-2 py-1 small fw-bold border border-2 border-white">
                                <i class="fas fa-dove"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                    <h3 class="fw-bold text-dark mb-1"><?php echo "{$r['fname']} {$r['mname']} {$r['lname']}"; ?></h3>
                    <p class="text-muted mb-4 fs-6 opacity-75"><?php echo $r['occ']; ?></p>
                    
                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <div class="bg-light rounded-4 p-3 border border-white">
                                <p class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.6rem;"><?php echo __('sex'); ?></p>
                                <h6 class="fw-bold text-dark mb-0"><?php echo $r['s']; ?></h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-4 p-3 border border-white">
                                <p class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.6rem;"><?php echo __('age'); ?></p>
                                <h6 class="fw-bold text-dark mb-0"><?php echo $r['age']; ?> <?php echo __('age_yrs'); ?></h6>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <?php if ($active_id): ?>
                            <a href="../idcards/view.php?id=<?php echo $active_id['id']; ?>" class="btn btn-outline-success rounded-pill fw-bold">
                                <i class="fas fa-id-badge me-2"></i><?php echo __('view_all'); ?> <?php echo __('active_ids'); ?>
                            </a>
                        <?php else: ?>
                            <a href="../idcards/generate.php?id=<?php echo $id; ?>" class="btn btn-outline-primary rounded-pill fw-bold">
                                <i class="fas fa-plus me-2"></i><?php echo __('issue_new_id'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Important Status/Quick Stats -->
            <div class="card border-0 shadow-sm p-4" style="border-radius: 24px;">
                <h6 class="fw-bold text-dark mb-3"><?php echo __('status'); ?></h6>
                <div class="list-group list-group-flush">
                    <div class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 py-3 border-bottom border-light">
                        <span class="text-muted small"><?php echo __('voter_status'); ?></span>
                        <?php if ($r['age'] >= 18): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill"><?php echo __('eligible'); ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 rounded-pill"><?php echo __('underage'); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 py-3 border-bottom border-light">
                        <span class="text-muted small"><?php echo __('resident'); ?> <?php echo __('status'); ?></span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill"><?php echo __('verified'); ?></span>
                    </div>
                    <?php if ($r['status'] === 'deceased'): ?>
                    <div class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 py-3">
                        <span class="text-muted small"><?php echo __('status'); ?></span>
                        <span class="badge bg-danger px-3 py-2 rounded-pill"><?php echo __('deceased_label'); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column: Detailed Tabs/Groups -->
        <div class="col-xl-8 col-lg-7">
            <!-- Tabs for organization -->
            <div class="card border-0 shadow-sm p-0 overflow-hidden" style="border-radius: 30px;">
                <div class="card-header bg-white border-bottom-0 p-4 pb-0">
                    <ul class="nav nav-pills gap-2" id="profileTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active rounded-pill px-4 py-2" data-bs-toggle="pill" data-bs-target="#personal"><?php echo __('primary_data'); ?></button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link rounded-pill px-4 py-2" data-bs-toggle="pill" data-bs-target="#parental"><?php echo __('lineage_family'); ?></button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link rounded-pill px-4 py-2" data-bs-toggle="pill" data-bs-target="#emergency"><?php echo __('supporting_docs'); ?></button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4 pt-4">
                    <div class="tab-content" id="pills-tabContent">
                        <!-- Personal Info Tab -->
                        <div class="tab-pane fade show active" id="personal">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="p-3 rounded-4 bg-light bg-opacity-50 border border-white h-100">
                                        <h6 class="text-primary fw-bold mb-3 small text-uppercase tracking-wider"><?php echo __('civil_information'); ?></h6>
                                        <div class="space-y-4">
                                            <div class="mb-3">
                                                <label class="text-muted small d-block mb-1"><?php echo __('full_identity'); ?></label>
                                                <span class="fw-bold text-dark fs-6"><?php echo "{$r['fname']} {$r['mname']} {$r['lname']}"; ?></span>
                                            </div>
                                            <div class="mb-3">
                                                <label class="text-muted small d-block mb-1"><?php echo __('birth_date'); ?></label>
                                                <span class="fw-bold text-dark"><?php echo date('l, F j, Y', strtotime($r['bdate'])); ?></span>
                                            </div>
                                            <div class="mb-3">
                                                <label class="text-muted small d-block mb-1"><?php echo __('birth_place'); ?></label>
                                                <span class="fw-bold text-dark"><?php echo $r['birth_place'] ?? 'Not Recorded'; ?></span>
                                            </div>
                                            <div class="mb-3">
                                                <label class="text-muted small d-block mb-1"><?php echo __('marital_status'); ?></label>
                                                <span class="fw-bold text-dark"><?php echo $r['mar']; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-4 bg-light bg-opacity-50 border border-white h-100">
                                        <h6 class="text-primary fw-bold mb-3 small text-uppercase tracking-wider"><?php echo __('contact_address'); ?></h6>
                                        <div class="space-y-4">
                                            <div class="mb-3">
                                                <label class="text-muted small d-block mb-1"><?php echo __('phone_connectivity'); ?></label>
                                                <span class="fw-bold text-dark"><i class="fas fa-phone-alt me-2 text-primary small"></i><?php echo $r['pho_no'] ?? 'N/A'; ?></span>
                                            </div>
                                            <div class="mb-3">
                                                <label class="text-muted small d-block mb-1"><?php echo __('primary_residence'); ?></label>
                                                <span class="fw-bold text-dark d-block">Kebele: <?php echo $r['kebele']; ?></span>
                                                <span class="text-muted small d-block"><?php echo "{$r['city']}, {$r['zone']}, {$r['region']}"; ?></span>
                                                <?php if (!empty($r['kebele_zone']) || !empty($r['garee']) || !empty($r['block'])): ?>
                                                    <div class="mt-2 pt-2 border-top border-light">
                                                        <?php if(!empty($r['kebele_zone'])): ?>
                                                            <span class="badge bg-success-subtle text-success me-1">Zone <?php echo $r['kebele_zone']; ?></span>
                                                        <?php endif; ?>
                                                        <?php if(!empty($r['garee'])): ?>
                                                            <span class="badge bg-info-subtle text-info me-1">Garee: <?php echo $r['garee']; ?></span>
                                                        <?php endif; ?>
                                                        <?php if(!empty($r['block'])): ?>
                                                            <span class="badge bg-warning-subtle text-warning">Block: <?php echo $r['block']; ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mb-3">
                                                <label class="text-muted small d-block mb-1"><?php echo __('nat_edu'); ?></label>
                                                <span class="badge bg-white shadow-sm text-dark border px-3 py-2 rounded-pill me-2"><?php echo $r['nat']; ?></span>
                                                <span class="badge bg-white shadow-sm text-dark border px-3 py-2 rounded-pill"><?php echo $r['level_edu']; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 p-4 rounded-4" style="background: rgba(79, 70, 229, 0.05); border: 1px dashed rgba(79, 70, 229, 0.2);">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-primary text-white rounded-circle p-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-certificate"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1"><?php echo __('official_doc_revocation'); ?></h6>
                                        <p class="text-muted small mb-0"><?php echo __('constituent_record_msg'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Lineage Tab -->
                        <div class="tab-pane fade" id="parental">
                            <div class="card border-light rounded-4 shadow-none bg-light bg-opacity-25 mb-4">
                                <div class="card-body p-4">
                                    <h6 class="text-dark fw-bold mb-4"><?php echo __('matrimonial_parental'); ?></h6>
                                    <div class="row g-4">
                                        <div class="col-md-6 border-end">
                                            <div class="mb-4">
                                                <label class="text-muted small d-block mb-1"><?php echo __('mother_name_reg'); ?></label>
                                                <h6 class="fw-bold mb-0"><?php echo $r['mother_full_name'] ?? 'Data Missing'; ?></h6>
                                                <span class="text-muted small"><?php echo __('nationality'); ?>: <?php echo $r['mother_nat'] ?? 'Itoophiyaa'; ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <label class="text-muted small d-block mb-1"><?php echo __('father_name_reg'); ?></label>
                                                <h6 class="fw-bold mb-0"><?php echo $r['father_full_name'] ?? 'Data Missing'; ?></h6>
                                                <span class="text-muted small"><?php echo __('nationality'); ?>: <?php echo $r['father_nat'] ?? 'Itoophiyaa'; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Emergency Contact -->
                            <div class="p-4 rounded-4 border-start border-4 border-danger bg-danger bg-opacity-10">
                                <h6 class="text-danger fw-bold mb-2"><?php echo __('emergency_info'); ?></h6>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <label class="text-muted small d-block"><?php echo __('emergency_contact'); ?> <?php echo __('full_name'); ?></label>
                                        <span class="fw-bold"><?php echo $r['emergency_contact_name'] ?? 'Not Set'; ?></span>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="text-muted small d-block"><?php echo __('phone'); ?></label>
                                        <span class="fw-bold"><?php echo $r['emergency_contact_phone'] ?? 'Not Set'; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Support Docs Tab -->
                        <div class="tab-pane fade" id="emergency">
                            <h6 class="fw-bold mb-4"><?php echo __('digitized_records'); ?></h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
                                                    <i class="fas fa-baby fs-5"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0">Birth Certificate</h6>
                                            </div>
                                            <?php if (!empty($r['doc_birth_cert'])): ?>
                                                <a href="../../uploads/docs/<?php echo $r['doc_birth_cert']; ?>" target="_blank" class="btn btn-sm btn-light w-100 rounded-pill">
                                                    <i class="fas fa-eye me-2"></i><?php echo __('view_scanned_doc'); ?>
                                                </a>
                                            <?php else: ?>
                                                <div class="text-center py-2">
                                                    <p class="text-muted small mb-0 italic"><?php echo __('no_birth_cert_found'); ?></p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-success bg-opacity-10 text-success rounded p-2 me-3">
                                                    <i class="fas fa-shield-check fs-5"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0">Clearance Document</h6>
                                            </div>
                                            <?php if (!empty($r['doc_clearance'])): ?>
                                                <a href="../../uploads/docs/<?php echo $r['doc_clearance']; ?>" target="_blank" class="btn btn-sm btn-light w-100 rounded-pill">
                                                    <i class="fas fa-eye me-2"></i><?php echo __('view_scanned_doc'); ?>
                                                </a>
                                            <?php else: ?>
                                                <div class="text-center py-2">
                                                    <p class="text-muted small mb-0 italic"><?php echo __('no_clearance_found'); ?></p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Audit Trail Preview (Internal for Admin) -->
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <div class="mt-4 p-4 bg-dark text-white rounded-4 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-0">
                        <h6 class="mb-0 fw-bold small"><i class="fas fa-user-secret me-2 text-info"></i> <?php echo __('recent_system_activity'); ?></h6>
                        <a href="../reports/audit_logs.php" class="text-info small text-decoration-none"><?php echo __('full_log'); ?> <i class="fas fa-chevron-right ms-1"></i></a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
