<?php
// modules/justice/police_list.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/payment_handler.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php'); exit;
}

// Handle Lost ID
if (isset($_POST['update_status'])) {
    $card_id = $_POST['card_id'];
    $new_status = $_POST['new_status'];
    $pdo->prepare("UPDATE police_id_cards SET status = ? WHERE id = ?")->execute([$new_status, $card_id]);
}

$search = $_GET['q'] ?? '';
$params = [];
$where = '';
if ($search) {
    $where = " AND (i.fname LIKE ? OR i.lname LIKE ? OR pr.badge_number LIKE ? OR pic.id_num LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%", "%$search%"];
}

// Fetch all police with their ID card info
$officers = $pdo->prepare("
    SELECT pr.*, i.fname, i.mname, i.lname, i.phot,
           pic.id AS card_id, pic.id_num, pic.issue_date, pic.expiry_date, pic.status AS card_status,
           (SELECT COUNT(*) FROM transactions t 
            WHERE t.resident_id = pr.individual_id 
            AND t.service_type = 'police_id' 
            AND t.status = 'Completed') as payment_done
    FROM police_records pr
    JOIN individuals i ON pr.individual_id = i.id
    LEFT JOIN police_id_cards pic ON pr.id = pic.police_record_id
    WHERE 1=1 $where
    ORDER BY pr.created_at DESC
");
$officers->execute($params);
$officers = $officers->fetchAll();
?>

<div class="mb-3">
    <a href="index.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold shadow-sm">
        <i class="fas fa-arrow-left me-2"></i><?php echo __('back_to_dashboard'); ?>
    </a>
</div>
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="fw-black mb-1" style="font-size: 2.4rem;"><?php echo __('police_registry'); ?></h2>
        <p class="text-muted mb-0" style="font-size: 1rem;"><?php echo __('police_registry_desc'); ?></p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <button class="btn btn-warning shadow-sm rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#lostIdModal">
            <i class="fas fa-search me-2"></i><?php echo __('report_lost_id'); ?>
        </button>
        <a href="police_create.php" class="btn btn-primary shadow-sm rounded-pill px-4 fw-bold">
            <i class="fas fa-user-plus me-2"></i><?php echo __('register_new_officer'); ?>
        </a>
        <a href="police_idcard.php" class="btn btn-success shadow-sm rounded-pill px-4 fw-bold">
            <i class="fas fa-id-card me-2"></i><?php echo __('issue_police_id'); ?>
        </a>
    </div>
</div>

<!-- Lost ID Modal -->
<div class="modal fade" id="lostIdModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i><?php echo __('report_lost_police_id_title'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <p class="text-muted small mb-4"><?php echo __('lost_id_modal_desc'); ?></p>
                    <select name="card_id" class="form-select border-warning" required>
                        <option value=""><?php echo __('choose_active_police_id'); ?></option>
                        <?php
                        $active = $pdo->query("SELECT pic.id, i.fname, i.lname, pic.id_num FROM police_id_cards pic JOIN police_records pr ON pic.police_record_id = pr.id JOIN individuals i ON pr.individual_id = i.id WHERE pic.status = 'Active' ORDER BY i.fname")->fetchAll();
                        foreach ($active as $a):
                        ?>
                            <option value="<?php echo $a['id']; ?>"><?php echo htmlspecialchars($a['fname'].' '.$a['lname'].' ('.$a['id_num'].')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="new_status" value="Lost">
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <button type="submit" name="update_status" class="btn btn-warning px-4 fw-bold"><?php echo __('confirm_lost'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Search -->
<div class="row mb-4">
    <div class="col-lg-5">
        <form method="GET" class="d-flex align-items-center gap-2">
            <div class="input-group shadow-sm rounded-pill overflow-hidden border" style="border-color:#e2e8f0!important;">
                <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-search text-muted"></i></span>
                <input type="text" name="q" class="form-control border-0 py-2" placeholder="<?php echo __('search_police_placeholder'); ?>" value="<?php echo htmlspecialchars($search); ?>">
                <?php if ($search): ?>
                    <a href="police_list.php" class="input-group-text bg-white border-0 text-danger"><i class="fas fa-times"></i></a>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary px-4 fw-bold rounded-0 rounded-end"><?php echo __('search'); ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card p-4 border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead style="background:#f8f9fa;" class="text-muted small text-uppercase">
                <tr>
                    <th class="border-0 px-3"><?php echo __('badge_id_num'); ?></th>
                    <th class="border-0"><?php echo __('full_name'); ?></th>
                    <th class="border-0"><?php echo __('rank_assignment'); ?></th>
                    <th class="border-0"><?php echo __('billing_status'); ?></th>
                    <th class="border-0 text-end px-3"><?php echo __('actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($officers as $officer):
                    $card_status = $officer['card_status'] ?? null;
                    $has_card = !empty($officer['id_num']);
                    $status_cls = $card_status === 'Active' ? 'bg-success' : ($card_status === 'Lost' ? 'bg-warning text-dark' : 'bg-secondary');
                    $officer_status_cls = $officer['status'] === 'Active' ? 'bg-success' : ($officer['status'] === 'Suspended' ? 'bg-warning text-dark' : 'bg-secondary');
                ?>
                <tr>
                    <td class="px-3">
                        <?php if ($has_card): ?>
                            <div class="fw-bold text-primary mb-1" style="font-size:1.15rem;"><?php echo htmlspecialchars($officer['id_num']); ?></div>
                            <small class="text-muted"><?php echo __('expiry'); ?>: <?php echo $officer['expiry_date']; ?></small><br>
                        <?php else: ?>
                            <span class="badge bg-secondary rounded-pill"><?php echo __('no_id_issued'); ?></span><br>
                        <?php endif; ?>
                        <small class="text-muted">Badge: <strong><?php echo htmlspecialchars($officer['badge_number']); ?></strong></small>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-warning-soft text-warning rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width:44px;height:44px;font-size:1.1rem;flex-shrink:0;">
                                <?php if (!empty($officer['phot']) && $officer['phot'] !== 'default_profile.png'): ?>
                                    <img src="../../uploads/residents/<?php echo $officer['phot']; ?>" style="width:44px;height:44px;object-fit:cover;border-radius:50%;">
                                <?php else: ?>
                                    <i class="fas fa-user-shield"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size:1.05rem;"><?php echo htmlspecialchars("{$officer['fname']} {$officer['mname']} {$officer['lname']}"); ?></div>
                                <small class="text-muted"><?php echo __('zone'); ?>: <?php echo htmlspecialchars($officer['station_assignment']); ?></small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="fw-bold text-dark small"><?php echo htmlspecialchars($officer['rank']); ?></div>
                        <span class="badge <?php echo $officer_status_cls; ?> rounded-pill px-2 mt-1" style="font-size:0.65rem;">
                            <i class="fas fa-circle me-1" style="font-size:0.5rem;"></i><?php echo strtoupper($officer['status']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($has_card): ?>
                            <div class="mb-1">
                                <?php if ($officer['payment_done']): ?>
                                    <span class="badge border border-success text-success rounded-pill px-2" style="font-size:0.6rem;"><?php echo __('paid'); ?></span>
                                <?php else: ?>
                                    <span class="badge border border-warning text-warning rounded-pill px-2" style="font-size:0.6rem;"><?php echo __('unpaid'); ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="badge <?php echo $status_cls; ?> px-2 py-1" style="font-size:0.65rem;">
                                <i class="fas <?php echo $card_status === 'Active' ? 'fa-check-circle' : 'fa-search'; ?> me-1"></i>
                                <?php echo strtoupper($card_status); ?>
                            </span>
                        <?php else: ?>
                            <span class="badge bg-light text-muted border rounded-pill px-2" style="font-size:0.65rem;"><?php echo __('no_id_card'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end px-3">
                        <div class="d-flex gap-1 justify-content-end flex-wrap">
                            <?php if ($has_card && $card_status === 'Active' && $officer['payment_done']): ?>
                                <a href="police_print.php?id=<?php echo $officer['card_id']; ?>" target="_blank" class="btn btn-sm btn-success shadow-sm px-3 rounded-pill fw-bold">
                                    <i class="fas fa-print me-1"></i><?php echo __('print'); ?>
                                </a>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="card_id" value="<?php echo $officer['card_id']; ?>">
                                    <input type="hidden" name="new_status" value="Lost">
                                    <button type="submit" name="update_status" class="btn btn-sm btn-outline-warning rounded-circle" title="Report Lost" onclick="return confirm('Mark this ID as lost?')">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </form>
                            <?php elseif ($has_card && ($card_status === 'Lost' || $card_status === 'Expired')): ?>
                                <a href="police_idcard.php?reapply=<?php echo $officer['id']; ?>" class="btn btn-sm btn-primary shadow-sm px-3 rounded-pill fw-bold">
                                    <i class="fas fa-redo me-1"></i><?php echo __('re_issue'); ?>
                                </a>
                            <?php elseif ($has_card && !$officer['payment_done']): ?>
                                <button class="btn btn-sm btn-dark shadow-sm px-3 rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#payModal<?php echo $officer['card_id']; ?>">
                                    <i class="fas fa-wallet me-1"></i><?php echo __('pay_to_activate'); ?>
                                </button>
                                <!-- Payment Modal -->
                                <div class="modal fade" id="payModal<?php echo $officer['card_id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg text-start">
                                        <div class="modal-content border-0 shadow-lg">
                                            <form method="POST" action="../../modules/vital/process_list_payment.php" enctype="multipart/form-data">
                                                <div class="modal-body p-0">
                                                    <?php displayPaymentGateway('police_id', $officer['individual_id'], "{$officer['fname']} {$officer['lname']}"); ?>
                                                    <input type="hidden" name="redirect_to" value="../../modules/justice/police_list.php">
                                                    <div class="p-3 text-end bg-light border-top">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                                                        <button type="submit" class="btn btn-primary px-4 fw-bold"><?php echo __('confirm_payment'); ?></button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <a href="police_idcard.php?officer=<?php echo $officer['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
                                    <i class="fas fa-id-card me-1"></i><?php echo __('issue_id'); ?>
                                </a>
                            <?php endif; ?>
                            <a href="police_create.php?view=<?php echo $officer['id']; ?>" class="btn btn-sm btn-outline-secondary rounded-circle" title="View Profile">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($officers)): ?>
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="fas fa-user-shield fa-3x mb-3 opacity-25 d-block"></i>
                        <?php echo __('no_police_records'); ?> <a href="police_create.php" class="fw-bold"><?php echo __('register_first_officer'); ?> &rarr;</a>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.bg-warning-soft { background: rgba(234,179,8,0.1); }
</style>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
