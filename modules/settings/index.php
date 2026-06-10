<?php
// modules/settings/index.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

$message = '';
$error = '';

// Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");
        
        foreach ($_POST['settings'] as $key => $value) {
            $stmt->execute([$value, $key]);
        }
        
        $pdo->commit();
        $message = __("settings_updated");
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = __("settings_update_failed") . $e->getMessage();
    }
}

// Fetch all settings grouped by group
$settingsData = $pdo->query("SELECT * FROM system_settings ORDER BY setting_group, setting_key")->fetchAll();
$groupedSettings = [];
foreach ($settingsData as $row) {
    $groupedSettings[$row['setting_group']][] = $row;
}
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800 fw-bold"><?php echo __('system_settings'); ?></h1>
            <p class="text-muted"><?php echo __('settings_desc'); ?></p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="row">
            <div class="col-lg-8">
                <?php foreach ($groupedSettings as $group => $items): ?>
                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 20px;">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold text-primary text-uppercase small" style="letter-spacing: 1px;">
                                <?php echo ucfirst($group); ?> <?php echo __('group_settings'); ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($items as $setting): ?>
                                <div class="mb-4">
                                    <label class="form-label fw-bold small text-muted text-uppercase"><?php echo str_replace('_', ' ', $setting['setting_key']); ?></label>
                                    <?php 
                                    $is_boolean = in_array($setting['setting_key'], ['dark_mode', 'maintenance_mode', 'enable_public_registration', 'require_email_verification']);
                                    if ($is_boolean): 
                                    ?>
                                        <div class="form-check form-switch mt-2">
                                            <input type="hidden" name="settings[<?php echo $setting['setting_key']; ?>]" value="0">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="settings[<?php echo $setting['setting_key']; ?>]" 
                                                   value="1" 
                                                   id="<?php echo $setting['setting_key']; ?>"
                                                   <?php echo $setting['setting_value'] == '1' ? 'checked' : ''; ?>
                                                   style="transform: scale(1.3); margin-left: -2em;">
                                            <label class="form-check-label ms-3" for="<?php echo $setting['setting_key']; ?>"><?php echo __('enable_feature'); ?></label>
                                        </div>
                                    <?php else: ?>
                                        <input type="text" 
                                               name="settings[<?php echo $setting['setting_key']; ?>]" 
                                               class="form-control form-control-lg border-0 bg-light" 
                                               value="<?php echo htmlspecialchars($setting['setting_value']); ?>"
                                               style="border-radius: 12px;">
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 sticky-top" style="border-radius: 20px; top: 20px;">
                    <div class="card-body p-4 text-center">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 d-inline-block mb-3">
                            <i class="fas fa-save fs-3"></i>
                        </div>
                        <h5 class="fw-bold mb-3"><?php echo __('save'); ?></h5>
                        <p class="small text-muted mb-4"><?php echo __('save_apply_desc'); ?></p>
                        <button type="submit" name="update_settings" class="btn btn-primary btn-lg w-100 shadow-sm" style="border-radius: 15px;">
                            <i class="fas fa-check me-2"></i> <?php echo __('update_settings_btn'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
