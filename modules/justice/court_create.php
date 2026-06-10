<?php
// modules/justice/court_create.php — Ethiopian Social Court (Mana Murtii Hawaasummaa)
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../auth/login.php'); exit; }

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $case_category = $_POST['case_category'];
    
    // Plaintiff logic
    $p_id = !empty($_POST['plaintiff_id']) ? $_POST['plaintiff_id'] : null;
    $plaintiff_name = $_POST['plaintiff_name'];
    if ($p_id) {
        $p_info = $pdo->prepare("SELECT fname, mname, lname FROM individuals WHERE id = ?");
        $p_info->execute([$p_id]);
        $row = $p_info->fetch();
        if ($row) $plaintiff_name = "{$row['fname']} {$row['mname']} {$row['lname']}";
    }

    // Defendant logic
    $d_id = !empty($_POST['defendant_id']) ? $_POST['defendant_id'] : null;
    $defendant_name = $_POST['defendant_name'];
    if ($d_id) {
        $d_info = $pdo->prepare("SELECT fname, mname, lname FROM individuals WHERE id = ?");
        $d_info->execute([$d_id]);
        $row = $d_info->fetch();
        if ($row) $defendant_name = "{$row['fname']} {$row['mname']} {$row['lname']}";
    }

    $description        = $_POST['description'];
    $presiding_judge    = $_POST['presiding_judge'];
    $filed_date         = $_POST['filed_date'];

    // Extra detail fields — stored as JSON in description if columns not yet added
    $extra = [
        'plaintiff_phone'    => $_POST['plaintiff_phone']    ?? '',
        'plaintiff_id_num'   => $_POST['plaintiff_id_num']   ?? '',
        'plaintiff_address'  => $_POST['plaintiff_address']  ?? '',
        'defendant_phone'    => $_POST['defendant_phone']    ?? '',
        'defendant_id_num'   => $_POST['defendant_id_num']   ?? '',
        'defendant_address'  => $_POST['defendant_address']  ?? '',
        'incident_date'      => $_POST['incident_date']      ?? '',
        'incident_time'      => $_POST['incident_time']      ?? '',
        'incident_location'  => $_POST['incident_location']  ?? '',
        'dispute_amount'     => $_POST['dispute_amount']     ?? '',
        'urgency_level'      => $_POST['urgency_level']      ?? '',
        'witnesses'          => $_POST['witnesses']          ?? '',
        'evidence_list'      => $_POST['evidence_list']      ?? '',
        'prior_mediation'    => $_POST['prior_mediation']    ?? '',
        'mediation_details'  => $_POST['mediation_details']  ?? '',
        'hearing_date'       => $_POST['hearing_date']       ?? '',
        'plaintiff_attorney' => $_POST['plaintiff_attorney'] ?? '',
        'defendant_attorney' => $_POST['defendant_attorney'] ?? '',
        'relief_sought'      => $_POST['relief_sought']      ?? '',
    ];
    $full_description = $description . "\n\n[CASE DETAILS]\n" . json_encode($extra, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    // Generate Ethiopian Court Case Number: KBL/SC/[MM]/[YYYY]/[SEQ]
    $year = date('Y');
    $month = date('m');
    $prefix = "KBL/SC/" . $month . "/" . $year . "/";
    $last = $pdo->query("SELECT case_number FROM court_cases WHERE case_number LIKE '$prefix%' ORDER BY id DESC LIMIT 1")->fetchColumn();
    $seq = $last ? (intval(substr($last, -4)) + 1) : 101;
    $case_number = $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);

    if ($plaintiff_name && $defendant_name && $case_category) {
        try {
            $stmt = $pdo->prepare("INSERT INTO court_cases (case_number, case_category, plaintiff_name, plaintiff_id, defendant_name, defendant_id, description, presiding_judge, filed_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Open')");
            $stmt->execute([$case_number, $case_category, $plaintiff_name, $p_id, $defendant_name, $d_id, $full_description, $presiding_judge, $filed_date]);
            
            // Audit Log
            $pdo->prepare("INSERT INTO audit_logs (user_id, action, details) VALUES (?, 'CREATE', ?)")
                ->execute([$_SESSION['user_id'], "Filed new court case: $case_number"]);
                
            $success = "Case successfully filed. Reference Number: <strong>$case_number</strong>";
        } catch (Exception $e) {
            $error = "File Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all mandatory legal fields.";
    }
}

