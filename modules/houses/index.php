<?php
// modules/houses/index.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

// Join with individuals to show owner name
$houses = $pdo->query("
    SELECT h.*, 
           i.id as ind_id, i.fname, i.lname, i.phot, i.occ, i.s,
           ag.age
    FROM houses h
    LEFT JOIN individuals i ON h.owner_individual_id = i.id
    LEFT JOIN ages ag ON i.id = ag.id
    ORDER BY h.hnum ASC
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-building me-2 text-primary"></i><?php echo __('house_mgmt'); ?></h2>
    <?php if ($_SESSION['role'] !== 'security'): ?>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i><?php echo __('add_house'); ?>
    </a>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th><?php echo __('house_number'); ?></th>
                    <th><?php echo __('owner_name'); ?></th>
                    <th><?php echo __('area'); ?></th>
                    <th><?php echo __('door_count'); ?></th>
                    <th>Residents</th>
                    <th><?php echo __('actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($houses as $h): ?>
                <?php
                    // Count residents living in this house
                    $res_count = $pdo->prepare("SELECT COUNT(*) FROM individuals WHERE id IN (SELECT resident_id FROM id_cards) AND id = ?");
                    // Simpler: count by house number if linked
                    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM individuals i WHERE i.id IN (SELECT owner_individual_id FROM houses WHERE hnum = ?)");
                    $count_stmt->execute([$h['hnum']]);
                ?>
                <tr>
                    <td>
                        <span class="badge bg-dark fs-6 fw-bold px-3 py-2">H-<?php echo $h['hnum']; ?></span>
                    </td>
                    <td>
                        <?php if ($h['ind_id']): ?>
                            <div class="d-flex align-items-center gap-3">
                                <img src="../../assets/images/<?php echo htmlspecialchars($h['phot'] ?: 'default_profile.png'); ?>"
                                     class="rounded-circle border"
                                     style="width:40px;height:40px;object-fit:cover;"
                                     onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($h['fname'].' '.$h['lname']); ?>&size=80&background=4f46e5&color=fff'">
                                <div>
                                    <div class="fw-bold"><?php echo htmlspecialchars("{$h['fname']} {$h['lname']}"); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($h['occ'] ?? ''); ?> · <?php echo $h['age'] ? $h['age'].' yrs' : ''; ?></small>
                                </div>
                            </div>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <?php echo $h['owner_id'] ? htmlspecialchars($h['owner_id']) : 'Unlinked'; ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $h['area']; ?> m²</td>
                    <td><i class="fas fa-door-open text-muted me-1"></i><?php echo $h['door']; ?></td>
                    <td>
                        <?php if ($h['ind_id']): ?>
                            <a href="../residents/view.php?id=<?php echo $h['ind_id']; ?>" class="btn btn-xs btn-outline-primary btn-sm">
                                <i class="fas fa-user me-1"></i> Profile
                            </a>
                        <?php else: ?>
                            <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="view.php?hnum=<?php echo $h['hnum']; ?>" class="btn btn-sm btn-outline-primary" title="View Details"><i class="fas fa-eye"></i></a>

                        <?php if ($_SESSION['role'] !== 'security'): ?>
                            <a href="edit.php?hnum=<?php echo $h['hnum']; ?>" class="btn btn-sm btn-outline-info" title="Edit"><i class="fas fa-edit"></i></a>

                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <a href="delete.php?hnum=<?php echo $h['hnum']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this house record?')" title="Delete"><i class="fas fa-trash"></i></a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($houses)): ?>
                <tr><td colspan="6" class="py-5 text-center text-muted"><?php echo __('no_records'); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
