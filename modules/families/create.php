<?php
// modules/families/create.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

$residents = $pdo->query("
    SELECT id, fname, lname 
    FROM individuals 
    WHERE id NOT IN (SELECT lead_id FROM families)
    ORDER BY fname
")->fetchAll();

$houses = $pdo->query("
    SELECT hnum 
    FROM houses 
    WHERE hnum NOT IN (SELECT hnum FROM families)
    ORDER BY hnum
")->fetchAll();

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hnum              = $_POST['hnum'];
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
    $registration_date = $_POST['registration_date'] ?: date('Y-m-d');

    // Validate redundancy
    $checkResult = $pdo->prepare("SELECT fam_no FROM families WHERE hnum = ? OR lead_id = ?");
    $checkResult->execute([$hnum, $lead_id]);
    if ($checkResult->rowCount() > 0) {
        $error = "Redundant Registration: This house already has a family, or the selected resident is already a family head.";
    } else {
        $sql = "INSERT INTO families (hnum, lead_id, fam_no, family_type, income_category, social_status, total_males, total_females, disabled_members, orphans_count, has_pension, is_vulnerable, registration_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([
                $hnum, $lead_id, $fam_no, $family_type, $income_category, 
                $social_status, $total_males, $total_females, $disabled_members, 
                $orphans_count, $has_pension, $is_vulnerable, $registration_date
            ]);
            $success = "Detailed Family profile for House #$hnum registered successfully!";
        } catch (PDOException $e) {
            $error = "Failed to register family: " . $e->getMessage();
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-people-group me-2 text-primary"></i>Register Detailed Family</h2>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
</div>

<?php if ($success): ?>
    <div class="alert alert-success shadow-sm border-0"><i class="fas fa-check-circle me-2"></i><?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger shadow-sm border-0"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST">
    <div class="row g-4">
        <!-- Section 1: Linking & Leadership -->
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary"><i class="fas fa-link me-2"></i>Linkage & Leadership</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">House Location <span class="text-danger">*</span></label>
                            <select name="hnum" class="form-select" required>
                                <option value="">-- Select House --</option>
                                <?php foreach ($houses as $h): ?>
                                    <option value="<?php echo $h['hnum']; ?>">House #<?php echo $h['hnum']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text small">Select the permanent house number.</div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Family Leader (Head) <span class="text-danger">*</span></label>
                            <select name="lead_id" class="form-select" required>
                                <option value="">-- Select Resident Head --</option>
                                <?php foreach ($residents as $r): ?>
                                    <option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars("{$r['fname']} {$r['lname']} (ID: #{$r['id']})"); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Registration Date</label>
                            <input type="date" name="registration_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Demographic Structure -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light py-3 border-bottom-0">
                    <h5 class="mb-0 text-dark"><i class="fas fa-chart-pie me-2"></i>Demographic Structure</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Family Type</label>
                            <select name="family_type" class="form-select">
                                <option value="Nuclear">Nuclear</option>
                                <option value="Extended">Extended</option>
                                <option value="Single Parent">Single Parent</option>
                                <option value="Child Headed">Child Headed</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Total Family Size <span class="text-danger">*</span></label>
                            <input type="number" name="fam_no" class="form-control" required min="1" placeholder="Total people">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Males</label>
                            <input type="number" name="total_males" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Females</label>
                            <input type="number" name="total_females" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Disabled Members</label>
                            <input type="number" name="disabled_members" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Orphans count</label>
                            <input type="number" name="orphans_count" class="form-control" min="0" value="0">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Socio-Economic Profile -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light py-3 border-bottom-0">
                    <h5 class="mb-0 text-dark"><i class="fas fa-hand-holding-dollar me-2"></i>Socio-Economic Profile</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Income Level</label>
                            <select name="income_category" class="form-select">
                                <option value="Low">Low Income / Vulnerable</option>
                                <option value="Medium">Medium Income</option>
                                <option value="High">High Income</option>
                                <option value="No Income">No Stable Income</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Social Status</label>
                            <select name="social_status" class="form-select">
                                <option value="Permanent Resident">Permanent Resident</option>
                                <option value="Temporary">Temporary</option>
                                <option value="IDP">Displaced (IDP)</option>
                                <option value="Returnee">Returnee</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold d-block mb-2">Pension Coverage?</label>
                            <div class="form-check form-check-inline mt-1">
                                <input class="form-check-input" type="radio" name="has_pension" value="Yes" id="p_yes">
                                <label class="form-check-label" for="p_yes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline mt-1">
                                <input class="form-check-input" type="radio" name="has_pension" value="No" id="p_no" checked>
                                <label class="form-check-label" for="p_no">No</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold d-block mb-2 text-danger">Target for Support?</label>
                            <div class="form-check form-check-inline mt-1">
                                <input class="form-check-input" type="radio" name="is_vulnerable" value="Yes" id="v_yes">
                                <label class="form-check-label" for="v_yes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline mt-1">
                                <input class="form-check-input" type="radio" name="is_vulnerable" value="No" id="v_no" checked>
                                <label class="form-check-label" for="v_no">No</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 mt-4 text-end">
            <hr class="mb-4">
            <button type="submit" class="btn btn-primary px-5 py-3 fw-bold rounded-pill shadow">
                <i class="fas fa-save me-2"></i>Confirm & Register Family Profile
            </button>
        </div>
    </div>
</form>


<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
