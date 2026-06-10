<?php
// includes/sidebar.php
// Quick unread chat badge for any logged-in user
$_chat_badge = 0;
if (isset($_SESSION['user_id'])) {
    try {
        $_cb = $pdo->prepare("SELECT COUNT(*) FROM staff_messages WHERE is_read=0 AND sender_id != ? AND (receiver_id=? OR receiver_id IS NULL)");
        $_cb->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
        $_chat_badge = (int)$_cb->fetchColumn();
    } catch(Exception $e){}
}
?>
<!-- Sidebar -->
<div id="sidebar-wrapper">
    <div class="sidebar-heading border-0 pb-2">
        <!-- Sidebar heading removed as branding is now in the top navbar -->
    </div>
    
    <div class="list-group list-group-flush px-2">
        <p class="text-muted text-uppercase fw-bolder sidebar-divider ms-3" style="font-size: 0.65rem; letter-spacing: 1.5px;"><?php echo __('main_menu'); ?></p>
        
        <a href="/Bosa Addis/dashboard.php" class="list-group-item list-group-item-action custom-sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-grid-2"></i><?php echo __('dashboard'); ?>
        </a>
        
        <a href="/Bosa Addis/modules/residents/index.php" class="list-group-item list-group-item-action custom-sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'residents') !== false ? 'active' : ''; ?>">
            <i class="fas fa-user-group"></i><?php echo __('residents'); ?>
        </a>
        
        <a href="/Bosa Addis/modules/idcards/index.php" class="list-group-item list-group-item-action custom-sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'idcards') !== false ? 'active' : ''; ?>">
            <i class="fas fa-id-card-clip"></i><?php echo __('id_cards'); ?>
        </a>

        <a href="/Bosa Addis/modules/houses/index.php" class="list-group-item list-group-item-action custom-sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'houses') !== false ? 'active' : ''; ?>">
            <i class="fas fa-city"></i><?php echo __('houses'); ?>
        </a>
        
        <a href="/Bosa Addis/modules/families/index.php" class="list-group-item list-group-item-action custom-sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'families') !== false ? 'active' : ''; ?>">
            <i class="fas fa-people-roof"></i><?php echo __('families'); ?>
        </a>

        <a href="/Bosa Addis/modules/vital/index.php" class="list-group-item list-group-item-action custom-sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'modules/vital') !== false ? 'active' : ''; ?>">
            <i class="fas fa-file-prescription"></i><?php echo __('vital_records'); ?>
        </a>

        <p class="text-muted text-uppercase fw-bolder sidebar-divider ms-3 mt-4" style="font-size: 0.65rem; letter-spacing: 1.5px;"><?php echo __('kebele_services', 'Kebele Services'); ?></p>
        
        <a href="/Bosa Addis/modules/justice/index.php" class="list-group-item list-group-item-action custom-sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'justice') !== false ? 'active' : ''; ?>">
            <i class="fas fa-shield-halved"></i><?php echo __('service_justice', 'Peace & Security'); ?>
        </a>

        <a href="/Bosa Addis/modules/health/index.php" class="list-group-item list-group-item-action custom-sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'health') !== false ? 'active' : ''; ?>">
            <i class="fas fa-hand-holding-medical"></i><?php echo __('service_health', 'Social & Health'); ?>
        </a>

        <a href="/Bosa Addis/modules/economic/index.php" class="list-group-item list-group-item-action custom-sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'economic') !== false ? 'active' : ''; ?>">
            <i class="fas fa-briefcase"></i><?php echo __('service_economic', 'Economic & Youth'); ?>
        </a>

        <a href="/Bosa Addis/modules/letters/index.php" class="list-group-item list-group-item-action custom-sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'letters') !== false ? 'active' : ''; ?>">
            <i class="fas fa-envelope-open-text"></i><?php echo __('service_letters', 'Admin Letters'); ?>
        </a>

        <a href="/Bosa Addis/modules/users/chat.php" class="list-group-item list-group-item-action custom-sidebar-link d-flex align-items-center <?php echo strpos($_SERVER['PHP_SELF'], 'chat') !== false ? 'active' : ''; ?>" style="<?php echo strpos($_SERVER['PHP_SELF'], 'chat') === false ? 'color:#38bdf8 !important;' : ''; ?>">
            <i class="fas fa-comments"></i>
            <span>Staff Chat Hub</span>
            <?php if ($_chat_badge > 0): ?>
            <span class="ms-auto badge rounded-pill" style="background:#ef4444;font-size:0.65rem;min-width:18px;"><?php echo $_chat_badge > 99 ? '99+' : $_chat_badge; ?></span>
            <?php endif; ?>
        </a>
        <a href="/Bosa Addis/modules/announcements/index.php" class="list-group-item list-group-item-action custom-sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'announcements') !== false ? 'active' : ''; ?>">
            <i class="fas fa-bullhorn"></i>Notice Board
        </a>

        <a href="/Bosa Addis/modules/complaints/index.php" class="list-group-item list-group-item-action custom-sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'complaints') !== false ? 'active' : ''; ?>">
            <i class="fas fa-ticket-alt"></i>Complaints
        </a>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <p class="text-muted text-uppercase fw-bolder sidebar-divider ms-3" style="font-size: 0.65rem; letter-spacing: 1.5px;"><?php echo __('administrative'); ?></p>
        
        <a href="/Bosa Addis/modules/reports/index.php" class="list-group-item list-group-item-action custom-sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'reports') !== false ? 'active' : ''; ?>">
            <i class="fas fa-chart-pie"></i><?php echo __('reports'); ?>
        </a>
        
        <a href="/Bosa Addis/modules/payments/index.php" class="list-group-item list-group-item-action custom-sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'payments') !== false ? 'active' : ''; ?>">
            <i class="fas fa-file-invoice-dollar"></i><?php echo __('revenue_payments'); ?>
        </a>
        
        <a href="/Bosa Addis/modules/users/index.php" class="list-group-item list-group-item-action custom-sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'users') !== false && strpos($_SERVER['PHP_SELF'], 'chat') === false ? 'active' : ''; ?>">
            <i class="fas fa-user-lock"></i><?php echo __('staff_mgmt'); ?>
        </a>


        <a href="/Bosa Addis/modules/export/index.php" class="list-group-item list-group-item-action custom-sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'export') !== false ? 'active' : ''; ?>">
            <i class="fas fa-file-excel"></i>Data Export
        </a>

        <a href="/Bosa Addis/modules/backup/index.php" class="list-group-item list-group-item-action custom-sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'backup') !== false ? 'active' : ''; ?>">
            <i class="fas fa-database"></i>DB Backups
        </a>

        <a href="/Bosa Addis/modules/settings/index.php" class="list-group-item list-group-item-action custom-sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'settings') !== false ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i><?php echo __('settings') ?? 'Settings'; ?>

        </a>

        <a href="/Bosa Addis/modules/reports/audit_logs.php" class="list-group-item list-group-item-action custom-sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'audit_logs') !== false ? 'active' : ''; ?>">
            <i class="fas fa-history"></i><?php echo __('activity_logs'); ?>
        </a>
        <?php endif; ?>

        <div class="mt-auto pt-5 pb-4">
            <a href="/Bosa Addis/auth/logout.php" class="list-group-item list-group-item-action custom-sidebar-link" style="background: rgba(239, 68, 68, 0.1) !important; color: #ef4444 !important;">
                <i class="fas fa-power-off"></i><?php echo __('logout'); ?>
            </a>
        </div>
    </div>
</div>
