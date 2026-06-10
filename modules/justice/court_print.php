<?php
// modules/justice/court_print.php — Official Social Court Case Record
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../auth/login.php'); exit; }

$id = $_GET['id'] ?? '';
$case = $pdo->prepare("
    SELECT c.*, 
           pi.fname AS p_fname, pi.mname AS p_mname, pi.lname AS p_lname,
           di.fname AS d_fname, di.mname AS d_mname, di.lname AS d_lname
    FROM court_cases c
    LEFT JOIN individuals pi ON c.plaintiff_id = pi.id
    LEFT JOIN individuals di ON c.defendant_id = di.id
    WHERE c.id = ?
");
$case->execute([$id]);
$c = $case->fetch();

if (!$c) { echo "Case not found."; exit; }
?>

<div class="container py-5" id="printable">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white p-5 border-bottom border-dark border-3">
            <div class="row align-items-center">
                <div class="col-3 text-center">
                    <img src="../../assets/img/ethiopia_flag.png" class="mb-2 rounded" height="60">
                    <div class="small fw-bold">Federal Rep. of Ethiopia</div>
                </div>
                <div class="col-6 text-center">
                    <h2 class="fw-black text-dark mb-1">SOCIAL COURT</h2>
                    <h3 class="fw-bold text-muted mb-0">Mana Murtii Hawaasummaa</h3>
                    <div class="mt-3 border-top border-bottom py-2 fw-black text-uppercase letter-spacing-2">
                        Official Judicial Case Record
                    </div>
                </div>
                <div class="col-3 text-center">
                    <img src="../../assets/img/oromia_flag.png" class="mb-2 rounded" height="60">
                    <div class="small fw-bold">Oromia Regional State</div>
                </div>
            </div>
        </div>
        
        <div class="card-body p-5">
            <div class="row mb-5">
                <div class="col-6">
                    <table class="table table-sm table-borderless">
                        <tr><td class="fw-bold text-muted small text-uppercase" width="150">Case Number:</td><td class="fw-black fs-4"><?php echo $c['case_number']; ?></td></tr>
                        <tr><td class="fw-bold text-muted small text-uppercase">Filed Date:</td><td class="fw-bold"><?php echo date('d M Y', strtotime($c['filed_date'])); ?></td></tr>
                        <tr><td class="fw-bold text-muted small text-uppercase">Case Status:</td><td><span class="badge bg-dark px-3"><?php echo strtoupper($c['status']); ?></span></td></tr>
                    </table>
                </div>
                <div class="col-6 text-end">
                    <div class="p-3 bg-light rounded-4 d-inline-block border">
                        <div class="small fw-bold text-muted text-uppercase mb-1">Presiding Judge</div>
                        <div class="fw-black fs-5"><?php echo htmlspecialchars($c['presiding_judge'] ?: 'TBD'); ?></div>
                        <div class="small text-muted mt-1">Bosa Addis Kebele Justice Office</div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <div class="p-4 rounded-4 bg-light border-start border-primary border-5 h-100">
                        <h6 class="fw-black text-primary text-uppercase mb-3">PLAINTIFF (Kasaayi)</h6>
                        <div class="display-6 fw-bold mb-1"><?php echo htmlspecialchars($c['plaintiff_name']); ?></div>
                        <div class="text-muted small"><?php echo $c['plaintiff_id'] ? 'Registered Social Resident' : 'External Litigation Party'; ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-4 rounded-4 bg-light border-start border-danger border-5 h-100">
                        <h6 class="fw-black text-danger text-uppercase mb-3">DEFENDANT (Haseeyi)</h6>
                        <div class="display-6 fw-bold mb-1"><?php echo htmlspecialchars($c['defendant_name']); ?></div>
                        <div class="text-muted small"><?php echo $c['defendant_id'] ? 'Registered Social Resident' : 'External Litigation Party'; ?></div>
                    </div>
                </div>
            </div>

            <div class="mb-5">
                <h6 class="fw-black border-bottom pb-2 mb-3">STATEMENT OF CLAIM & DISPUTE NATURE</h6>
                <div class="p-4 border rounded-4 fs-5" style="line-height:1.6; min-height: 150px;">
                    <?php echo nl2br(htmlspecialchars($c['description'])); ?>
                </div>
            </div>

            <div class="mb-5">
                <h6 class="fw-black border-bottom pb-2 mb-3">JUDICIAL DECISION / VERDICT</h6>
                <div class="p-4 bg-dark text-white rounded-4 fs-5" style="line-height:1.6; min-height: 120px;">
                    <?php if ($c['verdict']): ?>
                        <?php echo nl2br(htmlspecialchars($c['verdict'])); ?>
                    <?php else: ?>
                        <span class="opacity-50 italic small">Pending court ruling / Information not yet entered.</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row mt-5 pt-5 text-center">
                <div class="col-4">
                    <div class="border-top border-dark pt-2 mx-4">
                        <div class="small fw-bold text-uppercase">Plaintiff Signature</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="border-top border-dark pt-2 mx-4">
                        <div class="small fw-bold text-uppercase">Defendant Signature</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="border-top border-dark pt-2 mx-4">
                        <div class="small fw-bold text-uppercase">Judge / Registrar Official Seal</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-light p-4 text-center d-print-none">
            <button onclick="window.print()" class="btn btn-dark rounded-pill px-5 fw-black shadow">
                <i class="fas fa-print me-2"></i>PRINT OFFICIAL RECORD
            </button>
        </div>
    </div>
</div>

<style>
@media print {
    body { background: white !important; }
    .container { max-width: 100% !important; width: 100% !important; padding:0 !important; }
    .card { border: none !important; box-shadow: none !important; }
    header, footer, nav, aside, .d-print-none { display: none !important; }
    .card-header { border-bottom: 3px solid black !important; }
}
.letter-spacing-2 { letter-spacing: 2px; }
</style>
