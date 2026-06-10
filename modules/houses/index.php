<?php
// modules/houses/index.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

$search = $_GET['q'] ?? '';
$where_clause = "";
$params = [];

if ($search) {
    $where_clause = " WHERE h.hnum LIKE ? OR i.fname LIKE ? OR i.lname LIKE ? OR h.block_no LIKE ? ";
    $params = ["%$search%", "%$search%", "%$search%", "%$search%"];
}

// Join with individuals to show owner name
$query = "
    SELECT h.*, 
           i.id as ind_id, i.fname, i.lname, i.phot, i.occ, i.s,
           ag.age
    FROM houses h
    LEFT JOIN individuals i ON h.owner_individual_id = i.id
    LEFT JOIN ages ag ON i.id = ag.id
    $where_clause
    ORDER BY h.hnum ASC
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$houses = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-building me-2 text-primary"></i><?php echo __('house_mgmt'); ?></h2>
    <?php if ($_SESSION['role'] !== 'security'): ?>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i><?php echo __('add_house'); ?>
    </a>
    <?php endif; ?>
</div>
<div class="row mb-5">
    <div class="col-lg-6">
        <form method="GET" class="search-container-premium d-flex align-items-center">
            <div class="search-icon-box">
                <i class="fas fa-search"></i>
            </div>
            <input type="text" name="q" class="form-control search-input-premium" 
                   placeholder="<?php echo __('search_placeholder'); ?>" 
                   value="<?php echo htmlspecialchars($search); ?>">
            <?php if ($search): ?>
                <a href="index.php" class="clear-search-link me-2" title="Clear search">
                    <i class="fas fa-times"></i>
                </a>
            <?php endif; ?>
            <button type="submit" class="btn btn-search-premium">
                <?php echo __('search'); ?>
            </button>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th><?php echo __('house_number'); ?></th>
                    <th><?php echo __('type'); ?></th>
                    <th><?php echo __('block'); ?></th>
                    <th><?php echo __('owner_name'); ?></th>
                    <th><?php echo __('details'); ?></th>
                    <th><?php echo __('utilities'); ?></th>
                    <th><?php echo __('actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($houses as $h): ?>
                <tr>
                    <td>
                        <span class="badge bg-dark fs-6 fw-bold px-3 py-2">H-<?php echo $h['hnum']; ?></span>
                    </td>
                    <td>
                        <span class="badge bg-outline-primary text-primary border border-primary small"><?php echo htmlspecialchars($h['house_type'] ?: 'Res.'); ?></span>
                    </td>
                    <td><?php echo htmlspecialchars($h['block_no'] ?: '—'); ?></td>
                    <td>
                        <?php if ($h['ind_id']): ?>
                            <div class="d-flex align-items-center gap-2">
                                <img src="../../assets/images/<?php echo htmlspecialchars($h['phot'] ?: 'default_profile.png'); ?>"
                                     class="rounded-circle border"
                                     style="width:32px;height:32px;object-fit:cover;"
                                     onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($h['fname'].' '.$h['lname']); ?>&size=80&background=4f46e5&color=fff'">
                                <div class="small">
                                    <div class="fw-bold"><?php echo htmlspecialchars("{$h['fname']} {$h['lname']}"); ?></div>
                                    <div class="text-muted small">ID: #<?php echo $h['ind_id']; ?></div>
                                </div>
                            </div>
                        <?php else: ?>
                            <span class="badge bg-light text-dark border"><?php echo __('unlinked'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="small">
                             <i class="fas fa-expand text-muted me-1"></i><?php echo $h['area'] ?: '0'; ?> m²<br>
                            <i class="fas fa-door-open text-muted me-1"></i><?php echo $h['rooms_count'] ?: 1; ?> <?php echo __('rooms'); ?>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <i class="fas fa-faucet <?php echo $h['has_water'] === 'Yes' ? 'text-primary' : 'text-light'; ?>" title="Water"></i>
                            <i class="fas fa-bolt <?php echo $h['has_electricity'] === 'Yes' ? 'text-warning' : 'text-light'; ?>" title="Electricity"></i>
                            <i class="fas fa-restroom text-info" title="Toilet: <?php echo $h['toilet_type'] ?: 'None'; ?>"></i>
                        </div>
                    </td>
                    <td>
                        <div class="btn-group">
                             <a href="view.php?hnum=<?php echo $h['hnum']; ?>" class="btn btn-sm btn-outline-primary" title="<?php echo __('view_details'); ?>"><i class="fas fa-eye"></i></a>
                            <?php if ($_SESSION['role'] !== 'security'): ?>
                                <a href="edit.php?hnum=<?php echo $h['hnum']; ?>" class="btn btn-sm btn-outline-info" title="<?php echo __('edit'); ?>"><i class="fas fa-edit"></i></a>
                                <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <a href="delete.php?hnum=<?php echo $h['hnum']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('<?php echo addslashes(__('delete_confirm')); ?>')" title="<?php echo __('delete'); ?>"><i class="fas fa-trash"></i></a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
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
