<?php
// modules/justice/court_list.php — Social Court Cases Registry
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../auth/login.php'); exit; }

// Handle quick one-click status change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quick_status'])) {
    $case_id   = intval($_POST['case_id']);
    $new_status = $_POST['new_status'];
    $allowed   = ['Open', 'In Progress', 'Resolved', 'Dismissed', 'Appealed'];
    if (in_array($new_status, $allowed)) {
        $resolved_date = ($new_status === 'Resolved') ? date('Y-m-d') : null;
        $pdo->prepare("UPDATE court_cases SET status = ?, resolved_date = ? WHERE id = ?")
            ->execute([$new_status, $resolved_date, $case_id]);
        if (isset($_SESSION['user_id'])) {
            $pdo->prepare("INSERT INTO audit_logs (user_id, action, details) VALUES (?, 'UPDATE', ?)")
                ->execute([$_SESSION['user_id'], "Quick status change on Case ID #$case_id → $new_status"]);
        }
    }
    header("Location: court_list.php?msg=" . urlencode("Case #$case_id status changed to $new_status."));
    exit;
}

// Handle full verdict/status update (modal form)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_case'])) {
    $case_id = intval($_POST['case_id']);
    $new_status = $_POST['new_status'];
    $verdict = trim($_POST['verdict'] ?? '');
    $resolved_date = ($new_status === 'Resolved') ? date('Y-m-d') : null;

    $pdo->prepare("UPDATE court_cases SET status = ?, verdict = ?, resolved_date = ? WHERE id = ?")
        ->execute([$new_status, $verdict ?: null, $resolved_date, $case_id]);

    // Audit log
    if (isset($_SESSION['user_id'])) {
        $pdo->prepare("INSERT INTO audit_logs (user_id, action, details) VALUES (?, 'UPDATE', ?)")
            ->execute([$_SESSION['user_id'], "Updated Court Case ID #$case_id to status: $new_status with verdict."]);
    }
    header("Location: court_list.php?msg=Case+updated+successfully.");
    exit;
}

$search = $_GET['q'] ?? '';
$filter = $_GET['status'] ?? 'all';
$params = [];
$where = "WHERE 1=1";

