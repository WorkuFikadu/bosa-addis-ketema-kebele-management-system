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
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';

    if (!empty($username) && !empty($password) && !empty($role) && !empty($full_name)) {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, full_name, email, phone, password, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $full_name, $email, $phone, $hash, $role]);
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
            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="full_name" class="form-control" required placeholder="e.g. John Doe">
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                <input type="text" name="username" class="form-control" required placeholder="e.g. jdoe">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">System Role <span class="text-danger">*</span></label>
                <select name="role" class="form-select" required>
                    <?php
                    $all_roles = $pdo->query("SELECT * FROM system_roles ORDER BY role_name ASC")->fetchAll();
                    foreach ($all_roles as $role_row):
                    ?>
                        <option value="<?php echo htmlspecialchars($role_row['role_key']); ?>">
                            <?php echo htmlspecialchars($role_row['role_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="john@example.com">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Phone Number</label>
                <input type="text" name="phone" class="form-control" placeholder="+251...">
            </div>
        </div>
        <div class="mb-3 mt-4 pt-3 border-top">
            <label class="form-label fw-semibold">Temporary Password <span class="text-danger">*</span></label>
            <input type="password" name="password" class="form-control" required>
            <div class="form-text text-muted"><i class="fas fa-info-circle me-1"></i>Staff members can change their password later.</div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-success px-5 py-2">
                <i class="fas fa-save me-2"></i>Register Staff
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
