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
    <h2><i class="fas fa-home me-2 text-primary"></i>House Details — H-<?php echo $hnum; ?></h2>
    <div class="d-flex gap-2">
        <?php if ($_SESSION['role'] !== 'security'): ?>
            <a href="edit.php?hnum=<?php echo $hnum; ?>" class="btn btn-info text-white"><i class="fas fa-edit me-1"></i>Edit</a>
        <?php endif; ?>
        <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row g-4">

    <!-- ══ PROPERTY DETAILS ══ -->
    <div class="col-md-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0"><i class="fas fa-building me-2"></i>Property Characteristics</h5>
            </div>
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="display-5 fw-bold text-primary mb-1">H-<?php echo $h['hnum']; ?></div>
                    <div class="text-muted small fw-bold text-uppercase">Official House Number</div>
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 bg-light rounded text-center">
                            <div class="text-muted small mb-1">Total Area</div>
                            <div class="fw-bold fs-5"><?php echo $h['area']; ?> m²</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded text-center">
                            <div class="text-muted small mb-1">Rooms</div>
                            <div class="fw-bold fs-5"><?php echo $h['rooms_count'] ?: 1; ?></div>
                        </div>
                    </div>
                </div>

                <hr class="my-4 opacity-10">

                <table class="table table-sm table-borderless align-middle mb-0">
                    <tr>
                        <td class="ps-0 py-2"><i class="fas fa-home text-muted me-2"></i> House Type</td>
                        <td class="text-end fw-bold py-2"><?php echo htmlspecialchars($h['house_type'] ?: 'Residential'); ?></td>
                    </tr>
                    <tr>
                        <td class="ps-0 py-2"><i class="fas fa-hammer text-muted me-2"></i> Construction</td>
                        <td class="text-end fw-bold py-2"><?php echo htmlspecialchars($h['construction_type'] ?: 'Unknown'); ?></td>
                    </tr>
                    <tr>
                        <td class="ps-0 py-2"><i class="fas fa-border-all text-muted me-2"></i> Block No.</td>
                        <td class="text-end fw-bold py-2"><?php echo htmlspecialchars($h['block_no'] ?: 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td class="ps-0 py-2"><i class="fas fa-calendar-alt text-muted me-2"></i> Year Built</td>
                        <td class="text-end fw-bold py-2"><?php echo $h['constructed_year'] ?: 'Unknown'; ?></td>
                    </tr>
                    <tr>
                        <td class="ps-0 py-2"><i class="fas fa-door-open text-muted me-2"></i> Total Doors</td>
                        <td class="text-end fw-bold py-2"><?php echo $h['door']; ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-info text-white py-3">
                <h5 class="mb-0"><i class="fas fa-plug me-2"></i>Utilities & Features</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="bg-light p-2 rounded">
                                <i class="fas fa-faucet <?php echo $h['has_water'] === 'Yes' ? 'text-primary' : 'text-danger'; ?> fa-lg"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Water</small>
                                <span class="fw-bold small"><?php echo $h['has_water'] === 'Yes' ? 'Connected' : 'None'; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="bg-light p-2 rounded">
                                <i class="fas fa-bolt <?php echo $h['has_electricity'] === 'Yes' ? 'text-warning' : 'text-danger'; ?> fa-lg"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Electric</small>
                                <span class="fw-bold small"><?php echo $h['has_electricity'] === 'Yes' ? 'Connected' : 'None'; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-light p-2 rounded">
                                <i class="fas fa-restroom text-info fa-lg"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Sanitation</small>
                                <span class="fw-bold small"><?php echo htmlspecialchars($h['toilet_type'] ?: 'None'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-light p-2 rounded">
                                <i class="fas fa-layer-group text-secondary fa-lg"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Floor/Roof</small>
                                <span class="fw-bold small"><?php echo htmlspecialchars($h['floor_type'] ?: 'Earth'); ?> / <?php echo htmlspecialchars($h['roof_type'] ?: 'CIS'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($family): ?>
                <div class="mt-4 pt-4 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="text-primary fw-bold mb-0">Linked Family Profile</h6>
                        <span class="badge bg-dark"><?php echo $family['fam_no'] ?? '?'; ?> Members</span>
                    </div>
                    <a href="../families/view.php?hnum=<?php echo $hnum; ?>" class="btn btn-sm btn-outline-primary w-100 mt-3">View Family Residents</a>
                </div>
                <?php else: ?>
                <div class="mt-4 pt-4 border-top text-center">
                    <p class="text-muted small">No family profile registered for this house.</p>
                    <a href="../families/create.php?hnum=<?php echo $hnum; ?>" class="btn btn-sm btn-primary w-100">Create Family Record</a>
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
                    <div class="text-white-50 small fw-bold text-uppercase mb-1" style="letter-spacing:1px;">Registered House Owner</div>
                    <h4 class="text-white fw-bold mb-1"><?php echo htmlspecialchars("{$h['fname']} {$h['mname']} {$h['lname']}"); ?></h4>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge bg-white text-dark"><?php echo htmlspecialchars($h['occ'] ?? 'N/A'); ?></span>
                        <span class="badge bg-primary"><?php echo $h['age'] ?? '?'; ?> years old</span>
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
                        <h6 class="fw-bold text-muted text-uppercase mb-3" style="font-size:0.7rem; letter-spacing:1px;">Personal Details</h6>
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
                        <h6 class="fw-bold text-muted text-uppercase mb-3" style="font-size:0.7rem; letter-spacing:1px;">Contact & Address</h6>
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
                        <i class="fas fa-user me-2"></i>Full Resident Profile
                    </a>
                    <?php if (!$h['id_num']): ?>
                        <a href="../idcards/generate.php?resident_id=<?php echo $h['ind_id']; ?>" class="btn btn-outline-success">
                            <i class="fas fa-id-card me-2"></i>Issue ID Card
                        </a>
                    <?php endif; ?>
                    <a href="../vital/issue_clearance.php?resident_id=<?php echo $h['ind_id']; ?>" class="btn btn-outline-info">
                        <i class="fas fa-file-check me-2"></i>Issue Certificate
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
            <h4 class="text-muted mb-2">No Resident Linked as Owner</h4>
            <p class="text-muted mb-1">Legacy owner reference: <code><?php echo htmlspecialchars($h['owner_id'] ?? 'N/A'); ?></code></p>
            <p class="text-muted mb-4">Link this house to a registered resident to access their full personal information and services.</p>
            <a href="edit.php?hnum=<?php echo $hnum; ?>" class="btn btn-primary px-5">
                <i class="fas fa-link me-2"></i>Link to a Registered Resident
            </a>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
