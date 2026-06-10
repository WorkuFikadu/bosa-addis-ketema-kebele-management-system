<?php
// modules/payments/index.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

// Handle Approval/Rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['txn_id']) && isset($_POST['action'])) {
    $txn_id = $_POST['txn_id'];
    $action = $_POST['action'];
    $status = ($action === 'approve') ? 'Completed' : 'Rejected';
    
    $stmt = $pdo->prepare("UPDATE transactions SET status = ? WHERE id = ?");
    $stmt->execute([$status, $txn_id]);
    
    log_activity($pdo, 'UPDATED', 'payments', $txn_id, "Transaction #$txn_id was marked as $status");
    
    $success_msg = "Transaction #$txn_id has been " . strtolower($status) . ".";
}

// Stats (Only completed)
$total_revenue = $pdo->query("SELECT SUM(amount) FROM transactions WHERE status = 'Completed'")->fetchColumn() ?: 0;
$telebirr_revenue = $pdo->query("SELECT SUM(amount) FROM transactions WHERE payment_method = 'Telebirr' AND status = 'Completed'")->fetchColumn() ?: 0;
$cbe_revenue = $pdo->query("SELECT SUM(amount) FROM transactions WHERE payment_method = 'CBE Birr' AND status = 'Completed'")->fetchColumn() ?: 0;
$sinqe_revenue = $pdo->query("SELECT SUM(amount) FROM transactions WHERE payment_method = 'Sinqe Bank' AND status = 'Completed'")->fetchColumn() ?: 0;
$coop_revenue = $pdo->query("SELECT SUM(amount) FROM transactions WHERE payment_method = 'Coop Bank' AND status = 'Completed'")->fetchColumn() ?: 0;
$cash_revenue = $pdo->query("SELECT SUM(amount) FROM transactions WHERE payment_method = 'Cash' AND status = 'Completed'")->fetchColumn() ?: 0;

