<?php
// modules/economic/youth_id_issue.php — Issue Youth Empowerment ID
require_once '../../includes/header.php';
require_once '../../config/database.php';
require_once '../../includes/payment_handler.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../auth/login.php'); exit; }

// Registered youth without an active ID
$members = $pdo->query("
    SELECT ey.id, ey.skills, ey.employment_status, i.fname, i.lname, i.id AS individual_id
    FROM economic_youth_registry ey
    JOIN individuals i ON ey.individual_id = i.id
    WHERE ey.id NOT IN (
        SELECT youth_record_id FROM youth_id_cards
        WHERE status = 'Active' AND expiry_date >= CURDATE()
    )
    ORDER BY i.fname
")->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $youth_record_id = $_POST['youth_record_id'] ?? '';
    if (empty($youth_record_id)) { $error = __('please_select_member'); }
    else {
        try {
            $pdo->beginTransaction();
            $ey = $pdo->prepare("SELECT individual_id FROM economic_youth_registry WHERE id = ?");
            $ey->execute([$youth_record_id]);
            $individual_id = $ey->fetchColumn();

            processPaymentSubmission($pdo, $individual_id, 'youth_id');

            // Expire old cards
            $pdo->prepare("UPDATE youth_id_cards SET status = 'Expired' WHERE youth_record_id = ? AND status = 'Active'")->execute([$youth_record_id]);

            // Generate ID number
            $last = $pdo->query("SELECT id_num FROM youth_id_cards WHERE id_num LIKE 'YOUTH%' ORDER BY id DESC LIMIT 1")->fetchColumn();
            $seq = $last ? (intval(substr($last, 5)) + 1) : 1;
            $id_num = 'YOUTH' . str_pad($seq, 4, '0', STR_PAD_LEFT);
            $issue = date('Y-m-d');
            $expiry = date('Y-m-d', strtotime('+5 years'));

            $pdo->prepare("INSERT INTO youth_id_cards (youth_record_id, id_num, issue_date, expiry_date, status) VALUES (?, ?, ?, ?, 'Active')")
                ->execute([$youth_record_id, $id_num, $issue, $expiry]);

            $pdo->commit();
            header("Location: youth_list.php?success=Youth+ID+Card+issued!+ID:+$id_num"); exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
$preselect = $_GET['member'] ?? '';
?>

<div class="mb-3">
    <a href="youth_list.php" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold shadow-sm">
        <i class="fas fa-arrow-left me-2"></i><?php echo __('back'); ?>
    </a>
</div>

<div class="card border-0 shadow-lg rounded-4 overflow-hidden max-width-800 mx-auto">
    <div class="card-header bg-dark text-white p-4 border-0">
        <h4 class="fw-black mb-0 text-danger"><i class="fas fa-id-card-clip me-2"></i><?php echo __('issue_youth_id'); ?></h4>
    </div>
    
    <div class="card-body p-4 p-md-5">
        <?php if($error): ?><div class="alert alert-danger rounded-4"><?php echo $error; ?></div><?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row g-4">
                <div class="col-md-12">
                    <label class="form-label fw-bold small text-muted text-uppercase">1. <?php echo __('select_youth_member'); ?></label>
                    <select name="youth_record_id" class="form-select border-danger rounded-pill py-2" id="youthSelect" required>
                        <option value=""><?php echo __('choose_member'); ?></option>
                        <?php foreach ($members as $m): ?>
                            <option value="<?php echo $m['id']; ?>" 
                                data-name="<?php echo htmlspecialchars($m['fname'].' '.$m['lname']); ?>"
                                <?php echo ($preselect == $m['id'] ? 'selected' : ''); ?>>
                                <?php echo htmlspecialchars("{$m['fname']} {$m['lname']} — {$m['employment_status']}"); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-12">
                     <label class="form-label fw-bold small text-muted text-uppercase mb-3">2. <?php echo __('service_fee'); ?> (5 ETB)</label>
                     <?php displayPaymentGateway('youth_id', 0, __('select_resident')); ?>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" id="submitBtn" class="btn btn-danger w-100 rounded-pill py-3 fw-black shadow-lg" disabled>
                        <?php echo __('gen_youth_id'); ?>
                    </button>
                    <div id="hintText" class="text-center mt-2 small text-danger"><i class="fas fa-info-circle me-1"></i><?php echo __('select_member_first'); ?></div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('youthSelect').addEventListener('change', function() {
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
        if(nameSpan) nameSpan.textContent = '<?php echo __('select_resident'); ?>...';
    }
});
if(document.getElementById('youthSelect').value) document.getElementById('youthSelect').dispatchEvent(new Event('change'));
</script>

<?php require_once '../../includes/footer.php'; ?>
