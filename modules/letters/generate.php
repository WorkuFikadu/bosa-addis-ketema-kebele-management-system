<?php
// modules/letters/generate.php
require_once '../../includes/header.php';
require_once '../../config/database.php';
require_once '../../includes/payment_handler.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../auth/login.php'); exit; }

$type = $_GET['type'] ?? 'Residency';
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resident_id = $_POST['resident_id'];
    $letter_type = $_POST['letter_type'];
    $purpose = $_POST['purpose'];
    $issue_date = date('Y-m-d');
    
    // Generate Ref Number: KBL/LTR/[TYPE]/[YYYY]/[SEQ]
    $prefix = "KBL/" . strtoupper(substr($letter_type, 0, 3)) . "/";
    $year = date('Y');
    $last = $pdo->query("SELECT ref_number FROM generated_letters WHERE ref_number LIKE '$prefix$year%' ORDER BY id DESC LIMIT 1")->fetchColumn();
    $seq = $last ? (intval(substr($last, -4)) + 1) : 1;
    $ref_number = $prefix . $year . "/" . str_pad($seq, 4, '0', STR_PAD_LEFT);

    if ($resident_id && $letter_type) {
        try {
            $pdo->beginTransaction();
            
            // Payment handling mapping
            $pay_key = 'letter_' . strtolower($letter_type);
            processPaymentSubmission($pdo, $resident_id, $pay_key);

            $stmt = $pdo->prepare("INSERT INTO generated_letters (resident_id, letter_type, ref_number, purpose, issue_date, issued_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$resident_id, $letter_type, $ref_number, $purpose, $issue_date, $_SESSION['user_id']]);
            $new_id = $pdo->lastInsertId();
            
            $pdo->commit();
            header("Location: print.php?id=$new_id"); exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}

$residents = $pdo->query("SELECT id, fname, lname FROM individuals ORDER BY fname")->fetchAll();
?>

<div class="mb-3">
    <a href="index.php" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold shadow-sm">
        <i class="fas fa-arrow-left me-2"></i><?php echo __('back_to_letters'); ?>
    </a>
</div>

<div class="card border-0 shadow-lg rounded-4 overflow-hidden max-width-700 mx-auto">
    <div class="card-header bg-primary text-white p-4 border-0 text-center">
        <h4 class="fw-black mb-0"><i class="fas fa-stamp me-2"></i><?php echo sprintf(__('issue_letter_title'), __($type)); ?></h4>
        <p class="mb-0 small opacity-75"><?php echo __('official_correspondence'); ?></p>
    </div>
    
    <div class="card-body p-4 p-md-5">
        <?php if($error): ?>
            <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" class="row g-4" id="letterForm">
            <div class="col-md-12">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('recipient_resident'); ?> <span class="text-danger">*</span></label>
                <select name="resident_id" class="form-select rounded-pill px-4 bg-light border-0 py-2 shadow-sm" id="residentSelect" required>
                    <option value=""><?php echo __('select_resident_placeholder'); ?></option>
                    <?php foreach ($residents as $r): ?>
                        <option value="<?php echo $r['id']; ?>" data-name="<?php echo htmlspecialchars($r['fname'].' '.$r['lname']); ?>"><?php echo htmlspecialchars("{$r['fname']} {$r['lname']} (ID: #{$r['id']})"); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-12">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('type'); ?> <span class="text-danger">*</span></label>
                <select name="letter_type" class="form-select rounded-pill px-4 bg-light border-0 py-3 shadow-sm fw-bold" required>
                    <option value="Residency" <?php echo $type==='Residency'?'selected':''; ?>><?php echo __('residency_cert'); ?></option>
                    <option value="Conduct" <?php echo $type==='Conduct'?'selected':''; ?>><?php echo __('conduct_letter'); ?></option>
                    <option value="Verification" <?php echo $type==='Verification'?'selected':''; ?>><?php echo __('person_verify'); ?></option>
                    <option value="Clearance" <?php echo $type==='Clearance'?'selected':''; ?>><?php echo __('admin_clearance'); ?></option>
                </select>
            </div>

            <div class="col-md-12">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('purpose_label'); ?></label>
                <textarea name="purpose" class="form-control rounded-4 px-4 bg-light border-0 py-3 shadow-sm" rows="2" placeholder="<?php echo __('purpose_placeholder'); ?>"></textarea>
            </div>

            <div class="col-md-12 mt-4 bg-light p-3 rounded-4">
                 <label class="form-label fw-bold small text-muted text-uppercase mb-3"><?php echo __('service_fee_verify'); ?></label>
                 <?php displayPaymentGateway('letter_residency', 0, 'Select Resident...'); ?>
            </div>

            <div class="col-12 mt-5">
                <button type="submit" class="btn btn-primary text-white w-100 rounded-pill py-3 fw-black shadow-lg">
                    <i class="fas fa-file-export me-2"></i><?php echo __('gen_print_btn'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('residentSelect').addEventListener('change', function() {
    const nameSpan = document.getElementById('payment-target-name');
    if (this.value) {
        if(nameSpan) nameSpan.textContent = this.options[this.selectedIndex].dataset.name;
    } else {
        if(nameSpan) nameSpan.textContent = 'Select Resident...';
    }
});

// Dynamic price update if needed, but displayPaymentGateway handles static key
</script>

<style>
.max-width-700 { max-width: 700px; }
</style>

<?php require_once '../../includes/footer.php'; ?>
