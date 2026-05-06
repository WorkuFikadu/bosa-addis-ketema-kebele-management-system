<?php
// modules/vital/issue_marriage.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/payment_handler.php';

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

    try {
        $pdo->beginTransaction();

        // ———— HANDLE NEW GROOM REGISTRATION ————
        if ($groom_id === 'NEW_GROOM') {
            $gnf = $_POST['groom_new'];
            $gp = 'default.png';
            if (isset($_FILES['groom_new_photo']) && $_FILES['groom_new_photo']['error'] === 0) {
                $ext = pathinfo($_FILES['groom_new_photo']['name'], PATHINFO_EXTENSION);
                $gp = 'groom_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['groom_new_photo']['tmp_name'], "../../assets/images/" . $gp);
            }

            $stmt_ng = $pdo->prepare("INSERT INTO individuals (fname, mname, lname, s, mar, nat, occ, status, phot) VALUES (?, ?, ?, 'Male', 'Single', ?, ?, 'alive', ?)");
            $stmt_ng->execute([$gnf['fname'], $gnf['mname'], $gnf['lname'], $gnf['nat'] ?: 'Ethiopian', $gnf['occ'] ?: 'Other', $gp]);
            $groom_id = $pdo->lastInsertId();

            // Age
            $bdate = $gnf['bdate'];
            $age = $bdate ? date_diff(date_create($bdate), date_create('today'))->y : 20;
            $pdo->prepare("INSERT INTO ages (id, bdate, age) VALUES (?, ?, ?)")->execute([$groom_id, $bdate ?: date('Y-m-d', strtotime('-20 years')), $age]);
            
            // Address (External)
            $pdo->prepare("INSERT INTO addresses (id, region, zone, city, kebele, pho_no, email) VALUES (?, ?, ?, ?, ?, ?, ?)")
                ->execute([$groom_id, $gnf['region'] ?: 'External', 'Other', 'Other', 'Other', $gnf['phone'] ?: 'N/A', '']);
        }

        // ———— HANDLE NEW BRIDE REGISTRATION ————
        if ($bride_id === 'NEW_BRIDE') {
            $bnf = $_POST['bride_new'];
            $bp = 'default.png';
            if (isset($_FILES['bride_new_photo']) && $_FILES['bride_new_photo']['error'] === 0) {
                $ext = pathinfo($_FILES['bride_new_photo']['name'], PATHINFO_EXTENSION);
                $bp = 'bride_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['bride_new_photo']['tmp_name'], "../../assets/images/" . $bp);
            }

            $stmt_nb = $pdo->prepare("INSERT INTO individuals (fname, mname, lname, s, mar, nat, occ, status, phot) VALUES (?, ?, ?, 'Female', 'Single', ?, ?, 'alive', ?)");
            $stmt_nb->execute([$bnf['fname'], $bnf['mname'], $bnf['lname'], $bnf['nat'] ?: 'Ethiopian', $bnf['occ'] ?: 'Other', $bp]);
            $bride_id = $pdo->lastInsertId();

            // Age
            $bdate = $bnf['bdate'];
            $age = $bdate ? date_diff(date_create($bdate), date_create('today'))->y : 20;
            $pdo->prepare("INSERT INTO ages (id, bdate, age) VALUES (?, ?, ?)")->execute([$bride_id, $bdate ?: date('Y-m-d', strtotime('-20 years')), $age]);

            // Address (External)
            $pdo->prepare("INSERT INTO addresses (id, region, zone, city, kebele, pho_no, email) VALUES (?, ?, ?, ?, ?, ?, ?)")
                ->execute([$bride_id, $bnf['region'] ?: 'External', 'Other', 'Other', 'Other', $bnf['phone'] ?: 'N/A', '']);
        }

        if ($groom_id == $bride_id) {
            throw new Exception("Groom and Bride cannot be the same person.");
        }

        // ———— ISSUE CERTIFICATE ————
        $lastStmt = $pdo->prepare("SELECT cert_number FROM vital_certificates WHERE cert_type = 'marriage' AND cert_number LIKE 'IB-MR%' ORDER BY id DESC LIMIT 1");
        $lastStmt->execute();
        $lastId = $lastStmt->fetchColumn();
        $nextNumber = $lastId ? (intval(substr($lastId, 5)) + 1) : 1;
        $cert_number = "IB-MR" . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        // Photo logic for cert
        $groom_photo = 'default_profile.png';
        if (isset($_FILES['groom_photo']) && $_FILES['groom_photo']['error'] === 0) {
            $groom_photo = 'cert_g_' . time() . '.' . pathinfo($_FILES['groom_photo']['name'], PATHINFO_EXTENSION);
            move_uploaded_file($_FILES['groom_photo']['tmp_name'], "../../assets/images/" . $groom_photo);
        }
        $bride_photo = 'default_profile.png';
        if (isset($_FILES['bride_photo']) && $_FILES['bride_photo']['error'] === 0) {
            $bride_photo = 'cert_b_' . time() . '.' . pathinfo($_FILES['bride_photo']['name'], PATHINFO_EXTENSION);
            move_uploaded_file($_FILES['bride_photo']['tmp_name'], "../../assets/images/" . $bride_photo);
        }

        $stmt_cert = $pdo->prepare("INSERT INTO vital_certificates (resident_id, cert_type, cert_number, issue_date, remarks) VALUES (?, 'marriage', ?, ?, ?)");
        $stmt_cert->execute([$groom_id, $cert_number, $issue_date, $remarks]);
        $cert_id = $pdo->lastInsertId();

        $stmt_md = $pdo->prepare("INSERT INTO marriage_details (cert_id, groom_id, bride_id, marriage_date, marriage_place, witness1_name, witness2_name, groom_photo, bride_photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_md->execute([$cert_id, $groom_id, $bride_id, $marriage_date, $marriage_place, $witness1, $witness2, $groom_photo, $bride_photo]);

        $pdo->prepare("UPDATE individuals SET mar = 'Married' WHERE id IN (?, ?)")->execute([$groom_id, $bride_id]);

        $pdo->commit();
        $success = "Marriage certificate successfully generated: $cert_number. Please go to the list to process payment and print.";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = "Failed: " . $e->getMessage();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-heart-circle-check me-2 text-danger"></i>Issue Marriage Certificate</h2>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i><?php echo __('back'); ?></a>
</div>

<?php if ($success): ?>
    <div class="alert alert-success d-flex align-items-center border-0 shadow-sm mb-4">
        <i class="fas fa-check-circle me-3 fa-2x"></i>
        <div>
            <strong>Success!</strong> <?php echo $success; ?><br>
            <a href="index.php" class="btn btn-sm btn-primary mt-2">Go to Records List to Pay & Print</a>
        </div>
    </div>
<?php endif; ?>

<?php if ($error): ?><div class="alert alert-danger border-0 shadow-sm"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div><?php endif; ?>

<form method="POST" enctype="multipart/form-data" id="marriageForm">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="row g-4">
                <!-- ═══ GROOM SECTION ═══ -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm overflow-hidden h-100">
                        <div class="p-3 text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #1e3a5f, #2563eb);">
                            <span><i class="fas fa-mars me-2"></i><strong>GROOM / ሙሽራ</strong></span>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Select Groom</label>
                                <select name="groom_id" class="form-select border-primary" id="groomSelect" required>
                                    <option value="">— Choose Groom —</option>
                                    <optgroup label="Option 1: Registered Resident">
                                        <?php foreach ($males as $r): ?>
                                            <option value="<?php echo $r['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars("{$r['fname']} {$r['lname']}"); ?>"
                                                    data-age="<?php echo $r['age'] ?? ''; ?>"
                                                    data-photo="<?php echo htmlspecialchars($r['phot'] ?? 'default_profile.png'); ?>">
                                                <?php echo htmlspecialchars("{$r['fname']} {$r['lname']}"); ?> (ID: #<?php echo $r['id']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <optgroup label="Option 2: Non-Resident">
                                        <option value="NEW_GROOM" class="text-primary fw-bold">+ Register External</option>
                                    </optgroup>
                                </select>
                            </div>

                            <div id="groomPreview" class="d-none text-center p-3 bg-light rounded border mb-3">
                                <img id="groomImg" src="" class="rounded-circle border border-3 border-primary mb-2 shadow-sm" style="width:70px;height:70px;object-fit:cover;">
                                <div class="fw-bold small" id="groomName"></div>
                            </div>

                            <div id="newGroomForm" class="d-none p-3 border border-primary border-opacity-10 rounded bg-light mb-3">
                                <div class="row g-2">
                                    <div class="col-4"><input type="text" name="groom_new[fname]" class="form-control form-control-sm" placeholder="First"></div>
                                    <div class="col-4"><input type="text" name="groom_new[mname]" class="form-control form-control-sm" placeholder="Middle"></div>
                                    <div class="col-4"><input type="text" name="groom_new[lname]" class="form-control form-control-sm" placeholder="Last"></div>
                                    <div class="col-12"><input type="date" name="groom_new[bdate]" class="form-control form-control-sm"></div>
                                </div>
                            </div>
                            
                            <label class="small fw-bold">Certificate Photo</label>
                            <input type="file" name="groom_photo" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>

                <!-- ═══ BRIDE SECTION ═══ -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm overflow-hidden h-100">
                        <div class="p-3 text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #9d174d, #ec4899);">
                            <span><i class="fas fa-venus me-2"></i><strong>BRIDE / ሙሽሪት</strong></span>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Select Bride</label>
                                <select name="bride_id" class="form-select border-danger" id="brideSelect" required>
                                    <option value="">— Choose Bride —</option>
                                    <optgroup label="Option 1: Registered Resident">
                                        <?php foreach ($females as $r): ?>
                                            <option value="<?php echo $r['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars("{$r['fname']} {$r['lname']}"); ?>"
                                                    data-age="<?php echo $r['age'] ?? ''; ?>"
                                                    data-photo="<?php echo htmlspecialchars($r['phot'] ?? 'default_profile.png'); ?>">
                                                <?php echo htmlspecialchars("{$r['fname']} {$r['lname']}"); ?> (ID: #<?php echo $r['id']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <optgroup label="Option 2: Non-Resident">
                                        <option value="NEW_BRIDE" class="text-danger fw-bold">+ Register External</option>
                                    </optgroup>
                                </select>
                            </div>

                            <div id="bridePreview" class="d-none text-center p-3 bg-light rounded border mb-3">
                                <img id="brideImg" src="" class="rounded-circle border border-3 border-danger mb-2 shadow-sm" style="width:70px;height:70px;object-fit:cover;">
                                <div class="fw-bold small" id="brideName"></div>
                            </div>

                            <div id="newBrideForm" class="d-none p-3 border border-danger border-opacity-10 rounded bg-light mb-3">
                                <div class="row g-2">
                                    <div class="col-4"><input type="text" name="bride_new[fname]" class="form-control form-control-sm" placeholder="First"></div>
                                    <div class="col-4"><input type="text" name="bride_new[mname]" class="form-control form-control-sm" placeholder="Middle"></div>
                                    <div class="col-4"><input type="text" name="bride_new[lname]" class="form-control form-control-sm" placeholder="Last"></div>
                                    <div class="col-12"><input type="date" name="bride_new[bdate]" class="form-control form-control-sm"></div>
                                </div>
                            </div>

                            <label class="small fw-bold">Certificate Photo</label>
                            <input type="file" name="bride_photo" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>

                <!-- ═══ DETAILS ═══ -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm p-4">
                        <h6 class="fw-bold border-bottom pb-2 mb-3">Ceremony Details</h6>
                        <div class="row g-3">
                            <div class="col-md-4"><label class="small">Marriage Date</label><input type="date" name="marriage_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>"></div>
                            <div class="col-md-4"><label class="small">Witness 1</label><input type="text" name="witness1_name" class="form-control" placeholder="Witness 1"></div>
                            <div class="col-md-4"><label class="small">Witness 2</label><input type="text" name="witness2_name" class="form-control" placeholder="Witness 2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div id="paymentArea" class="d-none animate__animated animate__fadeIn">
                 <?php displayPaymentGateway('marriage_cert', 0, '<span id="selectedName"></span>', true); ?>
                 <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow mt-3">
                    <i class="fas fa-save me-2"></i>Issue Certificate & Order Service
                </button>
            </div>
            
            <div id="placeholder" class="card border-dashed border-2 p-5 text-center text-muted bg-light h-100 d-flex align-items-center justify-content-center">
                <i class="fas fa-info-circle fa-4x mb-3 opacity-25"></i>
                <h5>Select a resident to see payment instructions</h5>
                <p class="small">Payment verification happens after saving the record.</p>
            </div>
        </div>
    </div>
</form>

<script>
function handleSelection(selectId, previewId, imgId, nameId, formId) {
    const s = document.getElementById(selectId);
    s.addEventListener('change', function() {
        const val = this.value;
        const p = document.getElementById(previewId);
        const f = document.getElementById(formId);
        const opt = this.options[this.selectedIndex];
        
        // Show/Hide Payment Area logic
        const groomVal = document.getElementById('groomSelect').value;
        const brideVal = document.getElementById('brideSelect').value;
        const payArea = document.getElementById('paymentArea');
        const placeholder = document.getElementById('placeholder');
        
        if (groomVal || brideVal) {
            payArea.classList.remove('d-none');
            placeholder.classList.add('d-none');
            const primaryName = (groomVal && groomVal !== 'NEW_GROOM') ? document.getElementById('groomSelect').options[document.getElementById('groomSelect').selectedIndex].dataset.name : 
                               ((brideVal && brideVal !== 'NEW_BRIDE') ? document.getElementById('brideSelect').options[document.getElementById('brideSelect').selectedIndex].dataset.name : 'Couple');
            document.getElementById('selectedName').textContent = primaryName;
        } else {
            payArea.classList.add('d-none');
            placeholder.classList.remove('d-none');
        }

        if (val.startsWith('NEW_')) {
            p.classList.add('d-none'); f.classList.remove('d-none');
        } else if (val) {
            document.getElementById(imgId).src = '../../assets/images/' + (opt.dataset.photo || 'default_profile.png');
            document.getElementById(nameId).textContent = opt.dataset.name;
            p.classList.remove('d-none'); f.classList.add('d-none');
        } else {
            p.classList.add('d-none'); f.classList.add('d-none');
        }
    });
}
handleSelection('groomSelect', 'groomPreview', 'groomImg', 'groomName', 'newGroomForm');
handleSelection('brideSelect', 'bridePreview', 'brideImg', 'brideName', 'newBrideForm');
</script>

