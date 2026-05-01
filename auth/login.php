<?php
// auth/login.php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/lang.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header('Location: ../dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('login_btn'); ?> | IFA BULA KEBELE, RESIDENT MANAGEMENT SYSTEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('../assets/images/Abba_Jifar_Palace_Jimma_worqambatour.com.webp');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            margin: 0;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .login-card h2 {
            font-weight: 800;
            color: #1a2a3a;
            margin-bottom: 0.5rem;
            text-align: center;
            letter-spacing: -1px;
        }
        .login-subtitle {
            text-align: center;
            color: #64748b;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }
        .form-label {
            font-weight: 600;
            color: #475569;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
            border-color: #3498db;
        }
        .btn-primary {
            background: #3498db;
            border: none;
            padding: 0.8rem;
            font-weight: 700;
            border-radius: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }
        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        .brand-icon {
            width: 60px;
            height: 60px;
            background: #3498db;
            border-radius: 15px;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
            box-shadow: 0 10px 20px rgba(52, 152, 219, 0.2);
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-card">
        <div class="d-flex justify-content-center gap-3 mb-4">
            <img src="../assets/img/ethiopia_flag.png" alt="Ethiopia" height="30" class="rounded shadow-sm">
            <img src="../assets/img/oromia_flag.png" alt="Oromia" height="30" class="rounded shadow-sm">
        </div>
        <div class="brand-icon">
            <i class="fas fa-landmark"></i>
        </div>
        <h2 style="font-size: 1.5rem;">IFA BULA KEBELE, RESIDENT MANAGEMENT SYSTEM</h2>
        <p class="login-subtitle"><?php echo __('secure_staff_login'); ?></p>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label"><?php echo __('username'); ?></label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label"><?php echo __('password'); ?></label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100"><?php echo __('login_btn'); ?></button>
        </form>
        <div class="mt-4 text-center text-muted small">
            &copy; 2026 IFA BULA KEBELE, RESIDENT MANAGEMENT SYSTEM
        </div>
    </div>
</body>
</html>
