<?php
// modules/vital/index.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

// Fetch certificates with payment status
$query = "SELECT vc.*, i.fname, i.lname, i.status as ind_status, 
          (SELECT COUNT(*) FROM transactions t 
           WHERE t.resident_id = vc.resident_id 
           AND t.service_type LIKE CONCAT(vc.cert_type, '%')
           AND t.status = 'Completed') as payment_done
          FROM vital_certificates vc 
          JOIN individuals i ON vc.resident_id = i.id 
          ORDER BY vc.issue_date DESC";
$certs = $pdo->query($query)->fetchAll();
require_once __DIR__ . '/../../includes/payment_handler.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-file-signature me-2 text-primary"></i><?php echo __('vital_records'); ?></h2>
    <div class="d-flex gap-2 flex-wrap">
        <a href="issue_birth.php" class="btn btn-primary shadow-sm"><i class="fas fa-baby me-2"></i><?php echo __('birth_cert'); ?></a>
        <a href="issue_death.php" class="btn btn-danger shadow-sm"><i class="fas fa-skull me-2"></i><?php echo __('death_cert'); ?></a>
        <a href="issue_clearance.php" class="btn btn-success shadow-sm"><i class="fas fa-file-check me-2"></i><?php echo __('clearance_cert'); ?></a>
        <a href="issue_marriage.php" class="btn btn-warning shadow-sm"><i class="fas fa-heart me-2"></i>Marriage Cert</a>
        <a href="issue_divorce.php" class="btn btn-secondary shadow-sm"><i class="fas fa-heart-broken me-2"></i>Divorce Cert</a>
    </div>
</div>

<div class="card p-4 border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="text-muted small text-uppercase" style="background: #f8f9fa;">
                <tr>
                    <th class="border-0 px-3"><?php echo __('cert_no'); ?></th>
                    <th class="border-0"><?php echo __('resident'); ?></th>
                    <th class="border-0"><?php echo __('type'); ?></th>
                    <th class="border-0">Payment</th>
                    <th class="border-0 text-end px-3"><?php echo __('action'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($certs)): ?>
                    <tr><td colspan="5" class="text-center py-5 text-muted"><?php echo __('no_records'); ?></td></tr>
                <?php else: ?>
                    <?php foreach ($certs as $c): ?>
                        <tr class="<?php echo $c['payment_done'] ? '' : 'table-light'; ?>">
                            <td class="fw-bold text-primary px-3"><?php echo $c['cert_number']; ?></td>
                            <td>
                                <div class="fw-bold"><?php echo "{$c['fname']} {$c['lname']}"; ?></div>
                                <small class="text-muted">ID: #<?php echo $c['resident_id']; ?></small>
                            </td>
                            <td>
                                <?php
                                    $badge_map = [
                                        'birth' => ['bg-info', __('birth_cert')],
                                        'death' => ['bg-danger', __('death_cert')],
                                        'clearance' => ['bg-success', __('clearance_cert')],
                                        'marriage' => ['bg-warning text-dark', 'Marriage'],
                                        'divorce' => ['bg-secondary', 'Divorce'],
                                    ];
                                    $bt = $badge_map[$c['cert_type']] ?? ['bg-secondary', $c['cert_type']];
                                ?>
                                <span class="badge <?php echo $bt[0]; ?>"><?php echo $bt[1]; ?></span>
                            </td>
                            <td>
                                <?php if ($c['payment_done']): ?>
                                    <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-3 rounded-pill">
                                        <i class="fas fa-check-circle me-1"></i> Paid
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning border-opacity-25 px-3 rounded-pill">
                                        <i class="fas fa-clock me-1"></i> Pending
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end px-3">
                                <?php if ($c['payment_done']): ?>
                                    <a href="print.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-primary px-3 shadow-sm" target="_blank">
                                        <i class="fas fa-print me-1"></i> <?php echo __('print'); ?>
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-dark px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#payModal<?php echo $c['id']; ?>">
                                        <i class="fas fa-wallet me-1"></i> Pay Fee to Print
                                    </button>
                                    
                                    <!-- Payment Modal -->
                                    <div class="modal fade" id="payModal<?php echo $c['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg text-start">
                                            <div class="modal-content border-0 shadow-lg">
                                                <form method="POST" action="process_list_payment.php" enctype="multipart/form-data">
                                                    <div class="modal-body p-0">
                                                        <?php 
                                                        // Temporary override to show gateway in list
                                                        displayPaymentGateway($c['cert_type'].'_cert', $c['resident_id'], "{$c['fname']} {$c['lname']}"); 
                                                        ?>
                                                        <input type="hidden" name="cert_id" value="<?php echo $c['id']; ?>">
                                                        <input type="hidden" name="redirect_to" value="index.php">
                                                        <div class="p-3 text-end bg-light border-top">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary px-4">Verify Payment</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <a href="delete.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-danger ms-1" onclick="return confirm('Delete this record?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
