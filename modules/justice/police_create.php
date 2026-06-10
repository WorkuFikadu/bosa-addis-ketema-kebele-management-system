<?php
require_once '../../includes/header.php';
require_once '../../config/database.php';

$success = false;
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $individual_id = $_POST['individual_id'] ?? '';
    $badge_number = trim($_POST['badge_number'] ?? '');
    $rank = trim($_POST['rank'] ?? '');
    $station_assignment = trim($_POST['station_assignment'] ?? '');
    $weapon_serial = trim($_POST['weapon_serial'] ?? '');
    $joined_date = $_POST['joined_date'] ?? date('Y-m-d');
    $status = $_POST['status'] ?? 'Active';

    if (empty($individual_id) || empty($badge_number) || empty($rank) || empty($station_assignment)) {
        $error = 'Please fill out all required fields (Resident, Badge Number, Rank, Station).';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO police_records (individual_id, badge_number, rank, station_assignment, weapon_serial, status, joined_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$individual_id, $badge_number, $rank, $station_assignment, $weapon_serial, $status, $joined_date]);
            
            // Log audit
            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];
                $log_stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details) VALUES (?, 'CREATE', ?)");
                $log_stmt->execute([$user_id, "Registered new Police Officer. Badge: $badge_number"]);
            }
            
            $success = true;
        } catch (PDOException $e) {
            // Check for duplicate badge
            if ($e->getCode() == 23000) {
                $error = 'A police officer with this Badge Number is already registered.';
            } else {
                $error = 'Database Error: ' . $e->getMessage();
            }
        }
    }
}

// Fetch residents to link to the police profile
$residents = $pdo->query("SELECT id, fname, mname, lname FROM individuals ORDER BY fname ASC")->fetchAll();
?>

<div class="container-fluid py-4 min-vh-100">
    <div class="d-flex align-items-center mb-4">
        <a href="index.php" class="btn btn-light rounded-circle shadow-sm me-3" style="width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center;">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="fw-black text-dark mb-1"><i class="fas fa-user-shield text-warning me-2"></i>Register Police Officer</h2>
            <p class="text-muted small fw-bold mb-0">Add a new active police personnel to the Kebele security database.</p>
        </div>
    </div>

    <?php if ($success): ?>
    <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
        <i class="fas fa-check-circle me-2"></i> Police officer successfully registered to the database!
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
                <h5 class="fw-bold text-dark mb-4 border-bottom pb-2">Officer Identity & Assignment</h5>
                
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
                        <div class="form-text small">Only registered Kebele residents can be enrolled in local forces.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted small uppercase">Badge / ID Number <span class="text-danger">*</span></label>
                        <input type="text" name="badge_number" class="form-control rounded-pill px-4 bg-light border-0" placeholder="e.g. POL-45902" required>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-muted small uppercase">Officer Rank <span class="text-danger">*</span></label>
                        <select name="rank" class="form-select rounded-pill px-4 bg-light border-0" required>
                            <option value="">-- Select Rank --</option>
                            <option value="Constable">Constable</option>
                            <option value="Corporal">Corporal</option>
                            <option value="Sergeant">Sergeant</option>
                            <option value="Inspector">Inspector</option>
                            <option value="Commander">Commander</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-muted small uppercase">Station / Zone Assignment <span class="text-danger">*</span></label>
                        <input type="text" name="station_assignment" class="form-control rounded-pill px-4 bg-light border-0" placeholder="e.g. Zone 1 Command" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-muted small uppercase">Date Joined</label>
                        <input type="date" name="joined_date" class="form-control rounded-pill px-4 bg-light border-0" value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <h5 class="fw-bold text-dark mt-5 mb-4 border-bottom pb-2 w-100">Equipment & Status</h5>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted small uppercase">Assigned Weapon Serial</label>
                        <input type="text" name="weapon_serial" class="form-control rounded-pill px-4 bg-light border-0" placeholder="e.g. AK-74M-00912 (Optional)">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted small uppercase">Current Status</label>
                        <select name="status" class="form-select rounded-pill px-4 bg-light border-0">
                            <option value="Active" selected>Active / On Duty</option>
                            <option value="Suspended">Suspended</option>
                            <option value="Retired">Retired</option>
                            <option value="Transferred">Transferred</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                    <button type="reset" class="btn btn-light rounded-pill px-4 fw-bold">Clear Form</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-5 fw-bold shadow-sm">
                        <i class="fas fa-save me-2"></i> Save Officer Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
