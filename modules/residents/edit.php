<?php
// modules/residents/edit.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

if ($_SESSION['role'] === 'security') {
    $id = $_GET['id'] ?? '';
    header("Location: view.php?id=$id");
    exit;
}

$id = $_GET['id'] ?? null;
$stmt = $pdo->prepare("SELECT i.*, a.*, ag.bdate FROM individuals i 
                       LEFT JOIN addresses a ON i.id = a.id 
                       LEFT JOIN ages ag ON i.id = ag.id 
                       WHERE i.id = ?");
$stmt->execute([$id]);
$r = $stmt->fetch();

if (!$r) {
    header('Location: index.php');
    exit;
}

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!$pdo->inTransaction()) {
            $pdo->beginTransaction();
        }

        $stmtUpdate = $pdo->prepare("UPDATE individuals SET fname=?, lname=?, mname=?, mar=?, s=?, nat=?, level_edu=?, relg=?, occ=?, mother_full_name=?, father_full_name=?, mother_nat=?, father_nat=? WHERE id=?");
        $stmtUpdate->execute([
            $_POST['fname'], 
            $_POST['lname'], 
            $_POST['mname'], 
            $_POST['mar'] ?? $r['mar'], 
            $_POST['sex'], 
            $_POST['nat'] ?? $r['nat'], 
            $_POST['level_edu'] ?? $r['level_edu'], 
            $_POST['relg'] ?? $r['relg'], 
            $_POST['occ'], 
            $_POST['mother_full_name'],
            $_POST['father_full_name'],
            $_POST['mother_nat'],
            $_POST['father_nat'],
            $id
        ]);

        $bdate = $_POST['bdate'];
        $age = date_diff(date_create($bdate), date_create('today'))->y;
        $pdo->prepare("UPDATE ages SET bdate=?, age=? WHERE id=?")->execute([$bdate, $age, $id]);

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $phot_name = time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], "../../assets/images/" . $phot_name)) {
                $pdo->prepare("UPDATE individuals SET phot=? WHERE id=?")->execute([$phot_name, $id]);
            }
        }

        $pdo->commit();
        $success = "Details updated successfully!";
        
        // Refresh data
        $stmtRefresh = $pdo->prepare("SELECT i.*, a.*, ag.bdate FROM individuals i 
                               LEFT JOIN addresses a ON i.id = a.id 
                               LEFT JOIN ages ag ON i.id = ag.id 
                               WHERE i.id = ?");
        $stmtRefresh->execute([$id]);
        $r = $stmtRefresh->fetch();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = "Update failed: " . $e->getMessage();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Edit Resident: <?php echo "{$r['fname']} {$r['lname']}"; ?></h2>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
</div>

<?php if ($success): ?> <div class="alert alert-success"><?php echo $success; ?></div> <?php endif; ?>
<?php if ($error): ?> <div class="alert alert-danger"><?php echo $error; ?></div> <?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">First Name</label>
            <input type="text" name="fname" class="form-control" value="<?php echo $r['fname']; ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Middle Name</label>
            <input type="text" name="mname" class="form-control" value="<?php echo $r['mname']; ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Last Name</label>
            <input type="text" name="lname" class="form-control" value="<?php echo $r['lname']; ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Birth Date</label>
            <input type="date" name="bdate" class="form-control" value="<?php echo $r['bdate']; ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Sex</label>
            <select name="sex" class="form-select">
                <option <?php if($r['s'] == 'Male') echo 'selected'; ?>>Male</option>
                <option <?php if($r['s'] == 'Female') echo 'selected'; ?>>Female</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Occupation</label>
            <input type="text" name="occ" class="form-control" value="<?php echo $r['occ']; ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Phone</label>
            <input type="text" name="pho_no" class="form-control" value="<?php echo $r['pho_no']; ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Marital Status</label>
            <select name="mar" class="form-select">
                <option <?php if($r['mar'] == 'Single') echo 'selected'; ?>>Single</option>
                <option <?php if($r['mar'] == 'Married') echo 'selected'; ?>>Married</option>
                <option <?php if($r['mar'] == 'Divorced') echo 'selected'; ?>>Divorced</option>
                <option <?php if($r['mar'] == 'Widowed') echo 'selected'; ?>>Widowed</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Nationality</label>
            <input type="text" name="nat" class="form-control" value="<?php echo $r['nat']; ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Religion</label>
            <input type="text" name="relg" class="form-control" value="<?php echo $r['relg']; ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Education Level</label>
            <input type="text" name="level_edu" class="form-control" value="<?php echo $r['level_edu']; ?>" required>
        </div>

        <h5 class="border-bottom pb-2 mt-4 text-primary"><i class="fas fa-users me-2"></i>Parental Information (For Birth Certificates)</h5>
        <div class="col-md-6">
            <label class="form-label">Mother's Full Name</label>
            <input type="text" name="mother_full_name" class="form-control" value="<?php echo $r['mother_full_name']; ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Father's Full Name</label>
            <input type="text" name="father_full_name" class="form-control" value="<?php echo $r['father_full_name']; ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Mother's Nationality</label>
            <input type="text" name="mother_nat" class="form-control" value="<?php echo $r['mother_nat'] ?? 'Itoophiyaa'; ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Father's Nationality</label>
            <input type="text" name="father_nat" class="form-control" value="<?php echo $r['father_nat'] ?? 'Itoophiyaa'; ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Update Photo</label>
            <input type="file" name="photo" class="form-control" accept="image/*">
        </div>
        
        <div class="col-12 mt-4 text-end">
            <button type="submit" class="btn btn-success px-5">Save Changes</button>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