// Fetch residents for selection
$residents = $pdo->query("SELECT id, fname, mname, lname FROM individuals ORDER BY fname ASC")->fetchAll();
?>

<div class="mb-3">
    <a href="court_list.php" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-bold shadow-sm">
        <i class="fas fa-arrow-left me-2"></i>Back to Social Court Logs
    </a>
</div>

<div class="card border-0 shadow-lg rounded-4 overflow-hidden max-width-1000 mx-auto">
    <div class="card-header bg-dark text-white p-4 border-0 text-center">
        <div class="d-flex align-items-center justify-content-center gap-3 mb-2">
            <img src="../../assets/img/ethiopia_flag.png" height="30" class="rounded shadow-sm">
            <h4 class="fw-black mb-0">Kebele Social Court • Mana Murtii Hawaasummaa</h4>
            <img src="../../assets/img/oromia_flag.png" height="30" class="rounded shadow-sm">
        </div>
        <p class="mb-0 small opacity-75 text-uppercase fw-bold letter-spacing-1">Official Filing of Civil and Petty Legal Matters</p>
    </div>
    
    <div class="card-body p-4 p-md-5">
        <?php if($success): ?>
            <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4"><i class="fas fa-check-circle me-2"></i><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" class="row g-4">

            <!-- ╔══ SECTION 1: CASE CLASSIFICATION ══╗ -->
            <div class="col-12">
                <div class="section-header">
                    <span class="section-num">01</span>
                    <h6 class="fw-black mb-0">CASE CLASSIFICATION & CHRONOLOGY</h6>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">Legal Category <span class="text-danger">*</span></label>
                <select name="case_category" class="form-select form-field" required>
                    <optgroup label="Civil (Hawaasummaa)">
                        <option value="Civil">Standard Civil Dispute</option>
                        <option value="Boundary">Land / Boundary Dispute (Falmii Lafaa)</option>
                        <option value="Labor">Labor / Employment Dispute</option>
                    </optgroup>
                    <optgroup label="Family & Social">
                        <option value="Family">Family Matter (Gaa'ela/Maatii)</option>
                        <option value="Social">Social/Community Grievance</option>
                        <option value="Inheritance">Inheritance / Succession Dispute</option>
                        <option value="Divorce">Divorce / Marital Breakdown</option>
                        <option value="Child">Child Custody / Support</option>
                    </optgroup>
                    <optgroup label="Penal (Adabbii)">
                        <option value="Minor Criminal">Minor Criminal (Yakkaa Xiqqaa)</option>
                        <option value="Property Damage">Property Damage / Theft</option>
                        <option value="Assault">Assault / Physical Harm</option>
                        <option value="Defamation">Defamation / Insult (Arrabsoo)</option>
                    </optgroup>
                    <option value="Debt">Debt / Loan Recovery</option>
                    <option value="Other">Other Miscellaneous Matters</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">Official Filing Date <span class="text-danger">*</span></label>
                <input type="date" name="filed_date" class="form-control form-field" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">Urgency Level</label>
                <select name="urgency_level" class="form-select form-field">
                    <option value="Normal">🟢 Normal — Standard Processing</option>
                    <option value="Urgent">🟡 Urgent — Expedited Review Needed</option>
                    <option value="Critical">🔴 Critical — Immediate Hearing Required</option>
                </select>
            </div>

            <!-- ╔══ SECTION 2: PLAINTIFF INFORMATION ══╗ -->
            <div class="col-12 mt-4">
                <div class="section-header border-primary-subtle">
                    <span class="section-num bg-primary">02</span>
                    <h6 class="fw-black mb-0 text-primary"><i class="fas fa-user-injured me-2"></i>PLAINTIFF (KASAAYI / ACCUSER) — FULL DETAILS</h6>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">Select Registered Resident</label>
                <select name="plaintiff_id" class="form-select form-field" onchange="if(this.value) document.getElementById('p_name_input').value = this.options[this.selectedIndex].text;">
                    <option value="">-- Search Resident --</option>
                    <?php foreach ($residents as $res): ?>
                        <option value="<?php echo $res['id']; ?>"><?php echo htmlspecialchars("{$res['fname']} {$res['mname']} {$res['lname']}"); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">Full Legal Name <span class="text-danger">*</span></label>
                <input type="text" name="plaintiff_name" id="p_name_input" class="form-control form-field" placeholder="First, Father, Grand Father name" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">National ID / Kebele ID Number</label>
                <input type="text" name="plaintiff_id_num" class="form-control form-field" placeholder="e.g. ETH-12345678">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">Phone / Mobile Number</label>
                <input type="tel" name="plaintiff_phone" class="form-control form-field" placeholder="e.g. 0912-345-678">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">Kebele / Residential Address</label>
                <input type="text" name="plaintiff_address" class="form-control form-field" placeholder="Kebele, ward/ganda, house number">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">Legal Representative / Attorney (if any)</label>
                <input type="text" name="plaintiff_attorney" class="form-control form-field" placeholder="Name of legal representative">
            </div>

            <!-- ╔══ SECTION 3: DEFENDANT INFORMATION ══╗ -->
            <div class="col-12 mt-4">
                <div class="section-header">
                    <span class="section-num bg-danger">03</span>
                    <h6 class="fw-black mb-0 text-danger"><i class="fas fa-user-slash me-2"></i>DEFENDANT (HASEEYI / ACCUSED) — FULL DETAILS</h6>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">Select Registered Resident</label>
                <select name="defendant_id" class="form-select form-field" onchange="if(this.value) document.getElementById('d_name_input').value = this.options[this.selectedIndex].text;">
                    <option value="">-- Search Resident --</option>
                    <?php foreach ($residents as $res): ?>
                        <option value="<?php echo $res['id']; ?>"><?php echo htmlspecialchars("{$res['fname']} {$res['mname']} {$res['lname']}"); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">Full Legal Name <span class="text-danger">*</span></label>
                <input type="text" name="defendant_name" id="d_name_input" class="form-control form-field" placeholder="First, Father, Grand Father name" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">National ID / Kebele ID Number</label>
                <input type="text" name="defendant_id_num" class="form-control form-field" placeholder="e.g. ETH-12345678">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">Phone / Mobile Number</label>
                <input type="tel" name="defendant_phone" class="form-control form-field" placeholder="e.g. 0912-345-678">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">Kebele / Residential Address</label>
                <input type="text" name="defendant_address" class="form-control form-field" placeholder="Kebele, ward/ganda, house number">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">Legal Representative / Attorney (if any)</label>
                <input type="text" name="defendant_attorney" class="form-control form-field" placeholder="Name of legal representative">
            </div>

            <!-- ╔══ SECTION 4: INCIDENT DETAILS ══╗ -->
            <div class="col-12 mt-4">
                <div class="section-header">
                    <span class="section-num bg-warning text-dark">04</span>
                    <h6 class="fw-black mb-0">INCIDENT / DISPUTE DETAILS</h6>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">Date of Incident / Dispute</label>
                <input type="date" name="incident_date" class="form-control form-field">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">Time of Incident (Approx.)</label>
                <input type="time" name="incident_time" class="form-control form-field">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">Location / Place of Incident</label>
                <input type="text" name="incident_location" class="form-control form-field" placeholder="e.g. Near kebele office, Market area">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">Monetary Value Disputed (ETB) <small class="text-muted">(if applicable)</small></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0">ETB</span>
                    <input type="number" name="dispute_amount" class="form-control form-field" placeholder="0.00" step="0.01" min="0">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">Prior Mediation Attempted?</label>
                <select name="prior_mediation" class="form-select form-field" id="prior_mediation_select" onchange="document.getElementById('mediation_detail_row').style.display = this.value==='Yes' ? '' : 'none'">
                    <option value="No">No — First Filing</option>
                    <option value="Yes">Yes — Previous Mediation Failed</option>
                    <option value="Partial">Partial — Ongoing Mediation</option>
                </select>
            </div>
            <div class="col-md-4" id="mediation_detail_row" style="display:none;">
                <label class="form-label fw-bold small text-muted text-uppercase">Mediation Details / Outcome</label>
                <input type="text" name="mediation_details" class="form-control form-field" placeholder="Who mediated? When? Outcome?">
            </div>
            <div class="col-md-12">
                <label class="form-label fw-bold small text-muted text-uppercase">Relief Sought by Plaintiff (What does the plaintiff want the court to order?)</label>
                <input type="text" name="relief_sought" class="form-control form-field" placeholder="e.g. Compensation of 5000 ETB, Return of property, Formal apology...">
            </div>

            <!-- ╔══ SECTION 5: STATEMENT OF CLAIM ══╗ -->
            <div class="col-12 mt-4">
                <div class="section-header">
                    <span class="section-num bg-secondary">05</span>
                    <h6 class="fw-black mb-0">DETAILED STATEMENT OF CLAIM (IBSAANNOO GAAGA'AMA)</h6>
                </div>
            </div>
            <div class="col-md-12">
                <label class="form-label fw-bold small text-muted text-uppercase">Full Description of the Dispute <span class="text-danger">*</span></label>
                <textarea name="description" class="form-control form-field" rows="5" placeholder="Provide a thorough and factual account: What happened? When did it begin? How did it escalate? What is the current situation? Include all important dates and events." required></textarea>
            </div>

            <!-- ╔══ SECTION 6: WITNESSES & EVIDENCE ══╗ -->
            <div class="col-12 mt-4">
                <div class="section-header">
                    <span class="section-num" style="background:#0891b2;">06</span>
                    <h6 class="fw-black mb-0">WITNESSES (RAGAA) & EVIDENCE (RAGAA-MURTII)</h6>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase">Witness Names & Contact (one per line)</label>
                <textarea name="witnesses" class="form-control form-field" rows="4" placeholder="e.g.&#10;1. Abebe Bekele — 0911-222-333&#10;2. Chaltu Diro — nearby resident&#10;3. Shop owner near market"></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase">Evidence & Supporting Documents Available</label>
                <textarea name="evidence_list" class="form-control form-field" rows="4" placeholder="e.g.&#10;• Written agreement (contract)&#10;• Land registration certificate&#10;• Photographs of damage&#10;• Previous court orders&#10;• SMS / Written messages"></textarea>
            </div>

            <!-- ╔══ SECTION 7: JUDICIAL ASSIGNMENT ══╗ -->
            <div class="col-12 mt-4">
                <div class="section-header">
                    <span class="section-num bg-dark">07</span>
                    <h6 class="fw-black mb-0">JUDICIAL ASSIGNMENT & HEARING SCHEDULE</h6>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">Presiding Judge / Court President <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="fas fa-user-tie text-dark"></i></span>
                    <input type="text" name="presiding_judge" class="form-control form-field" placeholder="Full name of assigned judge" required>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">First Hearing / Court Date</label>
                <input type="date" name="hearing_date" class="form-control form-field">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">Hearing Venue / Courtroom</label>
                <input type="text" name="hearing_venue" class="form-control form-field" placeholder="e.g. Kebele Office Room 2, Social Court Hall">
            </div>

            <!-- SUBMIT -->
            <div class="col-12 mt-4">
                <hr class="border-2">
                <button type="submit" class="btn btn-warning text-dark w-100 rounded-pill py-3 fw-black shadow-lg hover-lift fs-5">
                    <i class="fas fa-file-signature me-2"></i>OFFICIALLY FILE & RECORD SOCIAL COURT CASE
                </button>
                <p class="text-center text-muted small mt-2"><i class="fas fa-shield-halved me-1"></i>This record is legally binding and stored in the Bosa Addis Kebele justice system.</p>
            </div>
        </form>
    </div>
</div>

<style>
.max-width-1000 { max-width: 1060px; }
.letter-spacing-1 { letter-spacing: 1px; }
.hover-lift { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.hover-lift:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important; }

/* Section headers */
.section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #f8f9fa;
    border-radius: 10px;
    padding: 10px 16px;
    border-left: 4px solid #212529;
    margin-bottom: 4px;
}
.section-num {
    background: #212529;
    color: #fff;
    font-weight: 900;
    font-size: 12px;
    min-width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Form fields */
.form-field {
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 16px;
    font-size: 14px;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.form-field:focus {
    border-color: #ffc107;
    box-shadow: 0 0 0 3px rgba(255,193,7,0.15);
    background: #fff;
    outline: none;
}
.form-select.form-field { padding-right: 2.5rem; }
</style>

<?php require_once '../../includes/footer.php'; ?>
