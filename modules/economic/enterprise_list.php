<?php
// modules/economic/enterprise_list.php
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../auth/login.php'); exit; }

$search = $_GET['q'] ?? '';
$where = '';
$params = [];
if ($search) {
    $where = " AND (i.fname LIKE ? OR ee.business_name LIKE ? OR ee.license_number LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%"];
}

$enterprises = $pdo->prepare("
    SELECT ee.*, i.fname, i.lname, i.phot
    FROM economic_enterprises ee
    JOIN individuals i ON ee.owner_id = i.id
    WHERE 1=1 $where
    ORDER BY ee.registration_date DESC
");
$enterprises->execute($params);
$enterprises = $enterprises->fetchAll();

$stats = $pdo->query("SELECT business_type, COUNT(*) as cnt FROM economic_enterprises GROUP BY business_type")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<<div class="mb-3">
    <a href="index.php" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold shadow-sm">
        <i class="fas fa-arrow-left me-2"></i><?php echo __('back'); ?>
    </a>
</div>

<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="fw-black mb-1" style="font-size: 2.4rem;"><i class="fas fa-store text-danger me-2"></i><?php echo __('sme_enterprise_registry'); ?></h2>
        <p class="text-muted mb-0"><?php echo __('sme_enterprise_registry_desc'); ?></p>
    </div>
    <a href="enterprise_create.php" class="btn btn-danger text-white shadow-sm rounded-pill px-4 fw-bold">
        <i class="fas fa-plus me-2"></i><?php echo __('register_enterprise'); ?>
    </a>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center border-start border-danger border-5">
            <div class="fw-black fs-2 text-danger"><?php echo array_sum($stats); ?></div>
            <div class="text-muted small fw-bold text-uppercase"><?php echo __('total_businesses'); ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center">
            <div class="fw-black fs-2 text-dark"><?php echo $stats['Retail'] ?? 0; ?></div>
            <div class="text-muted small fw-bold text-uppercase"><?php echo __('retailers'); ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center">
            <div class="fw-black fs-2 text-dark"><?php echo $stats['Agriculture'] ?? 0; ?></div>
            <div class="text-muted small fw-bold text-uppercase"><?php echo __('agri_business'); ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center">
            <div class="fw-black fs-2 text-dark"><?php echo $stats['Services'] ?? 0; ?></div>
            <div class="text-muted small fw-bold text-uppercase"><?php echo __('service_sector'); ?></div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm rounded-4 p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted small text-uppercase">
                <tr>
                    <th class="px-4 py-3"><?php echo __('business_name'); ?></th>
                    <th class="py-3"><?php echo __('owner'); ?></th>
                    <th class="py-3"><?php echo __('type'); ?></th>
                    <th class="py-3"><?php echo __('license_num'); ?></th>
                    <th class="py-3"><?php echo __('status'); ?></th>
                    <th class="px-4 py-3 text-end"><?php echo __('action'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($enterprises as $e): 
                    $st_cls = match($e['status']) { 'Active'=>'bg-success', 'Suspended'=>'bg-warning text-dark', default=>'bg-danger' };
                ?>
                <tr>
                    <td class="px-4">
                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($e['business_name']); ?></div>
                        <small class="text-muted"><?php echo __('registered'); ?>: <?php echo date('M Y', strtotime($e['registration_date'])); ?></small>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle overflow-hidden shadow-sm border" style="width:28px; height:28px;">
                                <img src="../../uploads/residents/<?php echo $e['phot'] ?: 'default_profile.png'; ?>" style="width:100%; height:100%; object-fit:cover;">
                            </div>
                            <span class="small fw-bold"><?php echo htmlspecialchars("{$e['fname']} {$e['lname']}"); ?></span>
                        </div>
                    </td>
                    <td><span class="badge bg-light text-dark border rounded-pill px-3" style="font-size:0.65rem;"><?php echo strtoupper($e['business_type']); ?></span></td>
                    <td><code class="small text-danger fw-bold"><?php echo htmlspecialchars($e['license_number'] ?: 'PNDG'); ?></code></td>
                    <td><span class="badge <?php echo $st_cls; ?> rounded-pill px-2" style="font-size:0.6rem;"><?php echo strtoupper($e['status']); ?></span></td>
                    <td class="px-4 text-end">
                        <button class="btn btn-sm btn-light rounded-circle shadow-sm"><i class="fas fa-edit text-danger"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($enterprises)): ?>
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fas fa-store fa-3x mb-3 opacity-25"></i>
                        <p><?php echo __('no_enterprises_found'); ?></p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
