<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

$roles = $pdo->query("SELECT * FROM system_roles ORDER BY role_name ASC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1"><i class="fas fa-user-tag me-2 text-primary"></i>System Roles</h2>
        <p class="text-muted small">Manage administrative and staff roles for the system.</p>
    </div>
    <a href="create.php" class="btn btn-primary shadow-sm">
        <i class="fas fa-plus me-2"></i>Add New Role
    </a>
</div>

<div class="card p-0 border-0 shadow-sm overflow-hidden" style="border-radius: 20px;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="px-4 py-3 text-uppercase small fw-bold text-muted border-0">Role Name</th>
                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Role Key</th>
                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Description</th>
                    <th class="py-3 text-uppercase small fw-bold text-muted border-0 text-end px-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $r): ?>
                <tr>
                    <td class="px-4 fw-bold text-dark"><?php echo htmlspecialchars($r['role_name']); ?></td>
                    <td>
                        <span class="badge bg-primary-subtle text-primary border border-primary-opacity-25 px-2 py-1">
                            <?php echo htmlspecialchars($r['role_key']); ?>
                        </span>
                    </td>
                    <td class="text-muted small"><?php echo htmlspecialchars($r['description']); ?></td>
                    <td class="text-end px-4">
                        <div class="btn-group">
                            <a href="edit.php?id=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-info" title="Edit"><i class="fas fa-edit"></i></a>
                            <?php if (!in_array($r['role_key'], ['admin', 'staff'])): ?>
                                <a href="delete.php?id=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Note: Deleting this role may affect users assigned to it. Proceed?')"><i class="fas fa-trash"></i></a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
