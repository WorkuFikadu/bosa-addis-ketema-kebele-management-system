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
    $lead_id           = $_POST['lead_id'];
    $fam_no            = (int)$_POST['fam_no'];
    $family_type       = $_POST['family_type'];
    $income_category   = $_POST['income_category'];
    $social_status     = $_POST['social_status'];
    $total_males       = (int)$_POST['total_males'];
    $total_females     = (int)$_POST['total_females'];
    $disabled_members  = (int)$_POST['disabled_members'];
    $orphans_count     = (int)$_POST['orphans_count'];
    $has_pension       = $_POST['has_pension'];
    $is_vulnerable     = $_POST['is_vulnerable'];
    $registration_date = $_POST['registration_date'];

    $sql_update = "UPDATE families SET 
                    lead_id = ?, fam_no = ?, family_type = ?, 
                    income_category = ?, social_status = ?, total_males = ?, 
                    total_females = ?, disabled_members = ?, orphans_count = ?, 
                    has_pension = ?, is_vulnerable = ?, registration_date = ? 
                   WHERE hnum = ?";
    $stmt_update = $pdo->prepare($sql_update);
    try {
        $stmt_update->execute([
            $lead_id, $fam_no, $family_type, 
            $income_category, $social_status, $total_males, 
            $total_females, $disabled_members, $orphans_count, 
            $has_pension, $is_vulnerable, $registration_date, 
            $hnum
        ]);
        $success = "Detailed family profile updated successfully!";
        // Refresh
        $stmt->execute([$hnum]);
        $family = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Update failed: " . $e->getMessage();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-edit me-2 text-info"></i>Edit Family (House #<?php echo $hnum; ?>)</h2>
    <div class="d-flex gap-2">
        <a href="view.php?hnum=<?php echo $hnum; ?>" class="btn btn-outline-primary"><i class="fas fa-eye me-1"></i>View Profile</a>
        <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success shadow-sm border-0"><i class="fas fa-check-circle me-2"></i><?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger shadow-sm border-0"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST">
    <div class="row g-4">
        <!-- Section 1: Leadership -->
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary"><i class="fas fa-user-tie me-2"></i>Family Head Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Current Family Head <span class="text-danger">*</span></label>
                            <select name="lead_id" class="form-select" required>
                                <?php foreach ($residents as $r): ?>
                                    <option value="<?php echo $r['id']; ?>" <?php echo ($r['id'] == $family['lead_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars("{$r['fname']} {$r['lname']} (ID: #{$r['id']})"); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Registration Date</label>
                            <input type="date" name="registration_date" class="form-control" value="<?php echo $family['registration_date']; ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">House Reference</label>
                            <input type="text" class="form-control bg-light" value="House #<?php echo $hnum; ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Demographic Details -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light py-3 border-bottom-0">
                    <h5 class="mb-0 text-dark"><i class="fas fa-chart-bar me-2"></i>Member Statistics</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Family Type</label>
                            <select name="family_type" class="form-select">
                                <?php 
                                $f_types = ['Nuclear', 'Extended', 'Single Parent', 'Child Headed', 'Other'];
                                foreach ($f_types as $ft): 
                                ?>
                                    <option value="<?php echo $ft; ?>" <?php echo ($family['family_type'] == $ft) ? 'selected' : ''; ?>><?php echo $ft; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Total Family Size <span class="text-danger">*</span></label>
                            <input type="number" name="fam_no" class="form-control" value="<?php echo $family['fam_no']; ?>" required min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Males</label>
                            <input type="number" name="total_males" class="form-control" value="<?php echo $family['total_males']; ?>" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Females</label>
                            <input type="number" name="total_females" class="form-control" value="<?php echo $family['total_females']; ?>" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Disabled Members</label>
                            <input type="number" name="disabled_members" class="form-control" value="<?php echo $family['disabled_members']; ?>" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Orphans Count</label>
                            <input type="number" name="orphans_count" class="form-control" value="<?php echo $family['orphans_count']; ?>" min="0">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Socio-Economic Profile -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light py-3 border-bottom-0">
                    <h5 class="mb-0 text-dark"><i class="fas fa-hand-holding-heart me-2"></i>Socio-Economic Status</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Income Level</label>
                            <select name="income_category" class="form-select">
                                <?php 
                                $incomes = ['Low', 'Medium', 'High', 'No Income'];
                                foreach ($incomes as $i): 
                                ?>
                                    <option value="<?php echo $i; ?>" <?php echo ($family['income_category'] == $i) ? 'selected' : ''; ?>><?php echo $i . ($i == 'Low' ? ' Income / Vulnerable' : ' Income'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Social Status</label>
                            <select name="social_status" class="form-select">
                                <?php 
                                $status = ['Permanent Resident', 'Temporary', 'IDP', 'Returnee'];
                                foreach ($status as $s): 
                                ?>
                                    <option value="<?php echo $s; ?>" <?php echo ($family['social_status'] == $s) ? 'selected' : ''; ?>><?php echo $s; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold d-block mb-2">Pension Coverage?</label>
                            <div class="form-check form-check-inline mt-1">
                                <input class="form-check-input" type="radio" name="has_pension" value="Yes" id="p_yes" <?php echo ($family['has_pension'] == 'Yes') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="p_yes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline mt-1">
                                <input class="form-check-input" type="radio" name="has_pension" value="No" id="p_no" <?php echo ($family['has_pension'] == 'No') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="p_no">No</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold d-block mb-2 text-danger">Target for Support?</label>
                            <div class="form-check form-check-inline mt-1">
                                <input class="form-check-input" type="radio" name="is_vulnerable" value="Yes" id="v_yes" <?php echo ($family['is_vulnerable'] == 'Yes') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="v_yes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline mt-1">
                                <input class="form-check-input" type="radio" name="is_vulnerable" value="No" id="v_no" <?php echo ($family['is_vulnerable'] == 'No') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="v_no">No</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 mt-4 text-center">
            <button type="submit" class="btn btn-info text-white px-5 py-3 fw-bold rounded-pill shadow">
                <i class="fas fa-save me-2"></i>Save Final family Details
            </button>
            <a href="index.php" class="btn btn-link text-muted ms-2">Discard Changes</a>
        </div>
    </div>
</form>


<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
