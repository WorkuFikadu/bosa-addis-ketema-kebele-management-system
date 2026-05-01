<?php
// modules/vital/issue_divorce.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

// Get male and female residents separately
$males = $pdo->query("SELECT i.id, i.fname, i.lname, i.mname, i.phot, ag.age 
    FROM individuals i LEFT JOIN ages ag ON i.id = ag.id 
    WHERE i.s = 'Male' AND i.status = 'alive' ORDER BY i.fname")->fetchAll();

$females = $pdo->query("SELECT i.id, i.fname, i.lname, i.mname, i.phot, ag.age 
    FROM individuals i LEFT JOIN ages ag ON i.id = ag.id 
    WHERE i.s = 'Female' AND i.status = 'alive' ORDER BY i.fname")->fetchAll();

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $husband_id       = $_POST['husband_id'];
    $wife_id       = $_POST['wife_id'];
    $divorce_date  = $_POST['divorce_date'];
    $divorce_place = $_POST['divorce_place'] ?: 'Ifa Bula Kebele, Jimma';
    $witness1       = $_POST['witness1_name'] ?? '';
    $witness2       = $_POST['witness2_name'] ?? '';
    $issue_date     = date('Y-m-d');
    $remarks        = $_POST['remarks'] ?? '';

    if ($husband_id == $wife_id) {
        $error = "husband and wife cannot be the same person.";
    } else {
        // Generate cert number: IB-DVXX
        $lastStmt = $pdo->prepare("SELECT cert_number FROM vital_certificates WHERE cert_type = 'divorce' AND cert_number LIKE 'IB-DV%' ORDER BY id DESC LIMIT 1");
        $lastStmt->execute();
        $lastId = $lastStmt->fetchColumn();
        
        $nextNumber = 1;
        if ($lastId) {
            $lastNumber = intval(substr($lastId, 5));
            $nextNumber = $lastNumber + 1;
        }
        $cert_number = "IB-DV" . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        // Handle husband photo upload
        $husband_photo = 'default_profile.png';
        if (isset($_FILES['husband_photo']) && $_FILES['husband_photo']['error'] === 0) {
            $ext = pathinfo($_FILES['husband_photo']['name'], PATHINFO_EXTENSION);
            $husband_photo = 'husband_' . time() . '_' . uniqid() . '.' . $ext;
            if (!is_dir("../../assets/images/")) mkdir("../../assets/images/", 0777, true);
            move_uploaded_file($_FILES['husband_photo']['tmp_name'], "../../assets/images/" . $husband_photo);
        }

        // Handle wife photo upload
        $wife_photo = 'default_profile.png';
        if (isset($_FILES['wife_photo']) && $_FILES['wife_photo']['error'] === 0) {
            $ext = pathinfo($_FILES['wife_photo']['name'], PATHINFO_EXTENSION);
            $wife_photo = 'wife_' . time() . '_' . uniqid() . '.' . $ext;
            if (!is_dir("../../assets/images/")) mkdir("../../assets/images/", 0777, true);
            move_uploaded_file($_FILES['wife_photo']['tmp_name'], "../../assets/images/" . $wife_photo);
        }

        try {
            $pdo->beginTransaction();

            // 1. Insert into vital_certificates (husband as primary resident)
            $stmt_cert = $pdo->prepare("INSERT INTO vital_certificates (resident_id, cert_type, cert_number, issue_date, remarks) VALUES (?, 'divorce', ?, ?, ?)");
            $stmt_cert->execute([$husband_id, $cert_number, $issue_date, $remarks]);
            $cert_id = $pdo->lastInsertId();

            // 2. Insert divorce details
            $stmt_md = $pdo->prepare("INSERT INTO divorce_details (cert_id, husband_id, wife_id, divorce_date, divorce_place, witness1_name, witness2_name, husband_photo, wife_photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_md->execute([$cert_id, $husband_id, $wife_id, $divorce_date, $divorce_place, $witness1, $witness2, $husband_photo, $wife_photo]);

            // 3. Update both individuals' marital status
            $pdo->prepare("UPDATE individuals SET mar = 'Divorced' WHERE id IN (?, ?)")->execute([$husband_id, $wife_id]);

            $pdo->commit();
            $success = "Divorce certificate generated: $cert_number";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed: " . $e->getMessage();
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-heart-broken me-2 text-danger"></i>Issue Divorce Certificate</h2>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i><?php echo __('back'); ?></a>
</div>

<?php if ($success): ?>
    <div class="alert alert-success d-flex align-items-center border-0 shadow-sm">
        <i class="fas fa-check-circle me-3 fa-2x"></i>
        <div>
            <strong><?php echo $success; ?></strong><br>
                <i class="fas fa-print me-1"></i>Print Divorce Certificate
        </div>
    </div>
<?php endif; ?>

<?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div><?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<div class="row g-4">

    <!-- ═══ HUSBAND ═══ -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm overflow-hidden h-100">
            <div class="p-3 text-white text-center" style="background: linear-gradient(135deg, #1e3a5f, #2563eb);">
                <i class="fas fa-mars fa-lg me-2"></i><strong>HUSBAND / ባል (Abbaa warraa)</strong>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Select Husband (Male Resident) <span class="text-danger">*</span></label>
                    <select name="husband_id" class="form-select" required id="husbandSelect">
                        <option value="">— Select Registered Male Resident —</option>
                        <?php foreach ($males as $r): ?>
                            <option value="<?php echo $r['id']; ?>"
                                    data-name="<?php echo htmlspecialchars("{$r['fname']} {$r['mname']} {$r['lname']}"); ?>"
                                    data-age="<?php echo $r['age'] ?? ''; ?>"
                                    data-photo="<?php echo htmlspecialchars($r['phot'] ?? 'default_profile.png'); ?>">
                                <?php echo htmlspecialchars("{$r['fname']} {$r['mname']} {$r['lname']}"); ?> (ID: #<?php echo $r['id']; ?> · Age: <?php echo $r['age'] ?? 'N/A'; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Preview -->
                <div id="husbandPreview" class="d-none text-center p-3 bg-light rounded">
                    <img id="husbandImg" src="" class="rounded-circle border border-3 border-primary mb-2" style="width:80px;height:80px;object-fit:cover;">
                    <div class="fw-bold" id="husbandName"></div>
                    <small class="text-muted" id="husbandAge"></small>
                </div>

                <div class="mt-3">
                    <label class="form-label fw-bold"><i class="fas fa-camera me-1"></i>Husband Photo for Certificate</label>
                    <input type="file" name="husband_photo" class="form-control" accept="image/*">
                    <div class="form-text">Upload formal photo for the divorce certificate.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ WIFE ═══ -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm overflow-hidden h-100">
            <div class="p-3 text-white text-center" style="background: linear-gradient(135deg, #9d174d, #ec4899);">
                <i class="fas fa-venus fa-lg me-2"></i><strong>WIFE / ሚስት (Haadha warraa)</strong>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Select Wife (Female Resident) <span class="text-danger">*</span></label>
                    <select name="wife_id" class="form-select" required id="wifeSelect">
                        <option value="">— Select Registered Female Resident —</option>
                        <?php foreach ($females as $r): ?>
                            <option value="<?php echo $r['id']; ?>"
                                    data-name="<?php echo htmlspecialchars("{$r['fname']} {$r['mname']} {$r['lname']}"); ?>"
                                    data-age="<?php echo $r['age'] ?? ''; ?>"
                                    data-photo="<?php echo htmlspecialchars($r['phot'] ?? 'default_profile.png'); ?>">
                                <?php echo htmlspecialchars("{$r['fname']} {$r['mname']} {$r['lname']}"); ?> (ID: #<?php echo $r['id']; ?> · Age: <?php echo $r['age'] ?? 'N/A'; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Preview -->
                <div id="wifePreview" class="d-none text-center p-3 bg-light rounded">
                    <img id="wifeImg" src="" class="rounded-circle border border-3 border-danger mb-2" style="width:80px;height:80px;object-fit:cover;">
                    <div class="fw-bold" id="wifeName"></div>
                    <small class="text-muted" id="wifeAge"></small>
                </div>

                <div class="mt-3">
                    <label class="form-label fw-bold"><i class="fas fa-camera me-1"></i>Wife Photo for Certificate</label>
                    <input type="file" name="wife_photo" class="form-control" accept="image/*">
                    <div class="form-text">Upload formal photo for the divorce certificate.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ DIVORCE DETAILS ═══ -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm p-4">
            <h5 class="border-bottom pb-3 mb-4"><i class="fas fa-ring text-warning me-2"></i>Divorce Details</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Date of Divorce <span class="text-danger">*</span></label>
                    <input type="date" name="divorce_date" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Place of Divorce</label>
                    <input type="text" name="divorce_place" class="form-control" value="Ifa Bula Kebele, Jimma City" placeholder="e.g. Jimma City Hall">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Witness 1 (Full Name)</label>
                    <input type="text" name="witness1_name" class="form-control" placeholder="First witness full name">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Witness 2 (Full Name)</label>
                    <input type="text" name="witness2_name" class="form-control" placeholder="Second witness full name">
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Remarks (Optional)</label>
                    <textarea name="remarks" class="form-control" rows="2" placeholder="Any additional notes..."></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-danger w-100 py-3 fw-bold mt-4 fs-6" onclick="return confirm('Are you sure you want to register this divorce? Both parties will be marked as Divorced.')">
                <i class="fas fa-heart-broken me-2"></i>Register Divorce &amp; Issue Certificate
            </button>
        </div>
    </div>

    <!-- ═══ LEGAL INFO ═══ -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-4 h-100" style="background: linear-gradient(135deg, #1a1a2e, #16213e); color: #fff;">
            <h6 class="fw-bold mb-3"><i class="fas fa-gavel me-2 text-warning"></i>Ethiopian Divorce Law</h6>
            <ul class="list-unstyled small" style="line-height: 2.2; opacity: 0.9;">
                <li><i class="fas fa-check-circle text-success me-2"></i>Minimum age: <strong>18 years</strong> for both parties</li>
                <li><i class="fas fa-check-circle text-success me-2"></i>Both must give <strong>free consent</strong></li>
                <li><i class="fas fa-check-circle text-success me-2"></i>Must be <strong>already Married</strong></li>
                <li><i class="fas fa-check-circle text-success me-2"></i>Divorce granted by <strong>legal authority/court</strong></li>
                <li><i class="fas fa-check-circle text-success me-2"></i>Registered under <strong>Revised Family Code</strong> (Proc. 213/2000)</li>
            </ul>
            <hr class="border-white border-opacity-25">
            <p class="text-white-50 small mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Per Article of the Revised Family Code of Ethiopia, divorce must be performed with the full, informed, and voluntary consent of both spouses or by court decision.
            </p>
        </div>
    </div>

</div>
</form>

<script>
function setupPreview(selectId, previewId, imgId, nameId, ageId) {
    document.getElementById(selectId)?.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        const preview = document.getElementById(previewId);
        if (this.value) {
            document.getElementById(imgId).src = '../../assets/images/' + opt.dataset.photo;
            document.getElementById(imgId).onerror = function() { this.src='https://ui-avatars.com/api/?name=' + encodeURIComponent(opt.dataset.name) + '&size=200&background=4f46e5&color=fff'; };
            document.getElementById(nameId).textContent = opt.dataset.name;
            document.getElementById(ageId).textContent = opt.dataset.age ? opt.dataset.age + ' years old' : '';
            preview.classList.remove('d-none');
        } else {
            preview.classList.add('d-none');
        }
    });
}
setupPreview('husbandSelect', 'husbandPreview', 'husbandImg', 'husbandName', 'husbandAge');
setupPreview('wifeSelect', 'wifePreview', 'wifeImg', 'wifeName', 'wifeAge');
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
