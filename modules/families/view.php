<?php
// modules/families/view.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

$hnum = $_GET['hnum'] ?? null;
$stmt = $pdo->prepare("SELECT f.*, i.*, h.area, ad.pho_no, ag.age
                       FROM families f 
                       JOIN individuals i ON f.lead_id = i.id
                       JOIN houses h ON f.hnum = h.hnum
                       LEFT JOIN addresses ad ON i.id = ad.id
                       LEFT JOIN ages ag ON i.id = ag.id
                       WHERE f.hnum = ?");
$stmt->execute([$hnum]);
$f = $stmt->fetch();

if (!$f) {
    header('Location: index.php');
    exit;
}

// Fetch Spouse if Married
$spouse = null;
if ($f['mar'] === 'Married') {
    $spouse_stmt = $pdo->prepare("
        SELECT i.id, i.fname, i.mname, i.lname, i.phot, a.age, i.occ, i.mar
        FROM marriage_details md
        JOIN individuals i ON (i.id = md.bride_id OR i.id = md.groom_id)
        LEFT JOIN ages a ON i.id = a.id
        WHERE (md.groom_id = ? OR md.bride_id = ?) AND i.id != ?
        ORDER BY md.marriage_date DESC LIMIT 1
    ");
    $spouse_stmt->execute([$f['lead_id'], $f['lead_id'], $f['lead_id']]);
    $spouse = $spouse_stmt->fetch();
}

// Fetch Children
$children_query = "SELECT i.id, i.fname, i.mname, i.lname, i.s as sex, a.age, i.occ FROM individuals i LEFT JOIN ages a ON i.id = a.id WHERE ";
$parent_full_name = "{$f['fname']} {$f['mname']} {$f['lname']}";
if ($f['s'] === 'Male') {
    $children_query .= "(i.mname = '{$f['fname']}' AND i.lname = '{$f['mname']}') OR i.father_full_name = :pname";
} else {
    $children_query .= "i.mother_full_name = :pname";
}
$child_stmt = $pdo->prepare($children_query);
$child_stmt->execute(['pname' => $parent_full_name]);
$children = $child_stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-id-card-clip me-2 text-primary"></i>Family Digital Profile: #<?php echo $hnum; ?></h2>
    <div class="d-flex gap-2">
        <a href="edit.php?hnum=<?php echo $hnum; ?>" class="btn btn-info text-white"><i class="fas fa-edit me-1"></i>Edit Data</a>
        <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row g-4">
    <!-- ══ FAMILY STATUS CARD ══ -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4 h-100 overflow-hidden">
            <div class="card-header bg-dark text-white py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-house-chimney me-2 text-warning"></i>Household Identity</h6>
            </div>
            <div class="card-body p-4 text-center">
                <div class="mb-4">
                    <div class="text-muted small fw-bold text-uppercase mb-1">Family Type</div>
                    <span class="badge bg-primary fs-6 px-4 py-2 rounded-pill"><?php echo $f['family_type'] ?: 'Nuclear'; ?></span>
                </div>
                
                <div class="row g-2 mb-4">
                    <div class="col-12">
                        <div class="p-3 bg-light rounded">
                            <div class="text-muted small mb-1">Total Family Size</div>
                            <div class="display-6 fw-bold"><?php echo $f['fam_no']; ?></div>
                            <div class="small text-muted mt-1">Consists of <?php echo $f['total_males']; ?> ♂ and <?php echo $f['total_females']; ?> ♀</div>
                        </div>
                    </div>
                </div>

                <table class="table table-sm table-borderless text-start align-middle">
                    <tr>
                        <td class="text-muted small"><i class="fas fa-location-dot me-2"></i> House No.</td>
                        <td class="text-end fw-bold">H-<?php echo $f['hnum']; ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted small"><i class="fas fa-calendar-check me-2"></i> Registered</td>
                        <td class="text-end fw-bold"><?php echo $f['registration_date'] ? date('M d, Y', strtotime($f['registration_date'])) : 'N/A'; ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted small"><i class="fas fa-handshake me-2"></i> Social Status</td>
                        <td class="text-end text-primary fw-bold"><?php echo $f['social_status']; ?></td>
                    </tr>
                </table>
                
                <?php if ($f['is_vulnerable'] === 'Yes'): ?>
                    <div class="mt-4 p-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded text-danger">
                        <i class="fas fa-hand-holding-heart me-1"></i> Target for Social Support
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ══ HEAD OF FAMILY & SOCIO-ECONOMIC ══ -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-user-shield me-2"></i>Socio-Economic Profile & Leadership</h6>
            </div>
            <div class="card-body p-4">
                <div class="row align-items-center g-4 mb-4">
                    <div class="col-md-auto text-center">
                        <img src="../../assets/images/<?php echo $f['phot']; ?>" 
                             class="rounded-circle border border-3 border-primary" 
                             style="width: 100px; height: 100px; object-fit: cover;"
                             onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($f['fname']); ?>&size=150&background=0d6efd&color=fff'">
                    </div>
                    <div class="col-md">
                        <div class="text-muted small fw-bold">Family Head / Leader</div>
                        <h4 class="mb-1"><?php echo "{$f['fname']} {$f['mname']} {$f['lname']}"; ?></h4>
                        <div class="d-flex gap-2">
                            <span class="badge bg-light text-dark border"><?php echo $f['occ']; ?></span>
                            <span class="badge bg-light text-dark border"><?php echo $f['age']; ?> yrs</span>
                            <span class="badge bg-light text-dark border"><?php echo $f['pho_no'] ?: 'No Phone'; ?></span>
                        </div>
                    </div>
                    <div class="col-md-auto">
                        <a href="../residents/view.php?id=<?php echo $f['lead_id']; ?>" class="btn btn-sm btn-outline-primary">Full Head Profile</a>
                    </div>
                </div>

                <hr class="opacity-10">

                <div class="row g-4 mt-1">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-muted small text-uppercase mb-3">Economic Indicators</h6>
                        <div class="p-3 border rounded">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Income Category</span>
                                <span class="fw-bold"><?php echo $f['income_category']; ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Pension Coverage</span>
                                <span class="badge <?php echo $f['has_pension'] === 'Yes' ? 'bg-success' : 'bg-secondary'; ?>"><?php echo $f['has_pension']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-muted small text-uppercase mb-3">Vulnerability / Special Cases</h6>
                        <div class="p-3 border rounded">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Disabled Members</span>
                                <span class="badge <?php echo $f['disabled_members'] > 0 ? 'bg-danger' : 'bg-light text-dark'; ?> fs-6"><?php echo $f['disabled_members']; ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Orphans Count</span>
                                <span class="badge <?php echo $f['orphans_count'] > 0 ? 'bg-warning text-dark' : 'bg-light text-dark'; ?> fs-6"><?php echo $f['orphans_count']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-info"><i class="fas fa-users-rectangle me-2"></i>Verified Family Members in Registry</h6>
            </div>
            <div class="card-body p-4">
                <?php if ($spouse): ?>
                    <h6 class="text-secondary small fw-bold text-uppercase mb-3">Spouse</h6>
                    <div class="d-flex align-items-center mb-4 p-2 border rounded bg-light bg-opacity-50">
                        <img src="../../assets/images/<?php echo $spouse['phot']; ?>" 
                             class="rounded-circle me-3" style="width: 45px; height: 45px; object-fit: cover;" 
                             onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($spouse['fname']); ?>'">
                        <div class="flex-grow-1">
                            <h6 class="mb-0"><a href="../residents/view.php?id=<?php echo $spouse['id']; ?>" class="text-decoration-none"><?php echo "{$spouse['fname']} {$spouse['lname']}"; ?></a></h6>
                            <small class="text-muted"><?php echo $spouse['occ']; ?> &middot; <?php echo $spouse['age']; ?> yrs</small>
                        </div>
                        <span class="badge bg-secondary">Spouse</span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($children)): ?>
                    <h6 class="text-secondary small fw-bold text-uppercase mb-3">Children / Dependents (Registry Matches)</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="bg-light">
                                <tr class="small text-muted">
                                    <th>Child Name</th>
                                    <th>Sex</th>
                                    <th>Age</th>
                                    <th>Occupation</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($children as $c): ?>
                                    <tr>
                                        <td><div class="fw-bold small"><?php echo "{$c['fname']} {$c['mname']} {$c['lname']}"; ?></div></td>
                                        <td><small><?php echo $c['sex']; ?></small></td>
                                        <td><small><?php echo $c['age'] ?? 'N/A'; ?> yrs</small></td>
                                        <td><small><?php echo $c['occ']; ?></small></td>
                                        <td class="text-end"><a href="../residents/view.php?id=<?php echo $c['id']; ?>" class="btn btn-xs btn-outline-info">Profile</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif (!$spouse): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-users-slash fa-3x text-muted mb-3 opacity-25"></i>
                        <p class="text-muted small">No related family members (Spouse/Children) found in the registration system matching this house and leader's parentage records.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>


<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
