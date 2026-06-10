<?php
// modules/health/health_list.php
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../auth/login.php'); exit; }

// Handle entry deletion
if (isset($_POST['delete_entry'])) {
    $pdo->prepare("DELETE FROM health_records WHERE id = ?")->execute([$_POST['entry_id']]);
}

$search = $_GET['q'] ?? '';
$params = [];
$where = '';
if ($search) {
    $where = " AND (i.fname LIKE ? OR i.lname LIKE ? OR hr.service_type LIKE ? OR hr.staff_name LIKE ?)";
    $params = ["%$search%", "%$search%", "%search%", "%$search%"];
}

$records = $pdo->prepare("
    SELECT hr.*, i.fname, i.mname, i.lname, i.phot, i.id AS individual_id
    FROM health_records hr
    JOIN individuals i ON hr.individual_id = i.id
    WHERE 1=1 $where
    ORDER BY hr.service_date DESC, hr.created_at DESC
");
$records->execute($params);
$records = $records->fetchAll();

$stats = $pdo->query("SELECT service_type, COUNT(*) as cnt FROM health_records GROUP BY service_type")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<div class="mb-3">
    <a href="index.php" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold shadow-sm">
        <i class="fas fa-arrow-left me-2"></i><?php echo __('back'); ?>
    </a>
</div>

<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="fw-black mb-1" style="font-size: 2.4rem;"><i class="fas fa-syringe text-info me-2"></i><?php echo __('health_extension_registry'); ?></h2>
        <p class="text-muted mb-0"><?php echo __('health_extension_registry_desc'); ?></p>
    </div>
    <a href="health_create.php" class="btn btn-info text-white shadow-sm rounded-pill px-4 fw-bold">
        <i class="fas fa-plus me-2"></i><?php echo __('log_service_entry'); ?>
    </a>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center border-start border-info border-5">
            <div class="fw-black fs-2 text-info"><?php echo array_sum($stats); ?></div>
            <div class="text-muted small fw-bold text-uppercase"><?php echo __('total_logged_services'); ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center">
            <div class="fw-black fs-2 text-dark"><?php echo $stats['Vaccination'] ?? 0; ?></div>
            <div class="text-muted small fw-bold text-uppercase"><?php echo __('vaccinations'); ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center">
            <div class="fw-black fs-2 text-dark"><?php echo $stats['Maternal Health'] ?? 0; ?></div>
            <div class="text-muted small fw-bold text-uppercase"><?php echo __('maternal_care'); ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center">
            <div class="fw-black fs-2 text-dark"><?php echo $stats['General Checkup'] ?? 0; ?></div>
            <div class="text-muted small fw-bold text-uppercase"><?php echo __('checkups'); ?></div>
        </div>
    </div>
</div>

<!-- Search -->
<div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
    <form method="GET" class="row g-3 align-items-center">
        <div class="col-md-6">
            <div class="input-group shadow-sm rounded-pill overflow-hidden border">
                <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-search text-muted"></i></span>
                <input type="text" name="q" class="form-control border-0 py-2" placeholder="<?php echo __('search_health_placeholder'); ?>" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-info text-white px-4"><?php echo __('search'); ?></button>
            </div>
        </div>
    </form>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm rounded-4 p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted small text-uppercase">
                <tr>
                    <th class="px-4 py-3"><?php echo __('resident'); ?></th>
                    <th class="py-3"><?php echo __('service_type'); ?></th>
                    <th class="py-3"><?php echo __('date'); ?></th>
                    <th class="py-3"><?php echo __('provider'); ?></th>
                    <th class="py-3"><?php echo __('notes'); ?></th>
                    <th class="px-4 py-3 text-end"><?php echo __('actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $r): 
                    $type_cls = match($r['service_type']) {
                        'Vaccination' => 'bg-info',
                        'Maternal Health' => 'bg-danger',
                        'General Checkup' => 'bg-success',
                        'Clinic Referral' => 'bg-warning text-dark',
                        default => 'bg-secondary'
                    };
                    $display_type = match($r['service_type']) {
                        'Vaccination' => __('vaccination'),
                        'Maternal Health' => __('maternal_health'),
                        'General Checkup' => __('general_checkup'),
                        'Clinic Referral' => __('clinic_referral'),
                        default => $r['service_type']
                    };
                ?>
                <tr>
                    <td class="px-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle overflow-hidden shadow-sm" style="width:32px; height:32px; border:1px solid #eee;">
                                <?php if($r['phot']): ?>
                                    <img src="../../uploads/residents/<?php echo $r['phot']; ?>" style="width:100%; height:100%; object-fit:cover;">
                                <?php else: ?>
                                    <i class="fas fa-user-medical text-muted p-2"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="fw-bold text-dark small"><?php echo htmlspecialchars("{$r['fname']} {$r['lname']}"); ?></div>
                                <small class="text-muted">ID: #<?php echo $r['individual_id']; ?></small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge <?php echo $type_cls; ?> rounded-pill px-3" style="font-size:0.65rem;">
                            <?php echo strtoupper($display_type); ?>
                        </span>
                    </td>
                    <td><small class="fw-bold"><?php echo date('M d, Y', strtotime($r['service_date'])); ?></small></td>
                    <td><small class="text-dark"><?php echo htmlspecialchars($r['staff_name']); ?></small></td>
                    <td><small class="text-muted text-truncate d-inline-block" style="max-width:200px;"><?php echo htmlspecialchars($r['notes']); ?></small></td>
                    <td class="px-4 text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <button class="btn btn-sm btn-light rounded-circle shadow-sm" title="<?php echo __('edit'); ?>"><i class="fas fa-edit text-info"></i></button>
                            <form method="POST" class="d-inline" onsubmit="return confirm('<?php echo __('delete_health_confirm'); ?>')">
                                <input type="hidden" name="entry_id" value="<?php echo $r['id']; ?>">
                                <button type="submit" name="delete_entry" class="btn btn-sm btn-light rounded-circle shadow-sm" title="<?php echo __('delete'); ?>"><i class="fas fa-trash text-danger"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($records)): ?>
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fas fa-clipboard-list fa-3x mb-3 opacity-25"></i>
                        <p><?php echo __('no_health_records'); ?></p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
