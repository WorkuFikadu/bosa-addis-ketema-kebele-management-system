<?php
// modules/houses/view.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

$hnum = $_GET['hnum'] ?? null;

// House + linked owner personal info
$stmt = $pdo->prepare("
    SELECT h.*,
           i.id as ind_id, i.fname, i.lname, i.mname, i.mar, i.s, i.nat, i.level_edu, i.relg, i.occ, i.phot, i.status as res_status,
           i.mother_full_name, i.father_full_name,
           ag.age, ag.bdate,
           a.region, a.zone, a.city, a.kebele, a.pho_no, a.email,
           ic.id_num, ic.issue_date as id_issue, ic.expiry_date
    FROM houses h
    LEFT JOIN individuals i ON h.owner_individual_id = i.id
    LEFT JOIN ages ag ON i.id = ag.id
    LEFT JOIN addresses a ON i.id = a.id
    LEFT JOIN id_cards ic ON i.id = ic.resident_id
    WHERE h.hnum = ?
");
$stmt->execute([$hnum]);
$h = $stmt->fetch();

if (!$h) {
    header('Location: index.php');
    exit;
}

// Fetch family linked to this house
$stmt_fam = $pdo->prepare("SELECT * FROM families WHERE hnum = ?");
$stmt_fam->execute([$hnum]);
$family = $stmt_fam->fetch();

// Count certificates issued to the owner
$cert_count = 0;
if ($h['ind_id']) {
    $cert_count = $pdo->prepare("SELECT COUNT(*) FROM vital_certificates WHERE resident_id = ?");
    $cert_count->execute([$h['ind_id']]);
    $cert_count = $cert_count->fetchColumn();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-home me-2 text-primary"></i><?php echo __('house_details'); ?> — H-<?php echo $hnum; ?></h2>
    <div class="d-flex gap-2">
        <?php if ($_SESSION['role'] !== 'security'): ?>
            <a href="edit.php?hnum=<?php echo $hnum; ?>" class="btn btn-info text-white"><i class="fas fa-edit me-1"></i><?php echo __('edit'); ?></a>
        <?php endif; ?>
        <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i><?php echo __('back'); ?></a>
    </div>
</div>

<div class="row g-4">

    <!-- ══ PROPERTY DETAILS ══ -->
    <div class="col-md-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0"><i class="fas fa-building me-2"></i><?php echo __('property_characteristics'); ?></h5>
            </div>
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="display-5 fw-bold text-primary mb-1">H-<?php echo $h['hnum']; ?></div>
                    <div class="text-muted small fw-bold text-uppercase"><?php echo __('official_house_number'); ?></div>
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 bg-light rounded text-center">
                            <div class="text-muted small mb-1"><?php echo __('total_area'); ?></div>
                            <div class="fw-bold fs-5"><?php echo $h['area']; ?> m²</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded text-center">
                            <div class="text-muted small mb-1"><?php echo __('rooms'); ?></div>
                            <div class="fw-bold fs-5"><?php echo $h['rooms_count'] ?: 1; ?></div>
                        </div>
                    </div>
                </div>

                <hr class="my-4 opacity-10">

                <table class="table table-sm table-borderless align-middle mb-0">
                    <tr>
                        <td class="ps-0 py-2"><i class="fas fa-home text-muted me-2"></i> <?php echo __('type'); ?></td>
                        <td class="text-end fw-bold py-2"><?php echo htmlspecialchars($h['house_type'] ?: 'Residential'); ?></td>
                    </tr>
                    <tr>
                        <td class="ps-0 py-2"><i class="fas fa-hammer text-muted me-2"></i> <?php echo __('construction_type'); ?></td>
                        <td class="text-end fw-bold py-2"><?php echo htmlspecialchars($h['construction_type'] ?: 'Unknown'); ?></td>
                    </tr>
                    <tr>
                        <td class="ps-0 py-2"><i class="fas fa-border-all text-muted me-2"></i> <?php echo __('block_number'); ?></td>
                        <td class="text-end fw-bold py-2"><?php echo htmlspecialchars($h['block_no'] ?: 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td class="ps-0 py-2"><i class="fas fa-calendar-alt text-muted me-2"></i> <?php echo __('year_constructed'); ?></td>
                        <td class="text-end fw-bold py-2"><?php echo $h['constructed_year'] ?: 'Unknown'; ?></td>
                    </tr>
                    <tr>
                        <td class="ps-0 py-2"><i class="fas fa-door-open text-muted me-2"></i> <?php echo __('door_count'); ?></td>
                        <td class="text-end fw-bold py-2"><?php echo $h['door']; ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-warning text-dark py-3">
                <h5 class="mb-0"><i class="fas fa-file-contract me-2"></i><?php echo __('documentation'); ?></h5>
            </div>
            <div class="card-body p-4">
                <?php if ($h['plan_certificate']): ?>
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white p-2 rounded shadow-sm">
                                <i class="fas fa-file-contract text-warning fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark"><?php echo __('plan_certificate_label'); ?></h6>
                                <small class="text-muted">House/Land Plan</small>
                            </div>
                        </div>
                        <a href="../../uploads/houses/<?php echo $h['plan_certificate']; ?>" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                            <i class="fas fa-download me-1"></i><?php echo __('view_file'); ?>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3 border border-dashed rounded bg-light opacity-75">
                        <i class="fas fa-file-contract text-muted mb-2 fs-3"></i>
                        <p class="text-muted small mb-0 italic"><?php echo __('no_plan_certificate_msg'); ?></p>
                        <a href="edit.php?hnum=<?php echo $hnum; ?>" class="small text-decoration-none mt-2 d-inline-block"><?php echo __('add_documented_plan'); ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ══ OWNER PERSONAL INFO ══ -->
    <?php if ($h['ind_id']): ?>
    <div class="col-md-7">
        <div class="card border-0 shadow-sm overflow-hidden">
            <!-- Owner Header Banner -->
            <div class="p-4 d-flex align-items-center gap-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);">
                <img src="../../assets/images/<?php echo htmlspecialchars($h['phot'] ?? 'default_profile.png'); ?>"
                     class="rounded-circle border border-3 border-white"
                     style="width:90px;height:90px;object-fit:cover;flex-shrink:0;"
                     onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode("{$h['fname']} {$h['lname']}"); ?>&size=200&background=6366f1&color=fff'">
                <div>
                    <div class="text-white-50 small fw-bold text-uppercase mb-1" style="letter-spacing:1px;"><?php echo __('registered_house_owner'); ?></div>
                    <h4 class="text-white fw-bold mb-1"><?php echo htmlspecialchars("{$h['fname']} {$h['mname']} {$h['lname']}"); ?></h4>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge bg-white text-dark"><?php echo htmlspecialchars($h['occ'] ?? 'N/A'); ?></span>
                        <span class="badge bg-primary"><?php echo $h['age'] ?? '?'; ?> <?php echo __('years_old'); ?></span>
                        <span class="badge bg-<?php echo $h['res_status'] === 'alive' ? 'success' : 'danger'; ?>"><?php echo ucfirst($h['res_status'] ?? 'N/A'); ?></span>
                    </div>
                </div>
                <div class="ms-auto text-end">
                    <div class="text-white-50 small">Resident ID</div>
                    <div class="text-white fw-bold fs-5">#<?php echo $h['ind_id']; ?></div>
                </div>
            </div>

            <div class="p-4">
                <div class="row g-4">
                    <!-- Personal Details -->
                    <div class="col-md-6">
                        <h6 class="fw-bold text-muted text-uppercase mb-3" style="font-size:0.7rem; letter-spacing:1px;"><?php echo __('personal_info'); ?></h6>
                        <table class="table table-sm table-borderless">
                            <tr><td class="text-muted" style="width:45%">Birth Date</td><td class="fw-bold"><?php echo $h['bdate'] ? date('M d, Y', strtotime($h['bdate'])) : 'N/A'; ?></td></tr>
                            <tr><td class="text-muted">Sex</td><td class="fw-bold"><?php echo $h['s'] === 'Male' ? '♂ Male' : '♀ Female'; ?></td></tr>
                            <tr><td class="text-muted">Marital Status</td><td class="fw-bold"><?php echo htmlspecialchars($h['mar'] ?? 'N/A'); ?></td></tr>
                            <tr><td class="text-muted">Nationality</td><td class="fw-bold"><?php echo htmlspecialchars($h['nat'] ?? 'N/A'); ?></td></tr>
                            <tr><td class="text-muted">Religion</td><td class="fw-bold"><?php echo htmlspecialchars($h['relg'] ?? 'N/A'); ?></td></tr>
                            <tr><td class="text-muted">Education</td><td class="fw-bold"><?php echo htmlspecialchars($h['level_edu'] ?? 'N/A'); ?></td></tr>
                        </table>
                    </div>

                    <!-- Contact & Address -->
                    <div class="col-md-6">
                        <h6 class="fw-bold text-muted text-uppercase mb-3" style="font-size:0.7rem; letter-spacing:1px;"><?php echo __('contact_address'); ?></h6>
                        <table class="table table-sm table-borderless">
                            <tr><td class="text-muted" style="width:45%">Phone</td><td class="fw-bold"><?php echo htmlspecialchars($h['pho_no'] ?? 'N/A'); ?></td></tr>
                            <tr><td class="text-muted">Email</td><td class="fw-bold"><?php echo htmlspecialchars($h['email'] ?? 'N/A'); ?></td></tr>
                            <tr><td class="text-muted">Kebele</td><td class="fw-bold"><?php echo htmlspecialchars($h['kebele'] ?? 'N/A'); ?></td></tr>
                            <tr><td class="text-muted">City</td><td class="fw-bold"><?php echo htmlspecialchars($h['city'] ?? 'N/A'); ?></td></tr>
                            <tr><td class="text-muted">Zone</td><td class="fw-bold"><?php echo htmlspecialchars($h['zone'] ?? 'N/A'); ?></td></tr>
                            <tr><td class="text-muted">Region</td><td class="fw-bold"><?php echo htmlspecialchars($h['region'] ?? 'N/A'); ?></td></tr>
                        </table>
                    </div>
                </div>

                <!-- Services Summary -->
                <div class="row g-3 mt-1">
                    <div class="col-12">
                        <h6 class="fw-bold text-muted text-uppercase mb-3" style="font-size:0.7rem; letter-spacing:1px;">Services Issued to Owner</h6>
                    </div>
                    <div class="col-sm-4">
                        <div class="card border-0 bg-light text-center p-3">
                            <i class="fas fa-id-card text-primary fa-2x mb-2"></i>
                            <div class="fw-bold"><?php echo $h['id_num'] ?? 'Not Issued'; ?></div>
                            <small class="text-muted">ID Card</small>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="card border-0 bg-light text-center p-3">
                            <i class="fas fa-file-signature text-success fa-2x mb-2"></i>
                            <div class="fw-bold"><?php echo $cert_count; ?></div>
                            <small class="text-muted">Certificates</small>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="card border-0 bg-light text-center p-3">
                            <i class="fas fa-home text-info fa-2x mb-2"></i>
                            <div class="fw-bold">H-<?php echo $hnum; ?></div>
                            <small class="text-muted">House</small>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2 flex-wrap mt-4 pt-3 border-top">
                    <a href="../residents/view.php?id=<?php echo $h['ind_id']; ?>" class="btn btn-primary">
                        <i class="fas fa-user me-2"></i><?php echo __('full_resident_profile'); ?>
                    </a>
                    <?php if (!$h['id_num']): ?>
                        <a href="../idcards/generate.php?resident_id=<?php echo $h['ind_id']; ?>" class="btn btn-outline-success">
                            <i class="fas fa-id-card me-2"></i><?php echo __('issue_id'); ?>
                        </a>
                    <?php endif; ?>
                    <a href="../vital/issue_clearance.php?resident_id=<?php echo $h['ind_id']; ?>" class="btn btn-outline-info">
                        <i class="fas fa-file-check me-2"></i><?php echo __('issue_marriage_cert'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- No owner linked -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm p-5 text-center">
            <i class="fas fa-user-slash fa-4x text-muted mb-4"></i>
            <h4 class="text-muted mb-2"><?php echo __('no_owner_linked'); ?></h4>
            <p class="text-muted mb-1">Legacy owner reference: <code><?php echo htmlspecialchars($h['owner_id'] ?? 'N/A'); ?></code></p>
            <p class="text-muted mb-4"><?php echo __('link_to_resident_msg'); ?></p>
            <a href="edit.php?hnum=<?php echo $hnum; ?>" class="btn btn-primary px-5">
                <i class="fas fa-link me-2"></i><?php echo __('link_to_resident_btn'); ?>
            </a>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
