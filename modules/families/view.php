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
    <h2>Family Details: House #<?php echo $hnum; ?></h2>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
</div>

<div class="row g-4">
    <!-- Family Summary -->
    <div class="col-md-4">
        <div class="card p-4 h-100 shadow-sm text-center">
            <h5 class="text-primary mb-3">Family Overview</h5>
            <div class="mb-3">
                <span class="display-4 fw-bold text-dark"><?php echo $f['fam_no']; ?></span>
                <p class="text-muted">Total Members</p>
            </div>
            <div class="p-3 bg-light rounded text-start">
                <p class="mb-1"><strong>House Area:</strong> <?php echo $f['area']; ?> m²</p>
                <p class="mb-0"><strong>Location:</strong> Ifa Bula Kebele </p>
            </div>
        </div>
    </div>

    <!-- Leader Details -->
    <div class="col-md-8">
        <div class="card p-4 h-100 shadow-sm">
            <h5 class="text-success mb-4"><i class="fas fa-user-tie me-2"></i>Family Leader Information</h5>
            <div class="row">
                <div class="col-md-3 text-center">
                    <img src="../../assets/images/<?php echo $f['phot']; ?>" class="img-fluid rounded shadow-sm" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($f['fname']); ?>&size=150'">
                </div>
                <div class="col-md-9">
                    <table class="table table-sm table-borderless mt-2">
                        <tr><td class="fw-bold" style="width: 150px;">Full Name:</td><td><?php echo "{$f['fname']} {$f['mname']} {$f['lname']}"; ?></td></tr>
                        <tr><td class="fw-bold">Birth Date:</td><td><?php echo $f['age']; ?> Years Old</td></tr>
                        <tr><td class="fw-bold">Occupation:</td><td><?php echo $f['occ']; ?></td></tr>
                        <tr><td class="fw-bold">Phone:</td><td><?php echo $f['pho_no'] ?? 'N/A'; ?></td></tr>
                        <tr><td class="fw-bold">Marital Status:</td><td><?php echo $f['mar']; ?></td></tr>
                    </table>
                    <div class="mt-3">
                        <a href="../residents/edit.php?id=<?php echo $f['lead_id']; ?>" class="btn btn-sm btn-outline-primary">Edit Leader Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-md-12">
        <div class="card p-4 shadow-sm">
            <h5 class="text-info mb-4"><i class="fas fa-users me-2"></i>Related Family Members</h5>
            
            <?php if ($spouse): ?>
                <h6 class="text-secondary border-bottom pb-2 mb-3">Spouse</h6>
                <div class="d-flex align-items-center mb-4 p-3 bg-light rounded">
                    <img src="../../assets/images/<?php echo $spouse['phot']; ?>" class="rounded-circle shadow-sm me-3" style="width: 60px; height: 60px; object-fit: cover;" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($spouse['fname']); ?>'">
                    <div>
                        <h6 class="mb-1"><a href="../residents/view.php?id=<?php echo $spouse['id']; ?>" class="text-decoration-none text-dark"><?php echo "{$spouse['fname']} {$spouse['mname']} {$spouse['lname']}"; ?></a></h6>
                        <small class="text-muted"><?php echo $spouse['age']; ?> Years Old &middot; <?php echo $spouse['occ']; ?></small>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($children)): ?>
                <h6 class="text-secondary border-bottom pb-2 mb-3">Children / Dependents (<?php echo count($children); ?>)</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Sex</th>
                                <th>Age</th>
                                <th>Occupation</th>
                                <th>Profile</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($children as $c): ?>
                                <tr>
                                    <td><a href="../residents/view.php?id=<?php echo $c['id']; ?>" class="fw-bold text-decoration-none"><?php echo "{$c['fname']} {$c['mname']} {$c['lname']}"; ?></a></td>
                                    <td><?php echo $c['sex']; ?></td>
                                    <td><?php echo $c['age'] ?? 'N/A'; ?> yrs</td>
                                    <td><?php echo $c['occ']; ?></td>
                                    <td><a href="../residents/view.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-info">View</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif (!$spouse): ?>
                <div class="alert alert-secondary mb-0">No related family members (Spouse/Children) found in the registration system matching this leader.</div>
            <?php endif; ?>
            
            <div class="mt-3 form-text text-muted">
                Note: Children are matched automatically based on the Father's / Mother's name in the resident registry mapping. Total registered family size: <?php echo $f['fam_no']; ?>.
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
