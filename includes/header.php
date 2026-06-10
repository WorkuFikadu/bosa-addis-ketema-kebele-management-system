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

$system_name = $sys_settings['system_name'] ?? 'Bosa Addis Kebele Management System';
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
    <link rel="stylesheet" href="/Bosa Addis/assets/css/style.css">
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
    <style>
        .custom-admin-nav { background: #020617 !important; border-bottom: 1px solid rgba(255,255,255,0.08) !important; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2) !important; }
        .dark-mode .custom-admin-nav { background: #000000 !important; }
        .nav-flag:hover { transform: translateY(-50%) scale(1.15) !important; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important; }
        .profile-avatar:hover { transform: scale(1.08); box-shadow: 0 10px 25px -5px rgba(13, 110, 253, 0.5) !important; }
        .bg-gradient-primary { background: linear-gradient(135deg, #0d6efd, #0dcaf0); }
        .dark-mode .text-dark { color: #f8fafc !important; }
        .btn-light { border: 1px solid rgba(0,0,0,0.05); }
        .dark-mode .btn-light { background: #334155; border-color: #475569; color: #f8fafc; }
        .dropdown-menu { box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important; }
        .dark-mode .dropdown-menu { background: #1e293b; border: 1px solid #334155 !important; }
        .dark-mode .dropdown-item { color: #e2e8f0; }
        .dark-mode .dropdown-item:hover { background: #334155; color: #fff; }
        
        /* Premium Sidebar Styles */
        #sidebar-wrapper { background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%) !important; box-shadow: 4px 0 25px rgba(0,0,0,0.1); border-right: 1px solid rgba(255,255,255,0.05); }
        .sidebar-heading { border-bottom: 1px solid rgba(255,255,255,0.08) !important; padding: 1.5rem !important; margin-bottom: 0.5rem; background: rgba(0,0,0,0.2) !important; }
        .custom-sidebar-link { 
            background: transparent !important; 
            color: #eab308 !important; 
            border: none !important; 
            border-radius: 12px !important; 
            margin: 0.25rem 0.75rem !important; 
            padding: 0.75rem 1rem !important; 
            font-weight: 500; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
        }
        .custom-sidebar-link:hover { 
            background-color: rgba(255,255,255,0.08) !important; 
            color: #fef08a !important; 
            transform: translateX(4px); 
        }
        .custom-sidebar-link.active { 
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%) !important; 
            color: #ffffff !important; 
            box-shadow: 0 4px 15px -3px rgba(13, 110, 253, 0.4); 
            font-weight: 600; 
        }
        .custom-sidebar-link i { width: 24px; text-align: center; }
        .sidebar-divider { margin: 1.5rem 0 0.5rem 0; color: rgba(234, 179, 8, 0.7) !important; }
    </style>
</head>
<body class="bg-light <?php echo (($sys_settings['dark_mode'] ?? '0') === '1') ? 'dark-mode' : ''; ?>">
    <!-- Navbar - Full Width Top Bar -->
    <nav class="navbar fixed-top border-bottom p-0 custom-admin-nav transition-all" style="min-height: 80px; z-index: 1200;">
        <!-- Center Content -->
        <div class="w-100 d-flex align-items-center justify-content-between px-3 px-lg-4"
             style="min-height: 80px;">
            
            <!-- Left Group: Flag, Logo, Title, and Toggle -->
            <div class="d-flex align-items-center gap-3">
                <!-- Ethiopia Flag -->
                <img src="/Bosa Addis/assets/img/ethiopia_flag.png" alt="Ethiopia Flag"
                     class="rounded-3 shadow-sm d-none d-md-block nav-flag"
                     style="height: 38px; border: 1px solid rgba(255,255,255,0.1);">

                <!-- System Logo -->
                <img src="/Bosa Addis/assets/images/logo of bosa addis.jpg" alt="Logo" 
                     class="rounded-3 shadow-sm border border-white border-opacity-10 ms-2" 
                     style="height: 50px; width: 50px; object-fit: cover;">

                <!-- Branding -->
                <div class="d-flex flex-column pe-3 border-end border-2 border-white border-opacity-10 py-1" style="min-width: 150px;">
                    <h5 class="mb-0 fw-bolder text-white" style="letter-spacing: 0.5px; white-space: nowrap; text-transform: uppercase;">
                        BOSA ADDIS
                    </h5>
                    <small class="text-white-50 fw-bold" style="font-size: 0.65rem; letter-spacing: 2px;">Kebele Management System</small>
                </div>

                <!-- Menu Toggle Button -->
                <button class="btn btn-primary rounded-circle shadow-sm d-flex align-items-center justify-content-center border-0 hover-lift" id="menu-toggle" style="width: 44px; height: 44px; background: linear-gradient(135deg, #0d6efd, #2563eb); position: relative; z-index: 2000;">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <!-- Global Search -->
            <div class="d-none d-lg-flex position-relative mx-4 flex-grow-1" style="max-width: 400px;">
                <form action="/Bosa Addis/search.php" method="GET" class="w-100 position-relative">
                    <input type="text" name="q" id="globalSearchInput" class="form-control rounded-pill border-0 ps-4 pe-5 py-2 shadow-sm" placeholder="Global search..." style="background: rgba(255,255,255,0.1); color: white; transition: all 0.3s ease;">
                    <button type="submit" class="btn border-0 position-absolute end-0 top-50 translate-middle-y text-white-50 p-2 me-1 hover-lift">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                <div id="globalSearchResults" class="dropdown-menu w-100 mt-2 shadow-lg border-0 rounded-4 p-2" style="position: absolute; top: 100%; left: 0; display: none;">
                    <!-- Results injected here -->
                </div>
            </div>

            <!-- Right Group: Controls and Oromia Flag -->
            <div class="d-flex align-items-center gap-4">
                <!-- Back Button -->
                <button onclick="history.back()" class="btn rounded-circle shadow-sm d-flex align-items-center justify-content-center border-0 hover-lift text-white" title="Go Back" style="width: 44px; height: 44px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <!-- Fullscreen Toggle -->
                <button class="btn rounded-circle shadow-sm d-flex align-items-center justify-content-center border-0 hover-lift text-white" id="fullscreen-toggle" style="width: 44px; height: 44px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
                    <i class="fas fa-expand"></i>
                </button>

                <!-- Language Switcher -->
                <div class="dropdown">
                    <button class="btn rounded-pill shadow-sm px-4 py-2 fw-bold d-flex align-items-center gap-2 transition-all hover-lift text-white" type="button" data-bs-toggle="dropdown" style="font-size: 0.85rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
                        <i class="fas fa-globe text-primary"></i> <?php echo strtoupper($current_lang); ?> <i class="fas fa-chevron-down ms-2 opacity-50" style="font-size: 0.7rem;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 rounded-4 mt-3 overflow-hidden p-2">
                        <li><a class="dropdown-item py-2 px-4 fw-semibold rounded-3" href="?lang=en">English</a></li>
                        <li><a class="dropdown-item py-2 px-4 fw-semibold rounded-3 mb-1 mt-1" href="?lang=om">Afaan Oromoo</a></li>
                        <li><a class="dropdown-item py-2 px-4 fw-semibold rounded-3" href="?lang=am">አማርኛ</a></li>
                    </ul>
                </div>

                <!-- Notifications -->
                <div class="dropdown">
                    <button class="btn rounded-circle shadow-sm d-flex align-items-center justify-content-center border-0 hover-lift text-white position-relative" type="button" data-bs-toggle="dropdown" style="width: 44px; height: 44px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
                        <i class="fas fa-bell"></i>
                        <span id="notif-badge" class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" style="display:none;">
                            <span class="visually-hidden">New alerts</span>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 rounded-4 mt-3 shadow-lg" style="width: 320px; max-height: 400px; overflow-y: auto;" id="notifications-dropdown">
                        <li><h6 class="dropdown-header fw-bold text-primary">Notifications</h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <div id="notif-list">
                            <li><a class="dropdown-item py-3 text-center text-muted small" href="#">Loading...</a></li>
                        </div>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center fw-bold text-primary small py-2" href="#">View All Notifications</a></li>
                    </ul>
                </div>

                <!-- User Info -->
                <div class="d-flex align-items-center border-start border-opacity-10 ps-4 gap-3 user-dropdown-container" style="border-color: rgba(255,255,255,0.1) !important;">
                    <div class="text-end d-none d-md-block">
                        <p class="text-white-50 small mb-0 fw-bold" style="letter-spacing: 1px; text-transform: uppercase; font-size: 0.6rem;"><?php echo __('welcome_back'); ?>,</p>
                        <p class="text-white mb-0 fw-bold" style="font-size: 0.9rem;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></p>
                    </div>
                    <div class="dropdown">
                        <a href="#" class="d-block link-dark text-decoration-none dropdown-toggle no-caret" id="dropdownUser1" data-bs-toggle="dropdown">
                            <div class="avatar-wrap position-relative">
                                <?php
                                    $current_user_photo = 'default_admin.png';
                                    if (isset($_SESSION['user_id'])) {
                                        $p_stmt = $pdo->prepare("SELECT photo FROM users WHERE id = ?");
                                        $p_stmt->execute([$_SESSION['user_id']]);
                                        $p_res = $p_stmt->fetchColumn();
                                        if ($p_res) $current_user_photo = $p_res;
                                    }
                                    $photo_url = "/Bosa Addis/uploads/profile/" . $current_user_photo;
                                    if ($current_user_photo == 'default_admin.png' || !file_exists(__DIR__ . '/../uploads/profile/' . $current_user_photo)) {
                                        $photo_url = "/Bosa Addis/assets/img/default_admin.png";
                                    }
                                ?>
                                <img src="<?php echo $photo_url; ?>" alt="user" width="45" height="45" class="rounded-circle border border-2 border-white shadow-sm">
                                <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-1 border-white rounded-circle"></span>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 mt-3 p-3" style="border-radius: 20px; min-width: 240px;">
                            <li class="text-center mb-3 pt-2">
                                <?php
                                    $role_key = $_SESSION['role'] ?? 'staff';
                                    if ($role_key === 'admin') $role_key = 'administrator';
                                ?>
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-4 py-2 fw-bolder text-uppercase" style="letter-spacing: 1px;"><?php echo __($role_key); ?></span>
                            </li>
                            <li><a class="dropdown-item rounded-4 py-2 px-3 fw-semibold mb-2 shadow-sm border-0 d-flex align-items-center" href="/Bosa Addis/auth/profile.php" style="background-color: rgba(13, 110, 253, 0.05); color: #0d6efd;">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 32px; height: 32px;"><i class="fas fa-user"></i></div>
                                <?php echo __('my_profile'); ?>
                            </a></li>
                            <li><hr class="dropdown-divider opacity-10 my-3"></li>
                            <li><a class="dropdown-item rounded-4 py-2 px-3 fw-semibold text-danger d-flex align-items-center hover-bg-danger-light transition-all" href="/Bosa Addis/auth/logout.php">
                                <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 32px; height: 32px;"><i class="fas fa-power-off"></i></div>
                                <?php echo __('logout'); ?>
                            </a></li>
                        </ul>
                    </div>
                </div>

                <!-- Oromia Flag -->
                <img src="/Bosa Addis/assets/img/oromia_flag.png" alt="Oromia Flag"
                     class="rounded-3 shadow-sm d-none d-lg-block nav-flag"
                     style="height: 42px; border: 1px solid rgba(255,255,255,0.1); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
            </div>
        </div>
    </nav>

    <div class="d-flex" id="wrapper">
<?php include_once 'sidebar.php'; ?>
        <div id="page-content-wrapper">
            <div class="container-fluid p-4">
