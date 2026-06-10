<?php
// modules/economic/youth_list.php
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../auth/login.php'); exit; }

$search = $_GET['q'] ?? '';
$where = '';
$params = [];
if ($search) {
    $where = " AND (i.fname LIKE ? OR ey.skills LIKE ? OR ey.education_level LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%"];
}

$youth = $pdo->prepare("
    SELECT ey.*, i.fname, i.lname, i.phot, i.s, i.id AS individual_id,
           yc.id AS card_id, yc.id_num, yc.issue_date, yc.expiry_date, yc.status AS card_status
    FROM economic_youth_registry ey
    JOIN individuals i ON ey.individual_id = i.id
    LEFT JOIN youth_id_cards yc ON ey.id = yc.youth_record_id
    WHERE 1=1 $where
    ORDER BY ey.registration_date DESC
");
$youth->execute($params);
$youth = $youth->fetchAll();

$unemployed = $pdo->query("SELECT COUNT(*) FROM economic_youth_registry WHERE employment_status = 'Unemployed'")->fetchColumn();
$total_ids = $pdo->query("SELECT COUNT(*) FROM youth_id_cards WHERE status = 'Active'")->fetchColumn();
?>

<div class="mb-3">
    <a href="index.php" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold shadow-sm">
        <i class="fas fa-arrow-left me-2"></i><?php echo __('back'); ?>
    </a>
</div>

<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="fw-black mb-1" style="font-size: 2.4rem;"><i class="fas fa-users-gear text-danger me-2"></i><?php echo __('youth_job_registry'); ?></h2>
        <p class="text-muted mb-0"><?php echo __('youth_registry_desc'); ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="youth_id_issue.php" class="btn btn-outline-danger shadow-sm rounded-pill px-4 fw-bold">
            <i class="fas fa-id-card me-2"></i><?php echo __('issue_new_id'); ?>
        </a>
        <a href="youth_create.php" class="btn btn-danger text-white shadow-sm rounded-pill px-4 fw-bold">
            <i class="fas fa-user-plus me-2"></i><?php echo __('register_job_seeker'); ?>
        </a>
    </div>
</div>

<!-- Search Bar -->
<div class="card border-0 shadow-sm rounded-4 mb-4 p-3">
    <form method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 rounded-start-pill border-danger"><i class="fas fa-search text-danger"></i></span>
                <input type="text" name="q" class="form-control border-start-0 rounded-end-pill border-danger" placeholder="<?php echo __('search_youth_placeholder'); ?>" value="<?php echo htmlspecialchars($search); ?>">
            </div>
        </div>
        <div class="col-md-auto">
            <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold"><?php echo __('search_youth'); ?></button>
            <?php if($search): ?>
                <a href="youth_list.php" class="btn btn-light rounded-pill px-3 ms-1"><i class="fas fa-times"></i></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center border-start border-danger border-5">
            <div class="fw-black fs-2 text-danger"><?php echo count($youth); ?></div>
            <div class="text-muted small fw-bold text-uppercase"><?php echo __('registered_youth'); ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center">
            <div class="fw-black fs-2 text-dark"><?php echo $unemployed; ?></div>
            <div class="text-muted small fw-bold text-uppercase"><?php echo __('actively_unemployed'); ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center">
            <div class="fw-black fs-2 text-primary"><?php echo $total_ids; ?></div>
            <div class="text-muted small fw-bold text-uppercase"><?php echo __('ids_issued'); ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center border-start border-success border-5">
            <div class="fw-black fs-2 text-success"><?php echo count(array_filter($youth, fn($y) => $y['employment_status'] === 'Self-employed')); ?></div>
            <div class="text-muted small fw-bold text-uppercase"><?php echo __('entrepreneurs'); ?></div>
        </div>
    </div>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success rounded-4 border-0 shadow-sm mb-4"><?php echo htmlspecialchars($_GET['success']); ?></div>
<?php endif; ?>

<!-- Youth Table -->
<div class="card border-0 shadow-sm rounded-4 p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted small text-uppercase">
                <tr>
                    <th class="px-4 py-3"><?php echo __('member_id_num'); ?></th>
                    <th class="py-3"><?php echo __('edu_skills'); ?></th>
                    <th class="py-3"><?php echo __('status'); ?></th>
                    <th class="py-3"><?php echo __('id_card'); ?></th>
                    <th class="px-4 py-3 text-end"><?php echo __('action'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($youth as $y): 
                    $has_card = !empty($y['id_num']);
                    $card_st = $y['card_status'] ?? 'None';
                    $st_cls = match($y['employment_status']) { 'Unemployed'=>'bg-danger', 'Self-employed'=>'bg-success', 'Student'=>'bg-info', default=>'bg-secondary' };
                    $c_cls = match($card_st) { 'Active'=>'bg-success', 'Expired'=>'bg-danger', 'Lost'=>'bg-warning text-dark', default=>'bg-light text-muted border' };
                ?>
                <tr>
                    <td class="px-4">
                        <div class="d-flex align-items-center gap-2">
                             <div class="rounded-circle bg-danger-soft text-danger d-flex align-items-center justify-content-center fw-bold small shadow-sm" style="width:36px; height:36px; border:1px solid rgba(220,38,38,0.2);">
                                <?php if($y['phot']): ?>
                                    <img src="../../uploads/residents/<?php echo $y['phot']; ?>" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                                <?php else: ?>
                                    <?php echo substr($y['fname'], 0, 1); ?>
                                <?php endif; ?>
                             </div>
                             <div>
                                <div class="fw-bold text-dark small"><?php echo htmlspecialchars("{$y['fname']} {$y['lname']}"); ?></div>
                                 <?php if($has_card): ?>
                                    <code class="text-danger fw-bold" style="font-size:0.75rem;"><?php echo $y['id_num']; ?></code>
                                <?php else: ?>
                                    <small class="text-muted"><?php echo __('no_id_issued', 'No ID Issued'); ?></small>
                                <?php endif; ?>
                             </div>
                        </div>
                    </td>
                    <td>
                        <div class="fw-bold text-dark small"><?php echo htmlspecialchars($y['education_level']); ?></div>
                        <small class="text-muted italic"><?php echo htmlspecialchars($y['skills'] ?: __('general_labor', 'General Labor')); ?></small>
                    </td>
                    <td><span class="badge <?php echo $st_cls; ?> rounded-pill px-2" style="font-size:0.6rem;"><?php echo strtoupper($y['employment_status']); ?></span></td>
                    <td><span class="badge <?php echo $c_cls; ?> rounded-pill px-2" style="font-size:0.6rem;"><?php echo strtoupper($card_st); ?></span></td>
                    <td class="px-4 text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <?php if($has_card && $card_st === 'Active'): ?>
                                <a href="youth_print.php?id=<?php echo $y['card_id']; ?>" target="_blank" class="btn btn-sm btn-danger text-white rounded-pill px-3 fw-bold shadow-sm"><i class="fas fa-print me-1"></i><?php echo __('print_id'); ?></a>
                                <a href="youth_id_lost.php?card_id=<?php echo $y['card_id']; ?>" onclick="return confirm('<?php echo __('report_lost_confirm', 'Report this ID as LOST? This will invalidate the current card.'); ?>')" class="btn btn-sm btn-warning text-dark rounded-pill px-3 fw-bold"><i class="fas fa-triangle-exclamation me-1"></i><?php echo __('lost'); ?> <?php echo __('id_card'); ?></a>
                            <?php else: ?>
                                <a href="youth_id_issue.php?member=<?php echo $y['id']; ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold"><i class="fas fa-id-card me-1"></i><?php echo __('issue_id'); ?></a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($youth)): ?>
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="fas fa-users-viewfinder fa-3x mb-3 opacity-25"></i>
                        <p><?php echo __('no_youth_records'); ?></p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
