<?php
// modules/residents/create.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 0. Verify Mandatory Fields
        $required_fields = ['fname', 'lname', 'mname', 'bdate', 'birth_place', 'sex', 'mar', 'nat', 'relg', 'level_edu', 'occ', 'region', 'zone', 'city', 'kebele', 'pho_no', 'mother_full_name', 'father_full_name'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Mandatory field '$field' is missing. All personal information must be fulfilled.");
            }
        }

        if ($_FILES['photo']['error'] !== 0) {
            throw new Exception("Profile photo is required for resident registration.");
        }

        // Supporting documents are now optional per user request.

        if (!$pdo->inTransaction()) {
            $pdo->beginTransaction();
        }

        // Handle File Uploads
        $phot = 'default.png';
        $doc_birth = '';
        $doc_clearance = '';

        if (!is_dir("../../uploads/docs/")) {
            mkdir("../../uploads/docs/", 0777, true);
        }
        if (!is_dir("../../assets/images/")) {
            mkdir("../../assets/images/", 0777, true);
        }

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $phot = time() . '_photo_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], "../../assets/images/" . $phot);
        }

        if (isset($_FILES['doc_birth_cert']) && $_FILES['doc_birth_cert']['error'] === 0) {
            $ext = pathinfo($_FILES['doc_birth_cert']['name'], PATHINFO_EXTENSION);
            $doc_birth = time() . '_birth_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['doc_birth_cert']['tmp_name'], "../../uploads/docs/" . $doc_birth);
        }

        if (isset($_FILES['doc_clearance']) && $_FILES['doc_clearance']['error'] === 0) {
            $ext = pathinfo($_FILES['doc_clearance']['name'], PATHINFO_EXTENSION);
            $doc_clearance = time() . '_clearance_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['doc_clearance']['tmp_name'], "../../uploads/docs/" . $doc_clearance);
        }

        // 1. Insert into individuals
        $stmt = $pdo->prepare("INSERT INTO individuals (fname, lname, mname, mar, s, nat, level_edu, relg, occ, phot, 
                                mother_full_name, father_full_name, mother_nat, father_nat,
                                birth_place, blood_type, emergency_contact_name, emergency_contact_phone, 
                                doc_birth_cert, doc_clearance) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $_POST['fname'], $_POST['lname'], $_POST['mname'], $_POST['mar'], $_POST['sex'], $_POST['nat'], 
            $_POST['level_edu'], $_POST['relg'], $_POST['occ'], $phot, 
            $_POST['mother_full_name'] ?? '', $_POST['father_full_name'] ?? '', 
            $_POST['mother_nat'] ?? 'Itoophiyaa', $_POST['father_nat'] ?? 'Itoophiyaa',
            $_POST['birth_place'] ?? '', $_POST['blood_type'] ?? '', 
            $_POST['emergency_contact_name'] ?? '', $_POST['emergency_contact_phone'] ?? '', 
            $doc_birth, $doc_clearance
        ]);
        
        $resident_id = $pdo->lastInsertId();

        // 2. Insert into ages
        $bdate = $_POST['bdate'];
        $age = date_diff(date_create($bdate), date_create('today'))->y;
        $stmt_age = $pdo->prepare("INSERT INTO ages (id, bdate, age) VALUES (?, ?, ?)");
        $stmt_age->execute([$resident_id, $bdate, $age]);

        // 3. Insert into addresses
        $stmt_addr = $pdo->prepare("INSERT INTO addresses (id, region, zone, city, kebele, pho_no, email, kebele_zone, garee, block) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_addr->execute([
            $resident_id,
            $_POST['region'],
            $_POST['zone'],
            $_POST['city'],
            $_POST['kebele'],
            $_POST['pho_no'],
            $_POST['email'],
            !empty($_POST['kebele_zone']) ? intval($_POST['kebele_zone']) : null,
            $_POST['garee'] ?? null,
            $_POST['block'] ?? null
        ]);

        $pdo->commit();
        log_activity($pdo, 'CREATED', 'residents', $resident_id, "Registered new resident: {$_POST['fname']} {$_POST['lname']}");
        $success = "Resident registered successfully! ID: #$resident_id";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = "Failed to register resident: " . $e->getMessage();
    }
}
?>

