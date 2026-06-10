<?php
// modules/health/health_create.php
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../auth/login.php'); exit; }

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $individual_id = $_POST['individual_id'];
    $service_type = $_POST['service_type'];
    $service_date = $_POST['service_date'];
    $staff_name = $_POST['staff_name'];
    $notes = $_POST['notes'];

    if ($individual_id && $service_type && $service_date) {
        try {
            $stmt = $pdo->prepare("INSERT INTO health_records (individual_id, service_type, service_date, staff_name, notes) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$individual_id, $service_type, $service_date, $staff_name, $notes]);
            $success = "Health entry successfully logged!";
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}

// Fetch residents for dropdown
$residents = $pdo->query("SELECT id, fname, lname FROM individuals ORDER BY fname")->fetchAll();
?>

<div class="mb-3">
    <a href="health_list.php" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold shadow-sm">
        <i class="fas fa-arrow-left me-2"></i><?php echo __('back_to_registry', 'Back to Registry'); ?>
    </a>
</div>

<div class="card border-0 shadow-lg rounded-4 overflow-hidden max-width-800 mx-auto">
    <div class="card-header bg-info text-white p-4 border-0">
        <h4 class="fw-black mb-0"><i class="fas fa-notes-medical me-2"></i><?php echo __('log_new_health_service', 'Log New Health Service'); ?></h4>
    </div>
    
    <div class="card-body p-4 p-md-5">
        <?php if($success): ?>
            <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4"><i class="fas fa-check-circle me-2"></i><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" class="row g-4">
            <div class="col-md-12">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('resident_name', 'Resident Name'); ?> <span class="text-danger">*</span></label>
                <select name="individual_id" class="form-select rounded-pill px-4 bg-light border-0 py-2 shadow-sm" required>
                    <option value=""><?php echo __('-- select_resident --', '-- Select Resident --'); ?></option>
                    <?php foreach ($residents as $r): ?>
                        <option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars("{$r['fname']} {$r['lname']} (ID: #{$r['id']})"); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('service_type', 'Service Type'); ?> <span class="text-danger">*</span></label>
                <select name="service_type" class="form-select rounded-pill px-4 bg-light border-0 py-2 shadow-sm" required>
                    <option value="Vaccination"><?php echo __('vaccination', 'Vaccination'); ?></option>
                    <option value="Maternal Health"><?php echo __('maternal_health', 'Maternal Health'); ?></option>
                    <option value="General Checkup"><?php echo __('general_checkup', 'General Checkup'); ?></option>
                    <option value="Clinic Referral"><?php echo __('clinic_referral', 'Clinic Referral'); ?></option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('service_date', 'Service Date'); ?> <span class="text-danger">*</span></label>
                <input type="date" name="service_date" class="form-control rounded-pill px-4 bg-light border-0 py-2 shadow-sm" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="col-md-12">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('provider', 'Health Staff / Provider Name'); ?></label>
                <input type="text" name="staff_name" class="form-control rounded-pill px-4 bg-light border-0 py-2 shadow-sm" placeholder="<?php echo __('enter_staff_name', 'Enter staff name...'); ?>">
            </div>

            <div class="col-md-12">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('notes', 'Service Notes'); ?></label>
                <textarea name="notes" class="form-control rounded-4 px-4 bg-light border-0 py-3 shadow-sm" rows="3" placeholder="<?php echo __('service_notes_placeholder', 'Additional details, referral destination, etc.'); ?>"></textarea>
            </div>

            <div class="col-12 mt-5">
                <button type="submit" class="btn btn-info text-white w-100 rounded-pill py-3 fw-black shadow-lg">
                    <i class="fas fa-save me-2"></i><?php echo __('save_health_entry', 'SAVE HEALTH ENTRY'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.max-width-800 { max-width: 800px; }
</style>

<?php require_once '../../includes/footer.php'; ?>
