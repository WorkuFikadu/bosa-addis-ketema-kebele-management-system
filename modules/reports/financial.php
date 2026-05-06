<?php
// modules/reports/financial.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    die("<div class='alert alert-danger m-5'>Access Denied: Administrative access required for financial reports.</div>");
}

$method = $_GET['method'] ?? 'all';
$date_from = $_GET['date_from'] ?? date('Y-m-01'); // Default to start of current month
$date_to = $_GET['date_to'] ?? date('Y-m-d');

// Financial Statistics by Bank
$where = "WHERE 1=1";
$params = [];

if ($method !== 'all') {
    $where .= " AND payment_method = ?";
    $params[] = $method;
}

if ($date_from) {
    $where .= " AND t.created_at >= ?";
    $params[] = $date_from . ' 00:00:00';
}

if ($date_to) {
    $where .= " AND t.created_at <= ?";
    $params[] = $date_to . ' 23:59:59';
}

$sql = "SELECT t.*, i.fname, i.mname, i.lname 
        FROM transactions t 
        LEFT JOIN individuals i ON t.resident_id = i.id 
        $where 
        ORDER BY t.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

$bankStats = $pdo->query("SELECT payment_method, COUNT(*) as count, SUM(amount) as total FROM transactions GROUP BY payment_method")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <h2><i class="fas fa-file-invoice-dollar me-2 text-success"></i> Detailed Financial Report</h2>
    <div>
        <button onclick="window.print()" class="btn btn-primary shadow-sm px-4 me-2">
            <i class="fas fa-print me-2"></i> Print Statement
        </button>
        <a href="index.php" class="btn btn-outline-secondary">Back to Summary</a>
    </div>
</div>

<!-- Bank Summary Cards -->
<div class="row mb-4 no-print">
    <?php foreach ($bankStats as $b): 
        $icon = 'fa-university'; $color = 'primary';
        if($b['payment_method'] == 'Telebirr') { $icon = 'fa-mobile-alt'; $color = 'primary'; }
        if($b['payment_method'] == 'CBE Birr') { $icon = 'fa-landmark'; $color = 'secondary'; }
        if($b['payment_method'] == 'Cash') { $icon = 'fa-money-bill-wave'; $color = 'success'; }
        if($b['payment_method'] == 'Sinqe Bank') { $icon = 'fa-building-columns'; $color = 'warning'; }
    ?>
    <div class="col-md-2 col-6">
        <div class="card border-0 shadow-sm text-center p-3">
            <i class="fas <?php echo $icon; ?> fa-2x text-<?php echo $color; ?> mb-2"></i>
            <h6 class="fw-bold mb-1 small"><?php echo $b['payment_method']; ?></h6>
            <h5 class="fw-bold text-dark mb-0"><?php echo number_format($b['total'], 0); ?> <small class="fs-6">ETB</small></h5>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="card p-4 shadow-sm mb-4 no-print">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label fw-bold">From Date</label>
            <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-bold">To Date</label>
            <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-bold">Payment Method</label>
            <select name="method" class="form-select">
                <option value="all" <?php echo $method === 'all' ? 'selected' : ''; ?>>All Methods</option>
                <option value="Telebirr" <?php echo $method === 'Telebirr' ? 'selected' : ''; ?>>Telebirr</option>
                <option value="CBE Birr" <?php echo $method === 'CBE Birr' ? 'selected' : ''; ?>>CBE Birr</option>
                <option value="Sinqe Bank" <?php echo $method === 'Sinqe Bank' ? 'selected' : ''; ?>>Sinqe Bank</option>
                <option value="Coop Bank" <?php echo $method === 'Coop Bank' ? 'selected' : ''; ?>>Coop Bank</option>
                <option value="Cash" <?php echo $method === 'Cash' ? 'selected' : ''; ?>>Cash</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-success w-100"><i class="fas fa-search me-2"></i> Filter Statement</button>
        </div>
    </form>
</div>

<!-- Printable Statement -->
<div class="report-header text-center mb-4 pb-3 border-bottom border-dark border-3 d-none d-print-block">
    <div class="d-flex justify-content-between align-items-center px-4">
        <img src="../../assets/img/oromia_flag.png" alt="Oromia" style="width: 100px; height: 60px; object-fit: cover; border: 2px solid #ccc;">
        <div>
            <h4 class="fw-bold mb-1">IFA BULA KEBELE ADMINISTRATION</h4>
            <h5 class="fw-bold mb-1 text-primary">Financial Transaction Statement</h5>
            <h6 class="text-muted fw-bold">Generated: <?php echo date('Y-m-d H:i'); ?></h6>
        </div>
        <img src="../../assets/img/ethiopia_flag.png" alt="Ethiopia" style="width: 100px; height: 60px; object-fit: cover; border: 2px solid #ccc;">
    </div>
    <div class="mt-4 text-start bg-light p-2 border">
        <p class="mb-0 small fw-bold">Period: <?php echo $date_from; ?> to <?php echo $date_to; ?></p>
        <p class="mb-0 small fw-bold">Filtered By: <?php echo strtoupper($method); ?></p>
    </div>
</div>

<div class="card border-0 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th class="px-3">Date</th>
                    <th>Resident Name</th>
                    <th>Service</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th class="text-end px-3">Amount (ETB)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">No transactions found for the selected period.</td>
                </tr>
                <?php else: 
                $grandTotal = 0;
                foreach ($transactions as $t): 
                    $grandTotal += $t['amount'];
                ?>
                <tr>
                    <td class="px-3 small text-muted"><?php echo date('M d, Y', strtotime($t['created_at'])); ?></td>
                    <td class="fw-bold"><?php echo "{$t['fname']} {$t['mname']} {$t['lname']}"; ?></td>
                    <td><span class="badge bg-light text-dark border"><?php echo strtoupper($t['service_type']); ?></span></td>
                    <td>
                        <?php 
                            $m = $t['payment_method'];
                            $cls = 'bg-secondary';
                            if($m == 'Telebirr') $cls = 'bg-primary';
                            if($m == 'Cash') $cls = 'bg-success';
                            if($m == 'CBE Birr') $cls = 'bg-info';
                            echo "<span class='badge $cls'>$m</span>";
                        ?>
                    </td>
                    <td class="font-monospace small"><?php echo $t['transaction_ref']; ?></td>
                    <td class="text-end px-3 fw-bold"><?php echo number_format($t['amount'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="table-light border-top border-dark border-3">
                    <td colspan="5" class="text-end fw-bold py-3 fs-5">TOTAL REVENUE (SELECTED):</td>
                    <td class="text-end px-3 fw-bold py-3 fs-4 text-primary"><?php echo number_format($grandTotal, 2); ?> ETB</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Official Signatures for Statement -->
<div class="mt-5 px-5 d-none d-print-block">
    <div class="row text-center mt-5 pt-5">
        <div class="col-4">
            <hr class="border-dark border-2 w-75 mx-auto">
            <h6 class="fw-bold">Cashier / Accountant</h6>
        </div>
        <div class="col-4">
            <div style="width: 130px; height: 130px; border: 4px double #000; border-radius: 50%; line-height:120px; margin: -20px auto 0; opacity: 0.2;">
                STAMP HERE
            </div>
        </div>
        <div class="col-4">
            <hr class="border-dark border-2 w-75 mx-auto">
            <h6 class="fw-bold">Administration Head</h6>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
