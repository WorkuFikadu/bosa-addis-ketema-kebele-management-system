<?php
// modules/economic/agriculture_create.php — Detailed Land & Agriculture Registry
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../auth/login.php'); exit; }

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $land_owner_id = $_POST['land_owner_id'];
    $plot_number = $_POST['plot_number'];
    $land_size_sqm = $_POST['land_size_sqm'];
    $land_use = $_POST['land_use'];
    $plot_status = $_POST['plot_status'];
    $main_crops = $_POST['main_crops'];
    $livestock_summary = $_POST['livestock_summary'];
    $water_source = $_POST['water_source'];
    $soil_type = $_POST['soil_type'];
    $fertilizer_received = $_POST['fertilizer_received'] ?: 0;
    $seed_received = $_POST['seed_received'] ?: 0;
    $input_payment_status = $_POST['input_payment_status'];

    if ($land_owner_id && $land_use) {
        try {
            $stmt = $pdo->prepare("INSERT INTO economic_agriculture (land_owner_id, plot_number, land_size_sqm, land_use, plot_status, main_crops, livestock_summary, water_source, soil_type, fertilizer_received, seed_received, input_payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$land_owner_id, $plot_number, $land_size_sqm, $land_use, $plot_status, $main_crops, $livestock_summary, $water_source, $soil_type, $fertilizer_received, $seed_received, $input_payment_status]);
            $success = "Detailed Land & Agriculture data recorded!";
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
    <a href="agriculture_list.php" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold shadow-sm">
        <i class="fas fa-arrow-left me-2"></i><?php echo __('back_to_land_dashboard', 'Back to Land Dashboard'); ?>
    </a>
</div>

<div class="card border-0 shadow-lg rounded-4 overflow-hidden max-width-1000 mx-auto">
    <div class="card-header bg-danger text-white p-4 border-0 text-center">
        <h4 class="fw-black mb-0"><i class="fas fa-tractor me-2"></i><?php echo __('comprehensive_land_agri_profile', 'Comprehensive Land & Agriculture Profile'); ?></h4>
        <p class="mb-0 small opacity-75"><?php echo __('land_resource_management_desc', 'Integrated Land Tenure and Agricultural Resource Management'); ?></p>
    </div>
    
    <div class="card-body p-4 p-md-5">
        <?php if($success): ?>
            <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4"><i class="fas fa-check-circle me-2"></i><?php echo __('agriculture_data_success', 'Detailed Land & Agriculture data recorded!'); ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" class="row g-4">
            <!-- SECTION 1: LAND TENURE -->
            <div class="col-12"><h6 class="fw-black text-danger border-bottom pb-2 mb-0"><?php echo __('land_details', '1. LAND TENURE & GEOGRAPHY'); ?></h6></div>
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('land_owner_farmer', 'Owner / Farmer Name'); ?> <span class="text-danger">*</span></label>
                <select name="land_owner_id" class="form-select rounded-pill px-4 bg-light border-0 py-2 shadow-sm" required>
                    <option value=""><?php echo __('-- select_person --', '-- Choose Person --'); ?></option>
                    <?php foreach ($residents as $r): ?>
                        <option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars("{$r['fname']} {$r['lname']} (ID: #{$r['id']})"); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('plot_num'); ?></label>
                <input type="text" name="plot_number" class="form-control rounded-pill px-4 bg-light border-0 py-2 shadow-sm" placeholder="e.g. PN-442">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('land_size_sqm', 'Land Size (sqm)'); ?></label>
                <input type="number" step="0.01" name="land_size_sqm" class="form-control rounded-pill px-4 bg-light border-0 py-2 shadow-sm">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('primary_land_use', 'Primary Land Use'); ?></label>
                <select name="land_use" class="form-select rounded-pill px-4 bg-light border-0 py-2 shadow-sm" required>
                    <option value="Farmland"><?php echo __('farmland'); ?></option>
                    <option value="Residential"><?php echo __('residential'); ?></option>
                    <option value="Commercial"><?php echo __('commercial'); ?></option>
                    <option value="Mixed"><?php echo __('mixed'); ?></option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('plot_status', 'Plot Current Status'); ?></label>
                <select name="plot_status" class="form-select rounded-pill px-4 bg-light border-0 py-2 shadow-sm">
                    <option value="Active"><?php echo __('active_productive', 'Active / Productive'); ?></option>
                    <option value="Idle"><?php echo __('idle_fallow', 'Idle / Fallow'); ?></option>
                    <option value="Disputed"><?php echo __('legal_dispute', 'Legal Dispute'); ?></option>
                    <option value="Rented"><?php echo __('rented_third_party', 'Rented to Third-Party'); ?></option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('soil_classification', 'Soil Classification'); ?></label>
                <input type="text" name="soil_type" class="form-control rounded-pill px-4 bg-light border-0 py-2 shadow-sm" placeholder="e.g. Black Cotton, Red Clay...">
            </div>

            <!-- SECTION 2: CROP & LIVESTOCK -->
            <div class="col-12 mt-5"><h6 class="fw-black text-danger border-bottom pb-2 mb-0">2. <?php echo __('crop_livestock_production_section', 'CROP & LIVESTOCK PRODUCTION'); ?></h6></div>
            <div class="col-md-8">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('main_crops_produced', 'Main Crops Produced'); ?></label>
                <input type="text" name="main_crops" class="form-control rounded-pill px-4 bg-light border-0 py-2 shadow-sm" placeholder="e.g. Wheat, Barley, Teff, Maize">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('water_source_label', 'Water Source'); ?></label>
                <select name="water_source" class="form-select rounded-pill px-4 bg-light border-0 py-2 shadow-sm">
                    <option value="Rainfed"><?php echo __('rain_fed_only', 'Rain-fed Only'); ?></option>
                    <option value="Irrigation"><?php echo __('kebele_irrigation_scheme', 'Kebele Irrigation Scheme'); ?></option>
                    <option value="River"><?php echo __('river_diversion', 'River Diversion'); ?></option>
                    <option value="Well"><?php echo __('private_well_borehole', 'Private Well / Borehole'); ?></option>
                </select>
            </div>
            <div class="col-md-12">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('livestock_summary_label', 'Livestock Summary (Count/Type)'); ?></label>
                <textarea name="livestock_summary" class="form-control rounded-4 px-4 bg-light border-0 py-3 shadow-sm" rows="2" placeholder="e.g. Cattle: 4, Sheep: 10, Goats: 5..."></textarea>
            </div>

            <!-- SECTION 3: INPUT LOGGING -->
            <div class="col-12 mt-5"><h6 class="fw-black text-danger border-bottom pb-2 mb-0"><?php echo __('input_distribution', '3. SEASONAL INPUT DISTRIBUTION'); ?></h6></div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('fertilizer_qtl', 'Fertilizer (Quintal)'); ?></label>
                <input type="number" step="0.01" name="fertilizer_received" class="form-control rounded-pill px-4 bg-light border-0 py-2 shadow-sm" placeholder="0.00">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('seed_kg', 'Improved Seed (Kg)'); ?></label>
                <input type="number" step="0.01" name="seed_received" class="form-control rounded-pill px-4 bg-light border-0 py-2 shadow-sm" placeholder="0.00">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('input_payment_status', 'Input Payment Status'); ?></label>
                <select name="input_payment_status" class="form-select rounded-pill px-4 bg-light border-0 py-2 shadow-sm">
                    <option value="Government Support"><?php echo __('government_support_free', 'Government Support (Free)'); ?></option>
                    <option value="Fully Paid"><?php echo __('fully_paid'); ?></option>
                    <option value="Partial"><?php echo __('partial_payment', 'Partial Payment'); ?></option>
                    <option value="Credit"><?php echo __('on_credit', 'On Credit'); ?></option>
                </select>
            </div>

            <div class="col-12 mt-5">
                <button type="submit" class="btn btn-danger text-white w-100 rounded-pill py-3 fw-black shadow-lg">
                    <i class="fas fa-save me-2"></i><?php echo __('save_agri_report_btn', 'SAVE COMPREHENSIVE AGRICULTURE REPORT'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.max-width-1000 { max-width: 1000px; }
</style>

<?php require_once '../../includes/footer.php'; ?>
