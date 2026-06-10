<?php
// modules/health/safetynet_id_issue.php — Issue PSNP Participation ID
require_once '../../includes/header.php';
require_once '../../config/database.php';
require_once '../../includes/payment_handler.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../auth/login.php'); exit; }

// Participants without an active safety net ID card
$members = $pdo->query("
    SELECT sn.id, sn.transfer_type, sn.work_status, i.fname, i.lname, i.id AS individual_id
    FROM safetynet_records sn
    JOIN individuals i ON sn.individual_id = i.id
    WHERE sn.id NOT IN (
        SELECT safetynet_record_id FROM safetynet_id_cards
        WHERE status = 'Active' AND expiry_date >= CURDATE()
    )
    ORDER BY i.fname
")->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sn_record_id = $_POST['safetynet_record_id'] ?? '';
    if (empty($sn_record_id)) { $error = 'Please select a PSNP participant.'; }
    else {
        try {
            $pdo->beginTransaction();
            $sn = $pdo->prepare("SELECT individual_id FROM safetynet_records WHERE id = ?");
            $sn->execute([$sn_record_id]);
            $individual_id = $sn->fetchColumn();

            processPaymentSubmission($pdo, $individual_id, 'safetynet_id');
            $txn_id = $pdo->lastInsertId();

            // Expire old cards
            $pdo->prepare("UPDATE safetynet_id_cards SET status = 'Expired' WHERE safetynet_record_id = ? AND status = 'Active'")->execute([$sn_record_id]);

            // Generate ID number
            $last = $pdo->query("SELECT id_num FROM safetynet_id_cards WHERE id_num LIKE 'PSNP%' ORDER BY id DESC LIMIT 1")->fetchColumn();
            $seq = $last ? (intval(substr($last, 4)) + 1) : 1;
            $id_num = 'PSNP' . str_pad($seq, 4, '0', STR_PAD_LEFT);
            $issue = date('Y-m-d');
            $expiry = date('Y-m-d', strtotime('+3 years'));

            $pdo->prepare("INSERT INTO safetynet_id_cards (safetynet_record_id, id_num, issue_date, expiry_date, status) VALUES (?, ?, ?, ?, 'Active')")
                ->execute([$sn_record_id, $id_num, $issue, $expiry]);

            if (isset($_SESSION['user_id'])) {
                $pdo->prepare("INSERT INTO audit_logs (user_id, action, details) VALUES (?, 'CREATE', ?)")
                    ->execute([$_SESSION['user_id'], "Issued Safety Net ID: $id_num"]);
            }
            $pdo->commit();
            header("Location: safetynet_list.php?success=PSNP+ID+Card+issued!+ID:+$id_num"); exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
$preselect = $_GET['member'] ?? '';
?>

<div class="mb-3">
    <a href="safetynet_list.php" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold shadow-sm">
        <i class="fas fa-arrow-left me-2"></i><?php echo __('back_to_registry', 'Back to Registry'); ?>
    </a>
</div>

<div class="card border-0 shadow-lg rounded-4 overflow-hidden max-width-800 mx-auto">
    <div class="card-header bg-dark text-white p-4 border-0">
        <h4 class="fw-black mb-0 text-success"><i class="fas fa-id-card-clip me-2"></i><?php echo __('issue_psnp_id_card', 'Issue PSNP Identification Card'); ?></h4>
    </div>
    
    <div class="card-body p-4 p-md-5">
        <?php if($error): ?><div class="alert alert-danger rounded-4"><?php echo $error; ?></div><?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row g-4">
                <div class="col-md-12">
                    <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('select_participant_step', '1. Select Participant'); ?></label>
                    <select name="safetynet_record_id" class="form-select border-success rounded-pill py-2" id="psnpSelect" required>
                        <option value=""><?php echo __('-- choose_participant --'); ?></option>
                        <?php foreach ($members as $m): ?>
                            <option value="<?php echo $m['id']; ?>" 
                                data-name="<?php echo htmlspecialchars($m['fname'].' '.$m['lname']); ?>"
                                <?php echo ($preselect == $m['id'] ? 'selected' : ''); ?>>
                                <?php echo htmlspecialchars("{$m['fname']} {$m['lname']} — {$m['transfer_type']} ({$m['work_status']})"); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-12">
                     <label class="form-label fw-bold small text-muted text-uppercase mb-3"><?php echo __('service_fee_step', '2. Service Fee (5 ETB)'); ?></label>
                     <?php displayPaymentGateway('safetynet_id', 0, __('select_participant_placeholder', 'Select Participant...')); ?>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" id="submitBtn" class="btn btn-success w-100 rounded-pill py-3 fw-black shadow-lg" disabled>
                        <?php echo __('generate_issue_psnp_card', 'GENERATE & ISSUE PSNP CARD'); ?>
                    </button>
                    <div id="hintText" class="text-center mt-2 small text-danger"><i class="fas fa-info-circle me-1"></i><?php echo __('select_participant_hint', 'Please select a participant first'); ?></div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('psnpSelect').addEventListener('change', function() {
    const btn = document.getElementById('submitBtn');
    const hint = document.getElementById('hintText');
    const nameSpan = document.getElementById('payment-target-name');
    if (this.value) {
        btn.disabled = false;
        hint.classList.add('d-none');
        if(nameSpan) nameSpan.textContent = this.options[this.selectedIndex].dataset.name;
    } else {
        btn.disabled = true;
        hint.classList.remove('d-none');
        if(nameSpan) nameSpan.textContent = "<?php echo __('select_participant_placeholder', 'Select Participant...'); ?>";
    }
});
if(document.getElementById('psnpSelect').value) document.getElementById('psnpSelect').dispatchEvent(new Event('change'));
</script>

<?php require_once '../../includes/footer.php'; ?>
