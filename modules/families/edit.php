<?php
// modules/families/edit.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

$hnum = $_GET['hnum'] ?? null;
$stmt = $pdo->prepare("SELECT * FROM families WHERE hnum = ?");
$stmt->execute([$hnum]);
$family = $stmt->fetch();

if (!$family) {
    header('Location: index.php');
    exit;
}

$residents = $pdo->query("SELECT id, fname, lname FROM individuals ORDER BY fname")->fetchAll();
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lead_id = $_POST['lead_id'];
    $fam_no = $_POST['fam_no'];

    $stmt_update = $pdo->prepare("UPDATE families SET lead_id = ?, fam_no = ? WHERE hnum = ?");
    try {
        $stmt_update->execute([$lead_id, $fam_no, $hnum]);
        $success = "Family profile updated successfully!";
        // Refresh
        $stmt->execute([$hnum]);
        $family = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Update failed: " . $e->getMessage();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Edit Family Profile (House #<?php echo $hnum; ?>)</h2>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
</div>

<?php if ($success): ?> <div class="alert alert-success"><?php echo $success; ?></div> <?php endif; ?>
<?php if ($error): ?> <div class="alert alert-danger"><?php echo $error; ?></div> <?php endif; ?>

<div class="row">
    <div class="col-md-7">
        <form method="POST" class="card p-4 shadow-sm">
            <div class="row g-3">
                <div class="col-md-12 text-muted mb-2">
                    <small><i class="fas fa-info-circle me-1"></i> House number cannot be changed here. To move a family, create a new profile.</small>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Family Leader</label>
                    <select name="lead_id" class="form-select" required>
                        <?php foreach ($residents as $r): ?>
                            <option value="<?php echo $r['id']; ?>" <?php echo ($r['id'] == $family['lead_id']) ? 'selected' : ''; ?>>
                                <?php echo "{$r['fname']} {$r['lname']} (ID: #{$r['id']})"; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Number of Family Members</label>
                    <input type="number" name="fam_no" class="form-control" value="<?php echo $family['fam_no']; ?>" required min="1">
                </div>
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary w-100 py-2">Update Family Data</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
