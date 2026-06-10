<?php
// modules/economic/youth_create.php — Detailed Youth Empowerment Registration
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../auth/login.php'); exit; }

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $individual_id = $_POST['individual_id'];
    $education_level = $_POST['education_level'];
    $skills = $_POST['skills'];
    $training_history = $_POST['training_history'];
    $training_interests = $_POST['training_interests'];
    $employment_status = $_POST['employment_status'];
    $preferred_sector = $_POST['preferred_sector'];
    $disability_status = $_POST['disability_status'];
    $dependency_count = $_POST['dependency_count'];
    $registration_date = $_POST['registration_date'];

    if ($individual_id && $registration_date) {
        try {
            $stmt = $pdo->prepare("INSERT INTO economic_youth_registry (individual_id, education_level, skills, training_history, training_interests, employment_status, preferred_sector, disability_status, dependency_count, registration_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$individual_id, $education_level, $skills, $training_history, $training_interests, $employment_status, $preferred_sector, $disability_status, $dependency_count, $registration_date]);
            $success = "youth_profile_success";
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "fill_required_fields";
    }
}

$residents = $pdo->query("SELECT id, fname, lname FROM individuals WHERE id NOT IN (SELECT individual_id FROM economic_youth_registry) ORDER BY fname")->fetchAll();
?>

<div class="mb-3">
    <a href="youth_list.php" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold shadow-sm">
        <i class="fas fa-arrow-left me-2"></i><?php echo __('back_to_youth_registry'); ?>
    </a>
</div>

<div class="card border-0 shadow-lg rounded-4 overflow-hidden max-width-900 mx-auto">
    <div class="card-header bg-danger text-white p-4 border-0 text-center">
        <h4 class="fw-black mb-0"><i class="fas fa-user-astronaut me-2"></i><?php echo __('youth_profile_title'); ?></h4>
        <p class="mb-0 small opacity-75"><?php echo __('youth_profile_desc'); ?></p>
    </div>
    
    <div class="card-body p-4 p-md-5">
        <?php if($success): ?>
            <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4"><i class="fas fa-check-circle me-2"></i><?php echo __($success); ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4"><i class="fas fa-exclamation-circle me-2"></i><?php echo __($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="row g-4">
            <!-- SECTION 1: IDENTITY -->
            <div class="col-12"><h6 class="fw-black text-danger border-bottom pb-2 mb-0">1. <?php echo __('core_identity'); ?></h6></div>
            <div class="col-md-8">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('select_resident'); ?> <span class="text-danger">*</span></label>
                <select name="individual_id" class="form-select rounded-pill px-4 bg-light border-0 py-2 shadow-sm" required>
                    <option value=""><?php echo __('choose_resident'); ?></option>
                    <?php foreach ($residents as $r): ?>
                        <option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars("{$r['fname']} {$r['lname']} (ID: #{$r['id']})"); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                 <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('reg_date'); ?></label>
                 <input type="date" name="registration_date" class="form-control rounded-pill px-4 bg-light border-0 py-2 shadow-sm" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <!-- SECTION 2: SKILLS & EDUCATION -->
            <div class="col-12 mt-5"><h6 class="fw-black text-danger border-bottom pb-2 mb-0">2. <?php echo __('skills_edu_training'); ?></h6></div>
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('edu_level_label'); ?></label>
                <select name="education_level" class="form-select rounded-pill px-4 bg-light border-0 py-2 shadow-sm">
                    <option value="None"><?php echo __('none'); ?></option>
                    <option value="Primary"><?php echo __('primary'); ?></option>
                    <option value="Secondary"><?php echo __('secondary'); ?></option>
                    <option value="TVET / Diploma"><?php echo __('tvet'); ?></option>
                    <option value="Bachelor Degree"><?php echo __('bachelors'); ?></option>
                    <option value="Masters / Above"><?php echo __('masters_plus'); ?></option>
                </select>
            </div>
            <div class="col-md-6">
                 <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('current_employment'); ?></label>
                 <select name="employment_status" class="form-select rounded-pill px-4 bg-light border-0 py-2 shadow-sm" required>
                    <option value="Unemployed"><?php echo __('unemployed'); ?></option>
                    <option value="Self-employed"><?php echo __('self_employed'); ?></option>
                    <option value="Employed"><?php echo __('employed'); ?></option>
                    <option value="Student"><?php echo __('student'); ?></option>
                </select>
            </div>
            <div class="col-md-12">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('edu_skills'); ?></label>
                <textarea name="skills" class="form-control rounded-4 px-4 bg-light border-0 py-3 shadow-sm" rows="2" placeholder="<?php echo __('skills_placeholder', 'What can they do already?'); ?>"></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('training_history'); ?></label>
                <textarea name="training_history" class="form-control rounded-4 px-4 bg-light border-0 py-3 shadow-sm" rows="2" placeholder="<?php echo __('training_history_placeholder', 'List any workshops/courses...'); ?>"></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('training_interests'); ?></label>
                <textarea name="training_interests" class="form-control rounded-4 px-4 bg-light border-0 py-3 shadow-sm" rows="2" placeholder="<?php echo __('training_interests_placeholder', 'What do they want to learn?'); ?>"></textarea>
            </div>

            <!-- SECTION 3: SOCIAL CONTEXT -->
            <div class="col-12 mt-5"><h6 class="fw-black text-danger border-bottom pb-2 mb-0">3. <?php echo __('social_empowerment_context'); ?></h6></div>
            <div class="col-md-4">
                 <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('preferred_sector'); ?></label>
                 <input type="text" name="preferred_sector" class="form-control rounded-pill px-4 bg-light border-0 py-2 shadow-sm" placeholder="<?php echo __('desired_field_placeholder', 'Desired field...'); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('disability_status'); ?></label>
                <select name="disability_status" class="form-select rounded-pill px-4 bg-light border-0 py-2 shadow-sm">
                    <option value="None"><?php echo __('none_able_bodied', 'None (Able-bodied)'); ?></option>
                    <option value="Physical"><?php echo __('physical_disability', 'Physical Disability'); ?></option>
                    <option value="Visual"><?php echo __('visual_impairment', 'Visual Impairment'); ?></option>
                    <option value="Hearing"><?php echo __('hearing_impairment', 'Hearing Impairment'); ?></option>
                    <option value="Other"><?php echo __('other_special_needs', 'Other Specialized Needs'); ?></option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase"><?php echo __('dependents'); ?></label>
                <input type="number" name="dependency_count" class="form-control rounded-pill px-4 bg-light border-0 py-2 shadow-sm" value="0" min="0">
            </div>

            <div class="col-12 mt-5">
                <button type="submit" class="btn btn-danger text-white w-100 rounded-pill py-3 fw-black shadow-lg">
                    <i class="fas fa-rocket me-2"></i><?php echo __('submit_youth_profile'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.max-width-900 { max-width: 900px; }
</style>

<?php require_once '../../includes/footer.php'; ?>
