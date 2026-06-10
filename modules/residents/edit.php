<?php
// modules/residents/edit.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

if ($_SESSION['role'] === 'security') {
    $id = $_GET['id'] ?? '';
    header("Location: view.php?id=$id");
    exit;
}

$id = $_GET['id'] ?? null;
$stmt = $pdo->prepare("SELECT i.*, a.*, ag.bdate FROM individuals i 
                       LEFT JOIN addresses a ON i.id = a.id 
                       LEFT JOIN ages ag ON i.id = ag.id 
                       WHERE i.id = ?");
$stmt->execute([$id]);
$r = $stmt->fetch();

if (!$r) {
    header('Location: index.php');
    exit;
}

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!$pdo->inTransaction()) {
            $pdo->beginTransaction();
        }

        // Update individuals
        $stmtUpdate = $pdo->prepare("UPDATE individuals SET 
            fname=?, lname=?, mname=?, mar=?, s=?, nat=?, level_edu=?, relg=?, occ=?, 
            mother_full_name=?, father_full_name=?, mother_nat=?, father_nat=?,
            birth_place=?, blood_type=?, emergency_contact_name=?, emergency_contact_phone=?
            WHERE id=?");
        
        $stmtUpdate->execute([
            $_POST['fname'], 
            $_POST['lname'], 
            $_POST['mname'], 
            $_POST['mar'], 
            $_POST['sex'], 
            $_POST['nat'], 
            $_POST['level_edu'], 
            $_POST['relg'], 
            $_POST['occ'], 
            $_POST['mother_full_name'],
            $_POST['father_full_name'],
            $_POST['mother_nat'],
            $_POST['father_nat'],
            $_POST['birth_place'] ?? $r['birth_place'],
            $_POST['blood_type'] ?? $r['blood_type'],
            $_POST['emergency_contact_name'] ?? $r['emergency_contact_name'],
            $_POST['emergency_contact_phone'] ?? $r['emergency_contact_phone'],
            $id
        ]);

        // Update Address
        $stmtAddr = $pdo->prepare("UPDATE addresses SET region=?, zone=?, city=?, kebele=?, pho_no=?, email=?, kebele_zone=?, garee=?, block=? WHERE id=?");
        $stmtAddr->execute([
            $_POST['region'],
            $_POST['zone'],
            $_POST['city'],
            $_POST['kebele'],
            $_POST['pho_no'],
            $_POST['email'],
            !empty($_POST['kebele_zone']) ? intval($_POST['kebele_zone']) : null,
            $_POST['garee'] ?? null,
            $_POST['block'] ?? null,
            $id
        ]);

        // Update Age
        $bdate = $_POST['bdate'];
        $age = date_diff(date_create($bdate), date_create('today'))->y;
        $pdo->prepare("UPDATE ages SET bdate=?, age=? WHERE id=?")->execute([$bdate, $age, $id]);

        // File uploads
        if (!is_dir("../../uploads/docs/")) mkdir("../../uploads/docs/", 0777, true);
        
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $phot_name = time() . '_photo_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], "../../assets/images/" . $phot_name)) {
                $pdo->prepare("UPDATE individuals SET phot=? WHERE id=?")->execute([$phot_name, $id]);
            }
        }

        if (isset($_FILES['doc_birth_cert']) && $_FILES['doc_birth_cert']['error'] === 0) {
            $ext = pathinfo($_FILES['doc_birth_cert']['name'], PATHINFO_EXTENSION);
            $doc_birth = time() . '_birth_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['doc_birth_cert']['tmp_name'], "../../uploads/docs/" . $doc_birth)) {
                $pdo->prepare("UPDATE individuals SET doc_birth_cert=? WHERE id=?")->execute([$doc_birth, $id]);
            }
        }

        if (isset($_FILES['doc_clearance']) && $_FILES['doc_clearance']['error'] === 0) {
            $ext = pathinfo($_FILES['doc_clearance']['name'], PATHINFO_EXTENSION);
            $doc_clearance = time() . '_clearance_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['doc_clearance']['tmp_name'], "../../uploads/docs/" . $doc_clearance)) {
                $pdo->prepare("UPDATE individuals SET doc_clearance=? WHERE id=?")->execute([$doc_clearance, $id]);
            }
        }

        $pdo->commit();
        $success = __('update_success');
        
        // Refresh data
        $stmtRefresh = $pdo->prepare("SELECT i.*, a.*, ag.bdate FROM individuals i 
                               LEFT JOIN addresses a ON i.id = a.id 
                               LEFT JOIN ages ag ON i.id = ag.id 
                               WHERE i.id = ?");
        $stmtRefresh->execute([$id]);
        $r = $stmtRefresh->fetch();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = __('update_failed') . $e->getMessage();
    }
}
?>

