<?php
// modules/economic/enterprise_create.php — Detailed SME Enterprise Registration
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../auth/login.php'); exit; }

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $owner_id = $_POST['owner_id'];
    $enterprise_name = $_POST['enterprise_name'];
    $business_tin = $_POST['business_tin'];
    $initial_capital = $_POST['initial_capital'] ?: 0;
    $ownership_type = $_POST['ownership_type'];
    $business_type = $_POST['business_type'];
    $business_category = $_POST['business_category'];
    $employee_count_m = $_POST['employee_count_m'] ?: 0;
    $employee_count_f = $_POST['employee_count_f'] ?: 0;
    $id_number = $_POST['id_number'];
    $location_details = $_POST['location_details'];
    $registration_date = $_POST['registration_date'];

    if ($owner_id && $enterprise_name && $id_number) {
        try {
            $stmt = $pdo->prepare("INSERT INTO economic_enterprises (owner_id, enterprise_name, business_tin, initial_capital, ownership_type, business_type, business_category, employee_count_m, employee_count_f, id_number, location_details, registration_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$owner_id, $enterprise_name, $business_tin, $initial_capital, $ownership_type, $business_type, $business_category, $employee_count_m, $employee_count_f, $id_number, $location_details, $registration_date]);
            $success = "Detailed Enterprise Registration complete!";
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}

$residents = $pdo->query("SELECT id, fname, lname FROM individuals ORDER BY fname")->fetchAll();
?>

<div class="mb-3">
    <a href="enterprise_list.php" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold shadow-sm">
        <i class="fas fa-arrow-left me-2"></i><?php echo __('back_to_sme_list', 'Back to SME List'); ?>
    </a>
</div>

<div class="card border-0 shadow-lg rounded-4 overflow-hidden max-width-1000 mx-auto">
    <div class="card-header bg-danger text-white p-4 border-0 text-center">
        <h4 class="fw-black mb-0"><i class="fas fa-building-circle-check me-2"></i><?php echo __('comprehensive_enterprise_reg', 'Comprehensive Enterprise Registration'); ?></h4>
        <p class="mb-0 small opacity-75"><?php echo __('mse_documentation_desc', 'Full Documentation for Micro and Small Enterprises (MSE)'); ?></p>
    </div>
    
    <div class="card-body p-4 p-md-5">
        <?php if($success): ?>
            <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4"><i class="fas fa-check-circle me-2"></i><?php echo __('registration_complete_msg'); ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" class="row g-4">
            <!-- SECTION 1: BUSINESS IDENTITY -->
            <div class="col-12"><h6 class="fw-black text-danger border-bottom pb-2 mb-0"><?php echo __('business_identity_section'); ?></h6></div>
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('enterprise_name', 'Enterprise Name'); ?> <span class="text-danger">*</span></label>
                <input type="text" name="enterprise_name" class="form-control rounded-pill px-4 bg-light border-0 py-2 shadow-sm" placeholder="<?php echo __('business_legal_name_placeholder', 'Legal name of business...'); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('owner_manager', 'Primary Owner / Manager'); ?> <span class="text-danger">*</span></label>
                <select name="owner_id" class="form-select rounded-pill px-4 bg-light border-0 py-2 shadow-sm" required>
                    <option value=""><?php echo __('-- select_resident --'); ?></option>
                    <?php foreach ($residents as $r): ?>
                        <option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars("{$r['fname']} {$r['lname']} (ID: #{$r['id']})"); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('ownership_structure'); ?></label>
                <select name="ownership_type" class="form-select rounded-pill px-4 bg-light border-0 py-2 shadow-sm">
                    <option value="Individual"><?php echo __('individual_proprietorship', 'Individual Propriertorship'); ?></option>
                    <option value="Family Business"><?php echo __('family_business', 'Family Business'); ?></option>
                    <option value="Association"><?php echo __('association', 'Group / Association'); ?></option>
                    <option value="Cooperative"><?php echo __('cooperative', 'Cooperative Society'); ?></option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('business_tin'); ?></label>
                <input type="text" name="business_tin" class="form-control rounded-pill px-4 bg-light border-0 py-2 shadow-sm" placeholder="<?php echo __('taxpayer_id_placeholder', 'Taxpayer ID...'); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('initial_capital'); ?></label>
                <input type="number" step="0.01" name="initial_capital" class="form-control rounded-pill px-4 bg-light border-0 py-2 shadow-sm" placeholder="0.00">
            </div>

            <!-- SECTION 2: OPERATIONAL FOCUS -->
            <div class="col-12 mt-5"><h6 class="fw-black text-danger border-bottom pb-2 mb-0"><?php echo __('operational_focus_section'); ?></h6></div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('sector_category'); ?></label>
                <select name="business_category" class="form-select rounded-pill px-4 bg-light border-0 py-2 shadow-sm">
                    <option value="Retail/Trade"><?php echo __('retail_trade', 'Retail / Trade'); ?></option>
                    <option value="Service"><?php echo __('service_sector'); ?></option>
                    <option value="Manufacturing"><?php echo __('manufacturing_production', 'Manufacturing / Production'); ?></option>
                    <option value="Agriculture"><?php echo __('commercial_agriculture', 'Commercial Agriculture'); ?></option>
                    <option value="Urban Farming"><?php echo __('urban_farming', 'Urban Farming'); ?></option>
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('main_activity'); ?> <span class="text-danger">*</span></label>
                <input type="text" name="business_type" class="form-control rounded-pill px-4 bg-light border-0 py-2 shadow-sm" placeholder="<?php echo __('business_type_examples', 'e.g. Woodwork, Boutique, Cafe...'); ?>" required>
            </div>
            <div class="col-md-6">
                <div class="p-3 bg-light rounded-4 border-start border-danger border-4">
                    <label class="form-label fw-bold small text-muted text-uppercase d-block mb-3"><?php echo __('workforce'); ?></label>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="small text-muted fw-bold"><?php echo __('male_employees', 'Male Employees'); ?></label>
                            <input type="number" name="employee_count_m" class="form-control border-0 shadow-sm rounded-pill" value="0">
                        </div>
                        <div class="col-6">
                             <label class="small text-muted fw-bold"><?php echo __('female_employees', 'Female Employees'); ?></label>
                             <input type="number" name="employee_count_f" class="form-control border-0 shadow-sm rounded-pill" value="0">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                 <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('office_location'); ?></label>
                 <textarea name="location_details" class="form-control rounded-4 px-4 bg-light border-0 py-2 shadow-sm" rows="3" placeholder="<?php echo __('location_details_placeholder', 'Specific location details within the Kebele...'); ?>"></textarea>
            </div>

            <!-- SECTION 3: LEGAL STATUS -->
            <div class="col-12 mt-5"><h6 class="fw-black text-danger border-bottom pb-2 mb-0"><?php echo __('legal_id_section'); ?></h6></div>
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('trade_license'); ?> <span class="text-danger">*</span></label>
                <input type="text" name="id_number" class="form-control rounded-pill px-4 bg-light border-0 py-2 shadow-sm" placeholder="<?php echo __('official_ref_placeholder', 'Official Reference No...'); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('registration_date'); ?></label>
                <input type="date" name="registration_date" class="form-control rounded-pill px-4 bg-light border-0 py-2 shadow-sm" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="col-12 mt-5">
                <button type="submit" class="btn btn-danger text-white w-100 rounded-pill py-3 fw-black shadow-lg">
                    <i class="fas fa-store me-2"></i><?php echo __('reg_enterprise_btn'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.max-width-1000 { max-width: 1000px; }
</style>

<?php require_once '../../includes/footer.php'; ?>
