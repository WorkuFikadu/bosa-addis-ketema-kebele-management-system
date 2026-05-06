<?php
// includes/sidebar.php
?>
<!-- Sidebar -->
<div id="sidebar-wrapper">
    <div class="sidebar-heading border-0 pb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary rounded-3 p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                <i class="fas fa-landmark text-white"></i>
            </div>
            <div>
                <h6 class="mb-0 text-white fw-bold" style="letter-spacing: 1px;"><?php echo $system_name; ?></h6>
                <small class="text-primary fw-bold" style="font-size: 0.75rem;"><?php echo __('kebele_admin'); ?></small>
            </div>
        </div>
    </div>
    
    <div class="list-group list-group-flush px-3">
        <p class="text-muted small text-uppercase fw-bold mb-2 ps-2" style="font-size: 0.65rem; letter-spacing: 1px;"><?php echo __('main_menu'); ?></p>
        
        <a href="/Ifa Bula/dashboard.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-grid-2 me-2"></i><?php echo __('dashboard'); ?>
        </a>
        
        <a href="/Ifa Bula/modules/residents/index.php" class="list-group-item list-group-item-action <?php echo strpos($_SERVER['PHP_SELF'], 'residents') !== false ? 'active' : ''; ?>">
            <i class="fas fa-user-group me-2"></i><?php echo __('residents'); ?>
        </a>
        
        <a href="/Ifa Bula/modules/families/index.php" class="list-group-item list-group-item-action <?php echo strpos($_SERVER['PHP_SELF'], 'families') !== false ? 'active' : ''; ?>">
            <i class="fas fa-people-roof me-2"></i><?php echo __('families'); ?>
        </a>
        
        <a href="/Ifa Bula/modules/houses/index.php" class="list-group-item list-group-item-action <?php echo strpos($_SERVER['PHP_SELF'], 'houses') !== false ? 'active' : ''; ?>">
            <i class="fas fa-city me-2"></i><?php echo __('houses'); ?>
        </a>
        
        <a href="/Ifa Bula/modules/idcards/index.php" class="list-group-item list-group-item-action <?php echo strpos($_SERVER['PHP_SELF'], 'idcards') !== false ? 'active' : ''; ?>">
            <i class="fas fa-id-card-clip me-2"></i><?php echo __('id_cards'); ?>
        </a>
 
        <a href="/Ifa Bula/modules/vital/index.php" class="list-group-item list-group-item-action <?php echo strpos($_SERVER['PHP_SELF'], 'modules/vital') !== false ? 'active' : ''; ?>">
            <i class="fas fa-file-signature me-2"></i><?php echo __('vital_records'); ?>
        </a>
 
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <p class="text-muted small text-uppercase fw-bold mt-4 mb-2 ps-2" style="font-size: 0.65rem; letter-spacing: 1px;"><?php echo __('administrative'); ?></p>
        
        <a href="/Ifa Bula/modules/reports/index.php" class="list-group-item list-group-item-action <?php echo strpos($_SERVER['PHP_SELF'], 'reports') !== false ? 'active' : ''; ?>">
            <i class="fas fa-chart-pie me-2"></i><?php echo __('reports'); ?>
        </a>
        
        <a href="/Ifa Bula/modules/payments/index.php" class="list-group-item list-group-item-action <?php echo strpos($_SERVER['PHP_SELF'], 'payments') !== false ? 'active' : ''; ?>">
            <i class="fas fa-file-invoice-dollar me-2"></i>Revenue & Payments
        </a>
        
        <a href="/Ifa Bula/modules/users/index.php" class="list-group-item list-group-item-action <?php echo strpos($_SERVER['PHP_SELF'], 'users') !== false ? 'active' : ''; ?>">
            <i class="fas fa-user-lock me-2"></i><?php echo __('staff_mgmt'); ?>
        </a>

        <a href="/Ifa Bula/modules/settings/index.php" class="list-group-item list-group-item-action <?php echo strpos($_SERVER['PHP_SELF'], 'settings') !== false ? 'active' : ''; ?>">
            <i class="fas fa-cog me-2"></i><?php echo __('settings') ?? 'Settings'; ?>
        </a>

        <a href="/Ifa Bula/modules/reports/audit_logs.php" class="list-group-item list-group-item-action <?php echo strpos($_SERVER['PHP_SELF'], 'audit_logs') !== false ? 'active' : ''; ?>">
            <i class="fas fa-history me-2"></i>System Activity Logs
        </a>
        <?php endif; ?>

        <div class="mt-auto pt-5">
            <a href="/Ifa Bula/auth/logout.php" class="list-group-item list-group-item-action text-danger opacity-75">
                <i class="fas fa-power-off me-2"></i><?php echo __('logout'); ?>
            </a>
        </div>
    </div>
</div>
