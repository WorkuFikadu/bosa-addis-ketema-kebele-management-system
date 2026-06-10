<?php
require_once '../../includes/header.php';
require_once '../../config/database.php';

$success = false;
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $individual_id = $_POST['individual_id'] ?? '';
    $committee_role = trim($_POST['committee_role'] ?? 'Member');
    $sector = trim($_POST['sector'] ?? '');
    $joined_date = $_POST['joined_date'] ?? date('Y-m-d');
    $status = $_POST['status'] ?? 'Active';

    if (empty($individual_id) || empty($committee_role) || empty($sector)) {
        $error = 'Please fill out all required fields (Resident, Committee Role, Sector).';
    } else {
        try {
            // Check for active duplicates
            $check_stmt = $pdo->prepare("SELECT id FROM gachana_records WHERE individual_id = ? AND status = 'Active'");
            $check_stmt->execute([$individual_id]);
            if ($check_stmt->rowCount() > 0) {
                $error = 'This individual is already registered as an active Gachana Sirna member.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO gachana_records (individual_id, committee_role, sector, status, joined_date) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$individual_id, $committee_role, $sector, $status, $joined_date]);
                
                // Log audit
                if (isset($_SESSION['user_id'])) {
                    $user_id = $_SESSION['user_id'];
                    $log_stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details) VALUES (?, 'CREATE', ?)");
                    $log_stmt->execute([$user_id, "Registered new Gachana Sirna Member. Role: $committee_role in $sector"]);
                }
                
                $success = true;
            }
        } catch (PDOException $e) {
            $error = 'Database Error: ' . $e->getMessage();
        }
    }
}

// Fetch residents
$residents = $pdo->query("SELECT id, fname, mname, lname FROM individuals ORDER BY fname ASC")->fetchAll();
?>

<div class="container-fluid py-4 min-vh-100">
    <div class="d-flex align-items-center mb-4">
        <a href="index.php" class="btn btn-light rounded-circle shadow-sm me-3" style="width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center;">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="fw-black text-dark mb-1"><i class="fas fa-users-viewfinder text-warning me-2"></i>Register Gachana Sirna</h2>
            <p class="text-muted small fw-bold mb-0">Enroll committed residents into community peace-keeping structures.</p>
        </div>
    </div>

    <?php if ($success): ?>
    <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
        <i class="fas fa-check-circle me-2"></i> Gachana Sirna member successfully registered!
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
        <i class="fas fa-triangle-exclamation me-2"></i> <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>

    <div class="card border-0 shadow-premium rounded-5 overflow-hidden">
        <div class="card-body p-4 p-md-5">
            <form method="POST" action="">
                <h5 class="fw-bold text-dark mb-4 border-bottom pb-2">Gachana Sirna Identity & Sector</h5>
                
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted small uppercase">Select Resident <span class="text-danger">*</span></label>
                        <select name="individual_id" class="form-select rounded-pill px-4 bg-light border-0" required>
                            <option value="">-- Choose Resident Profile --</option>
                            <?php foreach ($residents as $res): ?>
                                <option value="<?php echo $res['id']; ?>">
                                    <?php echo htmlspecialchars($res['fname'] . ' ' . $res['mname'] . ' ' . $res['lname']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted small uppercase">Committee Role <span class="text-danger">*</span></label>
                        <select name="committee_role" class="form-select rounded-pill px-4 bg-light border-0" required>
                            <option value="">-- Select Role --</option>
                            <option value="Chairperson">Chairperson</option>
                            <option value="Vice Chairperson">Vice Chairperson</option>
                            <option value="Secretary">Secretary</option>
                            <option value="Security Intelligence">Security Intelligence</option>
                            <option value="Member" selected>Standard Member</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted small uppercase">Sector / Ketena <span class="text-danger">*</span></label>
                        <select name="sector" class="form-select rounded-pill px-4 bg-light border-0" required>
                            <option value="">-- Select Deployment Sector --</option>
                            <option value="Ketena 1">Ketena 1</option>
                            <option value="Ketena 2">Ketena 2</option>
                            <option value="Ketena 3">Ketena 3</option>
                            <option value="Ketena 4">Ketena 4</option>
                            <option value="Ketena 5">Ketena 5</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small uppercase">Date Enrolled</label>
                        <input type="date" name="joined_date" class="form-control rounded-pill px-4 bg-light border-0" value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small uppercase">Status</label>
                        <select name="status" class="form-select rounded-pill px-4 bg-light border-0">
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                    <button type="reset" class="btn btn-light rounded-pill px-4 fw-bold">Clear Form</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-5 fw-bold shadow-sm">
                        <i class="fas fa-save me-2"></i> Save Gachana Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
