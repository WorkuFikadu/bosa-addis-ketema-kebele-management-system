<?php
// modules/economic/subsidy_list.php
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../auth/login.php'); exit; }

$records = $pdo->query("
    SELECT es.*, i.fname, i.lname, i.phot
    FROM economic_subsidies es
    JOIN individuals i ON es.individual_id = i.id
    ORDER BY es.distribution_date DESC, es.collected_at DESC
")->fetchAll();

$items = $pdo->query("SELECT item_type, SUM(quantity) as total FROM economic_subsidies GROUP BY item_type")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<div class="mb-3">
    <a href="index.php" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold shadow-sm">
        <i class="fas fa-arrow-left me-2"></i><?php echo __('back'); ?>
    </a>
</div>

<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="fw-black mb-1" style="font-size: 2.4rem;"><i class="fas fa-wheat-awn text-danger me-2"></i><?php echo __('subsidized_goods_logs'); ?></h2>
        <p class="text-muted mb-0"><?php echo __('subsidized_goods_logs_desc'); ?></p>
    </div>
    <a href="subsidy_create.php" class="btn btn-danger text-white shadow-sm rounded-pill px-4 fw-bold">
        <i class="fas fa-box-open me-2"></i><?php echo __('log_new_distribution'); ?>
    </a>
</div>

<!-- Resource Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-danger text-white">
            <h4 class="fw-black mb-1"><?php echo $items['Oil'] ?? 0; ?> L</h4>
            <div class="small fw-bold text-uppercase opacity-75"><?php echo __('cooking_oil_distributed'); ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border border-danger border-2 text-danger">
            <h4 class="fw-black mb-1"><?php echo $items['Sugar'] ?? 0; ?> Kg</h4>
            <div class="small fw-bold text-uppercase opacity-75"><?php echo __('sugar_distributed'); ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border border-danger border-2 text-danger">
            <h4 class="fw-black mb-1"><?php echo $items['Wheat'] ?? 0; ?> Kg</h4>
            <div class="small fw-bold text-uppercase opacity-75"><?php echo __('wheat_distributed'); ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border border-danger border-2 text-danger">
            <h4 class="fw-black mb-1"><?php echo count($records); ?></h4>
            <div class="small fw-bold text-uppercase opacity-75"><?php echo __('total_transactions'); ?></div>
        </div>
    </div>
</div>

<!-- logs -->
<div class="card border-0 shadow-sm rounded-4 p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted small text-uppercase">
                <tr>
                    <th class="px-4 py-3"><?php echo __('recipient'); ?></th>
                    <th class="py-3"><?php echo __('item_type'); ?></th>
                    <th class="py-3"><?php echo __('quantity'); ?></th>
                    <th class="py-3"><?php echo __('distribution_date'); ?></th>
                    <th class="px-4 py-3 text-end"><?php echo __('log_time'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $r): ?>
                <tr>
                    <td class="px-4">
                        <div class="fw-bold text-dark small"><?php echo htmlspecialchars("{$r['fname']} {$r['lname']}"); ?></div>
                        <small class="text-muted">ID: #<?php echo $r['individual_id']; ?></small>
                    </td>
                    <td><span class="badge bg-danger-soft text-danger rounded-pill px-3"><?php echo strtoupper($r['item_type']); ?></span></td>
                    <td><div class="fw-black text-dark"><?php echo $r['quantity']; ?> <?php echo $r['unit']; ?></div></td>
                    <td><small class="fw-bold"><?php echo date('M d, Y', strtotime($r['distribution_date'])); ?></small></td>
                    <td class="px-4 text-end text-muted small"><?php echo date('h:i A', strtotime($r['collected_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($records)): ?>
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="fas fa-dolly fa-3x mb-3 opacity-25"></i>
                        <p><?php echo __('no_distribution_records'); ?></p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
