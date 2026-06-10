<?php
// modules/letters/print.php
session_start();
require_once __DIR__ . '/../../config/database.php';
if (!isset($_SESSION['user_id'])) exit('Unauthorized');

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("
    SELECT gl.*, i.fname, i.mname, i.lname, i.phot, i.s, i.mar, h.hnum, ic.id_num as r_id, u.username as issuer
    FROM generated_letters gl
    JOIN individuals i ON gl.resident_id = i.id
    LEFT JOIN id_cards ic ON i.id = ic.resident_id AND ic.status = 'Active'
    LEFT JOIN houses h ON i.id = h.owner_individual_id
    JOIN users u ON gl.issued_by = u.id
    WHERE gl.id = ?
");
$stmt->execute([$id]);
$letter = $stmt->fetch();
if (!$letter) exit('Letter record not found.');

$fullName = strtoupper($letter['fname'] . ' ' . $letter['mname'] . ' ' . $letter['lname']);
$date = date('d/m/Y', strtotime($letter['issue_date']));
?>
<!DOCTYPE html>
<html lang="om">
<head>
    <meta charset="UTF-8">
    <title><?php echo $letter['letter_type']; ?> Letter - <?php echo $letter['ref_number']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Outfit:wght@800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Times New Roman', serif; background: #f0f0f0; padding: 50px; }
        .page { width: 210mm; min-height: 297mm; background: #fff; margin: 0 auto; padding: 30mm 25mm; position: relative; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 30px; }
        .header h1 { font-family: 'Outfit', sans-serif; font-size: 22px; color: #111; text-transform: uppercase; margin-bottom: 5px; }
        .header p { font-size: 14px; color: #444; }
        
        .meta-row { display: flex; justify-content: space-between; margin-bottom: 40px; font-weight: bold; }
        .ref-num { color: #d32f2f; }
        
        .to-whom { margin-bottom: 30px; font-weight: bold; font-size: 16px; text-decoration: underline; text-align: center; }
        
        .content { font-size: 16px; line-height: 1.6; text-align: justify; margin-bottom: 50px; }
        .content p { margin-bottom: 20px; }
        
        .details-table { width: 100%; border-collapse: collapse; margin: 30px 0; }
        .details-table td { padding: 8px 0; border-bottom: 1px dashed #ddd; }
        .details-table b { display: inline-block; width: 150px; }

        .signature-area { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 80px; }
        .seal { width: 120px; height: 120px; border: 2px dashed #ccc; border-radius: 50%; display: flex; align-items: center; text-align: center; font-size: 10px; color: #ccc; }
        .sign { border-top: 1px solid #000; padding-top: 10px; width: 200px; text-align: center; }

        .footer-note { position: absolute; bottom: 30mm; left: 25mm; right: 25mm; font-size: 10px; text-align: center; border-top: 1px solid #eee; padding-top: 10px; color: #999; }

        .no-print { position: fixed; top: 20px; right: 20px; }
        .btn { padding: 12px 24px; border-radius: 10px; background: #000; color: #fff; border: none; cursor: pointer; font-family: sans-serif; font-weight: bold; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }

        @media print {
            body { background: none; padding: 0; }
            .page { box-shadow: none; margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn">🖨 PRINT DOCUMENT</button>
    </div>

    <div class="page">
        <div class="header">
            <h1>OROMIA REGIONAL GOVERNMENT</h1>
            <p>Bosa Addis Kebele Administration | Jimma Zone, Ethiopia</p>
            <p>P.O.Box: 000 | Tel: +251 00 000 0000</p>
        </div>

        <div class="meta-row">
            <div>Ref No: <span class="ref-num"><?php echo $letter['ref_number']; ?></span></div>
            <div>Date: <?php echo $date; ?></div>
        </div>

        <div class="to-whom">
            TO WHOM IT MAY CONCERN
        </div>

        <div class="content">
            <?php if ($letter['letter_type'] === 'Residency'): ?>
                <p>This is to formally certify that <b><?php echo $fullName; ?></b> is a verified resident of Bosa Addis Kebele. According to our administrative records, the individual resides in <b>House Number <?php echo $letter['hnum'] ?: 'N/A'; ?></b>.</p>
                <p>He/She has been a resident of this community and is entitled to all administrative rights and recognition as a member of this Kebele. This letter is issued upon the request of the resident for the purpose of: <i>"<?php echo htmlspecialchars($letter['purpose'] ?: 'General administrative requirements'); ?>"</i>.</p>

            <?php elseif ($letter['letter_type'] === 'Conduct'): ?>
                <p>This certficate of good conduct is issued to <b><?php echo $fullName; ?></b>. Based on the reports from community headers and security intelligence within Bosa Addis Kebele, the individual has maintained a law-abiding and disciplined character during his/her stay in our Kebele.</p>
                <p>We confirm that there are no criminal records or disciplinary proceedings logged against the mentioned individual in our local registry. This testimonial is issued for: <i>"<?php echo htmlspecialchars($letter['purpose'] ?: 'Employment / Education verification'); ?>"</i>.</p>

            <?php elseif ($letter['letter_type'] === 'Verification'): ?>
                <p>The Bosa Addis Kebele Administration hereby verifies the identity and details of <b><?php echo $fullName; ?></b>. We have cross-referenced the individual's profile with our Master Registry System (MRS) and confirmed the following data points:</p>
                <table class="details-table">
                    <tr><td><b>Full Name:</b> <?php echo $fullName; ?></td></tr>
                    <tr><td><b>Gender:</b> <?php echo $letter['s'] == 'M' ? 'Male' : 'Female'; ?></td></tr>
                    <tr><td><b>Marital Status:</b> <?php echo $letter['mar']; ?></td></tr>
                    <tr><td><b>Resident ID:</b> <?php echo $letter['r_id'] ?: 'Pending'; ?></td></tr>
                </table>
                <p>This verification is issued for: <i>"<?php echo htmlspecialchars($letter['purpose'] ?: 'Official verification request'); ?>"</i>.</p>

            <?php elseif ($letter['letter_type'] === 'Clearance'): ?>
                <p>This document serves as an Official Clearance for <b><?php echo $fullName; ?></b>. We confirm that the individual has fulfilled all administrative duties, community obligations, and has no pending liabilities or legal disputes within the Bosa Addis Kebele territory.</p>
                <p>The administration clears the individual for transfer, application, or any other formal proceedings as requested. This clearance is valid from the date of issue for <i>"<?php echo htmlspecialchars($letter['purpose'] ?: 'Legal and administrative clearance'); ?>"</i>.</p>
            <?php endif; ?>
            
            <p>We request all concerned authorities to accord the necessary recognition and assistance to the mentioned individual.</p>
        </div>

        <div class="signature-area">
            <div class="seal">
                (OFFICIAL SEAL)
            </div>
            <div class="sign">
                <b>______________________</b><br>
                Kebele Administrator<br>
                (Issued by: <?php echo $letter['issuer']; ?>)
            </div>
        </div>

        <div class="footer-note">
            Note: This document is valid for 6 months from the date of issue. Please verify authenticity using the Ref Number <?php echo $letter['ref_number']; ?> at the Kebele Administrative Office. 
        </div>
    </div>
</body>
</html>
