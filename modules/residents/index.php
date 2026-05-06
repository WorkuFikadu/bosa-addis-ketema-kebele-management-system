<?php
// modules/residents/index.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

// Search functionality
$search = $_GET['search'] ?? '';
$query = "SELECT i.*, a.pho_no, ag.age 
          FROM individuals i 
          LEFT JOIN addresses a ON i.id = a.id 
          LEFT JOIN ages ag ON i.id = ag.id";

if ($search) {
    $query .= " WHERE i.fname LIKE :s OR i.lname LIKE :s OR i.id LIKE :s";
}

$stmt = $pdo->prepare($query);
if ($search) {
    $stmt->execute(['s' => "%$search%"]);
} else {
    $stmt->execute();
}
$residents = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?php echo __('resident_mgmt'); ?></h2>
    <?php if ($_SESSION['role'] !== 'security'): ?>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i><?php echo __('reg_new_resident'); ?>
    </a>
    <?php endif; ?>
</div>

<div class="card p-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <form class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="<?php echo __('search_placeholder'); ?>" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-secondary"><?php echo __('search'); ?></button>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th><?php echo __('photo'); ?></th>
                    <th><?php echo __('id'); ?></th>
                    <th><?php echo __('full_name'); ?></th>
                    <th><?php echo __('sex_age'); ?></th>
                    <th><?php echo __('occupation'); ?></th>
                    <th><?php echo __('phone'); ?></th>
                    <th><?php echo __('actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($residents as $r): ?>
                <tr>
                    <td>
                        <img src="../../assets/images/<?php echo $r['phot']; ?>" class="rounded-circle" width="40" height="40" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($r['fname']); ?>&background=random'">
                    </td>
                    <td>#<?php echo $r['id']; ?></td>
                    <td>
                        <div class="fw-bold"><?php echo "{$r['fname']} {$r['mname']} {$r['lname']}"; ?></div>
                        <?php if ($r['status'] === 'deceased'): ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 0.65rem;">
                                <i class="fas fa-dove me-1"></i> <?php echo __('deceased_label'); ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo "{$r['s']} / {$r['age']}"; ?></td>
                    <td><?php echo $r['occ']; ?></td>
                    <td><?php echo $r['pho_no'] ?? 'N/A'; ?></td>
                    <td>
                        <?php if ($_SESSION['role'] === 'security'): ?>
                            <a href="view.php?id=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-secondary" title="<?php echo __('view_details'); ?>"><i class="fas fa-eye"></i></a>
                        <?php else: ?>
                            <a href="edit.php?id=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-info" title="<?php echo __('edit'); ?>"><i class="fas fa-edit"></i></a>
                            
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <a href="delete.php?id=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this resident?')" title="<?php echo __('delete'); ?>"><i class="fas fa-trash"></i></a>
                            <?php endif; ?>

                            <?php 
                            $id_info = $pdo->prepare("SELECT status, id FROM id_cards WHERE resident_id = ? AND status = 'Active'");
                            $id_info->execute([$r['id']]);
                            $id_card = $id_info->fetch();
                            
                            $any_id_info = $pdo->prepare("SELECT status FROM id_cards WHERE resident_id = ? ORDER BY id DESC LIMIT 1");
                            $any_id_info->execute([$r['id']]);
                            $latest_id = $any_id_info->fetch();

                            if (!$latest_id): ?>
                                <a href="../idcards/generate.php?id=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-primary" title="Generate ID"><i class="fas fa-plus-circle me-1"></i> Issue ID</a>
                            <?php elseif ($id_card): ?>
                                <a href="../idcards/index.php" class="btn btn-sm btn-outline-success" title="View ID"><i class="fas fa-id-card me-1"></i> Active ID</a>
                            <?php else: ?>
                                <a href="../idcards/generate.php?reapply=<?php echo $r['id']; ?>" class="btn btn-sm btn-primary shadow-sm" title="Re-apply Lost/Expired"><i class="fas fa-redo me-1"></i> Re-apply</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($residents)): ?>
                <tr><td colspan="7" class="text-center py-4 text-muted"><?php echo __('no_records'); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
