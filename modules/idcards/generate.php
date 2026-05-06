<?php
// modules/idcards/generate.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

require_once __DIR__ . '/../../includes/payment_handler.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

// Get residents who don't have an ACTIVE ID card
// (They either have no ID at all, or their status is 'Lost' or 'Expired')
$residents = $pdo->query("SELECT id, fname, lname 
                          FROM individuals 
                          WHERE id NOT IN (SELECT resident_id FROM id_cards WHERE status = 'Active') 
                          ORDER BY fname")->fetchAll();

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resident_id = $_POST['resident_id'];
    $issue_date = date('Y-m-d');
    
    try {
        $pdo->beginTransaction();

        // 1. Process Digital Payment (If provided)
        processPaymentSubmission($pdo, $resident_id, 'id_card');
        $transaction_id = $pdo->lastInsertId();

        // 2. Generate ID Card
        $lastIdStmt = $pdo->prepare("SELECT id_num FROM id_cards WHERE id_num LIKE 'IB%' ORDER BY id DESC LIMIT 1");
        $lastIdStmt->execute();
        $lastId = $lastIdStmt->fetchColumn();
        $nextNumber = $lastId ? (intval(substr($lastId, 2)) + 1) : 0;
        $sequence = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        $id_num = "IB$sequence";
        $expiry_date = date('Y-m-d', strtotime('+5 years'));

        // If re-applying, we want to ensure any old records for this resident are no longer 'Active'
        $pdo->prepare("UPDATE id_cards SET status = 'Expired' WHERE resident_id = ? AND status = 'Active'")->execute([$resident_id]);

        $stmt = $pdo->prepare("INSERT INTO id_cards (resident_id, id_num, issue_date, expiry_date, status, transaction_id) VALUES (?, ?, ?, ?, 'Active', ?)");
        $stmt->execute([$resident_id, $id_num, $issue_date, $expiry_date, $transaction_id]);
        $id_card_db_id = $pdo->lastInsertId();

        $pdo->commit();
        header("Location: index.php?success=ID Card generated and payment recorded successfully! ID Number: $id_num");
        exit;
    } catch (PDOException $e) {
        if($pdo->inTransaction()) $pdo->rollBack();
        $error = "Failed to process: " . $e->getMessage();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-id-card-clip me-2 text-primary"></i>Issue New ID Card</h2>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
</div>

<?php if ($success): ?>
    <div class="alert alert-success d-flex align-items-center border-0 shadow-sm mb-4">
        <i class="fas fa-check-circle fa-2x me-3"></i>
        <div>
            <?php echo $success; ?><br>
            <a href="print.php?id=<?php echo $id_card_db_id; ?>" class="btn btn-sm btn-success mt-2" target="_blank">Print ID Now</a>
        </div>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger border-0 shadow-sm mb-4"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST" id="issueForm" enctype="multipart/form-data">
    <div class="row g-4">
        <!-- Left Column: Resident Selection -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-user-check me-2 text-primary"></i>1. Resident Selection</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-muted">Beneficiary / Resident <span class="text-danger">*</span></label>
                        <select name="resident_id" class="form-select border-primary py-2" id="residentSelect" required>
                            <option value="">-- Search & Choose Resident --</option>
                            <?php foreach ($residents as $r): 
                                $selected = (isset($_GET['reapply']) && $_GET['reapply'] == $r['id']) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $r['id']; ?>" data-name="<?php echo htmlspecialchars($r['fname'].' '.$r['lname']); ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($r['fname'].' '.$r['lname']); ?> (UID: #<?php echo $r['id']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text mt-2 text-muted small" style="font-size: 0.7rem;">
                            <i class="fas fa-info-circle me-1"></i> Only residents eligible for a new or replacement ID are listed.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Payment & Generation -->
        <div class="col-lg-7">
            <div id="paymentArea" class="h-100">
                <div class="mb-3 text-muted small fw-bold text-uppercase">
                    <i class="fas fa-credit-card me-2"></i>2. Payment Processing
                </div>
                
                <?php displayPaymentGateway('id_card', 0, 'Select Resident...'); ?>
                
                <div class="mt-4 text-end">
                    <button type="submit" id="submitBtn" class="btn btn-primary px-5 py-3 fw-bold rounded-pill shadow-lg" disabled>
                        <i class="fas fa-id-card me-2"></i>Finalize Payment & Generate ID
                    </button>
                    <div id="selectHint" class="text-danger small mt-2 fw-bold animate__animated animate__pulse animate__infinite">
                        <i class="fas fa-arrow-left me-1"></i> Please select a resident to activate payment
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
(function() {
    const sel = document.getElementById('residentSelect');
    const submitBtn = document.getElementById('submitBtn');
    const bName = document.getElementById('payment-target-name');
    const hint = document.getElementById('selectHint');
    const payResId = document.getElementById('payment_resident_id');
    
    sel.addEventListener('change', function() {
        if (this.value) {
            const name = this.options[this.selectedIndex].dataset.name;
            bName.textContent = name;
            bName.classList.add('text-primary');
            submitBtn.disabled = false;
            hint.classList.add('d-none');
            if(payResId) payResId.value = this.value;
            
            // Highlight the payment section
            const paySec = document.getElementById('paymentSection');
            if(paySec) {
                paySec.style.boxShadow = '0 0 15px rgba(13, 110, 253, 0.2)';
                paySec.classList.add('animate__animated', 'animate__pulse');
                setTimeout(() => paySec.classList.remove('animate__pulse'), 1000);
            }
        } else {
            bName.textContent = 'Select Resident...';
            bName.classList.remove('text-primary');
            submitBtn.disabled = true;
            hint.classList.remove('d-none');
        }
    });

    if(sel.value) sel.dispatchEvent(new Event('change'));
})();
</script>


<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
