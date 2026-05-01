<?php
// modules/vital/issue_marriage.php
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
    $groom_id       = $_POST['groom_id'];
    $bride_id       = $_POST['bride_id'];
    $marriage_date  = $_POST['marriage_date'];
    $marriage_place = $_POST['marriage_place'] ?: 'Ifa Bula Kebele, Jimma';
    $witness1       = $_POST['witness1_name'] ?? '';
    $witness2       = $_POST['witness2_name'] ?? '';
    $issue_date     = date('Y-m-d');
    $remarks        = $_POST['remarks'] ?? '';

    if ($groom_id == $bride_id) {
        $error = "Groom and Bride cannot be the same person.";
    } else {
        // Generate cert number: IB-MRXX
        $lastStmt = $pdo->prepare("SELECT cert_number FROM vital_certificates WHERE cert_type = 'marriage' AND cert_number LIKE 'IB-MR%' ORDER BY id DESC LIMIT 1");
        $lastStmt->execute();
        $lastId = $lastStmt->fetchColumn();
        
        $nextNumber = 1;
        if ($lastId) {
            $lastNumber = intval(substr($lastId, 5));
            $nextNumber = $lastNumber + 1;
        }
        $cert_number = "IB-MR" . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        // Handle groom photo upload
        $groom_photo = 'default_profile.png';
        if (isset($_FILES['groom_photo']) && $_FILES['groom_photo']['error'] === 0) {
            $ext = pathinfo($_FILES['groom_photo']['name'], PATHINFO_EXTENSION);
            $groom_photo = 'groom_' . time() . '_' . uniqid() . '.' . $ext;
            if (!is_dir("../../assets/images/")) mkdir("../../assets/images/", 0777, true);
            move_uploaded_file($_FILES['groom_photo']['tmp_name'], "../../assets/images/" . $groom_photo);
        }

        // Handle bride photo upload
        $bride_photo = 'default_profile.png';
        if (isset($_FILES['bride_photo']) && $_FILES['bride_photo']['error'] === 0) {
            $ext = pathinfo($_FILES['bride_photo']['name'], PATHINFO_EXTENSION);
            $bride_photo = 'bride_' . time() . '_' . uniqid() . '.' . $ext;
            if (!is_dir("../../assets/images/")) mkdir("../../assets/images/", 0777, true);
            move_uploaded_file($_FILES['bride_photo']['tmp_name'], "../../assets/images/" . $bride_photo);
        }

        try {
            $pdo->beginTransaction();

            // 1. Insert into vital_certificates (groom as primary resident)
            $stmt_cert = $pdo->prepare("INSERT INTO vital_certificates (resident_id, cert_type, cert_number, issue_date, remarks) VALUES (?, 'marriage', ?, ?, ?)");
            $stmt_cert->execute([$groom_id, $cert_number, $issue_date, $remarks]);
            $cert_id = $pdo->lastInsertId();

            // 2. Insert marriage details
            $stmt_md = $pdo->prepare("INSERT INTO marriage_details (cert_id, groom_id, bride_id, marriage_date, marriage_place, witness1_name, witness2_name, groom_photo, bride_photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_md->execute([$cert_id, $groom_id, $bride_id, $marriage_date, $marriage_place, $witness1, $witness2, $groom_photo, $bride_photo]);

            // 3. Update both individuals' marital status
            $pdo->prepare("UPDATE individuals SET mar = 'Married' WHERE id IN (?, ?)")->execute([$groom_id, $bride_id]);

            $pdo->commit();
            $success = "Marriage certificate generated: $cert_number";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed: " . $e->getMessage();
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-heart me-2 text-danger"></i>Issue Marriage Certificate</h2>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i><?php echo __('back'); ?></a>
</div>

<?php if ($success): ?>
    <div class="alert alert-success d-flex align-items-center border-0 shadow-sm">
        <i class="fas fa-check-circle me-3 fa-2x"></i>
        <div>
            <strong><?php echo $success; ?></strong><br>
            <a href="print.php?id=<?php echo $cert_id; ?>" class="btn btn-sm btn-success mt-2" target="_blank">
                <i class="fas fa-print me-1"></i>Print Marriage Certificate
            </a>
        </div>
    </div>
<?php endif; ?>

<?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div><?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<div class="row g-4">

    <!-- ═══ GROOM ═══ -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm overflow-hidden h-100">
            <div class="p-3 text-white text-center" style="background: linear-gradient(135deg, #1e3a5f, #2563eb);">
                <i class="fas fa-mars fa-lg me-2"></i><strong>GROOM / ሙሽራ (Dhiirsa)</strong>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Select Groom (Male Resident) <span class="text-danger">*</span></label>
                    <select name="groom_id" class="form-select" required id="groomSelect">
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
                <div id="groomPreview" class="d-none text-center p-3 bg-light rounded">
                    <img id="groomImg" src="" class="rounded-circle border border-3 border-primary mb-2" style="width:80px;height:80px;object-fit:cover;">
                    <div class="fw-bold" id="groomName"></div>
                    <small class="text-muted" id="groomAge"></small>
                </div>

                <div class="mt-3">
                    <label class="form-label fw-bold"><i class="fas fa-camera me-1"></i>Groom Photo for Certificate</label>
                    <input type="file" name="groom_photo" class="form-control" accept="image/*">
                    <div class="form-text">Upload formal photo for the marriage certificate.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ BRIDE ═══ -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm overflow-hidden h-100">
            <div class="p-3 text-white text-center" style="background: linear-gradient(135deg, #9d174d, #ec4899);">
                <i class="fas fa-venus fa-lg me-2"></i><strong>BRIDE / ሙሽሪት (Misirroo)</strong>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Select Bride (Female Resident) <span class="text-danger">*</span></label>
                    <select name="bride_id" class="form-select" required id="brideSelect">
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
                <div id="bridePreview" class="d-none text-center p-3 bg-light rounded">
                    <img id="brideImg" src="" class="rounded-circle border border-3 border-danger mb-2" style="width:80px;height:80px;object-fit:cover;">
                    <div class="fw-bold" id="brideName"></div>
                    <small class="text-muted" id="brideAge"></small>
                </div>

                <div class="mt-3">
                    <label class="form-label fw-bold"><i class="fas fa-camera me-1"></i>Bride Photo for Certificate</label>
                    <input type="file" name="bride_photo" class="form-control" accept="image/*">
                    <div class="form-text">Upload formal photo for the marriage certificate.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ MARRIAGE DETAILS ═══ -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm p-4">
            <h5 class="border-bottom pb-3 mb-4"><i class="fas fa-ring text-warning me-2"></i>Marriage Details</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Date of Marriage <span class="text-danger">*</span></label>
                    <input type="date" name="marriage_date" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Place of Marriage</label>
                    <input type="text" name="marriage_place" class="form-control" value="Ifa Bula Kebele, Jimma City" placeholder="e.g. Jimma City Hall">
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
            <button type="submit" class="btn btn-danger w-100 py-3 fw-bold mt-4 fs-6" onclick="return confirm('Are you sure you want to register this marriage? Both parties will be marked as Married.')">
                <i class="fas fa-heart me-2"></i>Register Marriage &amp; Issue Certificate
            </button>
        </div>
    </div>

    <!-- ═══ LEGAL INFO ═══ -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-4 h-100" style="background: linear-gradient(135deg, #1a1a2e, #16213e); color: #fff;">
            <h6 class="fw-bold mb-3"><i class="fas fa-gavel me-2 text-warning"></i>Ethiopian Marriage Law</h6>
            <ul class="list-unstyled small" style="line-height: 2.2; opacity: 0.9;">
                <li><i class="fas fa-check-circle text-success me-2"></i>Minimum age: <strong>18 years</strong> for both parties</li>
                <li><i class="fas fa-check-circle text-success me-2"></i>Both must give <strong>free consent</strong></li>
                <li><i class="fas fa-check-circle text-success me-2"></i>Must not be <strong>already married</strong></li>
                <li><i class="fas fa-check-circle text-success me-2"></i>Prohibited between close <strong>blood relatives</strong></li>
                <li><i class="fas fa-check-circle text-success me-2"></i>At least <strong>2 witnesses</strong> required</li>
                <li><i class="fas fa-check-circle text-success me-2"></i>Registered under <strong>Revised Family Code</strong> (Proc. 213/2000)</li>
            </ul>
            <hr class="border-white border-opacity-25">
            <p class="text-white-50 small mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Per Article 6-14 of the Revised Family Code of Ethiopia, marriage must be performed with the full, informed, and voluntary consent of both spouses.
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
setupPreview('groomSelect', 'groomPreview', 'groomImg', 'groomName', 'groomAge');
setupPreview('brideSelect', 'bridePreview', 'brideImg', 'brideName', 'brideAge');
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
