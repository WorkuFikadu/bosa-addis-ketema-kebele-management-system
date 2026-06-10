<?php
// modules/letters/index.php
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../auth/login.php'); exit; }

$letters = $pdo->query("
    SELECT gl.*, i.fname, i.lname, u.username as issuer
    FROM generated_letters gl
    JOIN individuals i ON gl.resident_id = i.id
    JOIN users u ON gl.issued_by = u.id
    ORDER BY gl.created_at DESC
    LIMIT 50
")->fetchAll();

$stats = $pdo->query("SELECT letter_type, COUNT(*) as cnt FROM generated_letters GROUP BY letter_type")->fetchAll(PDO::FETCH_KEY_PAIR);
$total = array_sum($stats);
?>

<div class="container-fluid py-4 min-vh-100 bg-light">
    <div class="mb-3">
        <a href="../../dashboard.php" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-bold shadow-sm">
            <i class="fas fa-arrow-left me-2"></i><?php echo __('back_to_dashboard'); ?>
        </a>
    </div>

    <!-- Header Section -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 position-relative overflow-hidden" style="background: linear-gradient(135deg, #4c1d95, #6366f1); color: white;">
        <div class="position-absolute top-0 end-0 opacity-10 p-5" style="transform: scale(2) translate(10%, -20%);">
            <i class="fas fa-envelope-open-text fa-10x"></i>
        </div>
        <div class="card-body p-4 p-md-5 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 position-relative z-1">
            <div>
                <h2 class="fw-black mb-2 text-warning"><i class="fas fa-file-signature me-3"></i><?php echo __('official_letters'); ?></h2>
                <p class="mb-0 text-white-50 fw-bold fs-5" style="max-width: 600px;"><?php echo __('letters_desc'); ?></p>
            </div>
            <button class="btn btn-warning text-dark rounded-pill fw-bold shadow-lg px-4 py-3 text-uppercase" data-bs-toggle="modal" data-bs-target="#lettersModal">
                <i class="fas fa-plus-circle me-2"></i> <?php echo __('issue_new_letter'); ?>
            </button>
        </div>
    </div>
    
    <!-- Quick Access Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white border-start border-primary border-4 h-100 hover-lift">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="small text-muted fw-bold text-uppercase"><?php echo __('letter_residency'); ?></span>
                        <h3 class="fw-black mb-0 text-dark"><?php echo $stats['Residency'] ?? 0; ?></h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-house-user fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white border-start border-success border-4 h-100 hover-lift">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="small text-muted fw-bold text-uppercase"><?php echo __('letter_conduct'); ?></span>
                        <h3 class="fw-black mb-0 text-dark"><?php echo $stats['Conduct'] ?? 0; ?></h3>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-user-check fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white border-start border-info border-4 h-100 hover-lift">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="small text-muted fw-bold text-uppercase"><?php echo __('letter_verification'); ?></span>
                        <h3 class="fw-black mb-0 text-dark"><?php echo $stats['Verification'] ?? 0; ?></h3>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-certificate fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white border-start border-warning border-4 h-100 hover-lift">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="small text-muted fw-bold text-uppercase"><?php echo __('letter_clearance'); ?></span>
                        <h3 class="fw-black mb-0 text-dark"><?php echo $stats['Clearance'] ?? 0; ?></h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-file-circle-check fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- logs -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mt-4 mb-5">
        <div class="card-header bg-white p-4 border-0 d-flex justify-content-between align-items-center">
            <h5 class="fw-black mb-0"><i class="fas fa-history text-secondary me-2"></i> <?php echo __('recent_letters_log'); ?></h5>
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold border border-primary"><?php echo __('total_issued'); ?>: <?php echo $total; ?></span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="px-4"><?php echo __('ref_num'); ?></th>
                        <th><?php echo __('recipient'); ?></th>
                        <th><?php echo __('type'); ?></th>
                        <th><?php echo __('issue_date'); ?></th>
                        <th><?php echo __('issued_by'); ?></th>
                        <th class="text-end px-4"><?php echo __('action'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($letters)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted p-5"><?php echo __('no_letters_issued'); ?></td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($letters as $l): 
                        $cls = match($l['letter_type']) { 'Residency'=>'text-primary', 'Conduct'=>'text-success', 'Verification'=>'text-info', 'Clearance'=>'text-warning' };
                    ?>
                    <tr>
                        <td class="px-4"><code class="fw-bold bg-light px-2 py-1 border rounded"><?php echo $l['ref_number']; ?></code></td>
                        <td>
                            <div class="fw-bold text-dark small"><?php echo htmlspecialchars("{$l['fname']} {$l['lname']}"); ?></div>
                            <small class="text-muted">ID: #<?php echo $l['resident_id']; ?></small>
                        </td>
                        <td><span class="badge bg-light <?php echo $cls; ?> border rounded-pill px-3 shadow-sm"><?php echo strtoupper($l['letter_type']); ?></span></td>
                        <td><small class="fw-bold"><?php echo date('M d, Y', strtotime($l['issue_date'])); ?></small></td>
                        <td><small class="text-muted"><?php echo $l['issuer']; ?></small></td>
                        <td class="text-end px-4">
                            <a href="print.php?id=<?php echo $l['id']; ?>" target="_blank" class="btn btn-sm btn-light rounded-pill px-3 shadow-sm text-primary fw-bold hover-lift">
                                <i class="fas fa-print me-1"></i> <?php echo __('print'); ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Quick Action Modal -->
<div class="modal fade" id="lettersModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-dark text-white border-0 py-3">
        <h5 class="modal-title fw-bold text-warning"><i class="fas fa-paper-plane me-2 mt-1"></i> <?php echo __('issue_official_letter'); ?></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <p class="text-muted small mb-4"><?php echo __('select_letter_type_desc'); ?></p>
        <div class="d-grid gap-3">
            <a href="generate.php?type=Residency" class="btn btn-outline-primary text-start p-3 rounded-3 d-flex align-items-center hover-lift">
                <i class="fas fa-house-user fs-3 me-3"></i>
                <div>
                    <div class="fw-bold"><?php echo __('residency_letter_title'); ?></div>
                    <div class="small text-muted"><?php echo __('residency_desc'); ?></div>
                </div>
            </a>
            <a href="generate.php?type=Conduct" class="btn btn-outline-success text-start p-3 rounded-3 d-flex align-items-center hover-lift">
                <i class="fas fa-user-check fs-3 me-3"></i>
                <div>
                    <div class="fw-bold"><?php echo __('conduct_letter_title'); ?></div>
                    <div class="small text-muted"><?php echo __('conduct_desc'); ?></div>
                </div>
            </a>
            <a href="generate.php?type=Verification" class="btn btn-outline-info text-start p-3 rounded-3 d-flex align-items-center hover-lift">
                <i class="fas fa-certificate fs-3 me-3"></i>
                <div>
                    <div class="fw-bold"><?php echo __('verification_letter_title'); ?></div>
                    <div class="small text-muted"><?php echo __('verification_desc'); ?></div>
                </div>
            </a>
            <a href="generate.php?type=Clearance" class="btn btn-outline-warning text-start p-3 rounded-3 d-flex align-items-center hover-lift text-dark">
                <i class="fas fa-file-circle-check fs-3 me-3"></i>
                <div>
                    <div class="fw-bold text-dark"><?php echo __('clearance_letter_title'); ?></div>
                    <div class="small text-dark opacity-75"><?php echo __('clearance_desc'); ?></div>
                </div>
            </a>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.hover-lift { transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease; background: #fff; }
.hover-lift:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.08) !important; }
</style>

<?php require_once '../../includes/footer.php'; ?>