<div class="row mb-5">
    <div class="col">
        <h1 class="h2 fw-bold text-dark mb-2"><?php echo __('reg_new_resident'); ?></h1>
        <p class="text-muted">Enter core administrative data and supporting documents for the constituent.</p>
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
                            <input type="text" name="fname" class="form-control form-control-lg bg-light border-0" required style="border-radius: 12px;">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('middle_name'); ?></label>
                            <input type="text" name="mname" class="form-control form-control-lg bg-light border-0" required style="border-radius: 12px;">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('last_name'); ?></label>
                            <input type="text" name="lname" class="form-control form-control-lg bg-light border-0" required style="border-radius: 12px;">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('birth_date'); ?></label>
                            <input type="date" name="bdate" class="form-control form-control-lg bg-light border-0" required style="border-radius: 12px;">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('birth_place'); ?></label>
                            <select name="birth_place" class="form-select form-select-lg bg-light border-0" required style="border-radius: 12px;">
                                <option value="">-- Select Birth Place --</option>
                                <optgroup label="Jimma Zone (Woredas & Towns)">
                                    <option value="Jimma City">Jimma City</option>
                                    <option value="Agaro Town">Agaro Town</option>
                                    <option value="Botor Xolay">Botor Xolay</option>
                                    <option value="Chora Botor">Chora Botor</option>
                                    <option value="Dedo">Dedo</option>
                                    <option value="Gera">Gera</option>
                                    <option value="Gomma">Gomma</option>
                                    <option value="Guma">Guma</option>
                                    <option value="Kersa">Kersa</option>
                                    <option value="Limmu Kosa">Limmu Kosa</option>
                                    <option value="Limmu Sakka">Limmu Sakka</option>
                                    <option value="Mana">Mana</option>
                                    <option value="Mancho">Mancho</option>
                                    <option value="Nadhi Gibe">Nadhi Gibe</option>
                                    <option value="Nono Benja">Nono Benja</option>
                                    <option value="Omo Beyam">Omo Beyam</option>
                                    <option value="Omo Nada">Omo Nada</option>
                                    <option value="Seka Chekorsa">Seka Chekorsa</option>
                                    <option value="Setema">Setema</option>
                                    <option value="Shebe Senbo">Shebe Senbo</option>
                                    <option value="Sigmo">Sigmo</option>
                                    <option value="Sokoru">Sokoru</option>
                                </optgroup>
                                <optgroup label="Other Locations">
                                    <option value="Oromia (Other)">Oromia (Other Zone)</option>
                                    <option value="Addis Ababa">Addis Ababa</option>
                                    <option value="Amhara">Amhara</option>
                                    <option value="Sidama">Sidama</option>
                                    <option value="SNNPR">SNNPR</option>
                                    <option value="South West Ethiopia">South West Ethiopia</option>
                                    <option value="Somali">Somali</option>
                                    <option value="Afar">Afar</option>
                                    <option value="Tigray">Tigray</option>
                                    <option value="Harari">Harari</option>
                                    <option value="Dire Dawa">Dire Dawa</option>
                                    <option value="Gambela">Gambela</option>
                                    <option value="Benishangul-Gumuz">Benishangul-Gumuz</option>
                                    <option value="Other">Other</option>
                                </optgroup>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('sex'); ?></label>
                            <select name="sex" class="form-select form-select-lg bg-light border-0" required style="border-radius: 12px;">
                                <option value="Male"><?php echo __('male'); ?></option>
                                <option value="Female"><?php echo __('female'); ?></option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('blood_type'); ?></label>
                            <select name="blood_type" class="form-select form-select-lg bg-light border-0" required style="border-radius: 12px;">
                                <option value="Unknown">Unknown</option>
                                <option value="A+">A+</option><option value="A-">A-</option>
                                <option value="B+">B+</option><option value="B-">B-</option>
                                <option value="AB+">AB+</option><option value="AB-">AB-</option>
                                <option value="O+">O+</option><option value="O-">O-</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('marital_status'); ?></label>
                            <select name="mar" class="form-select form-select-lg bg-light border-0" required style="border-radius: 12px;">
                                <option value="Single"><?php echo __('single'); ?></option>
                                <option value="Married"><?php echo __('married'); ?></option>
                                <option value="Divorced"><?php echo __('divorced'); ?></option>
                                <option value="Widowed"><?php echo __('widowed'); ?></option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('nationality'); ?></label>
                            <input type="text" name="nat" class="form-control form-control-lg bg-light border-0" value="Ethiopian" required style="border-radius: 12px;">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('religion'); ?></label>
                            <select name="relg" class="form-select form-select-lg bg-light border-0" required style="border-radius: 12px;">
                                <option value="">-- Select Religion --</option>
                                <option value="Orthodox">Orthodox</option>
                                <option value="Muslim">Muslim</option>
                                <option value="Protestant">Protestant</option>
                                <option value="Catholic">Catholic</option>
                                <option value="Traditional">Traditional</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('edu_level'); ?></label>
                            <select name="level_edu" class="form-select form-select-lg bg-light border-0" required style="border-radius: 12px;">
                                <option value="">-- Select Education --</option>
                                <option value="Illiterate">Illiterate</option>
                                <option value="Basic Education">Basic Education</option>
                                <option value="Primary School">Primary School (Grades 1-8)</option>
                                <option value="Secondary School">Secondary School (Grades 9-12)</option>
                                <option value="TVET/Certificate">TVET / Certificate</option>
                                <option value="Diploma">Diploma</option>
                                <option value="Bachelor's Degree">Bachelor's Degree</option>
                                <option value="Master's Degree">Master's Degree</option>
                                <option value="PhD">PhD</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('occupation'); ?></label>
                            <select name="occ" class="form-select form-select-lg bg-light border-0" required style="border-radius: 12px;">
                                <option value="">-- Select Occupation --</option>
                                <option value="Farmer">Farmer</option>
                                <option value="Merchant / Business Owner">Merchant / Business Owner</option>
                                <option value="Government Employee">Government Employee</option>
                                <option value="Private Sector Employee">Private Sector Employee</option>
                                <option value="Daily Laborer">Daily Laborer</option>
                                <option value="Student">Student</option>
                                <option value="Housewife">Housewife</option>
                                <option value="Pensioner / Retired">Pensioner / Retired</option>
                                <option value="Unemployed">Unemployed</option>
                                <option value="Other">Other</option>
                            </select>
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
                            <input type="text" name="region" class="form-control bg-light border-0" value="Oromia" required style="border-radius: 10px;">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('zone'); ?></label>
                            <input type="text" name="zone" class="form-control bg-light border-0" value="Jimma" required style="border-radius: 10px;">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('city'); ?></label>
                            <input type="text" name="city" class="form-control bg-light border-0" value="Jimma" required style="border-radius: 10px;">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('kebele'); ?></label>
                            <input type="text" name="kebele" class="form-control bg-light border-0" value="Bosa Addis Kebele" required style="border-radius: 10px;">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('phone'); ?></label>
                            <input type="text" name="pho_no" class="form-control bg-light border-0" placeholder="+251..." required style="border-radius: 10px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('email'); ?></label>
                            <input type="email" name="email" class="form-control bg-light border-0" style="border-radius: 10px;">
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
                                <option value="1">Zone 1</option>
                                <option value="2">Zone 2</option>
                                <option value="3">Zone 3</option>
                                <option value="4">Zone 4</option>
                                <option value="5">Zone 5</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold">Garee <span class="text-danger">*</span></label>
                            <input type="text" name="garee" class="form-control bg-light border-0" placeholder="e.g. Garee 1" required style="border-radius: 10px;">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted text-uppercase fw-bold">Block</label>
                            <input type="text" name="block" class="form-control bg-light border-0" placeholder="e.g. Block A" style="border-radius: 10px;">
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
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('emergency_contact'); ?> Name</label>
                            <input type="text" name="emergency_contact_name" class="form-control bg-light border-0" style="border-radius: 10px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('emergency_phone'); ?></label>
                            <input type="text" name="emergency_contact_phone" class="form-control bg-light border-0" style="border-radius: 10px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Right -->
        <div class="col-xl-4">
            <!-- Documentation -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 24px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 text-warning"><i class="fas fa-file-shield me-2"></i><?php echo __('supporting_docs'); ?></h5>
                    <div class="mb-4">
                        <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('profile_photo'); ?> <span class="text-danger">*</span></label>
                        <input type="file" name="photo" class="form-control border-light shadow-sm" accept="image/*" required style="border-radius: 10px;">
                    </div>
                    <div class="mb-4">
                        <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('birth_certificate'); ?></label>
                        <input type="file" name="doc_birth_cert" class="form-control border-light shadow-sm" accept="image/*,application/pdf" style="border-radius: 10px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('clearance_document'); ?></label>
                        <input type="file" name="doc_clearance" class="form-control border-light shadow-sm" accept="image/*,application/pdf" style="border-radius: 10px;">
                    </div>
                    <p class="small text-muted mt-2"><i class="fas fa-info-circle me-1"></i> Official government documents are optional but recommended for full verification.</p>
                </div>
            </div>

            <!-- Parents -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 24px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 text-primary"><i class="fas fa-people-arrows me-2"></i><?php echo __('parental_info'); ?></h5>
                    <div class="mb-3">
                        <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('mother_name'); ?></label>
                        <input type="text" name="mother_full_name" class="form-control bg-light border-0" required style="border-radius: 10px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted text-uppercase fw-bold"><?php echo __('father_name'); ?></label>
                        <input type="text" name="father_full_name" class="form-control bg-light border-0" required style="border-radius: 10px;">
                    </div>
                </div>
            </div>

            <!-- Action -->
            <div class="card border-0 shadow-lg text-white" style="border-radius: 24px; background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);">
                <div class="card-body p-4 text-center">
                    <h6 class="text-white-50 mb-3 text-uppercase fw-bold text-xs tracking-wider">Sample ID Preview</h6>
                    <img src="/Bosa Addis/assets/img/samples/sample_id_card.png" onerror="this.src='https://images.unsplash.com/photo-1613243555988-441166d4d6fd?q=80&w=600&auto=format&fit=crop'" class="img-fluid rounded border border-white border-opacity-25 shadow-sm mb-3" style="max-height: 120px; object-fit: contain; background: rgba(255,255,255,0.1);">
                    <h5 class="fw-bold mb-3">Complete Registration</h5>
                    <p class="small text-white-50 mb-4">Ensure all data matches physical documents before final submission.</p>
                    <button type="submit" class="btn btn-lg w-100 fw-bold shadow text-white border-0" style="border-radius: 15px; background: linear-gradient(135deg, #22c55e 0%, #15803d 100%);">
                        <i class="fas fa-plus-circle me-2"></i>Register Now
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
