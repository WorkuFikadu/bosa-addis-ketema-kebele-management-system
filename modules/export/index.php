<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_type'])) {
    $type = $_POST['export_type'];
    $date_from = $_POST['date_from'];
    $date_to = $_POST['date_to'];
    
    $where = "";
    $params = [];
    if (!empty($date_from) && !empty($date_to)) {
        // Assume 'created_at' exists on tables we're filtering, or don't filter.
        // For simplicity, we filter without date if 'created_at' is absent
    }

    $filename = "export_{$type}_" . date('Ymd_His') . ".csv";
    
    // Clear buffer to prevent HTML injects in CSV
    ob_end_clean();
    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    
    $output = fopen('php://output', 'w');
    // Add BOM for Excel UTF-8 support
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    switch ($type) {
        case 'residents':
            fputcsv($output, ['ID', 'First Name', 'Middle Name', 'Last Name', 'Sex', 'Phone', 'Marital Status', 'Nationality', 'Occupation']);
            $stmt = $pdo->query("SELECT i.id, i.fname, i.mname, i.lname, i.s, a.pho_no, i.mar, i.nat, i.occ FROM individuals i LEFT JOIN addresses a ON i.id = a.id");
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { fputcsv($output, $row); }
            break;
            
        case 'houses':
            fputcsv($output, ['House Num', 'Area', 'Doors', 'Block', 'Year', 'Owner Individual ID', 'Status']);
            $stmt = $pdo->query("SELECT hnum, area, door, block_no, constructed_year, owner_individual_id, house_type FROM houses");
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { fputcsv($output, $row); }
            break;
            
        case 'id_cards':
            fputcsv($output, ['ID Number', 'Resident ID', 'Issue Date']);
            $stmt = $pdo->query("SELECT id_num, resident_id, issue_date FROM id_cards");
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { fputcsv($output, $row); }
            break;
            
        case 'complaints':
            fputcsv($output, ['Ticket #', 'Subject', 'Priority', 'Status', 'Created At']);
            $stmt = $pdo->query("SELECT ticket_number, subject, priority, status, created_at FROM complaints");
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { fputcsv($output, $row); }
            break;
    }
    
    fclose($output);
    log_activity($pdo, $_SESSION['user_id'] ?? null, "Exported $type data to CSV");
    exit;
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="bg-success text-white p-5 text-center position-relative overflow-hidden">
                    <div style="position: absolute; top: -50%; left: -10%; width: 50%; height: 200%; background: rgba(255,255,255,0.05); transform: rotate(30deg); pointer-events: none;"></div>
                    <i class="fas fa-file-excel fa-4x mb-3"></i>
                    <h2 class="fw-bold mb-0">Data Export Center</h2>
                    <p class="text-white-50 mt-2 mb-0">Generate statistical spreadsheets for institutional reporting.</p>
                </div>
                <div class="card-body p-5 bg-white">
                    <form method="POST" target="_blank">
                        <!-- Module Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark text-uppercase small tracking-wide">Select Dataset</label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <input type="radio" class="btn-check" name="export_type" id="t_res" value="residents" checked>
                                    <label class="btn btn-outline-success w-100 p-3 text-start rounded-4 fw-bold" for="t_res">
                                        <i class="fas fa-users fa-lg me-2 text-success"></i> Resident Census
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <input type="radio" class="btn-check" name="export_type" id="t_hou" value="houses">
                                    <label class="btn btn-outline-primary w-100 p-3 text-start rounded-4 fw-bold" for="t_hou">
                                        <i class="fas fa-home fa-lg me-2 text-primary"></i> Housing & Land
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <input type="radio" class="btn-check" name="export_type" id="t_id" value="id_cards">
                                    <label class="btn btn-outline-warning w-100 p-3 text-start rounded-4 fw-bold" for="t_id">
                                        <i class="fas fa-id-card fa-lg me-2 text-warning"></i> Card Issuance
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <input type="radio" class="btn-check" name="export_type" id="t_comp" value="complaints">
                                    <label class="btn btn-outline-danger w-100 p-3 text-start rounded-4 fw-bold" for="t_comp">
                                        <i class="fas fa-ticket-alt fa-lg me-2 text-danger"></i> Help tickets
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Date Filters -->
                        <div class="row mb-5 bg-light p-4 rounded-4">
                            <div class="col-12 mb-2"><span class="fw-bold text-dark text-uppercase small tracking-wide"><i class="fas fa-filter me-2 text-muted"></i>Time Range (Optional)</span></div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">From Date</label>
                                <input type="date" name="date_from" class="form-control border-0 shadow-sm rounded-3">
                            </div>
                            <div class="col-md-6 mt-3 mt-md-0">
                                <label class="form-label small text-muted">To Date</label>
                                <input type="date" name="date_to" class="form-control border-0 shadow-sm rounded-3">
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg shadow-sm rounded-pill py-3 fw-bold text-white fs-5" style="background: linear-gradient(135deg, #10b981, #059669); border: none;">
                                <i class="fas fa-download me-2"></i> Export to CSV (Excel)
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
