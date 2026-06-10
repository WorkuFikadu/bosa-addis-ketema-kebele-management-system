<?php
// modules/vital/issue_marriage.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/payment_handler.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

// Get male and female residents separately with their marital status
$males = $pdo->query("SELECT i.id, i.fname, i.lname, i.mname, i.phot, i.mar, ag.age 
    FROM individuals i LEFT JOIN ages ag ON i.id = ag.id 
    WHERE i.s = 'Male' AND i.status = 'alive' ORDER BY i.fname")->fetchAll();

$females = $pdo->query("SELECT i.id, i.fname, i.lname, i.mname, i.phot, i.mar, ag.age 
    FROM individuals i LEFT JOIN ages ag ON i.id = ag.id 
    WHERE i.s = 'Female' AND i.status = 'alive' ORDER BY i.fname")->fetchAll();

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $groom_id       = $_POST['groom_id'];
    $bride_id       = $_POST['bride_id'];
    $marriage_date  = $_POST['marriage_date'];
    $marriage_place = ($_POST['marriage_place'] ?? '') ?: 'Bosa Addis Kebele, Jimma';
    $witness1       = $_POST['witness1_name'] ?? '';
    $witness2       = $_POST['witness2_name'] ?? '';
    $issue_date     = date('Y-m-d');
    $remarks        = $_POST['remarks'] ?? '';

    // ———— CHECK: At least one party must be a kebele resident ————
    if ($groom_id === 'NEW_GROOM' && $bride_id === 'NEW_BRIDE') {
        $error = "At least one party must be a resident of this Kebele. We do not issue marriage certificates for two external (non-resident) parties.";
    } else {
    try {
        $pdo->beginTransaction();

        // ———— HANDLE NEW GROOM REGISTRATION ————
        if ($groom_id === 'NEW_GROOM') {
            $gnf = $_POST['groom_new'] ?? [];
            $gp = 'default.png';
            if (isset($_FILES['groom_new_photo']) && $_FILES['groom_new_photo']['error'] === 0) {
                $ext = pathinfo($_FILES['groom_new_photo']['name'], PATHINFO_EXTENSION);
                $gp = 'groom_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['groom_new_photo']['tmp_name'], "../../assets/images/" . $gp);
            }

            $stmt_ng = $pdo->prepare("INSERT INTO individuals (fname, mname, lname, s, mar, nat, occ, status, phot) VALUES (?, ?, ?, 'Male', 'Single', ?, ?, 'alive', ?)");
            $stmt_ng->execute([$gnf['fname'], $gnf['mname'], $gnf['lname'], ($gnf['nat'] ?? 'Ethiopian') ?: 'Ethiopian', ($gnf['occ'] ?? 'Other') ?: 'Other', $gp]);
            $groom_id = $pdo->lastInsertId();

            // Age
            $bdate = $gnf['bdate'];
            $age = $bdate ? date_diff(date_create($bdate), date_create('today'))->y : 20;
            $pdo->prepare("INSERT INTO ages (id, bdate, age) VALUES (?, ?, ?)")->execute([$groom_id, $bdate ?: date('Y-m-d', strtotime('-20 years')), $age]);
            
            // Address (External)
            $pdo->prepare("INSERT INTO addresses (id, region, zone, city, kebele, pho_no, email) VALUES (?, ?, ?, ?, ?, ?, ?)")
                ->execute([$groom_id, ($gnf['region'] ?? 'External') ?: 'External', 'Other', 'Other', 'Other', ($gnf['phone'] ?? 'N/A') ?: 'N/A', '']);
        }

        // ———— HANDLE NEW BRIDE REGISTRATION ————
        if ($bride_id === 'NEW_BRIDE') {
            $bnf = $_POST['bride_new'] ?? [];
            $bp = 'default.png';
            if (isset($_FILES['bride_new_photo']) && $_FILES['bride_new_photo']['error'] === 0) {
                $ext = pathinfo($_FILES['bride_new_photo']['name'], PATHINFO_EXTENSION);
                $bp = 'bride_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['bride_new_photo']['tmp_name'], "../../assets/images/" . $bp);
            }

            $stmt_nb = $pdo->prepare("INSERT INTO individuals (fname, mname, lname, s, mar, nat, occ, status, phot) VALUES (?, ?, ?, 'Female', 'Single', ?, ?, 'alive', ?)");
            $stmt_nb->execute([$bnf['fname'], $bnf['mname'], $bnf['lname'], ($bnf['nat'] ?? 'Ethiopian') ?: 'Ethiopian', ($bnf['occ'] ?? 'Other') ?: 'Other', $bp]);
            $bride_id = $pdo->lastInsertId();

            // Age
            $bdate = $bnf['bdate'];
            $age = $bdate ? date_diff(date_create($bdate), date_create('today'))->y : 20;
            $pdo->prepare("INSERT INTO ages (id, bdate, age) VALUES (?, ?, ?)")->execute([$bride_id, $bdate ?: date('Y-m-d', strtotime('-20 years')), $age]);

            // Address (External)
            $pdo->prepare("INSERT INTO addresses (id, region, zone, city, kebele, pho_no, email) VALUES (?, ?, ?, ?, ?, ?, ?)")
                ->execute([$bride_id, ($bnf['region'] ?? 'External') ?: 'External', 'Other', 'Other', 'Other', ($bnf['phone'] ?? 'N/A') ?: 'N/A', '']);
        }

        if ($groom_id == $bride_id) {
            throw new Exception("Groom and Bride cannot be the same person.");
        }

        // ———— CHECK MARITAL STATUS ————
        if ($groom_id !== 'NEW_GROOM') {
            $stmt_g = $pdo->prepare("SELECT mar, fname, lname FROM individuals WHERE id = ?");
            $stmt_g->execute([$groom_id]);
            $g_data = $stmt_g->fetch();
            if ($g_data && $g_data['mar'] === 'Married') {
                throw new Exception("The selected groom ({$g_data['fname']} {$g_data['lname']}) is already marked as 'Married'. A divorce certificate must be issued first.");
            }
        }
        if ($bride_id !== 'NEW_BRIDE') {
            $stmt_b = $pdo->prepare("SELECT mar, fname, lname FROM individuals WHERE id = ?");
            $stmt_b->execute([$bride_id]);
            $b_data = $stmt_b->fetch();
            if ($b_data && $b_data['mar'] === 'Married') {
                throw new Exception("The selected bride ({$b_data['fname']} {$b_data['lname']}) is already marked as 'Married'. A divorce certificate must be issued first.");
            }
        }

        // ———— ISSUE CERTIFICATE ————
        $lastStmt = $pdo->prepare("SELECT cert_number FROM vital_certificates WHERE cert_type = 'marriage' AND cert_number LIKE 'BA-MR%' ORDER BY id DESC LIMIT 1");
        $lastStmt->execute();
        $lastId = $lastStmt->fetchColumn();
        $nextNumber = $lastId ? (intval(substr($lastId, 5)) + 1) : 1;
        $cert_number = "BA-MR" . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

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
        $success = __('marriage_cert_success') . ": $cert_number. " . __('go_to_list_msg');
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = "Failed: " . $e->getMessage();
    }
    } // end: at least one resident check
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-heart-circle-check me-2 text-danger"></i><?php echo __('issue_marriage_cert'); ?></h2>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i><?php echo __('back'); ?></a>
</div>

<?php if ($success): ?>
    <div class="alert alert-success d-flex align-items-center border-0 shadow-sm mb-4">
        <i class="fas fa-check-circle me-3 fa-2x"></i>
        <div>
            <strong><?php echo __('success'); ?></strong> <?php echo $success; ?><br>
            <a href="index.php" class="btn btn-sm btn-primary mt-2"><?php echo __('go_to_vital_list'); ?></a>
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
                            <span><i class="fas fa-mars me-2"></i><strong><?php echo __('groom'); ?></strong></span>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <label class="form-label fw-bold"><?php echo __('select_groom'); ?></label>
                                <select name="groom_id" class="form-select border-primary" id="groomSelect" required>
                                    <option value="">— <?php echo __('choose_groom'); ?> —</option>
                                    <optgroup label="<?php echo __('opt_registered_resident'); ?>">
                                        <?php foreach ($males as $r): ?>
                                            <option value="<?php echo $r['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars("{$r['fname']} {$r['lname']}"); ?>"
                                                    data-age="<?php echo $r['age'] ?? ''; ?>"
                                                    data-photo="<?php echo htmlspecialchars($r['phot'] ?? 'default_profile.png'); ?>"
                                                    <?php echo ($r['mar'] === 'Married') ? 'class="text-muted" disabled' : ''; ?>>
                                                <?php echo htmlspecialchars("{$r['fname']} {$r['lname']}"); ?> 
                                                (ID: #<?php echo $r['id']; ?>) 
                                                <?php echo ($r['mar'] === 'Married') ? '— [ALREADY MARRIED]' : ''; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <optgroup label="<?php echo __('opt_non_resident'); ?>">
                                        <option value="NEW_GROOM" class="text-primary fw-bold"><?php echo __('register_external'); ?></option>
                                    </optgroup>
                                </select>
                            </div>

                            <div id="groomPreview" class="d-none text-center p-3 bg-light rounded border mb-3">
                                <img id="groomImg" src="" class="rounded-circle border border-3 border-primary mb-2 shadow-sm" style="width:70px;height:70px;object-fit:cover;">
                                <div class="fw-bold small" id="groomName"></div>
                            </div>

                            <div id="newGroomForm" class="d-none">
                                <div class="row g-2">
                                    <div class="col-4"><input type="text" name="groom_new[fname]" class="form-control form-control-sm" placeholder="<?php echo __('first_name_input'); ?>"></div>
                                    <div class="col-4"><input type="text" name="groom_new[mname]" class="form-control form-control-sm" placeholder="<?php echo __('middle_name_input'); ?>"></div>
                                    <div class="col-4"><input type="text" name="groom_new[lname]" class="form-control form-control-sm" placeholder="<?php echo __('last_name_input'); ?>"></div>
                                    <div class="col-6"><input type="date" name="groom_new[bdate]" class="form-control form-control-sm" title="<?php echo __('birth_date'); ?>"></div>
                                    <div class="col-6"><input type="text" name="groom_new[nat]" class="form-control form-control-sm" placeholder="<?php echo __('nationality_input'); ?>" value="Ethiopian"></div>
                                    <div class="col-6"><input type="text" name="groom_new[occ]" class="form-control form-control-sm" placeholder="<?php echo __('occupation_input'); ?>"></div>
                                    <div class="col-6"><input type="text" name="groom_new[region]" class="form-control form-control-sm" placeholder="<?php echo __('region_input'); ?>" value="Oromia"></div>
                                    <div class="col-12"><input type="text" name="groom_new[phone]" class="form-control form-control-sm" placeholder="<?php echo __('phone_number_input'); ?>"></div>
                                </div>
                            </div>
                            
                            <label class="small fw-bold"><?php echo __('cert_photo'); ?></label>
                            <input type="file" name="groom_photo" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>

                <!-- ═══ BRIDE SECTION ═══ -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm overflow-hidden h-100">
                        <div class="p-3 text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #9d174d, #ec4899);">
                            <span><i class="fas fa-venus me-2"></i><strong><?php echo __('bride'); ?></strong></span>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <label class="form-label fw-bold"><?php echo __('select_bride'); ?></label>
                                <select name="bride_id" class="form-select border-danger" id="brideSelect" required>
                                    <option value="">— <?php echo __('choose_bride'); ?> —</option>
                                    <optgroup label="<?php echo __('opt_registered_resident'); ?>">
                                        <?php foreach ($females as $r): ?>
                                            <option value="<?php echo $r['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars("{$r['fname']} {$r['lname']}"); ?>"
                                                    data-age="<?php echo $r['age'] ?? ''; ?>"
                                                    data-photo="<?php echo htmlspecialchars($r['phot'] ?? 'default_profile.png'); ?>"
                                                    <?php echo ($r['mar'] === 'Married') ? 'class="text-muted" disabled' : ''; ?>>
                                                <?php echo htmlspecialchars("{$r['fname']} {$r['lname']}"); ?> 
                                                (ID: #<?php echo $r['id']; ?>)
                                                <?php echo ($r['mar'] === 'Married') ? '— [ALREADY MARRIED]' : ''; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <optgroup label="<?php echo __('opt_non_resident'); ?>">
                                        <option value="NEW_BRIDE" class="text-danger fw-bold"><?php echo __('register_external'); ?></option>
                                    </optgroup>
                                </select>
                            </div>

                            <div id="bridePreview" class="d-none text-center p-3 bg-light rounded border mb-3">
                                <img id="brideImg" src="" class="rounded-circle border border-3 border-danger mb-2 shadow-sm" style="width:70px;height:70px;object-fit:cover;">
                                <div class="fw-bold small" id="brideName"></div>
                            </div>

                            <div id="newBrideForm" class="d-none">
                                <div class="row g-2">
                                    <div class="col-4"><input type="text" name="bride_new[fname]" class="form-control form-control-sm" placeholder="<?php echo __('first_name_input'); ?>"></div>
                                    <div class="col-4"><input type="text" name="bride_new[mname]" class="form-control form-control-sm" placeholder="<?php echo __('middle_name_input'); ?>"></div>
                                    <div class="col-4"><input type="text" name="bride_new[lname]" class="form-control form-control-sm" placeholder="<?php echo __('last_name_input'); ?>"></div>
                                    <div class="col-6"><input type="date" name="bride_new[bdate]" class="form-control form-control-sm" title="<?php echo __('birth_date'); ?>"></div>
                                    <div class="col-6"><input type="text" name="bride_new[nat]" class="form-control form-control-sm" placeholder="<?php echo __('nationality_input'); ?>" value="Ethiopian"></div>
                                    <div class="col-6"><input type="text" name="bride_new[occ]" class="form-control form-control-sm" placeholder="<?php echo __('occupation_input'); ?>"></div>
                                    <div class="col-6"><input type="text" name="bride_new[region]" class="form-control form-control-sm" placeholder="<?php echo __('region_input'); ?>" value="Oromia"></div>
                                    <div class="col-12"><input type="text" name="bride_new[phone]" class="form-control form-control-sm" placeholder="<?php echo __('phone_number_input'); ?>"></div>
                                </div>
                            </div>

                            <label class="small fw-bold"><?php echo __('cert_photo'); ?></label>
                            <input type="file" name="bride_photo" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>

                <!-- ═══ DETAILS ═══ -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm p-4">
                        <h6 class="fw-bold border-bottom pb-2 mb-3"><?php echo __('ceremony_details'); ?></h6>
                        <div class="row g-3">
                            <div class="col-md-3"><label class="small"><?php echo __('marriage_date_label'); ?></label><input type="date" name="marriage_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>"></div>
                            <div class="col-md-3"><label class="small"><?php echo __('marriage_place_label'); ?></label><input type="text" name="marriage_place" class="form-control" placeholder="<?php echo __('marriage_place_label'); ?>" value="Bosa Addis Kebele, Jimma"></div>
                            <div class="col-md-3"><label class="small"><?php echo __('witness'); ?> 1</label><input type="text" name="witness1_name" class="form-control" placeholder="<?php echo __('witness'); ?> 1"></div>
                            <div class="col-md-3"><label class="small"><?php echo __('witness'); ?> 2</label><input type="text" name="witness2_name" class="form-control" placeholder="<?php echo __('witness'); ?> 2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Both External Warning -->
            <div id="bothExternalWarning" class="alert alert-danger border-0 shadow-sm d-none mb-3" role="alert">
                <i class="fas fa-ban me-2"></i>
                <strong>Not Allowed:</strong> At least one party must be a <strong>registered kebele resident</strong>. You cannot issue a marriage certificate for two external (non-resident) parties.
            </div>

            <div id="paymentArea" class="d-none animate__animated animate__fadeIn">
                 <?php displayPaymentGateway('marriage_cert', 0, '<span id="selectedName"></span>', true); ?>
                 <button type="submit" id="submitMarriageBtn" class="btn btn-warning btn-lg w-100 fw-bold shadow-sm py-3"><i class="fas fa-file-signature me-2"></i><?php echo __('issue_cert_order'); ?></button>
            </div>
            
            <div id="placeholder" class="card border-dashed border-2 p-5 text-center text-muted bg-light h-100 d-flex flex-column align-items-center justify-content-center">
                <h6 class="text-danger mb-3 text-uppercase fw-bold"><i class="fas fa-eye me-2"></i><?php echo __('sample_cert_preview'); ?></h6>
                <img src="https://images.unsplash.com/photo-1515934751635-c81c6bc9a2d8?q=80&w=600&auto=format&fit=crop" class="img-fluid rounded shadow-sm border mb-4" style="max-height: 200px; object-fit: cover; opacity: 0.85;">
                <i class="fas fa-info-circle fa-2x mb-2 opacity-25"></i>
                <h5><?php echo __('select_residents_begin'); ?></h5>
                <p class="small"><?php echo __('preview_disclaimer'); ?></p>
            </div>
        </div>
    </div>
</form>

<script>
function checkBothExternal() {
    const groomVal = document.getElementById('groomSelect').value;
    const brideVal = document.getElementById('brideSelect').value;
    const warning = document.getElementById('bothExternalWarning');
    const submitBtn = document.getElementById('submitMarriageBtn');

    if (groomVal === 'NEW_GROOM' && brideVal === 'NEW_BRIDE') {
        warning.classList.remove('d-none');
        submitBtn.disabled = true;
    } else {
        warning.classList.add('d-none');
        submitBtn.disabled = false;
    }
}

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

        // Check if both parties are external after every selection change
        checkBothExternal();
    });
}
handleSelection('groomSelect', 'groomPreview', 'groomImg', 'groomName', 'newGroomForm');
handleSelection('brideSelect', 'bridePreview', 'brideImg', 'brideName', 'newBrideForm');
</script>

