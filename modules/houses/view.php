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

    <!-- ══ PROPERTY INFO ══ -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-4 h-100">
            <h5 class="text-primary mb-4 border-bottom pb-3"><i class="fas fa-building me-2"></i>Property Info</h5>
            <table class="table table-sm table-borderless">
                <tr><td class="text-muted fw-bold">House No.</td><td><span class="badge bg-dark fs-6">H-<?php echo $h['hnum']; ?></span></td></tr>
                <tr><td class="text-muted fw-bold">Total Area</td><td><?php echo $h['area']; ?> m²</td></tr>
                <tr><td class="text-muted fw-bold">Doors</td><td><?php echo $h['door']; ?></td></tr>
                <tr><td class="text-muted fw-bold">Location</td><td>IFA BULA KEBELE, Jimma City</td></tr>
                <?php if ($h['id_num']): ?>
                <tr><td class="text-muted fw-bold">Owner ID Card</td><td><span class="badge bg-success"><?php echo $h['id_num']; ?></span></td></tr>
                <?php endif; ?>
            </table>

            <?php if ($family): ?>
            <div class="mt-3 pt-3 border-top">
                <h6 class="text-info fw-bold"><i class="fas fa-people-roof me-2"></i>Linked Family</h6>
                <p class="mb-1 small">Family Leader: <strong><?php echo htmlspecialchars($family['lead_id']); ?></strong></p>
                <p class="mb-2 small">Members: <span class="badge bg-dark"><?php echo $family['fam_no'] ?? '?'; ?> people</span></p>
                <a href="../families/view.php?hnum=<?php echo $hnum; ?>" class="btn btn-sm btn-outline-info w-100">View Family Profile</a>
            </div>
            <?php else: ?>
            <div class="mt-3 pt-3 border-top text-center">
                <i class="fas fa-users fa-2x text-muted mb-2"></i>
                <p class="text-muted small">No family profile linked.</p>
                <a href="../families/create.php?hnum=<?php echo $hnum; ?>" class="btn btn-sm btn-outline-success w-100">Create Family Profile</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══ OWNER PERSONAL INFO ══ -->
    <?php if ($h['ind_id']): ?>
    <div class="col-md-8">
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
