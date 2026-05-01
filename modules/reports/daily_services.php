<?php
// modules/reports/daily_services.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'operator') {
    die("<div class='alert alert-danger m-5'>Access Denied: You do not have permission to view reports.</div>");
}

$date = $_GET['date'] ?? date('Y-m-d');
$service = $_GET['service'] ?? 'all';

// Fetch Data Based on Service type
$services_data = [];

// Helper function to fetch ID Cards
if ($service === 'all' || $service === 'id_cards') {
    $stmt = $pdo->prepare("SELECT ic.id_num as ref_no, ic.issue_date, i.fname, i.mname, i.lname, 'ID Card' as type
                           FROM id_cards ic
                           JOIN individuals i ON ic.resident_id = i.id
                           WHERE ic.issue_date = ?");
    $stmt->execute([$date]);
    $services_data = array_merge($services_data, $stmt->fetchAll());
}

// Helper function to fetch Vital Certs
$vital_types = [
    'birth' => 'Birth Certificate',
    'death' => 'Death Certificate',
    'marriage' => 'Marriage Certificate',
    'divorce' => 'Divorce Certificate',
    'clearance' => 'Clearance Certificate'
];

foreach ($vital_types as $k => $label) {
    if ($service === 'all' || $service === $k) {
        $stmt = $pdo->prepare("SELECT vc.cert_number as ref_no, vc.issue_date, i.fname, i.mname, i.lname, ? as type
                               FROM vital_certificates vc
                               JOIN individuals i ON vc.resident_id = i.id
                               WHERE vc.cert_type = ? AND vc.issue_date = ?");
        $stmt->execute([$label, $k, $date]);
        $services_data = array_merge($services_data, $stmt->fetchAll());
    }
}

// Sort by name
usort($services_data, function($a, $b) {
    return strcmp($a['fname'], $b['fname']);
});
?>

<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <h2><i class="fas fa-calendar-day me-2 text-primary"></i> Daily Services Report</h2>
    <div>
        <button onclick="window.print()" class="btn btn-primary shadow-sm px-4 me-2">
            <i class="fas fa-print me-2"></i> Print Report
        </button>
        <a href="index.php" class="btn btn-outline-secondary">Back to Summary</a>
    </div>
</div>

<div class="card p-4 shadow-sm mb-4 no-print">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label fw-bold">Select Date</label>
            <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($date); ?>" required>
        </div>
        <div class="col-md-5">
            <label class="form-label fw-bold">Select Service Type</label>
            <select name="service" class="form-select">
                <option value="all" <?php echo $service === 'all' ? 'selected' : ''; ?>>All Services</option>
                <option value="id_cards" <?php echo $service === 'id_cards' ? 'selected' : ''; ?>>ID Cards Issued</option>
                <option value="birth" <?php echo $service === 'birth' ? 'selected' : ''; ?>>Birth Certificates</option>
                <option value="death" <?php echo $service === 'death' ? 'selected' : ''; ?>>Death Certificates</option>
                <option value="marriage" <?php echo $service === 'marriage' ? 'selected' : ''; ?>>Marriage Certificates</option>
                <option value="divorce" <?php echo $service === 'divorce' ? 'selected' : ''; ?>>Divorce Certificates</option>
                <option value="clearance" <?php echo $service === 'clearance' ? 'selected' : ''; ?>>Clearance Certificates</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-success w-100"><i class="fas fa-filter me-2"></i> Filter Data</button>
        </div>
    </form>
</div>

<!-- Professional Print Header -->
<div class="report-header text-center mb-4 pb-3 border-bottom border-dark border-3 d-none d-print-block">
    <div class="d-flex justify-content-between align-items-center px-4">
        <img src="../../assets/img/oromia_flag.png" alt="Oromia" style="width: 100px; height: 60px; object-fit: cover; border: 2px solid #ccc;">
        <div>
            <h4 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif; color: #1a1a2e;">IFA BULA KEBELE ADMINISTRATION</h4>
            <h5 class="fw-bold mb-1 text-primary">የኢፋ ቡላ ቀበሌ አስተዳደር | Bulchiinsa Ganda Ifa Bula</h5>
            <h6 class="text-muted fw-bold">Daily Services & Activities Log</h6>
        </div>
        <img src="../../assets/img/ethiopia_flag.png" alt="Ethiopia" style="width: 100px; height: 60px; object-fit: cover; border: 2px solid #ccc;">
    </div>
    <div class="mt-4">
        <h5 class="fw-bold fs-6">REPORT DATE: <?php echo date('l, F j, Y', strtotime($date)); ?></h5>
        <span class="badge bg-dark fw-normal">FILTER: <?php 
            $labels = ['all'=>'All Services', 'id_cards'=>'ID Cards', 'birth'=>'Birth Certs', 'death'=>'Death Certs', 'marriage'=>'Marriage Certs', 'divorce'=>'Divorce Certs', 'clearance'=>'Clearance Certs'];
            echo strtoupper($labels[$service]); 
        ?></span>
    </div>
</div>

<style>
    @media print {
        @page { margin: 15mm; }
        body { background: #fff; color: #000; }
        .no-print, .sidebar, .navbar { display: none !important; }
        .card { border: none !important; box-shadow: none !important; margin-bottom: 20px; }
        .bg-light { background: transparent !important; }
        .report-header { display: block !important; }
        main { padding: 0 !important; margin: 0 !important; width: 100% !important; }
        table { border-color: #000 !important; }
        th, td { border-color: #aaa !important; padding: 8px !important; }
    }
</style>

<div class="card p-0 shadow-sm border-0">
    <div class="card-header bg-dark text-white p-3 no-print">
        <h5 class="mb-0 text-uppercase fs-6">
            <i class="fas fa-list me-2"></i> Log Details for <?php echo date('M d, Y', strtotime($date)); ?>
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light border-bottom border-dark border-2">
                    <tr>
                        <th class="py-3 px-4">#</th>
                        <th class="py-3 px-4">Resident Name (Beneficiary)</th>
                        <th class="py-3 px-4">Service Provided</th>
                        <th class="py-3 px-4">Tracking / Document No.</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php if (empty($services_data)): ?>
                        <tr><td colspan="4" class="text-center py-5 text-muted h5">No services recorded for this criteria on <?php echo date('M d, Y', strtotime($date)); ?>.</td></tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($services_data as $row): ?>
                        <tr>
                            <td class="fw-bold text-muted px-4 py-3"><?php echo $no++; ?></td>
                            <td class="px-4 py-3 fw-bold"><?php echo "{$row['fname']} {$row['mname']} {$row['lname']}"; ?></td>
                            <td class="px-4 py-3">
                                <?php if($row['type'] == 'ID Card') echo '<span class="badge bg-primary px-2 py-1">ID Card</span>'; ?>
                                <?php if($row['type'] == 'Birth Certificate') echo '<span class="badge bg-info px-2 py-1">Birth Cert</span>'; ?>
                                <?php if($row['type'] == 'Death Certificate') echo '<span class="badge bg-dark px-2 py-1">Death Cert</span>'; ?>
                                <?php if($row['type'] == 'Marriage Certificate') echo '<span class="badge bg-danger px-2 py-1">Marriage Cert</span>'; ?>
                                <?php if($row['type'] == 'Divorce Certificate') echo '<span class="badge bg-secondary px-2 py-1">Divorce Cert</span>'; ?>
                                <?php if($row['type'] == 'Clearance Certificate') echo '<span class="badge bg-success px-2 py-1">Clearance Cert</span>'; ?>
                            </td>
                            <td class="px-4 py-3 font-monospace text-muted"><?php echo $row['ref_no']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <tr class="table-light border-top border-dark border-2">
                            <td colspan="3" class="text-end fw-bold py-3">TOTAL TRANSACTIONS LOGGED:</td>
                            <td class="fw-bold fs-5 text-primary py-3"><?php echo count($services_data); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Official Signatures -->
<div class="mt-5 px-5 d-none d-print-block">
    <div class="row text-center mt-5 pt-4">
        <div class="col-6">
            <hr class="border-dark border-2 w-50 mx-auto">
            <h6 class="fw-bold">Prepared By / Officer</h6>
            <p class="small text-muted">Daily Shift Signature</p>
        </div>
        <div class="col-6">
            <hr class="border-dark border-2 w-50 mx-auto">
            <h6 class="fw-bold">Checked & Approved By</h6>
            <p class="small text-muted">Head of Operations / Administration</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
