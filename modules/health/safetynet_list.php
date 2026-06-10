<?php
// modules/health/safetynet_list.php
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../auth/login.php'); exit; }

$search = $_GET['q'] ?? '';
$where = '';
$params = [];
if ($search) {
    $where = " AND (i.fname LIKE ? OR i.lname LIKE ?)";
    $params = ["%$search%", "%$search%"];
}

$records = $pdo->prepare("
    SELECT sn.*, i.fname, i.lname, i.phot, i.id AS individual_id,
           sic.id AS card_id, sic.id_num, sic.issue_date, sic.expiry_date, sic.status AS card_status
    FROM safetynet_records sn
    JOIN individuals i ON sn.individual_id = i.id
    LEFT JOIN safetynet_id_cards sic ON sn.id = sic.safetynet_record_id
    WHERE 1=1 $where
    ORDER BY sn.enrollment_date DESC
");
$records->execute($params);
$records = $records->fetchAll();

$stats = $pdo->query("SELECT transfer_type, COUNT(*) as cnt FROM safetynet_records GROUP BY transfer_type")->fetchAll(PDO::FETCH_KEY_PAIR);
$total_ids = $pdo->query("SELECT COUNT(*) FROM safetynet_id_cards WHERE status = 'Active'")->fetchColumn();
?>

<div class="mb-3">
    <a href="index.php" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold shadow-sm">
        <i class="fas fa-arrow-left me-2"></i><?php echo __('back'); ?>
    </a>
</div>

<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="fw-black mb-1" style="font-size: 2.4rem;"><i class="fas fa-shield-heart text-info me-2"></i><?php echo __('psnp_title'); ?></h2>
        <p class="text-muted mb-0"><?php echo __('psnp_desc'); ?></p>
    </div>
    <a href="safetynet_create.php" class="btn btn-info text-white shadow-sm rounded-pill px-4 fw-bold">
        <i class="fas fa-user-check me-2"></i><?php echo __('enroll_participant'); ?>
    </a>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center border-start border-success border-5">
            <div class="fw-black fs-2 text-success"><?php echo array_sum($stats); ?></div>
            <div class="text-muted small fw-bold text-uppercase"><?php echo __('active_participants'); ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center border-start border-info border-5">
            <div class="fw-black fs-2 text-info"><?php echo $total_ids; ?></div>
            <div class="text-muted small fw-bold text-uppercase"><?php echo __('ids_issued'); ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center">
            <div class="fw-black fs-2 text-dark"><?php echo $stats['Cash'] ?? 0; ?></div>
            <div class="text-muted small fw-bold text-uppercase"><?php echo __('cash_beneficiaries'); ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center">
            <div class="fw-black fs-2 text-dark"><?php echo $stats['Food'] ?? 0; ?></div>
            <div class="text-muted small fw-bold text-uppercase"><?php echo __('food_beneficiaries'); ?></div>
        </div>
    </div>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success rounded-4 border-0 shadow-sm mb-4"><?php echo htmlspecialchars($_GET['success']); ?></div>
<?php endif; ?>

<!-- Registry Table -->
<div class="card border-0 shadow-sm rounded-4 p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted small text-uppercase">
                <tr>
                    <th class="px-4 py-3"><?php echo __('participant_id'); ?></th>
                    <th class="py-3"><?php echo __('type_of_support'); ?></th>
                    <th class="py-3"><?php echo __('work_category'); ?></th>
                    <th class="py-3"><?php echo __('house_size'); ?></th>
                    <th class="py-3"><?php echo __('id_status'); ?></th>
                    <th class="px-4 py-3 text-end"><?php echo __('action'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $r): 
                    $has_card = !empty($r['id_num']);
                    $card_st = $r['card_status'] ?? 'None';
                    $t_cls = match($r['transfer_type']) { 'Cash'=>'text-success', 'Food'=>'text-warning', default=>'text-info' };
                    $w_cls = match($r['work_status']) { 'Public Work'=>'bg-primary', 'Direct Support'=>'bg-info', default=>'bg-secondary' };
                    $st_cls = match($card_st) { 'Active'=>'bg-success', 'Expired'=>'bg-danger', 'Lost'=>'bg-warning text-dark', default=>'bg-light text-muted border' };
                    
                    $display_card_status = match($card_st) {
                        'Active' => __('active'),
                        'Expired' => __('expired'),
                        'Lost' => __('lost'),
                        default => __('none', 'None')
                    };
                ?>
                <tr>
                    <td class="px-4">
                        <?php if($has_card): ?>
                            <div class="fw-bold text-info mb-1" style="font-size:1.1rem;"><?php echo $r['id_num']; ?></div>
                        <?php endif; ?>
                        <div class="fw-bold text-dark small"><?php echo htmlspecialchars("{$r['fname']} {$r['lname']}"); ?></div>
                        <small class="text-muted">ID: #<?php echo $r['individual_id']; ?></small>
                    </td>
                    <td><small class="fw-bold <?php echo $t_cls; ?>"><i class="fas <?php echo $r['transfer_type']=='Cash'?'fa-money-bill':'fa-bowl-food'; ?> me-1"></i><?php echo strtoupper($r['transfer_type']); ?></small></td>
                    <td>
                        <span class="badge <?php echo $w_cls; ?> rounded-pill px-2" style="font-size:0.6rem;">
                            <?php echo strtoupper($r['work_status']); ?>
                        </span>
                    </td>
                    <td><small class="text-dark fw-bold"><?php echo $r['household_size']; ?> <?php echo __('members'); ?></small></td>
                    <td>
                        <span class="badge <?php echo $st_cls; ?> px-2 rounded-pill" style="font-size:0.6rem;"><?php echo strtoupper($display_card_status); ?></span>
                    </td>
                    <td class="px-4 text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <?php if($has_card && $card_st === 'Active'): ?>
                                <a href="safetynet_print.php?id=<?php echo $r['card_id']; ?>" target="_blank" class="btn btn-sm btn-info text-white rounded-pill px-3 fw-bold"><i class="fas fa-print me-1"></i><?php echo __('print_id'); ?></a>
                            <?php else: ?>
                                <a href="safetynet_id_issue.php?member=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold"><i class="fas fa-id-card me-1"></i><?php echo __('issue_id'); ?></a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($records)): ?>
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fas fa-hand-holding-heart fa-3x mb-3 opacity-25"></i>
                        <p><?php echo __('no_psnp_participants'); ?></p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
