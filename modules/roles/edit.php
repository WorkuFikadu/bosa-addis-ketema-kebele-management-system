<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM system_roles WHERE id = ?");
$stmt->execute([$id]);
$role = $stmt->fetch();
if (!$role) { header('Location: index.php'); exit; }

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role_name = $_POST['role_name'];
    $role_key = $_POST['role_key'];
    $description = $_POST['description'];

    if (!empty($role_name)) {
        try {
            $stmt = $pdo->prepare("UPDATE system_roles SET role_name = ?, role_key = ?, description = ? WHERE id = ?");
            $stmt->execute([$role_name, $role_key, $description, $id]);
            $success = "Role updated successfully.";
            
            // Refresh data
            $stmt = $pdo->prepare("SELECT * FROM system_roles WHERE id = ?");
            $stmt->execute([$id]);
            $role = $stmt->fetch();
        } catch (PDOException $e) {
            $error = "Update failed: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in the role name.";
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Edit Role: <?php echo htmlspecialchars($role['role_name']); ?></h2>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
</div>

<?php if ($success): ?> <div class="alert alert-success"><?php echo $success; ?></div> <?php endif; ?>
<?php if ($error): ?> <div class="alert alert-danger"><?php echo $error; ?></div> <?php endif; ?>

<div class="card p-4 shadow-sm max-w-lg" style="border-radius: 20px;">
    <form method="POST">
        <div class="mb-3">
            <label class="form-label fw-semibold">Role Name <span class="text-danger">*</span></label>
            <input type="text" name="role_name" class="form-control" value="<?php echo htmlspecialchars($role['role_name']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Role Key</label>
            <input type="text" name="role_key" class="form-control" value="<?php echo htmlspecialchars($role['role_key']); ?>" <?php echo in_array($role['role_key'], ['admin', 'staff']) ? 'readonly' : ''; ?> required>
            <?php if (in_array($role['role_key'], ['admin', 'staff'])): ?>
                <div class="form-text text-danger">Standard system roles cannot change their key.</div>
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Description</label>
            <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($role['description']); ?></textarea>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary px-5 py-2" style="border-radius: 12px;">
                <i class="fas fa-save me-2"></i>Save Changes
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
