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
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Resident Details: <?php echo "{$r['fname']} {$r['lname']}"; ?></h2>
    <div>
        <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
        <?php if ($_SESSION['role'] !== 'security'): ?>
            <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-info text-white ms-2"><i class="fas fa-edit me-2"></i>Edit Profile</a>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm text-center p-4">
            <img src="../../assets/images/<?php echo $r['phot']; ?>" class="img-thumbnail rounded-circle mb-3 mx-auto" style="width: 200px; height: 200px; object-fit: cover;" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($r['fname']); ?>&size=200'">
            <h4 class="mb-1"><?php echo "{$r['fname']} {$r['mname']} {$r['lname']}"; ?></h4>
            <p class="text-muted"><?php echo $r['occ']; ?></p>
            <div class="mt-3">
                <span class="badge bg-primary px-3 py-2 text-uppercase"><?php echo $r['s']; ?></span>
                <span class="badge bg-secondary px-3 py-2"><?php echo $r['age']; ?> Years Old</span>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card shadow-sm p-4">
            <h5 class="border-bottom pb-2 mb-3 text-primary"><i class="fas fa-id-card-clip me-2"></i>Basic Information</h5>
            <div class="row mb-3">
                <div class="col-sm-4 text-muted fw-bold">Resident ID:</div>
                <div class="col-sm-8">#<?php echo $r['id']; ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4 text-muted fw-bold">Birth Date:</div>
                <div class="col-sm-8"><?php echo date('M d, Y', strtotime($r['bdate'])); ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4 text-muted fw-bold">Marital Status:</div>
                <div class="col-sm-8"><?php echo $r['mar']; ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4 text-muted fw-bold">Nationality:</div>
                <div class="col-sm-8"><?php echo $r['nat']; ?></div>
            </div>
            <div class="row mb-4">
                <div class="col-sm-4 text-muted fw-bold">Education:</div>
                <div class="col-sm-8"><?php echo $r['level_edu']; ?></div>
            </div>

            <h5 class="border-bottom pb-2 mb-3 text-primary"><i class="fas fa-map-location-dot me-2"></i>Contact Information</h5>
            <div class="row mb-3">
                <div class="col-sm-4 text-muted fw-bold">Phone Number:</div>
                <div class="col-sm-8"><?php echo $r['pho_no'] ?? 'N/A'; ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4 text-muted fw-bold">Email Address:</div>
                <div class="col-sm-8"><?php echo $r['email'] ?? 'N/A'; ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4 text-muted fw-bold">Address:</div>
                <div class="col-sm-8">
                    <?php echo "{$r['kebele']}, {$r['city']}, {$r['zone']}, {$r['region']}"; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
