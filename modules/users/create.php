<?php
// modules/users/create.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

// Only Administrator can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<div class='alert alert-danger'>Access Denied.</div>";
    require_once __DIR__ . '/../../includes/footer.php';
    exit;
}

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (!empty($username) && !empty($password) && !empty($role)) {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hash, $role]);
            $success = "Staff member '$username' has been successfully registered.";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "The username '$username' is already taken.";
            } else {
                $error = "Error: " . $e->getMessage();
            }
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Add New Staff Member</h2>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
</div>

<?php if ($success): ?> <div class="alert alert-success"><?php echo $success; ?></div> <?php endif; ?>
<?php if ($error): ?> <div class="alert alert-danger"><?php echo $error; ?></div> <?php endif; ?>

<div class="card p-4 shadow-sm max-w-lg">
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required placeholder="e.g. jdoe">
        </div>
        <div class="mb-3">
            <label class="form-label">Temporary Password</label>
            <input type="password" name="password" class="form-control" required>
            <div class="form-text">Staff members can change their password later.</div>
        </div>
        <div class="mb-3">
            <label class="form-label">System Role</label>
            <select name="role" class="form-select" required>
                <option value="secretary"><?php echo __('secretary'); ?></option>
                <option value="clerk"><?php echo __('data_clerk'); ?></option>
                <option value="manager"><?php echo __('manager'); ?></option>
                <option value="security"><?php echo __('security_committee'); ?></option>
                <option value="admin"><?php echo __('administrator'); ?></option>
            </select>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-success px-5 py-2">
                <i class="fas fa-save me-2"></i>Register Staff
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