if ($search) {
    $where .= " AND (c.case_number LIKE ? OR c.plaintiff_name LIKE ? OR c.defendant_name LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}
if ($filter !== 'all') {
    $where .= " AND c.status = ?";
    $params[] = $filter;
}

$cases = $pdo->prepare("
    SELECT c.*,
        pi.fname AS p_fname, pi.lname AS p_lname,
        di.fname AS d_fname, di.lname AS d_lname
    FROM court_cases c
    LEFT JOIN individuals pi ON c.plaintiff_id = pi.id
    LEFT JOIN individuals di ON c.defendant_id = di.id
    $where
    ORDER BY c.filed_date DESC, c.created_at DESC
");
$cases->execute($params);
$cases = $cases->fetchAll();

// Stats
$all_cases = $pdo->query("SELECT status, COUNT(*) as cnt FROM court_cases GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$total = array_sum($all_cases);
?>

<?php if (isset($_GET['msg'])): ?>
<div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($_GET['msg']); ?>
</div>
<?php endif; ?>

<div class="mb-3">
    <a href="index.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold shadow-sm">
        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
    </a>
</div>
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="fw-black mb-1" style="font-size:2.4rem;"><i class="fas fa-gavel text-warning me-2"></i>Court Cases Registry</h2>
        <p class="text-muted mb-0">Bosa Addis Kebele — Social Court & Civic Dispute Management</p>
    </div>
    <a href="court_create.php" class="btn btn-warning shadow-sm rounded-pill px-4 fw-bold">
        <i class="fas fa-plus me-2"></i>File New Case
    </a>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-2">
        <a href="court_list.php" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-3 text-center h-100 <?php echo $filter==='all'?'border border-warning border-2':''; ?>">
                <div class="fw-black fs-2 text-dark"><?php echo $total; ?></div>
                <div class="text-muted small fw-bold text-uppercase">All Cases</div>
            </div>
        </a>
    </div>
    <?php
    $statuses = ['Open' => ['color'=>'primary','icon'=>'fa-folder-open'], 'In Progress' => ['color'=>'warning','icon'=>'fa-spinner'], 'Resolved' => ['color'=>'success','icon'=>'fa-check-circle'], 'Dismissed' => ['color'=>'secondary','icon'=>'fa-ban'], 'Appealed' => ['color'=>'info','icon'=>'fa-arrow-up-from-bracket']];
    foreach ($statuses as $st => $conf):
        $cnt = $all_cases[$st] ?? 0;
    ?>
    <div class="col-6 col-md-2">
        <a href="court_list.php?status=<?php echo urlencode($st); ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-3 text-center h-100 <?php echo $filter===$st?'border border-'.$conf['color'].' border-2':''; ?>">
                <div class="fw-black fs-2 text-<?php echo $conf['color']; ?>"><?php echo $cnt; ?></div>
                <div class="text-muted small fw-bold text-uppercase"><?php echo $st; ?></div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<!-- Search & Filter -->
<div class="row mb-4 align-items-center">
    <div class="col-lg-5">
        <form method="GET" class="d-flex">
            <?php if ($filter !== 'all'): ?><input type="hidden" name="status" value="<?php echo htmlspecialchars($filter); ?>"><?php endif; ?>
            <div class="input-group shadow-sm rounded-pill overflow-hidden border">
                <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-search text-muted"></i></span>
                <input type="text" name="q" class="form-control border-0 py-2" placeholder="Search by case no., plaintiff, or defendant..." value="<?php echo htmlspecialchars($search); ?>">
                <?php if ($search || $filter !== 'all'): ?>
                    <a href="court_list.php" class="input-group-text bg-white border-0 text-danger" title="Clear filters"><i class="fas fa-times"></i></a>
                <?php endif; ?>
                <button type="submit" class="btn btn-warning px-4 fw-bold rounded-0 rounded-end">Search</button>
            </div>
        </form>
    </div>
</div>

<!-- Cases Table -->
<div class="card p-4 border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead style="background:#f8f9fa;" class="text-muted small text-uppercase">
                <tr>
                    <th class="border-0 px-3">Case No.</th>
                    <th class="border-0">Plaintiff (Accuser)</th>
                    <th class="border-0">Defendant (Accused)</th>
                    <th class="border-0">Type</th>
                    <th class="border-0">Filed</th>
                    <th class="border-0">Status</th>
                    <th class="border-0 text-end px-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cases as $case):
                    $st = $case['status'];
                    $status_conf = [
                        'Open'        => ['bg-primary',   'fa-folder-open'],
                        'In Progress' => ['bg-warning text-dark', 'fa-spinner'],
                        'Resolved'    => ['bg-success',   'fa-check-circle'],
                        'Dismissed'   => ['bg-secondary', 'fa-ban'],
                        'Appealed'    => ['bg-info',      'fa-arrow-up'],
                    ][$st] ?? ['bg-secondary','fa-circle'];
                    
                    $cat_cls = 'bg-secondary';
                    if (strpos($case['case_category'], 'Civil') !== false) $cat_cls = 'bg-primary';
                    elseif (strpos($case['case_category'], 'Boundary') !== false) $cat_cls = 'bg-warning text-dark';
                    elseif (strpos($case['case_category'], 'Family') !== false) $cat_cls = 'bg-pink text-white';
                    elseif (strpos($case['case_category'], 'Criminal') !== false) $cat_cls = 'bg-danger';
                    elseif (strpos($case['case_category'], 'Labor') !== false) $cat_cls = 'bg-info';
                ?>
                <tr>
                    <td class="px-3">
                        <div class="fw-bold text-warning" style="font-size:1.1rem;"><?php echo htmlspecialchars($case['case_number']); ?></div>
                        <?php if ($case['presiding_judge']): ?>
                            <small class="text-muted d-block mt-1"><i class="fas fa-user-tie me-1"></i><?php echo htmlspecialchars($case['presiding_judge']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;font-size:0.9rem;flex-shrink:0;">
                                <i class="fas fa-user-injured"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark small"><?php echo htmlspecialchars($case['plaintiff_name']); ?></div>
                                <?php if ($case['p_fname']): ?>
                                    <small class="text-primary"><i class="fas fa-link me-1"></i>Linked Profile</small>
                                <?php else: ?>
                                    <small class="text-muted">External</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;font-size:0.9rem;flex-shrink:0;">
                                <i class="fas fa-user-slash"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark small"><?php echo htmlspecialchars($case['defendant_name']); ?></div>
                                <?php if ($case['d_fname']): ?>
                                    <small class="text-danger"><i class="fas fa-link me-1"></i>Linked Profile</small>
                                <?php else: ?>
                                    <small class="text-muted">External</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge <?php echo $cat_cls; ?> rounded-pill px-3" style="font-size:0.65rem;"><?php echo htmlspecialchars($case['case_category']); ?></span>
                    </td>
                    <td>
                        <small class="text-muted"><?php echo date('M d, Y', strtotime($case['filed_date'])); ?></small>
                    </td>
                    <td>
                        <span class="badge <?php echo $status_conf[0]; ?> rounded-pill px-2 py-1" style="font-size:0.65rem;">
                            <i class="fas <?php echo $status_conf[1]; ?> me-1"></i><?php echo strtoupper($st); ?>
                        </span>
                    </td>
                    <td class="text-end px-3">
                        <div class="d-flex gap-1 justify-content-end flex-wrap">

                            <?php /* ── Quick status buttons (only show statuses different from current) ── */ ?>
                            <?php if ($st !== 'In Progress'): ?>
                            <form method="POST" class="d-inline" onsubmit="return confirmStatus('In Progress', '<?php echo htmlspecialchars($case['case_number']); ?>')">
                                <input type="hidden" name="case_id" value="<?php echo $case['id']; ?>">
                                <input type="hidden" name="new_status" value="In Progress">
                                <button type="submit" name="quick_status" class="btn btn-sm btn-outline-warning rounded-pill px-2 fw-bold quick-btn" title="Mark In Progress">
                                    <i class="fas fa-spinner me-1"></i><span class="d-none d-xl-inline">Progress</span>
                                </button>
                            </form>
                            <?php endif; ?>

                            <?php if ($st !== 'Resolved'): ?>
                            <form method="POST" class="d-inline" onsubmit="return confirmStatus('Resolved', '<?php echo htmlspecialchars($case['case_number']); ?>')">
                                <input type="hidden" name="case_id" value="<?php echo $case['id']; ?>">
                                <input type="hidden" name="new_status" value="Resolved">
                                <button type="submit" name="quick_status" class="btn btn-sm btn-outline-success rounded-pill px-2 fw-bold quick-btn" title="Mark Resolved">
                                    <i class="fas fa-check-circle me-1"></i><span class="d-none d-xl-inline">Resolve</span>
                                </button>
                            </form>
                            <?php endif; ?>

                            <?php if ($st !== 'Dismissed'): ?>
                            <form method="POST" class="d-inline" onsubmit="return confirmStatus('Dismissed', '<?php echo htmlspecialchars($case['case_number']); ?>')">
                                <input type="hidden" name="case_id" value="<?php echo $case['id']; ?>">
                                <input type="hidden" name="new_status" value="Dismissed">
                                <button type="submit" name="quick_status" class="btn btn-sm btn-outline-secondary rounded-pill px-2 fw-bold quick-btn" title="Dismiss Case">
                                    <i class="fas fa-ban me-1"></i><span class="d-none d-xl-inline">Dismiss</span>
                                </button>
                            </form>
                            <?php endif; ?>

                            <?php if ($st !== 'Appealed'): ?>
                            <form method="POST" class="d-inline" onsubmit="return confirmStatus('Appealed', '<?php echo htmlspecialchars($case['case_number']); ?>')">
                                <input type="hidden" name="case_id" value="<?php echo $case['id']; ?>">
                                <input type="hidden" name="new_status" value="Appealed">
                                <button type="submit" name="quick_status" class="btn btn-sm btn-outline-info rounded-pill px-2 fw-bold quick-btn" title="Mark Appealed">
                                    <i class="fas fa-arrow-up me-1"></i><span class="d-none d-xl-inline">Appeal</span>
                                </button>
                            </form>
                            <?php endif; ?>

                            <button class="btn btn-sm btn-dark rounded-pill px-2 fw-bold"
                                data-bs-toggle="modal" data-bs-target="#caseModal<?php echo $case['id']; ?>" title="Full Update with Verdict">
                                <i class="fas fa-pen me-1"></i>Verdict
                            </button>
                            <a href="court_print.php?id=<?php echo $case['id']; ?>" target="_blank" class="btn btn-sm btn-light rounded-circle shadow-sm" title="Print Case Record">
                                <i class="fas fa-print"></i>
                            </a>
                        </div>

                        <!-- Update Modal -->
                        <div class="modal fade" id="caseModal<?php echo $case['id']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg text-start">
                                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                                    <div class="modal-header bg-dark text-white border-0">
                                        <h5 class="modal-title fw-bold">
                                            <i class="fas fa-gavel me-2 text-warning"></i>
                                            Case File: <?php echo htmlspecialchars($case['case_number']); ?>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST">
                                        <input type="hidden" name="case_id" value="<?php echo $case['id']; ?>">
                                        <div class="modal-body p-4">
                                            <!-- Case Details Summary -->
                                            <?php
                                                $desc_raw = $case['description'];
                                                $desc_parts = explode("\n\n[CASE DETAILS]\n", $desc_raw);
                                                $main_desc = $desc_parts[0];
                                                $extra_data = [];
                                                if (isset($desc_parts[1])) {
                                                    $extra_data = json_decode($desc_parts[1], true) ?: [];
                                                }
                                            ?>
                                            <div class="row g-3 mb-4 p-4 bg-light rounded-4 shadow-sm border">
                                                <div class="col-md-6 border-end">
                                                    <div class="text-muted small fw-bold text-uppercase mb-1">Plaintiff (Accuser)</div>
                                                    <div class="fw-bold text-primary fs-5"><?php echo htmlspecialchars($case['plaintiff_name']); ?></div>
                                                    <small class="text-muted d-block"><?php echo $case['p_fname'] ? 'Kebele Resident' : 'External Party'; ?></small>
                                                    <?php if(!empty($extra_data['plaintiff_phone'])): ?><small class="text-dark d-block mt-1"><i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($extra_data['plaintiff_phone']); ?></small><?php endif; ?>
                                                </div>
                                                <div class="col-md-6 ps-md-4">
                                                    <div class="text-muted small fw-bold text-uppercase mb-1">Defendant (Accused)</div>
                                                    <div class="fw-bold text-danger fs-5"><?php echo htmlspecialchars($case['defendant_name']); ?></div>
                                                    <small class="text-muted d-block"><?php echo $case['d_fname'] ? 'Kebele Resident' : 'External Party'; ?></small>
                                                    <?php if(!empty($extra_data['defendant_phone'])): ?><small class="text-dark d-block mt-1"><i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($extra_data['defendant_phone']); ?></small><?php endif; ?>
                                                </div>
                                                
                                                <hr class="my-3 opacity-10">
                                                
                                                <div class="col-12">
                                                    <div class="text-muted small fw-bold text-uppercase mb-2">Judicial Context & Claim</div>
                                                    <div class="p-3 bg-white border rounded-3 small shadow-inner" style="line-height:1.6;">
                                                        <?php echo nl2br(htmlspecialchars($main_desc)); ?>
                                                    </div>
                                                </div>
                                                
                                                <?php if(!empty($extra_data['relief_sought'])): ?>
                                                <div class="col-12 mt-2">
                                                    <div class="p-2 border border-warning rounded-3 bg-warning bg-opacity-10 small">
                                                        <span class="fw-bold text-dark text-uppercase"><i class="fas fa-gavel me-1 text-warning"></i>Relief Sought:</span> 
                                                        <?php echo htmlspecialchars($extra_data['relief_sought']); ?>
                                                    </div>
                                                </div>
                                                <?php endif; ?>

                                                <div class="col-md-6">
                                                    <div class="text-muted small fw-bold text-uppercase mb-1">Category & Urgency</div>
                                                    <span class="badge <?php echo $cat_cls; ?> px-2"><?php echo htmlspecialchars($case['case_category']); ?></span>
                                                    <?php if(!empty($extra_data['urgency_level'])): ?>
                                                        <span class="badge bg-dark px-2"><?php echo htmlspecialchars($extra_data['urgency_level']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-6 ps-md-4 border-start">
                                                    <div class="text-muted small fw-bold text-uppercase mb-1">Assigned Presiding Judge</div>
                                                    <div class="badge bg-secondary px-2"><i class="fas fa-user-tie me-1"></i><?php echo htmlspecialchars($case['presiding_judge'] ?: 'Not Assigned'); ?></div>
                                                </div>

                                                <?php if(!empty($extra_data)): ?>
                                                <div class="col-12 mt-3">
                                                    <div class="accordion accordion-flush border rounded-3 overflow-hidden" id="accordionExtra<?php echo $case['id']; ?>">
                                                        <div class="accordion-item bg-transparent">
                                                            <h2 class="accordion-header">
                                                                <button class="accordion-button collapsed fw-bold py-2 bg-light text-dark small" type="button" data-bs-toggle="collapse" data-bs-target="#flush-extra<?php echo $case['id']; ?>">
                                                                    <i class="fas fa-folder-open me-2 text-primary"></i> View Full Case Details & Evidence
                                                                </button>
                                                            </h2>
                                                            <div id="flush-extra<?php echo $case['id']; ?>" class="accordion-collapse collapse" data-bs-parent="#accordionExtra<?php echo $case['id']; ?>">
                                                                <div class="accordion-body bg-white p-3 small">
                                                                    <div class="row g-3">
                                                                        <div class="col-6"><strong>Incident Date:</strong> <span class="text-muted"><?php echo htmlspecialchars($extra_data['incident_date'] ?? 'N/A'); ?></span></div>
                                                                        <div class="col-6"><strong>Incident Time:</strong> <span class="text-muted"><?php echo htmlspecialchars($extra_data['incident_time'] ?? 'N/A'); ?></span></div>
                                                                        <div class="col-12"><strong>Location:</strong> <span class="text-muted"><?php echo htmlspecialchars($extra_data['incident_location'] ?? 'N/A'); ?></span></div>
                                                                        <div class="col-12 mt-1"><strong>Dispute Amount (ETB):</strong> <span class="text-danger fw-bold"><?php echo htmlspecialchars($extra_data['dispute_amount'] ?? 'N/A'); ?></span></div>
                                                                        
                                                                        <div class="col-12 border-top pt-2 mt-3 text-muted"><strong>Witnesses:</strong><br>
                                                                            <?php echo nl2br(htmlspecialchars($extra_data['witnesses'] ?? 'None registered.')); ?>
                                                                        </div>
                                                                        <div class="col-12 border-top pt-2 mt-2 text-muted"><strong>Evidence List:</strong><br>
                                                                            <?php echo nl2br(htmlspecialchars($extra_data['evidence_list'] ?? 'None registered.')); ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Update Section -->
                                            <div class="p-3 border rounded-4 bg-white">
                                                <h6 class="fw-bold text-dark mb-3">Update Judicial Status & Verdict</h6>
                                                <div class="row g-3">
                                                    <div class="col-md-5">
                                                        <label class="form-label fw-bold small text-muted text-uppercase">Case Status</label>
                                                        <select name="new_status" class="form-select rounded-pill px-4 bg-light border-0 py-2 shadow-sm" required>
                                                            <?php foreach (['Open','In Progress','Resolved','Dismissed','Appealed'] as $status_opt): ?>
                                                                <option value="<?php echo $status_opt; ?>" <?php echo $case['status']===$status_opt?'selected':''; ?>><?php echo $status_opt; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-7">
                                                        <label class="form-label fw-bold small text-muted text-uppercase">Official Verdict / Judicial ruling</label>
                                                        <textarea name="verdict" class="form-control rounded-3 bg-light border-0 shadow-sm" rows="3" placeholder="Enter the court's final ruling or current progress notes..."><?php echo htmlspecialchars($case['verdict'] ?? ''); ?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light border-0 p-3">
                                            <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" name="update_case" class="btn btn-warning rounded-pill px-4 fw-black shadow-sm">
                                                <i class="fas fa-save me-1"></i>SAVE JUDICIAL RECORD
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($cases)): ?>
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <div class="display-4 opacity-10 mb-3"><i class="fas fa-gavel"></i></div>
                        <p class="fw-bold mb-0">No judicial records found match your criteria.</p>
                        <p class="small">Try adjusting your filters or search keywords.</p>
                        <a href="court_create.php" class="btn btn-warning rounded-pill px-4 fw-bold mt-2 shadow-sm">File First Case &rarr;</a>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.bg-pink { background-color: #f472b6; }
.shadow-inner { box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.05); }
.focus-border-warning:focus { border-color: #ffc107 !important; box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.25); }

/* Quick status buttons */
.quick-btn {
    font-size: 0.7rem;
    padding: 3px 8px;
    transition: all 0.18s ease;
    white-space: nowrap;
}
.quick-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.12);
}

/* Status badge pulse for open cases */
@keyframes pulse-open {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}
.badge-open-pulse {
    animation: pulse-open 2s infinite;
}
</style>

<script>
function confirmStatus(newStatus, caseNum) {
    const icons = {
        'In Progress': '⏳',
        'Resolved':    '✅',
        'Dismissed':   '🚫',
        'Appealed':    '⬆️'
    };
    const colors = {
        'In Progress': '#ffc107',
        'Resolved':    '#198754',
        'Dismissed':   '#6c757d',
        'Appealed':    '#0dcaf0'
    };
    return confirm(`${icons[newStatus] || '📋'} Change status of case ${caseNum} to "${newStatus}"?\n\nClick OK to confirm.`);
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