// Latest Transactions
$transactions = $pdo->query("SELECT t.*, i.fname, i.lname 
    FROM transactions t 
    JOIN individuals i ON t.resident_id = i.id 
    ORDER BY t.created_at DESC LIMIT 50")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0">Kebele Revenue Terminal</h2>
        <p class="text-muted small">Multi-Bank Digital Gateway Monitoring (Sinqe, COOP, Telebirr, CBE)</p>
    </div>
    <div class="btn-group">
        <button class="btn btn-outline-primary shadow-sm"><i class="fas fa-file-pdf me-2"></i>Tax Report</button>
        <button class="btn btn-primary shadow-sm" onclick="location.reload()"><i class="fas fa-sync me-2"></i>Sync Data</button>
    </div>
</div>

<?php if (isset($success_msg)): ?>
    <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> <?php echo $success_msg; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-3 mb-5">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-primary text-white p-4 h-100">
            <small class="text-white-50 text-uppercase fw-bold">Grand Total Revenue</small>
            <h2 class="fw-bold mb-0"><?php echo number_format($total_revenue, 2); ?> <small class="fs-6">ETB</small></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-4 h-100" style="border-left: 5px solid #00adef !important;">
            <small class="text-muted text-uppercase fw-bold">Telebirr Collection</small>
            <h3 class="fw-bold mb-0"><?php echo number_format($telebirr_revenue, 2); ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-4 h-100" style="border-left: 5px solid #6c2a8c !important;">
            <small class="text-muted text-uppercase fw-bold">CBE Birr Collection</small>
            <h3 class="fw-bold mb-0"><?php echo number_format($cbe_revenue, 2); ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-4 h-100" style="border-left: 5px solid #ffcc00 !important;">
            <small class="text-muted text-uppercase fw-bold">Sinqe Bank</small>
            <h3 class="fw-bold mb-0"><?php echo number_format($sinqe_revenue, 2); ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-4 h-100" style="border-left: 5px solid #2e7d32 !important;">
            <small class="text-muted text-uppercase fw-bold">COOP Bank</small>
            <h3 class="fw-bold mb-0"><?php echo number_format($coop_revenue, 2); ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-4 h-100 text-success" style="border-left: 5px solid #28a745 !important;">
            <small class="text-muted text-uppercase fw-bold">Physical Cash</small>
            <h3 class="fw-bold mb-0"><?php echo number_format($cash_revenue, 2); ?></h3>
        </div>
    </div>
</div>


<div class="card border-0 shadow-sm p-4">
    <h5 class="fw-bold mb-4">Transaction History & Proof Verification</h5>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Date/Time</th>
                    <th>Resident</th>
                    <th>Service</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Reference / Proof</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $t): ?>
                <tr>
                    <td>
                        <div class="fw-semibold small"><?php echo date('M d, Y', strtotime($t['created_at'])); ?></div>
                        <div class="text-muted" style="font-size: 0.7rem;"><?php echo date('h:i A', strtotime($t['created_at'])); ?></div>
                    </td>
                    <td>
                        <div class="fw-bold"><?php echo htmlspecialchars($t['fname'].' '.$t['lname']); ?></div>
                        <div class="small text-muted">ID: #<?php echo $t['resident_id']; ?></div>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.65rem;"><?php echo str_replace('_', ' ', $t['service_type']); ?></span>
                    </td>
                    <td><div class="fw-bold text-success font-monospace"><?php echo number_format($t['amount'], 2); ?> ETB</div></td>
                    <td>
                        <?php if($t['payment_method'] == 'Telebirr'): ?>
                            <span class="text-primary fw-bold" style="font-size: 0.8rem;"><i class="fas fa-mobile-screen me-1"></i>Telebirr</span>
                        <?php elseif($t['payment_method'] == 'CBE Birr'): ?>
                            <span class="text-info fw-bold" style="font-size: 0.8rem;"><i class="fas fa-university me-1"></i>CBE Birr</span>
                        <?php elseif($t['payment_method'] == 'Sinqe Bank' || $t['payment_method'] == 'Coop Bank'): ?>
                            <span class="text-warning fw-bold" style="font-size: 0.8rem;"><i class="fas fa-building-columns me-1"></i><?php echo $t['payment_method']; ?></span>
                        <?php else: ?>
                            <span class="text-success fw-bold" style="font-size: 0.8rem;"><i class="fas fa-money-bill me-1"></i>Cash</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <code class="small d-block mb-1"><?php echo $t['transaction_ref']; ?></code>
                        <?php if ($t['payment_proof']): ?>
                            <button class="btn btn-xs btn-outline-danger py-0 px-2" style="font-size: 0.65rem;" data-bs-toggle="modal" data-bs-target="#proofModal<?php echo $t['id']; ?>">
                                <i class="fas fa-image me-1"></i> View Screenshot
                            </button>
                            
                            <!-- Proof Modal -->
                            <div class="modal fade" id="proofModal<?php echo $t['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow-lg">
                                        <div class="modal-header">
                                            <h6 class="modal-title fw-bold">Payment Proof: <?php echo $t['transaction_ref']; ?></h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-0 text-center bg-dark">
                                            <img src="/Bosa Addis/uploads/payments/<?php echo $t['payment_proof']; ?>" class="img-fluid" alt="Payment Proof">
                                        </div>
                                        <div class="modal-footer bg-light py-2">
                                            <a href="/Bosa Addis/uploads/payments/<?php echo $t['payment_proof']; ?>" target="_blank" class="btn btn-sm btn-primary">Open in New Tab</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <span class="text-muted small italic">No screenshot</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($t['status'] === 'Completed'): ?>
                            <span class="badge bg-success rounded-pill px-3">Completed</span>
                        <?php elseif ($t['status'] === 'Pending'): ?>
                            <span class="badge bg-warning text-dark rounded-pill px-3">Pending Verification</span>
                        <?php else: ?>
                            <span class="badge bg-danger rounded-pill px-3">Rejected</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <?php if ($t['status'] === 'Pending'): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="txn_id" value="<?php echo $t['id']; ?>">
                                <div class="btn-group">
                                    <button type="submit" name="action" value="approve" class="btn btn-sm btn-success shadow-sm" onclick="return confirm('Approve this payment?')">
                                        <i class="fas fa-check me-1"></i> Approve
                                    </button>
                                    <button type="submit" name="action" value="reject" class="btn btn-sm btn-outline-danger" onclick="return confirm('Reject this payment?')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-sm btn-outline-secondary" onclick="alert('Printing Receipt ID: <?php echo $t['id']; ?>')"><i class="fas fa-print"></i></button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($transactions)): ?>
                <tr><td colspan="8" class="text-center py-5 text-muted">No payment transactions recorded yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
