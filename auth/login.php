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
    <title><?php echo __('login_btn'); ?> | IFA BULA KEBELE RIMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --bg-glass: rgba(255, 255, 255, 0.85);
            --border-glass: rgba(255, 255, 255, 0.3);
            --text-main: #1e293b;
            --text-secondary: #64748b;
            --accent-color: #3b82f6;
        }

        body {
            background: linear-gradient(rgba(0, 0, 0, 0.45), rgba(0, 0, 0, 0.45)), url('../assets/images/Abba_Jifar_Palace_Jimma_worqambatour.com.webp');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            overflow: hidden;
        }

        .login-container {
            width: 100%;
            max-width: 520px;
            padding: 24px;
            z-index: 10;
        }

        .login-card {
            background: var(--bg-glass);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border-radius: 28px;
            padding: 3.5rem 3rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 1px solid var(--border-glass);
            animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .brand-section {
            text-align: center;
            margin-bottom: 3rem;
        }

        .flags-wrapper {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin-bottom: 24px;
        }

        .flag-img {
            height: 32px;
            width: auto;
            border-radius: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .flag-img:hover {
            transform: scale(1.1);
        }

        .logo-box {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2rem;
            box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.4);
            transition: all 0.4s ease;
        }

        .login-card:hover .logo-box {
            transform: rotate(-5deg) scale(1.05);
        }

        .system-title {
            font-weight: 800;
            font-size: 1.75rem;
            color: var(--text-main);
            letter-spacing: -0.5px;
            margin-bottom: 0.75rem;
            line-height: 1.25;
        }

        .system-subtitle {
            font-size: 0.95rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-main);
            font-size: 0.9rem;
            margin-bottom: 0.6rem;
            margin-left: 4px;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 1.75rem;
        }

        .input-group-custom i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 1.2rem;
            transition: color 0.3s ease;
        }

        .form-control-custom {
            width: 100%;
            padding: 14px 18px 14px 54px;
            background: rgba(255, 255, 255, 0.6);
            border: 1.5px solid transparent;
            border-radius: 16px;
            font-size: 1.05rem;
            font-weight: 500;
            color: var(--text-main);
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .form-control-custom:focus {
            outline: none;
            background: white;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .form-control-custom:focus + i {
            color: var(--primary-color);
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            border: none;
            border-radius: 16px;
            font-weight: 700;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            margin-top: 0.5rem;
            box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.3);
            text-transform: uppercase;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px -5px rgba(37, 99, 235, 0.4);
            filter: brightness(1.1);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert-custom {
            border-radius: 14px;
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 1.75rem;
            border: none;
            background-color: rgba(239, 68, 68, 0.15);
            color: #b91c1c;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
        }

        .footer-text {
            text-align: center;
            margin-top: 2.5rem;
            color: var(--text-secondary);
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Ambient elements for depth */
        .ambient-blob {
            position: absolute;
            width: 300px;
            height: 300px;
            background: var(--primary-color);
            filter: blur(100px);
            opacity: 0.2;
            border-radius: 50%;
            z-index: 1;
        }

        .blob-1 { top: 10%; left: 15%; }
        .blob-2 { bottom: 10%; right: 15%; background: var(--accent-color); }
    </style>
</head>
<body>
    <div class="ambient-blob blob-1"></div>
    <div class="ambient-blob blob-2"></div>

    <div class="login-container">
        <div class="login-card">
            <div class="brand-section">
                <div class="flags-wrapper">
                    <img src="../assets/img/ethiopia_flag.png" alt="Ethiopia" class="flag-img" onerror="this.style.display='none'">
                    <img src="../assets/img/oromia_flag.png" alt="Oromia" class="flag-img" onerror="this.style.display='none'">
                </div>
                <div class="logo-box">
                    <i class="fas fa-landmark"></i>
                </div>
                <h1 class="system-title">IFA BULA KEBELE<br>RIMS</h1>
                <p class="system-subtitle"><?php echo __('secure_staff_login'); ?></p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-custom">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="input-group-custom">
                    <label class="form-label" for="username"><?php echo __('username'); ?></label>
                    <input type="text" id="username" name="username" class="form-control-custom" placeholder="Enter your username" required autofocus>
                    <i class="fas fa-user-circle" style="top: calc(50% + 14px);"></i>
                </div>

                <div class="input-group-custom">
                    <label class="form-label" for="password"><?php echo __('password'); ?></label>
                    <input type="password" id="password" name="password" class="form-control-custom" placeholder="Enter your password" required>
                    <i class="fas fa-lock" style="top: calc(50% + 14px);"></i>
                </div>

                <button type="submit" class="btn btn-login">
                    <?php echo __('login_btn'); ?>
                    <i class="fas fa-arrow-right ms-2" style="font-size: 0.8rem;"></i>
                </button>
            </form>

            <div class="footer-text border-t border-slate-200/50 pt-4 mt-6">
                <p class="mb-3 font-bold text-slate-800 tracking-tight">&copy; 2026 IFA BULA KEBELE RIMS</p>
                <div class="flex flex-col gap-2 text-[10px] uppercase font-bold tracking-widest leading-relaxed">
                    <span class="text-slate-400"><i class="fas fa-code text-primary me-1"></i> Developed by <span class="text-slate-700">Worku Fikadu</span></span>
                    <a href="mailto:workufikadu643@gmail.com" class="text-primary text-decoration-none hover:opacity-80"><i class="fas fa-envelope me-1"></i> workufikadu643@gmail.com</a>
                    <a href="tel:+251934953593" class="text-primary text-decoration-none hover:opacity-80"><i class="fas fa-phone me-1"></i> +251 934 953 593</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
