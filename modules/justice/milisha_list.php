<?php
// modules/justice/milisha_list.php — Milisha Registry
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/payment_handler.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../auth/login.php'); exit; }

// Handle status update
if (isset($_POST['update_status'])) {
    $pdo->prepare("UPDATE milisha_records SET status = ? WHERE id = ?")->execute([$_POST['new_status'], $_POST['member_id']]);
}
// Handle Lost ID
if (isset($_POST['update_id_status'])) {
    $pdo->prepare("UPDATE milisha_id_cards SET status = ? WHERE id = ?")->execute([$_POST['new_id_status'], $_POST['card_id']]);
}

$search = $_GET['q'] ?? '';
$params = [];
$where = '';
if ($search) {
    $where = " AND (i.fname LIKE ? OR i.lname LIKE ? OR mr.zone_assigned LIKE ? OR mic.id_num LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%", "%$search%"];
}

$members = $pdo->prepare("
    SELECT mr.*, i.fname, i.mname, i.lname, i.phot, i.id AS individual_id,
           mic.id AS card_id, mic.id_num, mic.issue_date, mic.expiry_date, mic.status AS card_status,
           (SELECT COUNT(*) FROM transactions t WHERE t.resident_id = mr.individual_id AND t.service_type = 'milisha_id' AND t.status = 'Completed') as payment_done
    FROM milisha_records mr
    JOIN individuals i ON mr.individual_id = i.id
    LEFT JOIN milisha_id_cards mic ON mr.id = mic.milisha_record_id
    WHERE 1=1 $where
    ORDER BY mr.created_at DESC
");
$members->execute($params);
$members = $members->fetchAll();

$total = count($members);
$active = count(array_filter($members, fn($m) => $m['status'] === 'Active'));
$lost_ids = count(array_filter($members, fn($m) => ($m['card_status'] ?? '') === 'Lost'));
?>

<div class="mb-3">
    <a href="index.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold shadow-sm">
        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
    </a>
</div>
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="fw-black mb-1" style="font-size:2.4rem;">Milisha Registry</h2>
        <p class="text-muted mb-0">Bosa Addis Kebele — Community Defense & Watch Personnel</p>
    </div>
    <a href="milisha_create.php" class="btn btn-warning shadow-sm rounded-pill px-4 fw-bold">
        <i class="fas fa-user-plus me-2"></i>Register New Milisha
    </a>
</div>

<!-- Stats Row -->
<div class="row g-4 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center" style="border-left:5px solid #6366f1!important;">
            <div class="fw-black fs-2 text-primary"><?php echo $total; ?></div>
            <div class="text-muted small fw-bold text-uppercase">Total Personnel</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center" style="border-left:5px solid #10b981!important;">
            <div class="fw-black fs-2 text-success"><?php echo $active; ?></div>
            <div class="text-muted small fw-bold text-uppercase">Active Members</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center" style="border-left:5px solid #ef4444!important;">
            <div class="fw-black fs-2 text-danger"><?php echo $lost_ids; ?></div>
            <div class="text-muted small fw-bold text-uppercase">Lost IDs</div>
        </div>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
        <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($_GET['success']); ?>
    </div>
<?php endif; ?>

<!-- Search -->
<div class="row mb-4">
    <div class="col-lg-5">
        <form method="GET" class="d-flex align-items-center gap-2">
            <div class="input-group shadow-sm rounded-pill overflow-hidden border">
                <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-search text-muted"></i></span>
                <input type="text" name="q" class="form-control border-0 py-2" placeholder="Search by name, zone, or ID..." value="<?php echo htmlspecialchars($search); ?>">
                <?php if ($search): ?><a href="milisha_list.php" class="input-group-text bg-white border-0 text-danger"><i class="fas fa-times"></i></a><?php endif; ?>
                <button type="submit" class="btn btn-warning px-4 fw-bold rounded-0 rounded-end">Search</button>
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
                    <th class="border-0 px-3">ID / Weapon</th>
                    <th class="border-0">Full Name</th>
                    <th class="border-0">Role & Zone</th>
                    <th class="border-0">Billing & Status</th>
                    <th class="border-0 text-end px-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $m):
                    $card_status = $m['card_status'] ?? null;
                    $has_card = !empty($m['id_num']);
                    $status_cls = $card_status === 'Active' ? 'bg-success' : ($card_status === 'Lost' ? 'bg-warning text-dark' : 'bg-secondary');
                    $member_status_cls = $m['status'] === 'Active' ? 'bg-success' : 'bg-danger';
                ?>
                <tr>
                    <td class="px-3">
                        <?php if ($has_card): ?>
                            <div class="fw-bold text-warning mb-1" style="font-size:1.1rem;"><?php echo htmlspecialchars($m['id_num']); ?></div>
                            <small class="text-muted d-block">Expires: <?php echo $m['expiry_date']; ?></small>
                        <?php else: ?>
                            <span class="badge bg-light text-muted border rounded-pill mb-1">No ID Issued</span>
                        <?php endif; ?>
                        <small class="text-muted">Serial: <strong><?php echo htmlspecialchars($m['weapon_serial'] ?: 'N/A'); ?></strong></small>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-warning-soft text-warning rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width:40px;height:40px;flex-shrink:0;">
                                <?php if (!empty($m['phot']) && $m['phot'] !== 'default_profile.png'): ?>
                                    <img src="../../uploads/residents/<?php echo $m['phot']; ?>" style="width:40px;height:40px;object-fit:cover;border-radius:50%;">
                                <?php else: ?>
                                    <i class="fas fa-person-military-pointing"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars("{$m['fname']} {$m['mname']} {$m['lname']}"); ?></div>
                                <small class="text-muted">Joined: <?php echo date('M d, Y', strtotime($m['joined_date'])); ?></small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="fw-bold small text-dark"><?php echo htmlspecialchars($m['role']); ?></div>
                        <small class="text-muted"><i class="fas fa-location-dot me-1"></i><?php echo htmlspecialchars($m['zone_assigned']); ?></small>
                    </td>
                    <td>
                        <div class="mb-1">
                            <span class="badge <?php echo $member_status_cls; ?> rounded-pill px-2 mb-1" style="font-size:0.6rem;"><?php echo strtoupper($m['status']); ?></span>
                            <?php if ($has_card): ?>
                                <?php if ($m['payment_done']): ?>
                                    <span class="badge border border-success text-success rounded-pill px-2" style="font-size:0.6rem;">Paid</span>
                                <?php else: ?>
                                    <span class="badge border border-warning text-warning rounded-pill px-2" style="font-size:0.6rem;">Unpaid</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <?php if ($has_card): ?>
                            <span class="badge <?php echo $status_cls; ?> px-2 py-1" style="font-size:0.65rem;">
                                <i class="fas <?php echo $card_status === 'Active' ? 'fa-check-circle' : 'fa-search'; ?> me-1"></i>
                                <?php echo strtoupper($card_status); ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end px-3">
                        <div class="d-flex gap-1 justify-content-end">
                            <?php if ($has_card && $card_status === 'Active' && $m['payment_done']): ?>
                                <a href="milisha_print.php?id=<?php echo $m['card_id']; ?>" target="_blank" class="btn btn-sm btn-success shadow-sm px-3 rounded-pill fw-bold">
                                    <i class="fas fa-print me-1"></i>Print ID
                                </a>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="card_id" value="<?php echo $m['card_id']; ?>">
                                    <input type="hidden" name="new_id_status" value="Lost">
                                    <button type="submit" name="update_id_status" class="btn btn-sm btn-outline-warning rounded-circle" title="Report Lost" onclick="return confirm('Mark this ID as lost?')">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </form>
                            <?php elseif ($has_card && ($card_status === 'Lost' || $card_status === 'Expired')): ?>
                                <a href="milisha_idcard.php?reapply=<?php echo $m['id']; ?>" class="btn btn-sm btn-primary shadow-sm px-3 rounded-pill fw-bold">
                                    <i class="fas fa-redo me-1"></i>Re-Issue
                                </a>
                            <?php elseif ($has_card && !$m['payment_done']): ?>
                                <button class="btn btn-sm btn-dark shadow-sm px-3 rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#payModal<?php echo $m['card_id']; ?>">
                                    <i class="fas fa-wallet me-1"></i>Pay to Activate
                                </button>
                                <!-- Payment Modal -->
                                <div class="modal fade" id="payModal<?php echo $m['card_id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg text-start">
                                        <div class="modal-content border-0 shadow-lg">
                                            <form method="POST" action="../../modules/vital/process_list_payment.php" enctype="multipart/form-data">
                                                <div class="modal-body p-0">
                                                    <?php displayPaymentGateway('milisha_id', $m['individual_id'], "{$m['fname']} {$m['lname']}"); ?>
                                                    <input type="hidden" name="redirect_to" value="../../modules/justice/milisha_list.php">
                                                    <div class="p-3 text-end bg-light border-top">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary px-4 fw-bold">Confirm Payment</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php elseif ($m['status'] === 'Active'): ?>
                                <a href="milisha_idcard.php?member=<?php echo $m['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
                                    <i class="fas fa-id-card me-1"></i>Issue ID
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($members)): ?>
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fas fa-person-military-pointing fa-3x mb-3 opacity-25 d-block"></i>
                        No Milisha members registered yet. <a href="milisha_create.php" class="fw-bold">Register First Member &rarr;</a>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
