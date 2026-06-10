<?php
// auth/profile.php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success = $error = '';

// Fetch current user data
$stmt = $pdo->prepare("SELECT username, role, photo FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify current password to allow changes
    $stmt_pass = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt_pass->execute([$user_id]);
    $stored_hash = $stmt_pass->fetchColumn();

    if (password_verify($current_password, $stored_hash)) {
        try {
            $pdo->beginTransaction();
            
            // Handle Photo Upload (from File Input)
            if (isset($_FILES['photo_file']) && $_FILES['photo_file']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['photo_file']['tmp_name'];
                $fileName = $_FILES['photo_file']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $newFileName = 'profile_' . $user_id . '_' . time() . '.' . $fileExtension;
                
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                if (in_array($fileExtension, $allowedExtensions)) {
                    $uploadPath = __DIR__ . '/../uploads/profile/' . $newFileName;
                    if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                        $stmt_photo = $pdo->prepare("UPDATE users SET photo = ? WHERE id = ?");
                        $stmt_photo->execute([$newFileName, $user_id]);
                        $user['photo'] = $newFileName;
                    } else {
                        $error = "Error moving the uploaded file.";
                    }
                } else {
                    $error = "Invalid file type. Allowed: " . implode(', ', $allowedExtensions);
                }
            }

            if (!$error && !empty($new_password)) {
                if ($new_password === $confirm_password) {
                    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt_update = $pdo->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
                    $stmt_update->execute([$username, $new_hash, $user_id]);
                    $success = "Profile updated successfully!";
                } else {
                    $error = "New passwords do not match.";
                }
            } elseif (!$error) {
                $stmt_update = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
                $stmt_update->execute([$username, $user_id]);
                $success = "Profile updated successfully!";
            }
            
            if (!$error) {
                $pdo->commit();
                $_SESSION['username'] = $username;
                $user['username'] = $username;
            } else {
                if ($pdo->inTransaction()) $pdo->rollBack();
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = "Update failed: " . $e->getMessage();
        }
    } else {
        $error = "Invalid current password.";
    }
}

$photo_path = !empty($user['photo']) ? '/Bosa Addis/uploads/profile/' . $user['photo'] : '/Bosa Addis/assets/img/default_admin.png';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-lg overflow-hidden" style="border-radius: 30px;">
                <div class="row g-0">
                    <!-- Photo Upload Section -->
                    <div class="col-md-5 bg-dark d-flex flex-column align-items-center justify-content-center p-4">
                        <div class="text-center mb-4">
                            <h5 class="text-white fw-bold mb-0">Profile Picture</h5>
                            <p class="text-white-50 small">Upload a professional photo</p>
                        </div>
                        
                        <div class="position-relative mb-4" style="width: 250px; height: 250px; border-radius: 50%; border: 6px solid rgba(255,255,255,0.1); overflow: hidden; background: #2d3748;">
                            <img id="image-preview" src="<?php echo $photo_path; ?>" class="w-100 h-100 object-fit-cover">
                        </div>

                        <div class="text-center w-100 px-4">
                            <label for="photo_file" class="btn btn-outline-light rounded-pill px-4 py-2 w-100 shadow-sm mb-3">
                                <i class="fas fa-cloud-upload-alt me-2"></i>Choose File
                            </label>
                            <p class="text-white-50 small mb-0">Accepted files: JPG, PNG, GIF</p>
                        </div>
                    </div>

                    <!-- Profile Info Section -->
                    <div class="col-md-7 bg-white p-4 p-md-5">
                        <div class="mb-4">
                            <h3 class="fw-bold mb-0 text-dark">Profile Settings</h3>
                            <p class="text-muted">Manage your identity and security</p>
                        </div>

                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show border-0 rounded-4 shadow-sm" role="alert">
                                <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show border-0 rounded-4 shadow-sm" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <input type="file" name="photo_file" id="photo_file" class="d-none" accept="image/*" onchange="previewImage(this)">
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-uppercase text-muted">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0" style="border-radius: 12px 0 0 12px;"><i class="fas fa-user-tag text-primary"></i></span>
                                    <input type="text" name="username" class="form-control form-control-lg bg-light border-0" value="<?php echo htmlspecialchars($user['username']); ?>" required style="border-radius: 0 12px 12px 0;">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold small text-uppercase text-muted">Current Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0" style="border-radius: 12px 0 0 12px;"><i class="fas fa-shield-halved text-warning"></i></span>
                                    <input type="password" name="current_password" class="form-control form-control-lg bg-light border-0" required style="border-radius: 0 12px 12px 0;" placeholder="Confirm password to save">
                                </div>
                            </div>

                            <div class="card bg-light border-0 p-4 mb-4" style="border-radius: 20px;">
                                <h6 class="fw-bold mb-3 small text-uppercase text-primary"><i class="fas fa-key me-2"></i>Security Update</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small text-muted">New Password</label>
                                        <input type="password" name="new_password" class="form-control border-0 shadow-sm" style="border-radius: 10px;" placeholder="Optional">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small text-muted">Confirm New</label>
                                        <input type="password" name="confirm_password" class="form-control border-0 shadow-sm" style="border-radius: 10px;">
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg shadow-sm py-3" style="border-radius: 18px;">
                                    <i class="fas fa-save me-2"></i>Update Account
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('image-preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
