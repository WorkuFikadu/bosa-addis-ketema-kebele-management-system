<?php
// modules/users/edit.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

// Only Administrator can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<div class='alert alert-danger'>Access Denied.</div>";
    require_once __DIR__ . '/../../includes/footer.php';
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE id = ?");
$stmt->execute([$id]);
$u = $stmt->fetch();

if (!$u) {
    header('Location: index.php');
    exit;
}

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $role = $_POST['role'];
    $password = $_POST['password'];

    try {
        if (!empty($password)) {
            // Update with new password
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ?, password = ? WHERE id = ?");
            $stmt->execute([$username, $role, $hash, $id]);
        } else {
            // Update without changing password
            $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
            $stmt->execute([$username, $role, $id]);
        }
        $success = "Staff information updated successfully.";
        
        // Refresh data
        $stmt_refresh = $pdo->prepare("SELECT id, username, role FROM users WHERE id = ?");
        $stmt_refresh->execute([$id]);
        $u = $stmt_refresh->fetch();
    } catch (PDOException $e) {
        $error = "Update failed: " . $e->getMessage();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Edit Staff Member: <?php echo $u['username']; ?></h2>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
</div>

<?php if ($success): ?> <div class="alert alert-success"><?php echo $success; ?></div> <?php endif; ?>
<?php if ($error): ?> <div class="alert alert-danger"><?php echo $error; ?></div> <?php endif; ?>

<div class="card p-4 shadow-sm max-w-lg">
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" value="<?php echo $u['username']; ?>" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">System Role</label>
            <select name="role" class="form-select" required>
                <option value="secretary" <?php if($u['role'] == 'secretary') echo 'selected'; ?>><?php echo __('secretary'); ?></option>
                <option value="clerk" <?php if($u['role'] == 'clerk') echo 'selected'; ?>><?php echo __('data_clerk'); ?></option>
                <option value="manager" <?php if($u['role'] == 'manager') echo 'selected'; ?>><?php echo __('manager'); ?></option>
                <option value="security" <?php if($u['role'] == 'security') echo 'selected'; ?>><?php echo __('security_committee'); ?></option>
                <option value="admin" <?php if($u['role'] == 'admin') echo 'selected'; ?>><?php echo __('administrator'); ?></option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Reset Password (Leave blank to keep current)</label>
            <input type="password" name="password" class="form-control" placeholder="Enter new password if needed">
            <div class="form-text">As an admin, you can reset their password here if they forgot it.</div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary px-5 py-2">
                <i class="fas fa-save me-2"></i>Save Changes
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
