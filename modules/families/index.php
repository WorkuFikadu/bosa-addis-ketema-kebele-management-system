<?php
// modules/families/index.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

$query = "SELECT f.*, h.area, i.fname, i.lname, i.mname
          FROM families f 
          LEFT JOIN houses h ON f.hnum = h.hnum
          LEFT JOIN individuals i ON f.lead_id = i.id";
$families = $pdo->query($query)->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?php echo __('family_mgmt'); ?></h2>
    <?php if ($_SESSION['role'] !== 'security'): ?>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i><?php echo __('reg_family'); ?>
    </a>
    <?php endif; ?>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th><?php echo __('house_no'); ?></th>
                    <th><?php echo __('family_leader'); ?></th>
                    <th><?php echo __('members_count'); ?></th>
                    <th><?php echo __('actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($families as $f): ?>
                <tr>
                    <td><strong>H-<?php echo $f['hnum']; ?></strong></td>
                    <td><?php echo htmlspecialchars($f['fname'] . ' ' . $f['mname'] . ' ' . $f['lname']); ?></td>
                    <td><span class="badge bg-info p-2 px-3"><?php echo $f['fam_no']; ?> <?php echo __('members'); ?></span></td>
                    <td>
                        <a href="view.php?hnum=<?php echo $f['hnum']; ?>" class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                        
                        <?php if ($_SESSION['role'] !== 'security'): ?>
                            <a href="edit.php?hnum=<?php echo $f['hnum']; ?>" class="btn btn-sm btn-outline-info" title="Edit"><i class="fas fa-edit"></i></a>
                            
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <a href="delete.php?hnum=<?php echo $f['hnum']; ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete this family record?')"><i class="fas fa-trash"></i></a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($families)): ?>
                <tr><td colspan="4" class="text-center py-4"><?php echo __('no_records'); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
