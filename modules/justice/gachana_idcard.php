<?php
// modules/justice/gachana_idcard.php — Issue Gachana Sirna ID Card with Payment
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/payment_handler.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../auth/login.php'); exit; }

// Members without an active gachana ID card
$members = $pdo->query("
    SELECT gr.id, gr.committee_role, gr.sector, i.fname, i.lname, i.id AS individual_id
    FROM gachana_records gr
    JOIN individuals i ON gr.individual_id = i.id
    WHERE gr.id NOT IN (
        SELECT gachana_record_id FROM gachana_id_cards
        WHERE status = 'Active' AND expiry_date >= CURDATE()
    )
    AND gr.status = 'Active'
    ORDER BY i.fname
")->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gachana_record_id = $_POST['gachana_record_id'] ?? '';
    if (empty($gachana_record_id)) { $error = 'Please select a Gachana Sirna member.'; }
    else {
        try {
            $pdo->beginTransaction();
            $gr = $pdo->prepare("SELECT individual_id FROM gachana_records WHERE id = ?");
            $gr->execute([$gachana_record_id]);
            $individual_id = $gr->fetchColumn();

            processPaymentSubmission($pdo, $individual_id, 'gachana_id');
            $txn_id = $pdo->lastInsertId();

            $pdo->prepare("UPDATE gachana_id_cards SET status = 'Expired' WHERE gachana_record_id = ? AND status = 'Active'")->execute([$gachana_record_id]);

            $last = $pdo->query("SELECT id_num FROM gachana_id_cards WHERE id_num LIKE 'GAC%' ORDER BY id DESC LIMIT 1")->fetchColumn();
            $seq = $last ? (intval(substr($last, 3)) + 1) : 1;
            $id_num = 'GAC' . str_pad($seq, 4, '0', STR_PAD_LEFT);
            $issue = date('Y-m-d');
            $expiry = date('Y-m-d', strtotime('+2 years'));

            $pdo->prepare("INSERT INTO gachana_id_cards (gachana_record_id, id_num, issue_date, expiry_date, status, transaction_id) VALUES (?, ?, ?, ?, 'Active', ?)")
                ->execute([$gachana_record_id, $id_num, $issue, $expiry, $txn_id ?: null]);

            if (isset($_SESSION['user_id'])) {
                $pdo->prepare("INSERT INTO audit_logs (user_id, action, details) VALUES (?, 'CREATE', ?)")
                    ->execute([$_SESSION['user_id'], "Issued Gachana Sirna ID Card: $id_num"]);
            }
            $pdo->commit();
            header("Location: gachana_list.php?success=Gachana+ID+Card+issued!+ID:+$id_num"); exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
$preselect = $_GET['member'] ?? $_GET['reapply'] ?? '';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-id-card-clip me-2 text-warning"></i>Issue Gachana Sirna ID Card</h2>
    <a href="gachana_list.php" class="btn btn-outline-secondary rounded-pill"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
</div>

<?php if ($error): ?>
<div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4"><i class="fas fa-triangle-exclamation me-2"></i><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-header bg-white py-3 border-bottom rounded-top-4">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-users-viewfinder me-2 text-warning"></i>1. Select Gachana Member</h6>
                </div>
                <div class="card-body p-4">
                    <label class="form-label fw-bold small text-uppercase text-muted">Active Member <span class="text-danger">*</span></label>
                    <select name="gachana_record_id" class="form-select border-warning py-2" id="memberSelect" required>
                        <option value="">-- Select Member --</option>
                        <?php foreach ($members as $m): ?>
                            <option value="<?php echo $m['id']; ?>"
                                data-name="<?php echo htmlspecialchars($m['fname'].' '.$m['lname']); ?>"
                                <?php echo ($preselect == $m['id'] ? 'selected' : ''); ?>>
                                <?php echo htmlspecialchars("{$m['fname']} {$m['lname']} — {$m['committee_role']} ({$m['sector']})"); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text mt-2 small"><i class="fas fa-info-circle me-1"></i>Only active members without a valid ID are listed.</div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="mb-3 text-muted small fw-bold text-uppercase"><i class="fas fa-credit-card me-2"></i>2. Payment Processing</div>
            <?php displayPaymentGateway('gachana_id', 0, 'Select Member...'); ?>
            <div class="mt-4 text-end">
                <button type="submit" id="submitBtn" class="btn btn-warning px-5 py-3 fw-bold rounded-pill shadow-lg" disabled>
                    <i class="fas fa-id-card me-2"></i>Finalize & Issue Gachana ID
                </button>
                <div id="selectHint" class="text-danger small mt-2 fw-bold"><i class="fas fa-arrow-left me-1"></i>Select a member to activate</div>
            </div>
        </div>
    </div>
</form>
<script>
(function() {
    const sel = document.getElementById('memberSelect');
    const btn = document.getElementById('submitBtn');
    const hint = document.getElementById('selectHint');
    const bName = document.getElementById('payment-target-name');
    sel.addEventListener('change', function() {
        if (this.value) { if(bName) bName.textContent = this.options[this.selectedIndex].dataset.name; btn.disabled = false; hint.classList.add('d-none'); }
        else { if(bName) bName.textContent = 'Select Member...'; btn.disabled = true; hint.classList.remove('d-none'); }
    });
    if(sel.value) sel.dispatchEvent(new Event('change'));
})();
</script>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
