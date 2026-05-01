<?php
// modules/vital/print_death_cert.php
// This file is included by print.php when cert_type is 'death'

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Death Certificate - <?php echo $c['cert_number']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Outfit:wght@800&display=swap" rel="stylesheet">
    <style>
        @page { size: landscape; margin: 0; }
        body { font-family: 'Inter', sans-serif; background: #f8f9fa; margin: 0; padding: 0; }
        
        .certificate-wrapper {
            width: 1120px;
            height: 790px;
            margin: 20px auto;
            background: white;
            padding: 15px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .border-outer {
            border: 12px solid #991b1b; /* Red theme for death certificate */
            height: 100%;
            padding: 20px;
            position: relative;
        }

        .header-content {
            text-align: center;
            margin-bottom: 40px;
        }

        .header-text h6 {
            color: #991b1b;
            font-weight: 700;
            margin-bottom: 2px;
            font-size: 14px;
        }

        .header-text h4 {
            color: #991b1b;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            margin-top: 10px;
            font-size: 28px;
            border-bottom: 3px solid #991b1b;
            display: inline-block;
            padding-bottom: 5px;
            text-transform: uppercase;
        }

        .info-grid {
            margin-top: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px 50px;
        }

        .field {
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .label {
            font-weight: 600;
            color: #555;
            font-size: 13px;
        }

        .value {
            font-weight: 700;
            color: #111;
            font-size: 16px;
        }

        .footer-section {
            margin-top: 80px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .signature-area {
            width: 250px;
            text-align: center;
            border-top: 2px solid #991b1b;
            padding-top: 10px;
            font-size: 12px;
            color: #991b1b;
            font-weight: bold;
        }

        .stamp-circle {
            width: 140px;
            height: 140px;
            border: 2px solid #eee;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #eee;
            font-size: 12px;
            font-weight: 800;
            transform: rotate(-20deg);
        }

        @media print {
            body { background: white; }
            .certificate-wrapper { margin: 0; box-shadow: none; border: none; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print p-3 text-center bg-dark">
        <button onclick="window.print()" class="btn btn-danger px-5"><i class="fas fa-print me-2"></i>Print Official Death Certificate</button>
        <a href="index.php" class="btn btn-outline-light ms-2">Back to Records</a>
    </div>

    <div class="certificate-wrapper">
        <div class="border-outer">
            <div class="header-content">
                <div class="header-text">
                    <div class="d-flex justify-content-between align-items-center mb-3 px-5">
                        <img src="../../assets/img/ethiopia_flag.png" style="width: 80px; height: 50px; border: 1px solid #ddd;">
                        <img src="../../assets/img/oromia_flag.png" style="width: 80px; height: 50px; border: 1px solid #ddd;">
                    </div>
                    <h6>FEDERAL DEMOCRATIC REPUBLIC OF ETHIOPIA</h6>
                    <h6>Ifa Bula Kebele  ADMINISTRATION | VITAL RECORDS DEPARTMENT</h6>
                    <h4>Death Certificate / የሞት ምስክር ወረቀት</h4>
                    <div class="text-muted mt-2 fw-bold">Official Registration No: <?php echo $c['cert_number']; ?></div>
                </div>
            </div>

            <div class="info-grid">
                <div class="field">
                    <span class="label">Full Name / ሙሉ ስም:</span>
                    <span class="value"><?php echo "{$c['fname']} {$c['mname']} {$c['lname']}"; ?></span>
                </div>
                <div class="field">
                    <span class="label">Date of Death / የሞተበት ቀን:</span>
                    <span class="value"><?php echo date('F d, Y', strtotime($c['death_date'])); ?></span>
                </div>
                <div class="field">
                    <span class="label">Place of Birth / የትውልድ ቦታ:</span>
                    <span class="value"><?php echo "{$c['city']}, {$c['zone']}"; ?></span>
                </div>
                <div class="field">
                    <span class="label">Nationality / ዜግነት:</span>
                    <span class="value"><?php echo $c['nat']; ?></span>
                </div>
                <div class="field" style="grid-column: span 2;">
                    <span class="label">Cause of Death / የሞት ምክንያት:</span>
                    <span class="value"><?php echo $c['death_reason']; ?></span>
                </div>
                <div class="field">
                    <span class="label">Issued Date / የተሰጠበት ቀን:</span>
                    <span class="value"><?php echo date('d/m/Y', strtotime($c['issue_date'])); ?></span>
                </div>
                <div class="field">
                    <span class="label">Registrar Name / የመዝጋቢው ስም:</span>
                    <span class="value"><?php echo $_SESSION['username']; ?></span>
                </div>
            </div>

            <p class="mt-5 text-muted small italic">
                This document serves as the official legal record of death as registered in the Kebele archives. 
                Any alterations to this document render it invalid.
            </p>

            <div class="footer-section">
                <div class="stamp-circle">OFFICIAL SEAL</div>
                <div class="signature-area">
                    Authorized Signature & Title<br>
                    ህጋዊ ፊርማ እና ማህተም
                </div>
            </div>
        </div>
    </div>
</body>
</html>
