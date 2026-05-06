<?php
// modules/reports/index.php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    die("<div class='alert alert-danger m-5'>Access Denied: You do not have permission to view administrative reports.</div>");
}

// 1. Demographics
$sexDist = $pdo->query("SELECT s, COUNT(*) as count FROM individuals GROUP BY s")->fetchAll();
$ageDist = $pdo->query("SELECT 
    CASE 
        WHEN age < 18 THEN 'Under 18 (Minors)'
        WHEN age BETWEEN 18 AND 35 THEN '18-35 (Youth)'
        WHEN age BETWEEN 36 AND 60 THEN '36-60 (Adults)'
        ELSE '60+ (Seniors)'
    END as age_group,
    COUNT(*) as count
    FROM ages GROUP BY age_group ORDER BY age_group")->fetchAll();
$statusStats = $pdo->query("SELECT status, COUNT(*) as count FROM individuals GROUP BY status")->fetchAll();

// 2. Housing & Properties
$houseStats = $pdo->query("SELECT COUNT(*) as total_houses, AVG(area) as avg_area FROM houses")->fetch();
$familyStats = $pdo->query("SELECT COUNT(*) as total_families, SUM(fam_no) as total_people FROM families")->fetch();

// 3. Vital Events (Certificates)
$vitalStats = $pdo->query("SELECT cert_type, COUNT(*) as count FROM vital_certificates GROUP BY cert_type")->fetchAll();

// 4. Identification Services
$idStats = $pdo->query("SELECT 
    COUNT(*) as total_ids, 
    SUM(CASE WHEN expiry_date >= CURDATE() THEN 1 ELSE 0 END) as active_ids,
    SUM(CASE WHEN expiry_date < CURDATE() THEN 1 ELSE 0 END) as expired_ids
    FROM id_cards")->fetch();

// 5. Financial Summary
$financialStats = $pdo->query("SELECT payment_method, COUNT(*) as count, SUM(amount) as total FROM transactions GROUP BY payment_method")->fetchAll();
$totalRevenue = array_sum(array_column($financialStats, 'total')) ?: 0;

// 6. Detailed Demographics
$eduDist = $pdo->query("SELECT level_edu, COUNT(*) as count FROM individuals GROUP BY level_edu ORDER BY count DESC")->fetchAll();
$relgDist = $pdo->query("SELECT relg, COUNT(*) as count FROM individuals GROUP BY relg ORDER BY count DESC")->fetchAll();

// 7. Detailed Housing
$houseTypeDist = $pdo->query("SELECT house_type, COUNT(*) as count FROM houses GROUP BY house_type")->fetchAll();
$waterStats = $pdo->query("SELECT has_water, COUNT(*) as count FROM houses GROUP BY has_water")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <h2><i class="fas fa-chart-line me-2 text-primary"></i> Comprehensive Kebele Reports</h2>
    <div>
        <a href="financial.php" class="btn btn-success shadow-sm px-4 me-2">
            <i class="fas fa-money-bill-wave me-2"></i> Financial Report
        </a>
        <a href="daily_services.php" class="btn btn-warning shadow-sm px-4 me-2">
            <i class="fas fa-list-check me-2"></i> Daily Services Log
        </a>
        <button onclick="window.print()" class="btn btn-primary shadow-sm px-4">
            <i class="fas fa-print me-2"></i> Print Official Report
        </button>
    </div>
</div>

<!-- Professional Header for Print -->
<div class="report-header text-center mb-5 pb-3 border-bottom border-dark border-3">
    <div class="d-flex justify-content-between align-items-center px-4">
        <img src="../../assets/img/oromia_flag.png" alt="Oromia" style="width: 100px; height: 60px; object-fit: cover; border: 2px solid #ccc;">
        <div>
            <h4 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif; color: #1a1a2e;">IFA BULA KEBELE ADMINISTRATION</h4>
            <h5 class="fw-bold mb-1 text-primary">የኢፋ ቡላ ቀበሌ አስተዳደር | Bulchiinsa Ganda Ifa Bula</h5>
            <h6 class="text-muted fw-bold">Jimma Zone, Oromia Regional State, Ethiopia</h6>
        </div>
        <img src="../../assets/img/ethiopia_flag.png" alt="Ethiopia" style="width: 100px; height: 60px; object-fit: cover; border: 2px solid #ccc;">
    </div>
    <div class="mt-4 bg-light p-2 border rounded shadow-sm d-inline-block">
        <h5 class="fw-bold mb-1"><i class="fas fa-file-invoice me-2 text-danger"></i> CONSOLIDATED SYSTEM REPORT</h5>
        <span class="badge bg-dark fw-normal fs-6">Generated on: <?php echo date('l, F j, Y \a\t H:i:s A'); ?></span>
    </div>
</div>

<style>
    .report-section { break-inside: avoid; page-break-inside: avoid; }
    @media print {
        @page { margin: 15mm; }
        body { background: #fff; color: #000; }
        .no-print, .sidebar, .navbar { display: none !important; }
        .card { border: none !important; box-shadow: none !important; margin-bottom: 20px; }
        .bg-light { background: transparent !important; }
        .report-header { display: block !important; }
        main { padding: 0 !important; margin: 0 !important; width: 100% !important; }
    }
</style>

    <!-- 1. Demographics & Resident Status -->
    <div class="col-md-12 report-section mb-4">
        <div class="card p-4 shadow-sm border-top border-primary border-4">
            <h4 class="mb-4 text-dark fw-bold"><i class="fas fa-users me-2 text-primary"></i> 1. Demographic & Population Report</h4>
            <div class="row">
                <div class="col-md-4">
                    <h6 class="fw-bold text-secondary text-uppercase border-bottom pb-2">Population by Sex</h6>
                    <table class="table table-sm table-striped">
                        <tbody>
                            <?php 
                            $totalSex = array_sum(array_column($sexDist, 'count')) ?: 1;
                            foreach ($sexDist as $row): 
                                $percent = round(($row['count'] / $totalSex) * 100, 1);
                            ?>
                            <tr>
                                <td><?php echo $row['s']; ?></td>
                                <td class="fw-bold text-end"><?php echo $row['count']; ?></td>
                                <td class="text-end text-muted"><?php echo $percent; ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <h6 class="fw-bold text-secondary text-uppercase border-bottom pb-2 mt-4">Resident Vital Status</h6>
                    <table class="table table-sm table-striped">
                        <tbody>
                            <?php foreach ($statusStats as $row): ?>
                            <tr>
                                <td><span class="badge bg-<?php echo $row['status'] == 'alive' ? 'success' : 'danger'; ?> text-uppercase"><?php echo $row['status']; ?></span></td>
                                <td class="fw-bold text-end"><?php echo $row['count']; ?> Residents</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="col-md-4">
                    <h6 class="fw-bold text-secondary text-uppercase border-bottom pb-2">Population by Age Group</h6>
                    <table class="table table-sm table-striped">
                        <tbody>
                            <?php 
                            $totalAge = array_sum(array_column($ageDist, 'count')) ?: 1;
                            foreach ($ageDist as $row): 
                                $percent = round(($row['count'] / $totalAge) * 100, 1);
                            ?>
                            <tr>
                                <td><?php echo $row['age_group']; ?></td>
                                <td class="fw-bold text-end"><?php echo $row['count']; ?></td>
                                <td class="text-end text-muted"><?php echo $percent; ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <h6 class="fw-bold text-secondary text-uppercase border-bottom pb-2 mt-4">Nationality Distribution</h6>
                    <table class="table table-sm table-striped">
                        <tbody>
                            <?php 
                            $natDist = $pdo->query("SELECT nat, COUNT(*) as count FROM individuals GROUP BY nat ORDER BY count DESC LIMIT 3")->fetchAll();
                            foreach ($natDist as $row): ?>
                            <tr>
                                <td><?php echo $row['nat']; ?></td>
                                <td class="fw-bold text-end"><?php echo $row['count']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="col-md-4">
                    <h6 class="fw-bold text-secondary text-uppercase border-bottom pb-2">Education Levels</h6>
                    <table class="table table-sm table-striped">
                        <tbody>
                            <?php foreach (array_slice($eduDist, 0, 4) as $row): ?>
                            <tr>
                                <td class="small"><?php echo $row['level_edu']; ?></td>
                                <td class="fw-bold text-end"><?php echo $row['count']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <h6 class="fw-bold text-secondary text-uppercase border-bottom pb-2 mt-4">Religious Distribution</h6>
                    <table class="table table-sm table-striped">
                        <tbody>
                            <?php foreach (array_slice($relgDist, 0, 4) as $row): ?>
                            <tr>
                                <td class="small"><?php echo $row['relg']; ?></td>
                                <td class="fw-bold text-end"><?php echo $row['count']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Vital Events (Certificates) -->
    <div class="col-md-8 report-section">
        <div class="card p-4 shadow-sm h-100 border-top border-warning border-4">
            <h4 class="mb-4 text-dark fw-bold"><i class="fas fa-file-signature me-2 text-warning"></i> 2. Vital Events Registry</h4>
            <p class="text-muted small">Total certificates processed by the administration office across all categories.</p>
            <div class="table-responsive">
                <table class="table table-hover border">
                    <thead class="table-light">
                        <tr>
                            <th>Certificate Type</th>
                            <th class="text-end">Total Issued</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalCerts = 0;
                        if(empty($vitalStats)): ?>
                            <tr><td colspan="2" class="text-muted text-center">No vital records found</td></tr>
                        <?php else:
                        foreach ($vitalStats as $v): $totalCerts += $v['count']; ?>
                        <tr>
                            <td class="fw-bold text-capitalize">
                                <?php if($v['cert_type'] == 'marriage' || $v['cert_type'] == 'divorce') echo '<i class="fas fa-heart text-danger me-2"></i>'; ?>
                                <?php if($v['cert_type'] == 'birth') echo '<i class="fas fa-baby text-info me-2"></i>'; ?>
                                <?php if($v['cert_type'] == 'death') echo '<i class="fas fa-skull text-dark me-2"></i>'; ?>
                                <?php if($v['cert_type'] == 'clearance') echo '<i class="fas fa-check-circle text-success me-2"></i>'; ?>
                                <?php echo $v['cert_type']; ?> Certificate
                            </td>
                            <td class="text-end fw-bold fs-5"><?php echo $v['count']; ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                    <tfoot class="table-group-divider">
                        <tr>
                            <td class="fw-bold text-end">GRAND TOTAL:</td>
                            <td class="text-end fw-bold fs-5 text-primary"><?php echo $totalCerts; ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- 3. Identification Services -->
    <div class="col-md-4 report-section">
        <div class="card p-4 shadow-sm h-100 border-top border-info border-4">
            <h4 class="mb-4 text-dark fw-bold"><i class="fas fa-id-card me-2 text-info"></i> 3. ID Cards Status</h4>
            <div class="d-flex flex-column gap-3">
                <div class="p-3 bg-light rounded text-center border">
                    <h2 class="text-primary fw-bold mb-0"><?php echo $idStats['total_ids'] ?? 0; ?></h2>
                    <span class="text-muted fw-bold text-uppercase" style="font-size:11px;">Total IDs Issued</span>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="p-3 bg-success bg-opacity-10 rounded text-center border border-success">
                            <h3 class="text-success fw-bold mb-0"><?php echo $idStats['active_ids'] ?? 0; ?></h3>
                            <span class="text-success fw-bold" style="font-size:10px;">ACTIVE</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-danger bg-opacity-10 rounded text-center border border-danger">
                            <h3 class="text-danger fw-bold mb-0"><?php echo $idStats['expired_ids'] ?? 0; ?></h3>
                            <span class="text-danger fw-bold" style="font-size:10px;">EXPIRED</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Financial Performance Summary -->
    <div class="col-md-12 report-section mt-4">
        <div class="card p-4 shadow-sm border-top border-success border-4 bg-light bg-opacity-50">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0 text-dark fw-bold"><i class="fas fa-coins me-2 text-success"></i> 4. Financial Performance Summary</h4>
                <h3 class="fw-bold text-primary mb-0">Total: <?php echo number_format($totalRevenue, 2); ?> ETB</h3>
            </div>
            <div class="row">
                <?php if(empty($financialStats)): ?>
                    <div class="col-12 text-center py-4 text-muted">No financial transactions recorded yet.</div>
                <?php else: ?>
                    <?php foreach ($financialStats as $f): 
                        $bank_color = '#6c757d';
                        if($f['payment_method'] == 'Telebirr') $bank_color = '#0d6efd';
                        if($f['payment_method'] == 'CBE Birr') $bank_color = '#6c2a8c';
                        if($f['payment_method'] == 'Sinqe Bank') $bank_color = '#ffcc00';
                        if($f['payment_method'] == 'Coop Bank') $bank_color = '#2e7d32';
                        if($f['payment_method'] == 'Cash') $bank_color = '#198754';
                    ?>
                    <div class="col-md-2 col-6 mb-3">
                        <div class="card border-0 shadow-sm text-center p-3 h-100" style="border-bottom: 5px solid <?php echo $bank_color; ?> !important;">
                            <span class="fw-bold text-muted small text-uppercase"><?php echo $f['payment_method']; ?></span>
                            <h4 class="fw-bold my-2" style="color: <?php echo $bank_color; ?>;"><?php echo number_format($f['total'], 0); ?></h4>
                            <span class="badge bg-light text-dark border"><?php echo $f['count']; ?> TXNs</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 5. Housing & Property Details -->
    <div class="col-md-12 report-section mt-4">
        <div class="card p-4 shadow-sm border-top border-secondary border-4">
            <h4 class="mb-4 text-dark fw-bold"><i class="fas fa-home me-2 text-secondary"></i> 5. Housing & Property Management</h4>
            <div class="row">
                <div class="col-md-3 border-end">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-dark mb-0"><?php echo $houseStats['total_houses']; ?></h2>
                        <p class="text-muted fw-bold text-uppercase small">Total Houses</p>
                    </div>
                    <h6 class="fw-bold text-muted small text-uppercase border-bottom pb-1">House Utilities</h6>
                    <table class="table table-sm table-borderless">
                        <?php foreach($waterStats as $w): ?>
                        <tr>
                            <td class="small">Water Access (<?php echo $w['has_water']; ?>)</td>
                            <td class="text-end fw-bold"><?php echo $w['count']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <div class="col-md-5 border-end">
                    <h6 class="fw-bold text-muted small text-uppercase border-bottom pb-1">House Types Breakdown</h6>
                    <div class="row g-2 mt-2">
                        <?php foreach($houseTypeDist as $ht): ?>
                        <div class="col-6">
                            <div class="p-2 border rounded bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-truncate"><?php echo $ht['house_type']; ?></span>
                                    <span class="fw-bold"><?php echo $ht['count']; ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="row g-0">
                        <div class="col-6 p-2">
                            <h3 class="fw-bold text-dark mb-0"><?php echo $familyStats['total_families']; ?></h3>
                            <p class="text-muted fw-bold text-uppercase" style="font-size:10px;">Families</p>
                        </div>
                        <div class="col-6 p-2">
                            <h3 class="fw-bold text-dark mb-0"><?php echo $familyStats['total_people'] ?? 0; ?></h3>
                            <p class="text-muted fw-bold text-uppercase" style="font-size:10px;">People</p>
                        </div>
                    </div>
                    <div class="mt-2 p-3 bg-dark text-white rounded">
                        <h4 class="mb-0"><?php echo number_format($houseStats['avg_area'] ?? 0, 1); ?> m²</h4>
                        <span class="small opacity-75">Average Property Area</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Official Signatures -->
    <div class="col-md-12 report-section mt-5 px-5 d-none d-print-block">
        <div class="row text-center mt-5">
            <div class="col-4">
                <hr class="border-dark border-2 w-75 mx-auto">
                <h6 class="fw-bold">Prepared By</h6>
                <p class="small text-muted">System Administrator</p>
            </div>
            <div class="col-4">
                <div style="width: 120px; height: 120px; border: 3px dashed #ccc; border-radius: 50%; line-height:110px; margin: 0 auto -20px; transform: rotate(-15deg); color: #ccc;">
                    OFFICIAL SEAL
                </div>
            </div>
            <div class="col-4">
                <hr class="border-dark border-2 w-75 mx-auto">
                <h6 class="fw-bold">Approved By</h6>
                <p class="small text-muted">Kebele Chairman</p>
            </div>
        </div>
    </div>
</div>

<div class="mt-4 text-center text-muted small no-print">
    <i class="fas fa-shield-alt me-1"></i> Note: This data is securely summarized from the Ifa Bula Kebele centralized database.
</div>

<?php 
// Add reports link to sidebar dynamically if needed, or I'll just update sidebar.php manually next.
require_once __DIR__ . '/../../includes/footer.php'; 
?>
