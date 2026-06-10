<?php
require_once '../../includes/header.php';
require_once '../../config/database.php';

$success = false;
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $individual_id = $_POST['individual_id'] ?? '';
    $role = trim($_POST['role'] ?? 'Member');
    $zone_assigned = trim($_POST['zone_assigned'] ?? '');
    $weapon_serial = trim($_POST['weapon_serial'] ?? '');
    $joined_date = $_POST['joined_date'] ?? date('Y-m-d');
    $status = $_POST['status'] ?? 'Active';

    if (empty($individual_id) || empty($role) || empty($zone_assigned)) {
        $error = 'Please fill out all required fields (Resident, Role, Zone Assignment).';
    } else {
        try {
            // Check if individual is already a milisha member to prevent duplicate assignments
            $check_stmt = $pdo->prepare("SELECT id FROM milisha_records WHERE individual_id = ? AND status = 'Active'");
            $check_stmt->execute([$individual_id]);
            if ($check_stmt->rowCount() > 0) {
                $error = 'This individual is already registered as an active Milisha member.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO milisha_records (individual_id, role, zone_assigned, weapon_serial, status, joined_date) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$individual_id, $role, $zone_assigned, $weapon_serial, $status, $joined_date]);
                
                // Log audit
                if (isset($_SESSION['user_id'])) {
                    $user_id = $_SESSION['user_id'];
                    $log_stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details) VALUES (?, 'CREATE', ?)");
                    $log_stmt->execute([$user_id, "Registered new Milisha Member. Role: $role in $zone_assigned"]);
                }
                
                $success = true;
            }
        } catch (PDOException $e) {
            $error = 'Database Error: ' . $e->getMessage();
        }
    }
}

// Fetch residents to link to the milisha profile
$residents = $pdo->query("SELECT id, fname, mname, lname FROM individuals ORDER BY fname ASC")->fetchAll();
?>

<div class="container-fluid py-4 min-vh-100">
    <div class="d-flex align-items-center mb-4">
        <a href="index.php" class="btn btn-light rounded-circle shadow-sm me-3" style="width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center;">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="fw-black text-dark mb-1"><i class="fas fa-person-military-pointing text-warning me-2"></i>Register Milisha Member</h2>
            <p class="text-muted small fw-bold mb-0">Add a new community defense and watch personnel.</p>
        </div>
    </div>

    <?php if ($success): ?>
    <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
        <i class="fas fa-check-circle me-2"></i> Milisha personnel successfully registered!
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
                <h5 class="fw-bold text-dark mb-4 border-bottom pb-2">Milisha Identity & Assignment</h5>
                
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
                        <label class="form-label fw-bold text-muted small uppercase">Squad / Command Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select rounded-pill px-4 bg-light border-0" required>
                            <option value="">-- Select Role --</option>
                            <option value="Commander">Commander</option>
                            <option value="Platoon Leader">Platoon Leader</option>
                            <option value="Squad Leader">Squad Leader</option>
                            <option value="Member" selected>Member</option>
                        </select>
                    </div>
                    
                    <div class="col-md-8">
                        <label class="form-label fw-bold text-muted small uppercase">Zone / Kebele Area Assignment <span class="text-danger">*</span></label>
                        <input type="text" name="zone_assigned" class="form-control rounded-pill px-4 bg-light border-0" placeholder="e.g. Ketena 3, Block 12 Area" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-muted small uppercase">Date Joined</label>
                        <input type="date" name="joined_date" class="form-control rounded-pill px-4 bg-light border-0" value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <h5 class="fw-bold text-dark mt-5 mb-4 border-bottom pb-2 w-100">Equipment & Status</h5>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted small uppercase">Assigned Weapon Serial</label>
                        <input type="text" name="weapon_serial" class="form-control rounded-pill px-4 bg-light border-0" placeholder="e.g. SKS-40291 (Leave blank if unarmed)">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted small uppercase">Current Status</label>
                        <select name="status" class="form-select rounded-pill px-4 bg-light border-0">
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Dismissed">Dismissed</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                    <button type="reset" class="btn btn-light rounded-pill px-4 fw-bold">Clear Form</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-5 fw-bold shadow-sm">
                        <i class="fas fa-save me-2"></i> Save Milisha Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
