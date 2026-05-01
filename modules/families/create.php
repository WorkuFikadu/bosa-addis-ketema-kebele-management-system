<?php
// modules/families/create.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

$residents = $pdo->query("SELECT id, fname, lname FROM individuals ORDER BY fname")->fetchAll();
$houses = $pdo->query("SELECT hnum FROM houses ORDER BY hnum")->fetchAll();

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hnum = $_POST['hnum'];
    $lead_id = $_POST['lead_id'];
    $fam_no = $_POST['fam_no'];

    $stmt = $pdo->prepare("INSERT INTO families (hnum, lead_id, fam_no) VALUES (?, ?, ?)");
    try {
        $stmt->execute([$hnum, $lead_id, $fam_no]);
        $success = "Family for House #$hnum registered successfully!";
    } catch (PDOException $e) {
        $error = "Failed to register family: " . $e->getMessage();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Register Family</h2>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-7">
        <form method="POST" class="card p-4 shadow-sm">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">House Number</label>
                    <select name="hnum" class="form-select" required>
                        <option value="">-- Select House --</option>
                        <?php foreach ($houses as $h): ?>
                            <option value="<?php echo $h['hnum']; ?>">House #<?php echo $h['hnum']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Family Leader</label>
                    <select name="lead_id" class="form-select" required>
                        <option value="">-- Select Leader --</option>
                        <?php foreach ($residents as $r): ?>
                            <option value="<?php echo $r['id']; ?>"><?php echo "{$r['fname']} {$r['lname']} (ID: #{$r['id']})"; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Number of Family Members</label>
                    <input type="number" name="fam_no" class="form-control" required min="1">
                </div>
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary w-100 py-2">Create Family Profile</button>
                </div>
            </div>
        </form>
    </div>
    <div class="col-md-5">
        <div class="card p-3 bg-light border-0 shadow-sm">
            <h5>Quick Start Guide</h5>
            <ul>
                <li>First, register the individual as a resident.</li>
                <li>Second, register the house in House Management.</li>
                <li>Finally, link them here by selecting the house and the leader.</li>
            </ul>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
