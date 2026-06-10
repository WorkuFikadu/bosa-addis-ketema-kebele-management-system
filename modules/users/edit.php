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

$stmt = $pdo->prepare("SELECT id, username, full_name, email, phone, role FROM users WHERE id = ?");
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
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';

    try {
        if (!empty($password)) {
            // Update with new password
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET username = ?, full_name = ?, email = ?, phone = ?, role = ?, password = ? WHERE id = ?");
            $stmt->execute([$username, $full_name, $email, $phone, $role, $hash, $id]);
        } else {
            // Update without changing password
            $stmt = $pdo->prepare("UPDATE users SET username = ?, full_name = ?, email = ?, phone = ?, role = ? WHERE id = ?");
            $stmt->execute([$username, $full_name, $email, $phone, $role, $id]);
        }
        $success = "Staff information updated successfully.";
        
        // Refresh data
        $stmt_refresh = $pdo->prepare("SELECT id, username, full_name, email, phone, role FROM users WHERE id = ?");
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
            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($u['full_name'] ?? ''); ?>" required>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($u['username']); ?>" required>
            </div>
            
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">System Role <span class="text-danger">*</span></label>
                <select name="role" class="form-select" required>
                    <?php
                    $all_roles = $pdo->query("SELECT * FROM system_roles ORDER BY role_name ASC")->fetchAll();
                    foreach ($all_roles as $role_row):
                    ?>
                        <option value="<?php echo htmlspecialchars($role_row['role_key']); ?>" <?php if($u['role'] == $role_row['role_key']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($role_row['role_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Email Address</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($u['email'] ?? ''); ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Phone Number</label>
                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($u['phone'] ?? ''); ?>">
            </div>
        </div>

        <div class="mb-3 mt-4 pt-3 border-top">
            <label class="form-label fw-semibold">Reset Password</label>
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
