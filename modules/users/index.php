<?php
// modules/users/index.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

// Only Administrator can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<div class='alert alert-danger'>Access Denied. Only Administrators can view this page.</div>";
    require_once __DIR__ . '/../../includes/footer.php';
    exit;
}

$stmt = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY id ASC");
$users = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?php echo __('staff_role_mgmt'); ?></h2>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-user-plus me-2"></i><?php echo __('add_staff'); ?>
    </a>
</div>

<div class="card p-4 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th><?php echo __('id'); ?></th>
                    <th><?php echo __('username'); ?></th>
                    <th><?php echo __('system_role'); ?></th>
                    <th><?php echo __('date_created'); ?></th>
                    <th><?php echo __('status'); ?></th>
                    <th><?php echo __('actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): 
                    $role_key = $u['role'];
                    if ($role_key === 'admin') $role_key = 'administrator';
                    if ($role_key === 'security') $role_key = 'security_committee';
                    if ($role_key === 'clerk') $role_key = 'data_clerk';
                    
                    $badge_class = 'bg-secondary';
                    if ($u['role'] === 'admin') $badge_class = 'bg-danger';
                    if ($u['role'] === 'manager') $badge_class = 'bg-primary';
                    if ($u['role'] === 'secretary') $badge_class = 'bg-success';
                    if ($u['role'] === 'clerk') $badge_class = 'bg-warning text-dark';
                ?>
                <tr>
                    <td>#<?php echo $u['id']; ?></td>
                    <td class="fw-bold"><?php echo $u['username']; ?></td>
                    <td>
                        <span class="badge <?php echo $badge_class; ?> px-3 py-2">
                            <?php echo __($role_key); ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                    <td><span class="text-success"><i class="fas fa-circle me-1 small"></i> <?php echo __('active'); ?></span></td>
                    <td>
                        <a href="edit.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-edit"></i></a>
                        <?php if ($u['username'] !== $_SESSION['username']): ?>
                            <a href="delete.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this staff member?')"><i class="fas fa-trash"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
