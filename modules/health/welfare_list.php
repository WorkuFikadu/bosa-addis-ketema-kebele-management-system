<?php
// modules/health/welfare_list.php
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../auth/login.php'); exit; }

// Handle status update
if (isset($_POST['update_welfare_status'])) {
    $pdo->prepare("UPDATE welfare_records SET aid_status = ? WHERE id = ?")->execute([$_POST['new_status'], $_POST['record_id']]);
}

$search = $_GET['q'] ?? '';
$where = '';
$params = [];
if ($search) {
    $where = " AND (i.fname LIKE ? OR i.lname LIKE ? OR wr.vulnerability_type LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%"];
}

$records = $pdo->prepare("
    SELECT wr.*, i.fname, i.mname, i.lname, i.phot, i.id AS individual_id
    FROM welfare_records wr
    JOIN individuals i ON wr.individual_id = i.id
    WHERE 1=1 $where
    ORDER BY wr.created_at DESC
");
$records->execute($params);
$records = $records->fetchAll();

$stats = $pdo->query("SELECT vulnerability_type, COUNT(*) as cnt FROM welfare_records GROUP BY vulnerability_type")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<div class="mb-3">
    <a href="index.php" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold shadow-sm">
        <i class="fas fa-arrow-left me-2"></i><?php echo __('back'); ?>
    </a>
</div>

<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="fw-black mb-1" style="font-size: 2.4rem;"><i class="fas fa-hands-holding-child text-info me-2"></i><?php echo __('social_welfare_registry'); ?></h2>
        <p class="text-muted mb-0"><?php echo __('social_welfare_registry_desc'); ?></p>
    </div>
    <a href="welfare_create.php" class="btn btn-info text-white shadow-sm rounded-pill px-4 fw-bold">
        <i class="fas fa-user-plus me-2"></i><?php echo __('new_welfare_entry'); ?>
    </a>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center border-start border-danger border-5">
            <div class="fw-black fs-2 text-danger"><?php echo array_sum($stats); ?></div>
            <div class="text-muted small fw-bold text-uppercase"><?php echo __('total_beneficiaries'); ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center">
            <div class="fw-black fs-2 text-dark"><?php echo $stats['Disabled'] ?? 0; ?></div>
            <div class="text-muted small fw-bold text-uppercase"><?php echo __('disabilities'); ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center">
            <div class="fw-black fs-2 text-dark"><?php echo $stats['Elderly'] ?? 0; ?></div>
            <div class="text-muted small fw-bold text-uppercase"><?php echo __('senior_citizens'); ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center">
            <div class="fw-black fs-2 text-dark"><?php echo $stats['Orphan'] ?? 0; ?></div>
            <div class="text-muted small fw-bold text-uppercase"><?php echo __('orphans'); ?></div>
        </div>
    </div>
</div>

<!-- Registry Table -->
<div class="card border-0 shadow-sm rounded-4 p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted small text-uppercase">
                <tr>
                    <th class="px-4 py-3"><?php echo __('resident'); ?></th>
                    <th class="py-3"><?php echo __('category'); ?></th>
                    <th class="py-3"><?php echo __('aid_type'); ?></th>
                    <th class="py-3"><?php echo __('aid_status'); ?></th>
                    <th class="py-3"><?php echo __('date'); ?></th>
                    <th class="px-4 py-3 text-end"><?php echo __('actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $r): 
                    $stat_cls = match($r['aid_status']) {
                        'Receiving Aid' => 'bg-success',
                        'Waitlist' => 'bg-warning text-dark',
                        'Graduated' => 'bg-info',
                        default => 'bg-secondary'
                    };
                    $display_status = match($r['aid_status']) {
                        'Receiving Aid' => __('receiving_aid', 'Receiving Aid'),
                        'Waitlist' => __('waitlist', 'Waitlist'),
                        'Graduated' => __('graduated', 'Graduated'),
                        default => $r['aid_status']
                    };
                    $display_cat = match($r['vulnerability_type']) {
                        'Disabled' => __('disabled', 'Disabled'),
                        'Elderly' => __('elderly', 'Elderly'),
                        'Orphan' => __('orphan', 'Orphan'),
                        default => $r['vulnerability_type']
                    };
                ?>
                <tr>
                    <td class="px-4">
                        <div class="fw-bold text-dark small"><?php echo htmlspecialchars("{$r['fname']} {$r['lname']}"); ?></div>
                        <small class="text-muted"><?php echo __('sex'); ?>: <?php echo $r['id'] % 2 == 0 ? __('male', 'M') : __('female', 'F'); ?> | <?php echo __('age'); ?>: --</small>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border rounded-pill px-2" style="font-size:0.65rem;">
                            <?php echo strtoupper($display_cat); ?>
                        </span>
                    </td>
                    <td><small class="text-dark fw-bold"><?php echo htmlspecialchars($r['aid_type'] ?: __('pending_review', 'Pending Review')); ?></small></td>
                    <td>
                        <span class="badge <?php echo $stat_cls; ?> rounded-pill px-2" style="font-size:0.6rem;">
                            <?php echo strtoupper($display_status); ?>
                        </span>
                    </td>
                    <td><small class="text-muted"><?php echo date('M d, Y', strtotime($r['created_at'])); ?></small></td>
                    <td class="px-4 text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light rounded-pill px-3 shadow-sm dropdown-toggle fw-bold" type="button" data-bs-toggle="dropdown">
                                <?php echo __('status'); ?>
                            </button>
                            <ul class="dropdown-menu border-0 shadow-lg">
                                <li>
                                    <form method="POST">
                                        <input type="hidden" name="record_id" value="<?php echo $r['id']; ?>">
                                        <input type="hidden" name="new_status" value="Receiving Aid">
                                        <button type="submit" name="update_welfare_status" class="dropdown-item small text-success fw-bold"><?php echo __('mark_receiving_aid', 'Mark as Receiving Aid'); ?></button>
                                    </form>
                                </li>
                                <li>
                                    <form method="POST">
                                        <input type="hidden" name="record_id" value="<?php echo $r['id']; ?>">
                                        <input type="hidden" name="new_status" value="Waitlist">
                                        <button type="submit" name="update_welfare_status" class="dropdown-item small text-warning fw-bold"><?php echo __('move_to_waitlist', 'Move to Waitlist'); ?></button>
                                    </form>
                                </li>
                                <li>
                                    <form method="POST">
                                        <input type="hidden" name="record_id" value="<?php echo $r['id']; ?>">
                                        <input type="hidden" name="new_status" value="Graduated">
                                        <button type="submit" name="update_welfare_status" class="dropdown-item small text-info fw-bold"><?php echo __('case_graduated', 'Case Graduated'); ?></button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($records)): ?>
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fas fa-hands-holding-child fa-3x mb-3 opacity-25"></i>
                        <p><?php echo __('no_welfare_participants'); ?></p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
