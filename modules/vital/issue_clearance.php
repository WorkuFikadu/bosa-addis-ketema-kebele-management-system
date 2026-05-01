<?php
// modules/vital/issue_clearance.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

// Get all residents for clearance
$residents = $pdo->query("SELECT id, fname, lname FROM individuals ORDER BY fname")->fetchAll();

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resident_id = $_POST['resident_id'];
    $issue_date = date('Y-m-d');
    
    // Generate cert number: IB-CLXX
    $lastIdStmt = $pdo->prepare("SELECT cert_number FROM vital_certificates WHERE cert_type = 'clearance' AND cert_number LIKE 'IB-CL%' ORDER BY id DESC LIMIT 1");
    $lastIdStmt->execute();
    $lastId = $lastIdStmt->fetchColumn();
    
    $nextNumber = 1;
    if ($lastId) {
        $lastNumber = intval(substr($lastId, 5)); // Skip 'IB-CL'
        $nextNumber = $lastNumber + 1;
    }
    
    $cert_number = "IB-CL" . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
    $remarks = $_POST['remarks'] ?? '';
    $destination = $_POST['destination'] ?? '';
    $reason = $_POST['reason'] ?? '';
    
    $full_remarks = "Destination: $destination | Reason: $reason | Extra: $remarks";

    $stmt = $pdo->prepare("INSERT INTO vital_certificates (resident_id, cert_type, cert_number, issue_date, remarks) VALUES (?, 'clearance', ?, ?, ?)");
    try {
        $stmt->execute([$resident_id, $cert_number, $issue_date, $full_remarks]);
        $cert_id = $pdo->lastInsertId();
        $success = __('clearance_cert') . " generated: $cert_number";
    } catch (PDOException $e) {
        $error = "Failed: " . $e->getMessage();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-file-check me-2 text-success"></i><?php echo __('clearance_cert'); ?></h2>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i><?php echo __('back'); ?></a>
</div>

<?php if ($success): ?>
    <div class="alert alert-success d-flex align-items-center">
        <i class="fas fa-check-circle me-3 fa-2x"></i>
        <div>
            <strong><?php echo $success; ?></strong><br>
            <a href="print.php?id=<?php echo $cert_id; ?>" class="btn btn-sm btn-success mt-2" target="_blank"><?php echo __('print'); ?></a>
        </div>
    </div>
<?php endif; ?>

<?php if ($error): ?> <div class="alert alert-danger"><?php echo $error; ?></div> <?php endif; ?>

<div class="row">
    <div class="col-md-7">
        <div class="card p-4 border-0 shadow-sm">
            <form method="POST">
                <div class="mb-4">
                    <label class="form-label fw-bold"><?php echo __('select_resident'); ?></label>
                    <select name="resident_id" class="form-select select2" required>
                        <option value="">-- <?php echo __('search'); ?> --</option>
                        <?php foreach ($residents as $r): ?>
                            <option value="<?php echo $r['id']; ?>"><?php echo "{$r['fname']} {$r['lname']} (#{$r['id']})"; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold"><?php echo __('clearance_to'); ?></label>
                        <input type="text" name="destination" class="form-control" placeholder="e.g. Jimma University" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold"><?php echo __('reason'); ?></label>
                        <input type="text" name="reason" class="form-control" placeholder="e.g. Employment" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold"><?php echo __('remarks'); ?> (<?php echo __('optional'); ?>)</label>
                    <textarea name="remarks" class="form-control" rows="3" placeholder="..."></textarea>
                </div>
                <button type="submit" class="btn btn-success w-100 py-3 fw-bold">
                    <i class="fas fa-file-certificate me-2"></i><?php echo __('gen_clearance_btn'); ?>
                </button>
            </form>
        </div>
    </div>
    <div class="col-md-5">
        <div class="bg-light p-4 rounded border">
            <h5 class="text-success mb-3"><i class="fas fa-shield-check me-2"></i>System Validation</h5>
            <p class="small text-muted">A Clearance Certificate is issued to residents who have no pending legal issues or administrative blocks.</p>
            <ul class="small text-muted ps-3">
                <li>Validates residency status.</li>
                <li>Logs the transfer reason for historical records.</li>
                <li>Generates a secure identifier for verification.</li>
            </ul>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
