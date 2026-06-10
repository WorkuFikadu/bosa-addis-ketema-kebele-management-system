<?php
// modules/health/safetynet_create.php
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../auth/login.php'); exit; }

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $individual_id = $_POST['individual_id'];
    $enrollment_date = $_POST['enrollment_date'];
    $household_size = $_POST['household_size'];
    $transfer_type = $_POST['transfer_type'];
    $work_status = $_POST['work_status'];
    $vulnerability_criteria = $_POST['vulnerability_criteria'] ?? NULL;
    $proxy_name = $_POST['proxy_name'] ?: NULL;
    $duty_station = $_POST['duty_station'] ?: NULL;
    $monthly_entitlement = $_POST['monthly_entitlement'] ?: NULL;
    $payment_method = $_POST['payment_method'] ?? 'Cash';
    $payment_status = $_POST['payment_status'] ?? 'Up to date';

    if ($individual_id && $enrollment_date && $transfer_type && $work_status) {
        try {
            $stmt = $pdo->prepare("INSERT INTO safetynet_records (individual_id, enrollment_date, household_size, transfer_type, work_status, vulnerability_criteria, proxy_name, duty_station, monthly_entitlement, payment_method, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$individual_id, $enrollment_date, $household_size, $transfer_type, $work_status, $vulnerability_criteria, $proxy_name, $duty_station, $monthly_entitlement, $payment_method, $payment_status]);
            $success = "Participant successfully enrolled in PSNP with full information!";
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}

// Fetch residents for dropdown
$residents = $pdo->query("SELECT id, fname, lname FROM individuals WHERE id NOT IN (SELECT individual_id FROM safetynet_records) ORDER BY fname")->fetchAll();
?>

<div class="mb-3">
    <a href="safetynet_list.php" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold shadow-sm">
        <i class="fas fa-arrow-left me-2"></i><?php echo __('back_to_psnp_registry', 'Back to PSNP Registry'); ?>
    </a>
</div>

<div class="card border-0 shadow-lg rounded-4 overflow-hidden max-width-900 mx-auto mb-5">
    <div class="card-header bg-success text-white p-4 border-0 text-center">
        <h4 class="fw-black mb-0"><i class="fas fa-user-plus me-2"></i><?php echo __('psnp_enrollment_form', 'PSNP Enrollment Form'); ?></h4>
        <p class="mb-0 small opacity-75"><?php echo __('psnp_enrollment_desc', 'Productive Safety Net Program Registration'); ?></p>
    </div>
    
    <div class="card-body p-4 p-md-5">
        <?php if($success): ?>
            <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4"><i class="fas fa-check-circle me-2"></i><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" class="row g-4">
            <!-- SECTION 1: PARTICIPANT & HOUSEHOLD -->
            <div class="col-12"><h6 class="fw-black text-success border-bottom pb-2 mb-0"><?php echo __('participant_household_sec', '1. PARTICIPANT & HOUSEHOLD'); ?></h6></div>
            
            <div class="col-md-8">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('select_individual', 'Select Individual'); ?> <span class="text-danger">*</span></label>
                <select name="individual_id" class="form-select rounded-pill px-4 bg-light border-0 py-2 shadow-sm" required>
                    <option value=""><?php echo __('-- choose_resident_for_psnp --', '-- Choose Resident for PSNP --'); ?></option>
                    <?php foreach ($residents as $r): ?>
                        <option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars("{$r['fname']} {$r['lname']} (ID: #{$r['id']})"); ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text mt-1 text-success small"><i class="fas fa-info-circle me-1"></i><?php echo __('psnp_only_new_residents', 'Only residents not currently in PSNP are listed.'); ?></div>
            </div>
            
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('enrollment_date', 'Enrollment Date'); ?> <span class="text-danger">*</span></label>
                <input type="date" name="enrollment_date" class="form-control rounded-pill px-4 bg-light border-0 py-2 shadow-sm" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('household_size', 'Household Size'); ?></label>
                <input type="number" name="household_size" class="form-control rounded-pill px-4 bg-light border-0 py-2 shadow-sm" value="1" min="1">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('vulnerability_criteria', 'Vulnerability Criteria'); ?></label>
                <select name="vulnerability_criteria" class="form-select rounded-pill px-4 bg-light border-0 py-2 shadow-sm">
                    <option value="Extreme Poverty"><?php echo __('extreme_poverty', 'Extreme Poverty'); ?></option>
                    <option value="Female-Headed"><?php echo __('female_headed_household', 'Female-Headed Household'); ?></option>
                    <option value="Elderly/Disabled"><?php echo __('elderly_disabled_head', 'Elderly / Disabled Head'); ?></option>
                    <option value="Lactating/Pregnant"><?php echo __('lactating_pregnant', 'Lactating / Pregnant'); ?></option>
                    <option value="Other"><?php echo __('other', 'Other'); ?></option>
                </select>
            </div>

            <!-- SECTION 2: WORK ASSIGNMENT & DUTIES -->
            <div class="col-12 mt-4"><h6 class="fw-black text-success border-bottom pb-2 mb-0"><?php echo __('work_assignment_sec', '2. WORK ASSIGNMENT & DUTIES'); ?></h6></div>

            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('work_requirement', 'Work Requirement'); ?> <span class="text-danger">*</span></label>
                <select name="work_status" class="form-select rounded-pill px-4 bg-light border-0 py-2 shadow-sm" required>
                    <option value="Public Work"><?php echo __('public_work', 'Public Work (Labor-oriented)'); ?></option>
                    <option value="Direct Support"><?php echo __('direct_support', 'Direct Support (Elders/Disabled)'); ?></option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('duty_station', 'Duty Station / Public Work Site'); ?></label>
                <input type="text" name="duty_station" class="form-control rounded-pill px-4 bg-light border-0 py-2 shadow-sm" placeholder="<?php echo __('eg_kebele_farm', 'e.g., Kebele Farm Maintenance'); ?>">
            </div>
            
            <div class="col-md-12">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('proxy_name_label', 'Proxy Receiver Name (If Direct Support)'); ?></label>
                <input type="text" name="proxy_name" class="form-control rounded-pill px-4 bg-light border-0 py-2 shadow-sm" placeholder="<?php echo __('proxy_name_placeholder', 'Full Name of Proxy (Optional)...'); ?>">
            </div>

            <!-- SECTION 3: ENTITLEMENT & PAYMENT -->
            <div class="col-12 mt-4"><h6 class="fw-black text-success border-bottom pb-2 mb-0"><?php echo __('entitlement_payment_sec', '3. ENTITLEMENT & DISBURSEMENT'); ?></h6></div>

            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('transfer_type', 'Transfer Type'); ?> <span class="text-danger">*</span></label>
                <select name="transfer_type" class="form-select rounded-pill px-4 bg-light border-0 py-2 shadow-sm" required>
                    <option value="Food"><?php echo __('food_only', 'Food Only (Relief)'); ?></option>
                    <option value="Cash"><?php echo __('cash_transfer', 'Cash Transfer'); ?></option>
                    <option value="Mixed"><?php echo __('mixed_food_cash', 'Mixed (Food & Cash)'); ?></option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('payment_method', 'Payment Method'); ?></label>
                <select name="payment_method" class="form-select rounded-pill px-4 bg-light border-0 py-2 shadow-sm">
                    <option value="Cash"><?php echo __('cash_in_hand', 'Cash in Hand'); ?></option>
                    <option value="Mobile Money"><?php echo __('mobile_money', 'Telebirr / CBE Birr'); ?></option>
                    <option value="Bank Transfer"><?php echo __('bank_transfer', 'Direct Bank Transfer'); ?></option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('monthly_entitlement', 'Monthly Entitlement (ETB/KG)'); ?></label>
                <input type="number" step="0.01" name="monthly_entitlement" class="form-control rounded-pill px-4 bg-light border-0 py-2 shadow-sm" placeholder="e.g. 500.00">
            </div>
            
            <div class="col-md-12 mt-3">
                 <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('payment_status_label', 'Initial Status'); ?></label>
                 <select name="payment_status" class="form-select rounded-pill px-4 bg-light border-0 py-2 shadow-sm fw-bold text-success">
                    <option value="Up to date"><?php echo __('status_up_to_date', 'Up to date'); ?></option>
                    <option value="Pending"><?php echo __('status_pending', 'Pending Approval'); ?></option>
                    <option value="Overdue"><?php echo __('status_overdue', 'Overdue'); ?></option>
                </select>
            </div>

            <div class="col-12 mt-5">
                <button type="submit" class="btn btn-success text-white w-100 rounded-pill py-3 fw-black shadow-lg">
                    <i class="fas fa-check-circle me-2"></i><?php echo __('complete_registration', 'COMPLETE REGISTRATION'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.max-width-900 { max-width: 900px; }
</style>

<?php require_once '../../includes/footer.php'; ?>
