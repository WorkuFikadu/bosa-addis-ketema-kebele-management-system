<?php
// modules/economic/subsidy_create.php — Detailed Commodity Distribution Log
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../auth/login.php'); exit; }

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resident_id = $_POST['resident_id'];
    $commodity = $_POST['commodity'];
    $distribution_point = $_POST['distribution_point'];
    $distribution_period = $_POST['distribution_period'];
    $quantity = $_POST['quantity'];
    $payment_mode = $_POST['payment_mode'];
    $recipient_type = $_POST['recipient_type'];
    $proxy_name = $_POST['proxy_name'] ?: NULL;
    $distribution_date = $_POST['distribution_date'];
    $approval_status = $_POST['approval_status'];

    if ($resident_id && $commodity && $quantity) {
        try {
            $stmt = $pdo->prepare("INSERT INTO economic_subsidies (resident_id, commodity, distribution_point, distribution_period, quantity, payment_mode, recipient_type, proxy_name, distribution_date, approval_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$resident_id, $commodity, $distribution_point, $distribution_period, $quantity, $payment_mode, $recipient_type, $proxy_name, $distribution_date, $approval_status]);
            $success = "Detailed Commodity Distribution logged successfully!";
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
    <a href="subsidy_list.php" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold shadow-sm">
        <i class="fas fa-arrow-left me-2"></i><?php echo __('back_to_distribution_log', 'Back to Distribution Log'); ?>
    </a>
</div>

<div class="card border-0 shadow-lg rounded-4 overflow-hidden max-width-900 mx-auto">
    <div class="card-header bg-danger text-white p-4 border-0 text-center">
        <h4 class="fw-black mb-0"><i class="fas fa-box-open me-2"></i><?php echo __('commodity_distribution_log', 'Detailed Commodity Distribution Log'); ?></h4>
        <p class="mb-0 small opacity-75"><?php echo __('subsidy_tracking_desc', 'Precision Tracking for Essential Kebele Subsidies'); ?></p>
    </div>
    
    <div class="card-body p-4 p-md-5">
        <?php if($success): ?>
            <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4"><i class="fas fa-check-circle me-2"></i><?php echo __('distribution_log_success', 'Detailed Commodity Distribution logged successfully!'); ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" class="row g-4">
            <!-- SECTION 1: RECIPIENT & PERIOD -->
            <div class="col-12"><h6 class="fw-black text-danger border-bottom pb-2 mb-0"><?php echo __('recipient_period_section', '1. RECIPIENT & TIME PERIOD'); ?></h6></div>
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('subsidized_resident', 'Subsidized Resident'); ?> <span class="text-danger">*</span></label>
                <select name="resident_id" class="form-select rounded-pill px-4 bg-light border-0 py-2 shadow-sm" required>
                    <option value=""><?php echo __('-- select_resident --'); ?></option>
                    <?php foreach ($residents as $r): ?>
                        <option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars("{$r['fname']} {$r['lname']} (ID: #{$r['id']})"); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                 <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('distribution_date'); ?></label>
                 <input type="date" name="distribution_date" class="form-control rounded-pill px-4 bg-light border-0 py-2 shadow-sm" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="col-md-3">
                 <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('quota_period', 'Quota Period'); ?></label>
                 <input type="text" name="distribution_period" class="form-control rounded-pill px-4 bg-light border-0 py-2 shadow-sm" placeholder="e.g. May 2026" required>
            </div>

            <!-- SECTION 2: COMMODITY DETAILS -->
            <div class="col-12 mt-5"><h6 class="fw-black text-danger border-bottom pb-2 mb-0"><?php echo __('commodity_details_section', '2. COMMODITY & DISTRIBUTION POINT'); ?></h6></div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('item_type', 'Commodity Type'); ?> <span class="text-danger">*</span></label>
                <select name="commodity" class="form-select rounded-pill px-4 bg-light border-0 py-2 shadow-sm" required>
                    <option value="Cooking Oil"><?php echo __('cooking_oil', 'Cooking Oil (Liters)'); ?></option>
                    <option value="Sugar"><?php echo __('refined_sugar', 'Refined Sugar (Kg)'); ?></option>
                    <option value="Wheat"><?php echo __('wheat_grains', 'Wheat Grains (Kg)'); ?></option>
                    <option value="Flour"><?php echo __('wheat_flour', 'Wheat Flour (Kg)'); ?></option>
                    <option value="Soap"><?php echo __('laundry_soap', 'Laundry Soap (pcs)'); ?></option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('quantity'); ?> <span class="text-danger">*</span></label>
                <input type="number" step="0.01" name="quantity" class="form-control rounded-pill px-4 bg-light border-0 py-2 shadow-sm" required>
            </div>
            <div class="col-md-5">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('distribution_point', 'Distribution Point'); ?></label>
                <input type="text" name="distribution_point" class="form-control rounded-pill px-4 bg-light border-0 py-2 shadow-sm" value="<?php echo __('main_kebele_store', 'Main Kebele Cooperative Store'); ?>">
            </div>

            <!-- SECTION 3: RECIPIENT LOGISTICS -->
            <div class="col-12 mt-5"><h6 class="fw-black text-danger border-bottom pb-2 mb-0"><?php echo __('recipient_logistics_section', '3. RECIPIENT MODE & STATUS'); ?></h6></div>
            <div class="col-md-4">
                 <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('payment_mode'); ?></label>
                 <select name="payment_mode" class="form-select rounded-pill px-4 bg-light border-0 py-2 shadow-sm">
                    <option value="Cash"><?php echo __('cash_counter', 'Cash at Counter'); ?></option>
                    <option value="Digital Pay"><?php echo __('digital_pay_cbe', 'CBE Birr / TeleBirr'); ?></option>
                    <option value="Credit"><?php echo __('credit_pre_auth', 'Credit (Pre-auth)'); ?></option>
                    <option value="Free/Voucher"><?php echo __('gov_voucher', 'Government Voucher'); ?></option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('received_by'); ?></label>
                <select name="recipient_type" class="form-select rounded-pill px-4 bg-light border-0 py-2 shadow-sm">
                    <option value="Self"><?php echo __('resident_self', 'Resident (Self)'); ?></option>
                    <option value="Family Member"><?php echo __('family_member_proxy', 'Family Member (Proxy)'); ?></option>
                    <option value="Guardian"><?php echo __('legal_guardian', 'Legal Guardian'); ?></option>
                </select>
            </div>
            <div class="col-md-4">
                 <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('proxy_name_label', 'Proxy Name (If applicable)'); ?></label>
                 <input type="text" name="proxy_name" class="form-control rounded-pill px-4 bg-light border-0 py-2 shadow-sm" placeholder="<?php echo __('proxy_name_placeholder', 'Full Name of Proxy...'); ?>">
            </div>
            
            <div class="col-md-12 mt-3">
                 <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('activity_status'); ?></label>
                 <select name="approval_status" class="form-select rounded-pill px-4 bg-light border-0 py-2 shadow-sm fw-bold text-success">
                    <option value="Distributed"><?php echo __('distributed_final', 'Distributed (Final)'); ?></option>
                    <option value="Logged"><?php echo __('logged_reserved', 'Logged (Reserved)'); ?></option>
                    <option value="In Transit"><?php echo __('dispatched_transit', 'Dispatched (Not Collected)'); ?></option>
                </select>
            </div>

            <div class="col-12 mt-5">
                <button type="submit" class="btn btn-danger text-white w-100 rounded-pill py-3 fw-black shadow-lg">
                    <i class="fas fa-truck-loading me-2"></i><?php echo __('complete_dist_log_btn', 'COMPLETE DISTRIBUTION LOG'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.max-width-900 { max-width: 900px; }
</style>

<?php require_once '../../includes/footer.php'; ?>
