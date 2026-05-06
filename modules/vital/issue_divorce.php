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

    if ($husband_id == $wife_id && $husband_id != 'NEW_HUSBAND') {
        $error = "Husband and wife cannot be the same person.";
    } elseif ($husband_id === 'NEW_HUSBAND' && $wife_id === 'NEW_WIFE') {
        $error = "At least one party must be a resident of this Kebele. We do not issue certificates for two external parties.";
    } else {
        try {
            $pdo->beginTransaction();

            // ———— HANDLE NEW HUSBAND REGISTRATION ————
            if ($husband_id === 'NEW_HUSBAND') {
                $hnf = $_POST['husband_new'];
                $hp = 'default.png';
                if (isset($_FILES['husband_new_photo']) && $_FILES['husband_new_photo']['error'] === 0) {
                    $ext = pathinfo($_FILES['husband_new_photo']['name'], PATHINFO_EXTENSION);
                    $hp = 'husband_ext_' . time() . '.' . $ext;
                    move_uploaded_file($_FILES['husband_new_photo']['tmp_name'], "../../assets/images/" . $hp);
                }

                $stmt_nh = $pdo->prepare("INSERT INTO individuals (fname, mname, lname, s, mar, nat, occ, status, phot) VALUES (?, ?, ?, 'Male', 'Married', ?, ?, 'alive', ?)");
                $stmt_nh->execute([$hnf['fname'], $hnf['mname'], $hnf['lname'], $hnf['nat'] ?: 'Ethiopian', $hnf['occ'] ?: 'Other', $hp]);
                $husband_id = $pdo->lastInsertId();

                // Age
                $bdate = $hnf['bdate'];
                $age = $bdate ? date_diff(date_create($bdate), date_create('today'))->y : 20;
                $pdo->prepare("INSERT INTO ages (id, bdate, age) VALUES (?, ?, ?)")->execute([$husband_id, $bdate ?: date('Y-m-d', strtotime('-25 years')), $age]);
                
                // Address (External)
                $pdo->prepare("INSERT INTO addresses (id, region, zone, city, kebele, pho_no, email) VALUES (?, ?, ?, ?, ?, ?, ?)")
                    ->execute([$husband_id, $hnf['region'] ?: 'External', 'Other', 'Other', 'Other', $hnf['phone'] ?: 'N/A', '']);
            }

            // ———— HANDLE NEW WIFE REGISTRATION ————
            if ($wife_id === 'NEW_WIFE') {
                $wnf = $_POST['wife_new'];
                $wp = 'default.png';
                if (isset($_FILES['wife_new_photo']) && $_FILES['wife_new_photo']['error'] === 0) {
                    $ext = pathinfo($_FILES['wife_new_photo']['name'], PATHINFO_EXTENSION);
                    $wp = 'wife_ext_' . time() . '.' . $ext;
                    move_uploaded_file($_FILES['wife_new_photo']['tmp_name'], "../../assets/images/" . $wp);
                }

                $stmt_nw = $pdo->prepare("INSERT INTO individuals (fname, mname, lname, s, mar, nat, occ, status, phot) VALUES (?, ?, ?, 'Female', 'Married', ?, ?, 'alive', ?)");
                $stmt_nw->execute([$wnf['fname'], $wnf['mname'], $wnf['lname'], $wnf['nat'] ?: 'Ethiopian', $wnf['occ'] ?: 'Other', $wp]);
                $wife_id = $pdo->lastInsertId();

                // Age
                $bdate = $wnf['bdate'];
                $age = $bdate ? date_diff(date_create($bdate), date_create('today'))->y : 20;
                $pdo->prepare("INSERT INTO ages (id, bdate, age) VALUES (?, ?, ?)")->execute([$wife_id, $bdate ?: date('Y-m-d', strtotime('-25 years')), $age]);

                // Address (External)
                $pdo->prepare("INSERT INTO addresses (id, region, zone, city, kebele, pho_no, email) VALUES (?, ?, ?, ?, ?, ?, ?)")
                    ->execute([$wife_id, $wnf['region'] ?: 'External', 'Other', 'Other', 'Other', $wnf['phone'] ?: 'N/A', '']);
            }

            // ———— ISSUE CERTIFICATE ————
            $lastStmt = $pdo->prepare("SELECT cert_number FROM vital_certificates WHERE cert_type = 'divorce' AND cert_number LIKE 'IB-DV%' ORDER BY id DESC LIMIT 1");
            $lastStmt->execute();
            $lastId = $lastStmt->fetchColumn();
            $nextNumber = $lastId ? (intval(substr($lastId, 5)) + 1) : 1;
            $cert_number = "IB-DV" . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            // Handle photos for cert
            $husband_photo = 'default_profile.png';
            if (isset($_FILES['husband_photo']) && $_FILES['husband_photo']['error'] === 0) {
                $husband_photo = 'cert_h_' . time() . '.' . pathinfo($_FILES['husband_photo']['name'], PATHINFO_EXTENSION);
                move_uploaded_file($_FILES['husband_photo']['tmp_name'], "../../assets/images/" . $husband_photo);
            }
            $wife_photo = 'default_profile.png';
            if (isset($_FILES['wife_photo']) && $_FILES['wife_photo']['error'] === 0) {
                $wife_photo = 'cert_w_' . time() . '.' . pathinfo($_FILES['wife_photo']['name'], PATHINFO_EXTENSION);
                move_uploaded_file($_FILES['wife_photo']['tmp_name'], "../../assets/images/" . $wife_photo);
            }

            $stmt_cert = $pdo->prepare("INSERT INTO vital_certificates (resident_id, cert_type, cert_number, issue_date, remarks) VALUES (?, 'divorce', ?, ?, ?)");
            $stmt_cert->execute([$husband_id, $cert_number, $issue_date, $remarks]);
            $cert_id = $pdo->lastInsertId();

            $stmt_md = $pdo->prepare("INSERT INTO divorce_details (cert_id, husband_id, wife_id, divorce_date, divorce_place, witness1_name, witness2_name, husband_photo, wife_photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_md->execute([$cert_id, $husband_id, $wife_id, $divorce_date, $divorce_place, $witness1, $witness2, $husband_photo, $wife_photo]);

            $pdo->prepare("UPDATE individuals SET mar = 'Divorced' WHERE id IN (?, ?)")->execute([$husband_id, $wife_id]);

            $pdo->commit();
            $success = "Divorce certificate generated successfully: $cert_number. Please go to the list to process payment and print.";
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
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
            <a href="index.php" class="btn btn-sm btn-primary mt-2">Go to Records List to Pay & Print</a>
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
                    <select name="husband_id" class="form-select border-primary" required id="husbandSelect">
                        <option value="">— Select Husband —</option>
                        <optgroup label="Option 1: Registered Resident">
                            <?php foreach ($males as $r): ?>
                                <option value="<?php echo $r['id']; ?>"
                                        data-name="<?php echo htmlspecialchars("{$r['fname']} {$r['mname']} {$r['lname']}"); ?>"
                                        data-age="<?php echo $r['age'] ?? ''; ?>"
                                        data-photo="<?php echo htmlspecialchars($r['phot'] ?? 'default_profile.png'); ?>">
                                    <?php echo htmlspecialchars("{$r['fname']} {$r['mname']} {$r['lname']}"); ?> (ID: #<?php echo $r['id']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Option 2: Non-Resident">
                            <option value="NEW_HUSBAND" class="text-primary fw-bold">+ Register External</option>
                        </optgroup>
                    </select>
                </div>

                <div id="husbandPreview" class="d-none text-center p-3 bg-light rounded border mb-3">
                    <img id="husbandImg" src="" class="rounded-circle border border-3 border-primary mb-2 shadow-sm" style="width:70px;height:70px;object-fit:cover;">
                    <div class="fw-bold small" id="husbandName"></div>
                    <div class="text-muted extra-small" id="husbandAge"></div>
                </div>

                <div id="newHusbandForm" class="d-none p-3 border border-primary border-opacity-10 rounded bg-light mb-3">
                    <div class="row g-2">
                        <div class="col-4"><input type="text" name="husband_new[fname]" class="form-control form-control-sm" placeholder="First"></div>
                        <div class="col-4"><input type="text" name="husband_new[mname]" class="form-control form-control-sm" placeholder="Middle"></div>
                        <div class="col-4"><input type="text" name="husband_new[lname]" class="form-control form-control-sm" placeholder="Last"></div>
                        <div class="col-12"><input type="date" name="husband_new[bdate]" class="form-control form-control-sm"></div>
                        <div class="col-12"><input type="text" name="husband_new[region]" class="form-control form-control-sm" placeholder="Region/Address"></div>
                    </div>
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
                    <select name="wife_id" class="form-select border-danger" required id="wifeSelect">
                        <option value="">— Select Wife —</option>
                        <optgroup label="Option 1: Registered Resident">
                            <?php foreach ($females as $r): ?>
                                <option value="<?php echo $r['id']; ?>"
                                        data-name="<?php echo htmlspecialchars("{$r['fname']} {$r['mname']} {$r['lname']}"); ?>"
                                        data-age="<?php echo $r['age'] ?? ''; ?>"
                                        data-photo="<?php echo htmlspecialchars($r['phot'] ?? 'default_profile.png'); ?>">
                                    <?php echo htmlspecialchars("{$r['fname']} {$r['mname']} {$r['lname']}"); ?> (ID: #<?php echo $r['id']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Option 2: Non-Resident">
                            <option value="NEW_WIFE" class="text-danger fw-bold">+ Register External</option>
                        </optgroup>
                    </select>
                </div>

                <div id="wifePreview" class="d-none text-center p-3 bg-light rounded border mb-3">
                    <img id="wifeImg" src="" class="rounded-circle border border-3 border-danger mb-2 shadow-sm" style="width:70px;height:70px;object-fit:cover;">
                    <div class="fw-bold small" id="wifeName"></div>
                    <div class="text-muted extra-small" id="wifeAge"></div>
                </div>

                <div id="newWifeForm" class="d-none p-3 border border-danger border-opacity-10 rounded bg-light mb-3">
                    <div class="row g-2">
                        <div class="col-4"><input type="text" name="wife_new[fname]" class="form-control form-control-sm" placeholder="First"></div>
                        <div class="col-4"><input type="text" name="wife_new[mname]" class="form-control form-control-sm" placeholder="Middle"></div>
                        <div class="col-4"><input type="text" name="wife_new[lname]" class="form-control form-control-sm" placeholder="Last"></div>
                        <div class="col-12"><input type="date" name="wife_new[bdate]" class="form-control form-control-sm"></div>
                        <div class="col-12"><input type="text" name="wife_new[region]" class="form-control form-control-sm" placeholder="Region/Address"></div>
                    </div>
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
            <div id="paymentArea" class="d-none animate__animated animate__fadeIn">
                 <?php 
                 require_once __DIR__ . '/../../includes/payment_handler.php';
                 displayPaymentGateway('divorce_cert', 0, '<span id="selectedName"></span>', true); 
                 ?>
                 <button type="submit" class="btn btn-danger w-100 py-3 fw-bold mt-4 fs-6" onclick="return confirm('Are you sure you want to register this divorce? Both parties will be marked as Divorced.')">
                    <i class="fas fa-save me-2"></i>Issue Divorce &amp; Order Service
                </button>
            </div>

            <div id="placeholder" class="card border-dashed border-2 p-5 text-center text-muted bg-light h-100 d-flex align-items-center justify-content-center">
                <i class="fas fa-info-circle fa-4x mb-3 opacity-25"></i>
                <h5>Select a resident to see payment instructions</h5>
                <p class="small">Payment verification happens after saving the record.</p>
            </div>
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
function setupPreview(selectId, previewId, imgId, nameId, ageId, formId) {
    document.getElementById(selectId)?.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        const preview = document.getElementById(previewId);
        const form = document.getElementById(formId);
        const husbandVal = document.getElementById('husbandSelect').value;
        const wifeVal = document.getElementById('wifeSelect').value;
        const payArea = document.getElementById('paymentArea');
        const placeholder = document.getElementById('placeholder');
        
        if (husbandVal || wifeVal) {
            payArea.classList.remove('d-none');
            placeholder.classList.add('d-none');
            const hOpt = document.getElementById('husbandSelect').options[document.getElementById('husbandSelect').selectedIndex];
            const wOpt = document.getElementById('wifeSelect').options[document.getElementById('wifeSelect').selectedIndex];
            
            const primaryName = (husbandVal && husbandVal !== 'NEW_HUSBAND') ? hOpt.dataset.name : 
                               ((wifeVal && wifeVal !== 'NEW_WIFE') ? wOpt.dataset.name : 'Couple');
            document.getElementById('selectedName').textContent = primaryName;
        } else {
            payArea.classList.add('d-none');
            placeholder.classList.remove('d-none');
        }

        if (this.value === 'NEW_HUSBAND' || this.value === 'NEW_WIFE') {
            preview.classList.add('d-none');
            form.classList.remove('d-none');
        } else if (this.value) {
            document.getElementById(imgId).src = '../../assets/images/' + (opt.dataset.photo || 'default_profile.png');
            document.getElementById(imgId).onerror = function() { this.src='https://ui-avatars.com/api/?name=' + encodeURIComponent(opt.dataset.name) + '&size=200&background=4f46e5&color=fff'; };
            document.getElementById(nameId).textContent = opt.dataset.name;
            document.getElementById(ageId).textContent = opt.dataset.age ? opt.dataset.age + ' years old' : '';
            preview.classList.remove('d-none');
            form.classList.add('d-none');
        } else {
            preview.classList.add('d-none');
            form.classList.add('d-none');
        }
    });
}
setupPreview('husbandSelect', 'husbandPreview', 'husbandImg', 'husbandName', 'husbandAge', 'newHusbandForm');
setupPreview('wifeSelect', 'wifePreview', 'wifeImg', 'wifeName', 'wifeAge', 'newWifeForm');
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
