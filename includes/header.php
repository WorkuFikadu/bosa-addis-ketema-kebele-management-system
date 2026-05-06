<?php
// includes/header.php
ob_start(); // Buffer output so header() redirects work in any including file
require_once __DIR__ . '/lang.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';


// Fetch system settings
$sys_settings = [];
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
    while ($row = $stmt->fetch()) {
        $sys_settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    // Fail silently if table doesn't exist yet
}

$system_name = $sys_settings['system_name'] ?? 'Ifa Bula RIMS';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $system_name; ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/Ifa Bula/assets/css/style.css">
    <?php if (($sys_settings['dark_mode'] ?? '0') === '1'): ?>
    <style>
        body { background-color: #0f172a !important; color: #f1f5f9 !important; }
        .card { background-color: #1e293b !important; color: #f1f5f9 !important; }
        .text-dark { color: #f1f5f9 !important; }
        .text-muted { color: #94a3b8 !important; }
        .bg-white { background-color: #1e293b !important; }
        .list-group-item { background-color: #1e293b !important; color: #f1f5f9 !important; border-color: #334155 !important; }
        .list-group-item:hover { background-color: #334155 !important; }
        .navbar { background-color: #1e293b !important; border-color: #334155 !important; }
        .form-control { background-color: #334155 !important; color: #f1f5f9 !important; border-color: #475569 !important; }
        .table { color: #f1f5f9 !important; }
        .table-hover tbody tr:hover { background-color: #334155 !important; color: #f1f5f9 !important; }
        #sidebar-wrapper { background-color: #0f172a !important; }
    </style>
    <?php endif; ?>
</head>
<body class="bg-light <?php echo (($sys_settings['dark_mode'] ?? '0') === '1') ? 'dark-mode' : ''; ?>">
    <!-- Navbar - Full Width Top Bar -->
    <nav class="navbar fixed-top border-bottom bg-white shadow-sm p-0" style="min-height: 70px;">
        <!-- Left Corner: Ethiopia Flag -->
        <img src="/Ifa Bula/assets/img/ethiopia_flag.png" alt="Ethiopia Flag"
             class="position-absolute top-50 translate-middle-y rounded-2 shadow d-none d-lg-block"
             style="left: 20px; height: 42px; border: 1px solid rgba(0,0,0,0.15); z-index: 10;">

        <!-- Center Content -->
        <div class="w-100 d-flex align-items-center justify-content-between"
             style="padding-left: 130px; padding-right: 130px; min-height: 70px;">

            <!-- Left: Menu toggle + Title -->
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-secondary btn-sm px-3" id="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="d-flex flex-column border-start ps-3">
                    <h6 class="mb-0 fw-bold text-dark" style="letter-spacing: 0.6px; font-size: 0.95rem; white-space: nowrap;"><?php echo $system_name; ?></h6>
                    <p class="text-muted mb-0 fw-semibold" style="font-size: 0.58rem; letter-spacing: 0.14em; text-transform: uppercase; white-space: nowrap;"><?php echo __('admin_portal'); ?></p>
                </div>
            </div>

            <!-- Right: Language + User -->
            <div class="d-flex align-items-center gap-3">
                <!-- Language Switcher -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-primary dropdown-toggle border-0 px-3" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-globe me-1"></i> <?php echo strtoupper($current_lang); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                        <li><a class="dropdown-item" href="?lang=en">English</a></li>
                        <li><a class="dropdown-item" href="?lang=om">Afaan Oromoo</a></li>
                        <li><a class="dropdown-item" href="?lang=am">አማርኛ</a></li>
                    </ul>
                </div>

                <!-- User Info -->
                <div class="d-flex align-items-center gap-2 border-start ps-3">
                    <div class="text-end d-none d-xl-block">
                        <p class="mb-0 text-muted" style="font-size: 0.7rem; line-height: 1.2;"><?php echo __('welcome'); ?>,</p>
                        <p class="mb-0 fw-bold text-dark" style="font-size: 0.82rem; line-height: 1.2; white-space: nowrap;"><?php echo $_SESSION['username'] ?? 'User'; ?></p>
                    </div>
                    <?php
                        $role_key = $_SESSION['role'] ?? 'staff';
                        if ($role_key === 'admin') $role_key = 'administrator';
                    ?>
                    <div class="dropdown">
                        <a class="nav-link p-0" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <?php 
                                $user_photo = !empty($sys_settings['user_photo_current']) ? $sys_settings['user_photo_current'] : ''; // This is a bit complex since header doesn't know current user's photo yet unless we fetch it.
                                // Actually, I already have $sys_settings but that's for system name.
                                // I need to fetch the current user's photo in header.php.
                            ?>
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm overflow-hidden" style="width: 38px; height: 38px;">
                                <?php
                                    // Let's fetch the photo for the logged in user
                                    $current_user_photo = 'default_admin.png';
                                    if (isset($_SESSION['user_id'])) {
                                        $p_stmt = $pdo->prepare("SELECT photo FROM users WHERE id = ?");
                                        $p_stmt->execute([$_SESSION['user_id']]);
                                        $p_res = $p_stmt->fetchColumn();
                                        if ($p_res) $current_user_photo = $p_res;
                                    }
                                    $photo_url = "/Ifa Bula/uploads/profile/" . $current_user_photo;
                                    if ($current_user_photo == 'default_admin.png' || !file_exists(__DIR__ . '/../uploads/profile/' . $current_user_photo)) {
                                        // Try assets folder
                                        $photo_url = "/Ifa Bula/assets/img/default_admin.png";
                                    }
                                ?>
                                <img src="<?php echo $photo_url; ?>" alt="User" class="w-100 h-100 object-fit-cover">
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 p-2" style="border-radius: 12px;">
                            <li><h6 class="dropdown-header small text-muted"><?php echo __($role_key); ?></h6></li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li><a class="dropdown-item rounded-3 mb-1" href="/Ifa Bula/auth/profile.php">
                                <i class="fas fa-user-circle me-2 text-primary"></i><?php echo __('my_profile'); ?>
                            </a></li>
                            <li><a class="dropdown-item rounded-3" href="/Ifa Bula/auth/logout.php">
                                <i class="fas fa-sign-out-alt me-2 text-danger"></i><?php echo __('logout'); ?>
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Corner: Oromia Flag -->
        <img src="/Ifa Bula/assets/img/oromia_flag.png" alt="Oromia Flag"
             class="position-absolute top-50 translate-middle-y rounded-2 shadow d-none d-lg-block"
             style="right: 20px; height: 42px; border: 1px solid rgba(0,0,0,0.15); z-index: 10;">
    </nav>

    <div class="d-flex" id="wrapper">
<?php include_once 'sidebar.php'; ?>
        <div id="page-content-wrapper">
            <div class="container-fluid p-4">