<div class="row mb-5">
    <div class="col">
        <h1 class="h2 fw-bold text-dark mb-2">Edit Resident: <?php echo "{$r['fname']} {$r['lname']}"; ?></h1>
        <p class="text-muted"><?php echo __('modify_resident_desc'); ?></p>
    </div>
    <div class="col-auto">
        <a href="index.php" class="btn btn-light border rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i><?php echo __('back_to_list'); ?>
        </a>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4"><?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <div class="row g-4">
        <!-- Main Info Left -->
        <div class="col-xl-8">
            <!-- Personal Info -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 24px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 text-primary"><i class="fas fa-user-circle me-2"></i><?php echo __('personal_info'); ?></h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('first_name'); ?></label>
                            <input type="text" name="fname" class="form-control form-control-lg bg-light border-0" value="<?php echo $r['fname']; ?>" required style="border-radius: 12px;">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('middle_name'); ?></label>
                            <input type="text" name="mname" class="form-control form-control-lg bg-light border-0" value="<?php echo $r['mname']; ?>" required style="border-radius: 12px;">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('last_name'); ?></label>
                            <input type="text" name="lname" class="form-control form-control-lg bg-light border-0" value="<?php echo $r['lname']; ?>" required style="border-radius: 12px;">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('birth_date'); ?></label>
                            <input type="date" name="bdate" class="form-control form-control-lg bg-light border-0" value="<?php echo $r['bdate']; ?>" required style="border-radius: 12px;">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('birth_place'); ?></label>
                            <input type="text" name="birth_place" class="form-control form-control-lg bg-light border-0" value="<?php echo $r['birth_place'] ?? ''; ?>" required style="border-radius: 12px;">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('sex'); ?></label>
                            <select name="sex" class="form-select form-select-lg bg-light border-0" required style="border-radius: 12px;">
                                <option value="Male" <?php if($r['s'] == 'Male') echo 'selected'; ?>><?php echo __('male'); ?></option>
                                <option value="Female" <?php if($r['s'] == 'Female') echo 'selected'; ?>><?php echo __('female'); ?></option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('blood_type'); ?></label>
                            <select name="blood_type" class="form-select form-select-lg bg-light border-0" required style="border-radius: 12px;">
                                <option value="Unknown" <?php if(($r['blood_type']??'Unknown') == 'Unknown') echo 'selected'; ?>><?php echo __('unknown'); ?></option>
                                <option value="A+" <?php if(($r['blood_type']??'') == 'A+') echo 'selected'; ?>>A+</option>
                                <option value="A-" <?php if(($r['blood_type']??'') == 'A-') echo 'selected'; ?>>A-</option>
                                <option value="B+" <?php if(($r['blood_type']??'') == 'B+') echo 'selected'; ?>>B+</option>
                                <option value="B-" <?php if(($r['blood_type']??'') == 'B-') echo 'selected'; ?>>B-</option>
                                <option value="AB+" <?php if(($r['blood_type']??'') == 'AB+') echo 'selected'; ?>>AB+</option>
                                <option value="AB-" <?php if(($r['blood_type']??'') == 'AB-') echo 'selected'; ?>>AB-</option>
                                <option value="O+" <?php if(($r['blood_type']??'') == 'O+') echo 'selected'; ?>>O+</option>
                                <option value="O-" <?php if(($r['blood_type']??'') == 'O-') echo 'selected'; ?>>O-</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('marital_status'); ?></label>
                            <select name="mar" class="form-select form-select-lg bg-light border-0" required style="border-radius: 12px;">
                                <option value="Single" <?php if($r['mar'] == 'Single') echo 'selected'; ?>><?php echo __('single'); ?></option>
                                <option value="Married" <?php if($r['mar'] == 'Married') echo 'selected'; ?>><?php echo __('married'); ?></option>
                                <option value="Divorced" <?php if($r['mar'] == 'Divorced') echo 'selected'; ?>><?php echo __('divorced'); ?></option>
                                <option value="Widowed" <?php if($r['mar'] == 'Widowed') echo 'selected'; ?>><?php echo __('widowed'); ?></option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('nationality'); ?></label>
                            <input type="text" name="nat" class="form-control form-control-lg bg-light border-0" value="<?php echo $r['nat']; ?>" required style="border-radius: 12px;">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('religion'); ?></label>
                            <input type="text" name="relg" class="form-control form-control-lg bg-light border-0" value="<?php echo $r['relg']; ?>" required style="border-radius: 12px;">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('edu_level'); ?></label>
                            <input type="text" name="level_edu" class="form-control form-control-lg bg-light border-0" value="<?php echo $r['level_edu']; ?>" required style="border-radius: 12px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('occupation'); ?></label>
                            <input type="text" name="occ" class="form-control form-control-lg bg-light border-0" value="<?php echo $r['occ']; ?>" required style="border-radius: 12px;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact & Address -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 24px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 text-primary"><i class="fas fa-map-location-dot me-2"></i><?php echo __('contact_address'); ?></h5>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('region'); ?></label>
                            <input type="text" name="region" class="form-control bg-light border-0" value="<?php echo $r['region'] ?? 'Oromia'; ?>" required style="border-radius: 10px;">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('zone'); ?></label>
                            <input type="text" name="zone" class="form-control bg-light border-0" value="<?php echo $r['zone'] ?? 'Jimma'; ?>" required style="border-radius: 10px;">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('city'); ?></label>
                            <input type="text" name="city" class="form-control bg-light border-0" value="<?php echo $r['city'] ?? 'Jimma'; ?>" required style="border-radius: 10px;">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('kebele'); ?></label>
                            <input type="text" name="kebele" class="form-control bg-light border-0" value="<?php echo $r['kebele'] ?? 'Bosa Addis Kebele'; ?>" required style="border-radius: 10px;">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('phone'); ?></label>
                            <input type="text" name="pho_no" class="form-control bg-light border-0" value="<?php echo $r['pho_no']; ?>" required style="border-radius: 10px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('email'); ?></label>
                            <input type="email" name="email" class="form-control bg-light border-0" value="<?php echo $r['email'] ?? ''; ?>" style="border-radius: 10px;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kebele Information -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 24px; border-left: 4px solid #16a34a !important;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-1 text-success"><i class="fas fa-location-pin me-2"></i>Kebele Information</h5>
                    <p class="text-muted small mb-4">Specific location within Bosa Addis Kebele administration.</p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold">Kebele Zone <span class="text-danger">*</span></label>
                            <select name="kebele_zone" class="form-select bg-light border-0" required style="border-radius: 10px;">
                                <option value="">-- Select Zone --</option>
                                <?php for ($z = 1; $z <= 5; $z++): ?>
                                <option value="<?php echo $z; ?>" <?php if(($r['kebele_zone'] ?? '') == $z) echo 'selected'; ?>>
                                    Zone <?php echo $z; ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold">Garee <span class="text-danger">*</span></label>
                            <input type="text" name="garee" class="form-control bg-light border-0" value="<?php echo htmlspecialchars($r['garee'] ?? ''); ?>" placeholder="e.g. Garee 1" required style="border-radius: 10px;">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold">Block</label>
                            <input type="text" name="block" class="form-control bg-light border-0" value="<?php echo htmlspecialchars($r['block'] ?? ''); ?>" placeholder="e.g. Block A" style="border-radius: 10px;">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Emergency -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 24px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 text-danger"><i class="fas fa-truck-medical me-2"></i><?php echo __('emergency_contact'); ?></h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('emergency_contact'); ?> <?php echo __('full_name'); ?></label>
                            <input type="text" name="emergency_contact_name" class="form-control bg-light border-0" value="<?php echo $r['emergency_contact_name'] ?? ''; ?>" style="border-radius: 10px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('emergency_phone'); ?></label>
                            <input type="text" name="emergency_contact_phone" class="form-control bg-light border-0" value="<?php echo $r['emergency_contact_phone'] ?? ''; ?>" style="border-radius: 10px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Right -->
        <div class="col-xl-4">
            <!-- Current Photo -->
            <div class="card border-0 shadow-sm mb-4 overflow-hidden" style="border-radius: 24px;">
                <div class="position-relative">
                    <img src="/Bosa Addis/assets/images/<?php echo $r['phot']; ?>" class="w-100 object-fit-cover" style="height: 250px;">
                    <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-dark bg-opacity-50 text-white backdrop-blur">
                        <p class="small mb-0"><?php echo __('current_profile_photo'); ?></p>
                    </div>
                </div>
                <div class="card-body p-4">
                    <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('update_profile_photo'); ?></label>
                    <input type="file" name="photo" class="form-control border-light shadow-sm" accept="image/*" style="border-radius: 10px;">
                </div>
            </div>

            <!-- Documentation -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 24px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 text-warning"><i class="fas fa-file-shield me-2"></i><?php echo __('supporting_docs'); ?></h5>
                    <div class="mb-4">
                        <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('birth_certificate'); ?></label>
                        <input type="file" name="doc_birth_cert" class="form-control border-light shadow-sm" accept="image/*,application/pdf" style="border-radius: 10px;">
                        <?php if(!empty($r['doc_birth_cert'])): ?>
                            <div class="mt-2 text-xs">
                                <a href="/Bosa Addis/uploads/docs/<?php echo $r['doc_birth_cert']; ?>" target="_blank" class="text-primary text-decoration-none">
                                    <i class="fas fa-download me-1"></i> <?php echo __('view_current_cert'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('clearance_document'); ?></label>
                        <input type="file" name="doc_clearance" class="form-control border-light shadow-sm" accept="image/*,application/pdf" style="border-radius: 10px;">
                        <?php if(!empty($r['doc_clearance'])): ?>
                            <div class="mt-2 text-xs">
                                <a href="/Bosa Addis/uploads/docs/<?php echo $r['doc_clearance']; ?>" target="_blank" class="text-primary text-decoration-none">
                                    <i class="fas fa-download me-1"></i> <?php echo __('view_current_clearance'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Parents -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 24px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 text-primary"><i class="fas fa-people-arrows me-2"></i><?php echo __('parental_info'); ?></h5>
                    <div class="mb-3">
                        <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('mother_name'); ?></label>
                        <input type="text" name="mother_full_name" class="form-control bg-light border-0" value="<?php echo $r['mother_full_name']; ?>" required style="border-radius: 10px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('mother_nat'); ?></label>
                        <input type="text" name="mother_nat" class="form-control bg-light border-0" value="<?php echo $r['mother_nat'] ?? 'Itoophiyaa'; ?>" style="border-radius: 10px;">
                    </div>
                    <hr class="my-4 opacity-50">
                    <div class="mb-3">
                        <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('father_name'); ?></label>
                        <input type="text" name="father_full_name" class="form-control bg-light border-0" value="<?php echo $r['father_full_name']; ?>" required style="border-radius: 10px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('father_nat'); ?></label>
                        <input type="text" name="father_nat" class="form-control bg-light border-0" value="<?php echo $r['father_nat'] ?? 'Itoophiyaa'; ?>" style="border-radius: 10px;">
                    </div>
                </div>
            </div>

            <!-- Action -->
            <div class="card border-0 shadow-lg text-white" style="border-radius: 24px; background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);">
                <div class="card-body p-4 text-center">
                    <h6 class="text-white-50 mb-3 text-uppercase fw-bold text-xs tracking-wider"><?php echo __('sample_id_preview'); ?></h6>
                    <img src="/Bosa Addis/assets/img/samples/sample_id_card.png" onerror="this.src='https://images.unsplash.com/photo-1613243555988-441166d4d6fd?q=80&w=600&auto=format&fit=crop'" class="img-fluid rounded border border-white border-opacity-25 shadow-sm mb-3" style="max-height: 120px; object-fit: contain; background: rgba(255,255,255,0.1);">
                    <h5 class="fw-bold mb-3"><?php echo __('update_profile'); ?></h5>
                    <p class="small text-white-50 mb-4"><?php echo __('verify_changes_msg'); ?></p>
                    <button type="submit" class="btn btn-lg w-100 fw-bold shadow text-white border-0" style="border-radius: 15px; background: linear-gradient(135deg, #22c55e 0%, #15803d 100%);">
                        <i class="fas fa-save me-2"></i><?php echo __('save_all_changes'); ?>
                    </button>
                    <a href="view.php?id=<?php echo $id; ?>" class="btn btn-link text-white-50 btn-sm mt-3 text-decoration-none"><?php echo __('cancel_exit'); ?></a>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
