<?php
// modules/justice/police_idcard.php — Issue/Generate Police ID Card with Payment
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/payment_handler.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../auth/login.php'); exit; }

// Officers who don't yet have an active unexpired police ID
$officers = $pdo->query("
    SELECT pr.id, pr.badge_number, pr.rank, i.fname, i.lname
    FROM police_records pr
    JOIN individuals i ON pr.individual_id = i.id
    WHERE pr.id NOT IN (
        SELECT police_record_id FROM police_id_cards
        WHERE status = 'Active' AND expiry_date >= CURDATE()
    )
    ORDER BY i.fname
")->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $police_record_id = $_POST['police_record_id'] ?? '';
    if (empty($police_record_id)) { $error = 'Please select an officer.'; }
    else {
        try {
            $pdo->beginTransaction();

            // Get individual_id for payment
            $pr = $pdo->prepare("SELECT individual_id FROM police_records WHERE id = ?");
            $pr->execute([$police_record_id]);
            $pr_row = $pr->fetch();
            $individual_id = $pr_row['individual_id'];

            // Process payment
            processPaymentSubmission($pdo, $individual_id, 'police_id');
            $txn_id = $pdo->lastInsertId();

            // Expire any old card
            $pdo->prepare("UPDATE police_id_cards SET status = 'Expired' WHERE police_record_id = ? AND status = 'Active'")->execute([$police_record_id]);

            // Generate new ID number
            $last = $pdo->query("SELECT id_num FROM police_id_cards WHERE id_num LIKE 'POL%' ORDER BY id DESC LIMIT 1")->fetchColumn();
            $seq = $last ? (intval(substr($last, 3)) + 1) : 1;
            $id_num = 'POL' . str_pad($seq, 4, '0', STR_PAD_LEFT);
            $issue = date('Y-m-d');
            $expiry = date('Y-m-d', strtotime('+3 years'));

            $pdo->prepare("INSERT INTO police_id_cards (police_record_id, id_num, issue_date, expiry_date, status, transaction_id) VALUES (?, ?, ?, ?, 'Active', ?)")
                ->execute([$police_record_id, $id_num, $issue, $expiry, $txn_id ?: null]);

            // Audit log
            if (isset($_SESSION['user_id'])) {
                $pdo->prepare("INSERT INTO audit_logs (user_id, action, details) VALUES (?, 'CREATE', ?)")
                    ->execute([$_SESSION['user_id'], "Issued Police ID Card: $id_num"]);
            }

            $pdo->commit();
            header("Location: police_list.php?success=Police+ID+Card+issued+successfully!+ID:+$id_num");
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

$preselect = $_GET['officer'] ?? $_GET['reapply'] ?? '';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-id-card-clip me-2 text-warning"></i>Issue Police ID Card</h2>
    <a href="police_list.php" class="btn btn-outline-secondary rounded-pill"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
</div>

<?php if ($error): ?>
<div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4"><i class="fas fa-triangle-exclamation me-2"></i><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST" id="issueForm" enctype="multipart/form-data">
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-header bg-white py-3 border-bottom rounded-top-4">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-user-shield me-2 text-warning"></i>1. Select Officer</h6>
                </div>
                <div class="card-body p-4">
                    <label class="form-label fw-bold small text-uppercase text-muted">Officer <span class="text-danger">*</span></label>
                    <select name="police_record_id" class="form-select border-warning py-2" id="officerSelect" required>
                        <option value="">-- Select Officer --</option>
                        <?php foreach ($officers as $o): ?>
                            <option value="<?php echo $o['id']; ?>"
                                data-name="<?php echo htmlspecialchars($o['fname'].' '.$o['lname']); ?>"
                                <?php echo ($preselect == $o['id'] ? 'selected' : ''); ?>>
                                <?php echo htmlspecialchars("{$o['fname']} {$o['lname']} — {$o['rank']} ({$o['badge_number']})"); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text mt-2 small"><i class="fas fa-info-circle me-1"></i>Only officers without an active unexpired ID are listed.</div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div id="paymentArea" class="h-100">
                <div class="mb-3 text-muted small fw-bold text-uppercase">
                    <i class="fas fa-credit-card me-2"></i>2. Payment Processing
                </div>
                <?php displayPaymentGateway('police_id', 0, 'Select Officer...'); ?>
                <div class="mt-4 text-end">
                    <button type="submit" id="submitBtn" class="btn btn-warning px-5 py-3 fw-bold rounded-pill shadow-lg" disabled>
                        <i class="fas fa-id-card me-2"></i>Finalize Payment & Issue Police ID
                    </button>
                    <div id="selectHint" class="text-danger small mt-2 fw-bold">
                        <i class="fas fa-arrow-left me-1"></i>Please select an officer to activate payment
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
(function() {
    const sel = document.getElementById('officerSelect');
    const submitBtn = document.getElementById('submitBtn');
    const bName = document.getElementById('payment-target-name');
    const hint = document.getElementById('selectHint');
    const payResId = document.getElementById('payment_resident_id');

    sel.addEventListener('change', function() {
        if (this.value) {
            const name = this.options[this.selectedIndex].dataset.name;
            if(bName) bName.textContent = name;
            submitBtn.disabled = false;
            hint.classList.add('d-none');
        } else {
            if(bName) bName.textContent = 'Select Officer...';
            submitBtn.disabled = true;
            hint.classList.remove('d-none');
        }
    });

    if(sel.value) sel.dispatchEvent(new Event('change'));
})();
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
