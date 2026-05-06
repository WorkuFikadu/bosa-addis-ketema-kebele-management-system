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
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th><?php echo __('house_no'); ?></th>
                    <th>Family Structure</th>
                    <th><?php echo __('family_leader'); ?></th>
                    <th>Size (M/F)</th>
                    <th>Support Status</th>
                    <th><?php echo __('actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($families as $f): ?>
                <tr>
                    <td><span class="badge bg-dark px-3 py-2">H-<?php echo $f['hnum']; ?></span></td>
                    <td>
                        <div class="fw-bold"><?php echo $f['family_type'] ?: 'Nuclear'; ?></div>
                        <small class="text-muted"><?php echo $f['social_status'] ?: 'Resident'; ?></small>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-user-tie text-muted"></i>
                            <div>
                                <div class="fw-bold"><?php echo htmlspecialchars($f['fname'] . ' ' . $f['lname']); ?></div>
                                <small class="text-muted">Lead ID: #<?php echo $f['lead_id']; ?></small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="fw-bold fs-5 mb-0"><?php echo $f['fam_no']; ?> members</div>
                        <div class="small text-muted"><?php echo $f['total_males']; ?>M / <?php echo $f['total_females']; ?>F</div>
                    </td>
                    <td>
                        <?php if ($f['is_vulnerable'] === 'Yes'): ?>
                            <span class="badge bg-danger rounded-pill"><i class="fas fa-hand-holding-heart me-1"></i> Priority</span>
                        <?php else: ?>
                            <span class="badge bg-light text-dark border rounded-pill">Standard</span>
                        <?php endif; ?>
                        <div class="mt-1 small text-muted"><?php echo $f['income_category']; ?> Inc.</div>
                    </td>
                    <td>
                        <div class="btn-group">
                            <a href="view.php?hnum=<?php echo $f['hnum']; ?>" class="btn btn-sm btn-outline-primary" title="View Detail"><i class="fas fa-eye"></i></a>
                            <?php if ($_SESSION['role'] !== 'security'): ?>
                                <a href="edit.php?hnum=<?php echo $f['hnum']; ?>" class="btn btn-sm btn-outline-info" title="Edit"><i class="fas fa-edit"></i></a>
                                <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <a href="delete.php?hnum=<?php echo $f['hnum']; ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete family record?')"><i class="fas fa-trash"></i></a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($families)): ?>
                <tr><td colspan="6" class="text-center py-5 text-muted"><?php echo __('no_records'); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
