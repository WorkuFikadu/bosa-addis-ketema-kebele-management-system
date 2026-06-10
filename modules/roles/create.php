<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role_name = $_POST['role_name'];
    $role_key = strtolower(str_replace(' ', '_', $_POST['role_key'] ?: $role_name));
    $description = $_POST['description'];

    if (!empty($role_name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO system_roles (role_name, role_key, description) VALUES (?, ?, ?)");
            $stmt->execute([$role_name, $role_key, $description]);
            $success = "Role '$role_name' has been successfully created.";
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in the role name.";
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Add New System Role</h2>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
</div>

<?php if ($success): ?> <div class="alert alert-success"><?php echo $success; ?></div> <?php endif; ?>
<?php if ($error): ?> <div class="alert alert-danger"><?php echo $error; ?></div> <?php endif; ?>

<div class="card p-4 shadow-sm max-w-lg" style="border-radius: 20px;">
    <form method="POST">
        <div class="mb-3">
            <label class="form-label fw-semibold">Role Name <span class="text-danger">*</span></label>
            <input type="text" name="role_name" class="form-control" required placeholder="e.g. Secretary">
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Role Key <small class="text-muted">(Optional - auto generated if empty)</small></label>
            <input type="text" name="role_key" class="form-control" placeholder="e.g. secretary">
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="What parts of the system can this role access?"></textarea>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-success px-5 py-2" style="border-radius: 12px;">
                <i class="fas fa-save me-2"></i>Create Role
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
