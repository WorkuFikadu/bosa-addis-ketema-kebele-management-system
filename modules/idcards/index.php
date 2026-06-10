<?php
// modules/idcards/index.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

// Handle Status Updates (Lost / Renew)
if (isset($_POST['update_status'])) {
    $card_id = $_POST['card_id'];
    $new_status = $_POST['new_status'];
    $stmt = $pdo->prepare("UPDATE id_cards SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $card_id]);
    echo "<div class='alert alert-success m-3'>ID Status updated to $new_status successfully.</div>";
}

$search = $_GET['q'] ?? '';
$where_clause = "";
$params = [];

if ($search) {
    $where_clause = " WHERE i.fname LIKE ? OR i.lname LIKE ? OR ic.id_num LIKE ? ";
    $params = ["%$search%", "%$search%", "%$search%"];
}

$query = "SELECT ic.*, i.fname, i.lname, i.phot, ag.age, i.occ, i.nat,
          (SELECT COUNT(*) FROM transactions t 
           WHERE t.resident_id = ic.resident_id 
           AND t.service_type = 'id_card' 
           AND t.status = 'Completed' 
           AND t.created_at >= ic.issue_date) as payment_done,
          CASE 
            WHEN ic.status = 'Active' AND ic.expiry_date < CURDATE() THEN 'Expired'
            ELSE ic.status 
          END as current_status
          FROM id_cards ic
          JOIN individuals i ON ic.resident_id = i.id
          LEFT JOIN ages ag ON i.id = ag.id
          $where_clause
          ORDER BY ic.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$id_cards = $stmt->fetchAll();
require_once __DIR__ . '/../../includes/payment_handler.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0" style="font-size: 2.8rem;"><?php echo __('id_card_mgmt'); ?></h2>
        <p class="text-muted" style="font-size: 1.1rem;"><?php echo __('id_registry_desc'); ?></p>
    </div>
    <?php if ($_SESSION['role'] !== 'security'): ?>
    <div>
        <button class="btn btn-warning shadow-sm rounded-pill px-4 me-2" data-bs-toggle="modal" data-bs-target="#lostIdModal">
            <i class="fas fa-search me-2"></i><?php echo __('report_lost'); ?>
        </button>
        <a href="generate.php" class="btn btn-primary shadow-sm rounded-pill px-4">
            <i class="fas fa-plus me-2"></i><?php echo __('issue_new_id'); ?>
        </a>
    </div>

    <!-- Lost ID Search Modal -->
    <div class="modal fade" id="lostIdModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-dark border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i><?php echo __('report_lost'); ?>/Replacement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body p-4">
                        <p class="text-muted small mb-4"><?php echo __('lost_id_modal_desc'); ?></p>
                        <div class="mb-3">
                            <label class="form-label fw-bold"><?php echo __('select_resident'); ?></label>
                            <select name="card_id" class="form-select border-warning" required>
                                <option value=""><?php echo __('choose_active_id'); ?></option>
                                <?php 
                                $active_ids = $pdo->query("SELECT ic.id, i.fname, i.lname, ic.id_num FROM id_cards ic JOIN individuals i ON ic.resident_id = i.id WHERE ic.status = 'Active' ORDER BY i.fname")->fetchAll();
                                foreach($active_ids as $aid): ?>
                                    <option value="<?php echo $aid['id']; ?>"><?php echo "{$aid['fname']} {$aid['lname']} ({$aid['id_num']})"; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <input type="hidden" name="new_status" value="Lost">
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_status" class="btn btn-warning px-4 fw-bold"><?php echo __('confirm_id_lost'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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

<div class="card p-4 border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="text-muted small text-uppercase" style="background: #f8f9fa;">
                <tr>
                    <th class="border-0 px-3"><?php echo __('id_number_label'); ?></th>
                    <th class="border-0"><?php echo __('full_name'); ?></th>
                    <th class="border-0"><?php echo __('billing_status'); ?></th>
                    <th class="border-0 text-end px-3"><?php echo __('actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($id_cards as $card): 
                    $is_expired = (strtotime($card['expiry_date']) < time());
                    $status_cls = 'bg-success';
                    if($card['current_status'] == 'Expired') $status_cls = 'bg-danger';
                    if($card['current_status'] == 'Lost') $status_cls = 'bg-warning text-dark';
                ?>
                <tr>
                    <td class="px-3">
                        <div class="fw-bold text-primary mb-1" style="font-size: 1.2rem;"><?php echo $card['id_num']; ?></div>
                        <small class="text-muted">Expires: <?php echo $card['expiry_date']; ?></small>
                    </td>
                    <td>
                        <div class="fw-bold text-dark" style="font-size: 1.15rem;"><?php echo "{$card['fname']} {$card['lname']}"; ?></div>
                        <div class="text-muted small">Origin: <?php echo $card['nat']; ?></div>
                    </td>
                    <td>
                        <div class="mb-1">
                            <?php if ($card['payment_done']): ?>
                                <span class="badge bg-success-line border border-success text-success px-2 rounded-pill" style="font-size: 0.6rem;"><?php echo __('paid'); ?></span>
                            <?php else: ?>
                                <span class="badge bg-warning-line border border-warning text-warning px-2 rounded-pill" style="font-size: 0.6rem;"><?php echo __('unpaid'); ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="badge <?php echo $status_cls; ?> px-2 py-1" style="font-size: 0.65rem;">
                            <i class="fas <?php echo $card['current_status'] == 'Active' ? 'fa-check-circle' : ($card['current_status'] == 'Lost' ? 'fa-search' : 'fa-clock'); ?> me-1"></i>
                            <?php echo strtoupper($card['current_status']); ?>
                        </span>
                    </td>
                    <td class="text-end px-3">
                        <?php if ($_SESSION['role'] !== 'security'): ?>
                            <?php if ($card['current_status'] == 'Active' && $card['payment_done']): ?>
                                 <a href="print.php?id=<?php echo $card['id']; ?>" class="btn btn-sm btn-success shadow-sm px-3" target="_blank">
                                    <i class="fas fa-print me-1"></i> <?php echo __('print'); ?>
                                </a>
                                <form method="POST" class="d-inline ms-1">
                                    <input type="hidden" name="card_id" value="<?php echo $card['id']; ?>">
                                    <input type="hidden" name="new_status" value="Lost">
                                     <button type="submit" name="update_status" class="btn btn-sm btn-outline-warning" title="<?php echo __('report_lost'); ?>" onclick="return confirm('<?php echo addslashes(__('confirm_lost_id')); ?>')">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </form>
                            <?php elseif ($card['current_status'] == 'Lost' || $card['current_status'] == 'Expired'): ?>
                                <a href="generate.php?reapply=<?php echo $card['resident_id']; ?>" class="btn btn-sm btn-primary shadow-sm px-3">
                                    <i class="fas fa-redo me-1"></i> <?php echo __('re_apply'); ?>
                                </a>
                            <?php elseif (!$card['payment_done']): ?>
                                <button class="btn btn-sm btn-dark shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#payModal<?php echo $card['id']; ?>">
                                    <i class="fas fa-wallet me-1"></i> <?php echo __('pay_to_activate'); ?>
                                </button>
                                
                                <!-- Payment Modal -->
                                <div class="modal fade" id="payModal<?php echo $card['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg text-start">
                                        <div class="modal-content border-0 shadow-lg">
                                            <form method="POST" action="../vital/process_list_payment.php" enctype="multipart/form-data">
                                                <div class="modal-body p-0">
                                                    <?php displayPaymentGateway('id_card', $card['resident_id'], "{$card['fname']} {$card['lname']}"); ?>
                                                    <input type="hidden" name="redirect_to" value="../../modules/idcards/index.php">
                                                    <div class="p-3 text-end bg-light border-top">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary px-4"><?php echo __('confirm_payment'); ?></button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <a href="delete.php?id=<?php echo $card['id']; ?>" class="btn btn-sm btn-outline-danger ms-1" onclick="return confirm('<?php echo __('revoke_id_confirm'); ?>')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                             <span class="text-muted small italic"><?php echo __('security_view_only'); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($id_cards)): ?>
                <tr><td colspan="4" class="text-center py-4"><?php echo __('no_records'); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>


<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
