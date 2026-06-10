<?php
// modules/vital/issue_birth.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/payment_handler.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

// Get residents without birth cert
$residents = $pdo->query("SELECT id, fname, lname FROM individuals WHERE id NOT IN (SELECT resident_id FROM vital_certificates WHERE cert_type = 'birth') ORDER BY fname")->fetchAll();

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resident_id = $_POST['resident_id'];
    $issue_date = date('Y-m-d');
    
    try {
        $pdo->beginTransaction();

        // 1. Generate Cert Number
        $lastIdStmt = $pdo->prepare("SELECT cert_number FROM vital_certificates WHERE cert_type = 'birth' AND cert_number LIKE 'BA-BC%' ORDER BY id DESC LIMIT 1");
        $lastIdStmt->execute();
        $lastId = $lastIdStmt->fetchColumn();
        $nextNumber = $lastId ? (intval(substr($lastId, 5)) + 1) : 0;
        $cert_number = "BA-BC" . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
        
        $remarks = $_POST['remarks'] ?? '';

        $stmt = $pdo->prepare("INSERT INTO vital_certificates (resident_id, cert_type, cert_number, issue_date, remarks) VALUES (?, 'birth', ?, ?, ?)");
        $stmt->execute([$resident_id, $cert_number, $issue_date, $remarks]);
        $cert_id = $pdo->lastInsertId();

        $pdo->commit();
        $success = "Birth certificate generated successfully: $cert_number. Please go to the list to process payment and print.";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = "Failed: " . $e->getMessage();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0"><i class="fas fa-baby me-2 text-primary"></i><?php echo __('birth_cert'); ?></h2>
        <p class="text-muted small">Newborn Registration & Civil Registry</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i><?php echo __('back'); ?></a>
</div>

<?php if ($success): ?>
    <div class="alert alert-success d-flex align-items-center border-0 shadow-sm mb-4">
        <i class="fas fa-check-circle me-3 fa-2x"></i>
        <div>
            <strong><?php echo $success; ?></strong><br>
            <a href="index.php" class="btn btn-sm btn-primary mt-2">Go to Records List to Pay & Print</a>
        </div>
    </div>
<?php endif; ?>

<?php if ($error): ?> <div class="alert alert-danger border-0 shadow-sm mb-4"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div> <?php endif; ?>

<form method="POST">
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card p-4 border-0 shadow-sm h-100">
            <h6 class="fw-bold mb-4 border-bottom pb-2">Certificate Details</h6>
            <div class="mb-4">
                <label class="form-label fw-bold"><?php echo __('select_resident'); ?></label>
                <select name="resident_id" class="form-select border-primary" id="residentSelect" required>
                    <option value="">-- <?php echo __('search'); ?> --</option>
                    <?php foreach ($residents as $r): ?>
                        <option value="<?php echo $r['id']; ?>" data-name="<?php echo htmlspecialchars($r['fname'].' '.$r['lname']); ?>">
                            <?php echo "{$r['fname']} {$r['lname']} (#{$r['id']})"; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label class="form-label fw-bold"><?php echo __('remarks'); ?> (<?php echo __('optional'); ?>)</label>
                <textarea name="remarks" class="form-control" rows="3" placeholder="Notes for birth record..."></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-lg">
                <i class="fas fa-save me-2"></i>Finalize Record & Order Service
            </button>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div id="paymentBox" class="d-none">
            <?php displayPaymentGateway('birth_cert', 0, '<span id="selectedName"></span>', true); ?>
        </div>
        
        <div id="placeholder" class="card border-dashed border-2 p-5 text-center text-muted bg-light h-100 d-flex flex-column align-items-center justify-content-center">
            <h6 class="text-primary mb-3 text-uppercase fw-bold"><i class="fas fa-eye me-2"></i>Sample Certificate Preview</h6>
            <img src="https://images.unsplash.com/photo-1544126592-807daa2b56fd?q=80&w=600&auto=format&fit=crop" class="img-fluid rounded shadow-sm border mb-4" style="max-height: 200px; object-fit: cover; opacity: 0.85;">
            <i class="fas fa-info-circle fa-2x mb-2 opacity-25"></i>
            <h5>Select a resident to begin</h5>
            <p class="small">Preview above shows the official certificate format. Payment instructions will appear after selection.</p>
        </div>
    </div>
</div>
</form>

<script>
document.getElementById('residentSelect').addEventListener('change', function() {
    const name = this.options[this.selectedIndex].dataset.name;
    const box = document.getElementById('paymentBox');
    const ph = document.getElementById('placeholder');
    if(this.value) {
        document.getElementById('selectedName').textContent = name;
        box.classList.remove('d-none');
        ph.classList.add('d-none');
    } else {
        box.classList.add('d-none');
        ph.classList.remove('d-none');
    }
});
</script>


<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
