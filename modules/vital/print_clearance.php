<?php
// modules/vital/print_clearance.php
// This is included by print.php when cert_type is 'clearance'

// Parse remarks to get Destination and Reason
$remarks_raw = $c['remarks'];
$destination = 'N/A';
$reason = 'Administrative';
$extra = '';

if (strpos($remarks_raw, 'Destination:') !== false) {
    preg_match('/Destination: (.*?) \| Reason: (.*?) \| Extra: (.*)/', $remarks_raw, $matches);
    if ($matches) {
        $destination = $matches[1];
        $reason = $matches[2];
        $extra = $matches[3];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Clearance Certificate - <?php echo $c['cert_number']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Playfair+Display:wght@400;700&family=IBM+Plex+Sans:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        body { font-family: 'IBM+Plex+Sans', sans-serif; background: #e9ecef; }
        .clearance-container {
            width: 800px;
            min-height: 1050px;
            margin: 30px auto;
            background: white;
            padding: 50px;
            border: 2px solid #2c3e50;
            position: relative;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .office-header { text-align: center; margin-bottom: 30px; border-bottom: 3px double #2c3e50; padding-bottom: 15px; }
        .office-header h3 { font-family: 'Cinzel', serif; font-weight: 700; margin-bottom: 5px; color: #1a237e; }
        .office-header h5 { font-weight: 600; color: #34495e; }
        
        .ref-date { display: flex; justify-content: space-between; margin-bottom: 30px; font-weight: 600; }
        
        .cert-title { text-align: center; text-decoration: underline; margin-bottom: 40px; font-family: 'Playfair Display', serif; font-size: 28px; font-weight: 700; }
        
        .cert-body { line-height: 1.8; font-size: 16px; text-align: justify; }
        .highlight { font-weight: 700; border-bottom: 1px solid #000; padding: 0 5px; }
        
        .resident-details { margin: 30px 0; border: 1px solid #ccc; padding: 20px; border-radius: 8px; background: #f8f9fa; display: flex; gap: 30px; }
        .resident-photo-big { width: 120px; height: 140px; object-fit: cover; border: 2px solid #2c3e50; }
        
        .official-footer { margin-top: 80px; display: flex; justify-content: space-between; }
        .signature-box { text-align: center; width: 250px; }
        .signature-box .line { border-top: 2px solid #000; margin-top: 60px; margin-bottom: 5px; }
        
        .watermark { 
            position: absolute; top: 40%; left: 15%; transform: rotate(-45deg); 
            font-size: 100px; color: rgba(46, 204, 113, 0.1); font-weight: 900; 
            pointer-events: none; z-index: 0;
        }
        .v-box-clear {
            margin-top: 30px;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        #qrcode-clear img {
            width: 80px !important;
            height: 80px !important;
            padding: 5px;
            background: white;
            border: 1px solid #2c3e50;
        }
        #barcode-clear {
            width: 150px;
            height: 50px;
        }

        @media print {
            body { background: white; }
            .clearance-container { margin: 0; border: none; box-shadow: none; width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print p-3 text-center bg-dark">
        <button onclick="window.print()" class="btn btn-success px-5 fw-bold"><i class="fas fa-print me-2"></i>PRINT OFFICIAL CLEARANCE</button>
        <a href="index.php" class="btn btn-outline-light ms-2">Back</a>
    </div>

    <div class="clearance-container">
        <div class="watermark">OFFICIAL</div>
        
        <div class="office-header">
            <div class="d-flex justify-content-center gap-5 mb-3">
                <img src="/Ifa Bula/assets/img/ethiopia_flag.png" style="width: 80px; height: 50px; border: 1px solid #ddd;">
                <img src="/Ifa Bula/assets/img/oromia_flag.png" style="width: 80px; height: 50px; border: 1px solid #ddd;">
            </div>
            <h3>OROMIA NATIONAL REGIONAL STATE</h3>
            <h5>JIMMA ZONE, IFA BULA KEBELE ADMINISTRATION</h5>
            <p class="mb-1"><strong>OFFICE OF THE KEBELE MANAGER</strong></p>
            <p class="small text-muted">Afaan Oromoo: Bulchiinsa Ganda Ifa Bulaa | Amharic: የኢፋ ቡላ ቀበሌ አስተዳደር</p>
        </div>

        <div class="ref-date">
            <span>Ref No: <span class="text-danger"><?php echo $c['cert_number']; ?></span></span>
            <span>Date: <?php echo date('d/m/Y', strtotime($c['issue_date'])); ?></span>
        </div>

        <div class="cert-title">CLEARANCE CERTIFICATE / WARAQAA QULQULLINAA</div>

        <div class="cert-body">
            <p>This is to formally certify that the individual whose details are listed below is a recognized resident of <span class="highlight">Ifa Bula Kebele</span>, Jimma Zone, Oromia National Regional State, and is currently registered under our administrative registry.</p>
            
            <div class="resident-details">
                <img src="../../assets/images/<?php echo $c['phot']; ?>" class="resident-photo-big" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($c['fname']); ?>&size=150'">
                <div>
                    <p class="mb-1"><strong>Full Name:</strong> <span class="highlight text-uppercase"><?php echo "{$c['fname']} {$c['mname']} {$c['lname']}"; ?></span></p>
                    <p class="mb-1"><strong>Resident ID:</strong> <span class="highlight">IB-<?php echo str_pad($c['resident_id'], 4, '0', STR_PAD_LEFT); ?></span></p>
                    <p class="mb-1"><strong>Gender:</strong> <span class="highlight"><?php echo $c['s']; ?></span></p>
                    <p class="mb-1"><strong>Nationality:</strong> <span class="highlight"><?php echo $c['nat']; ?></span></p>
                    <p class="mb-1"><strong>Occupation:</strong> <span class="highlight"><?php echo $c['occ']; ?></span></p>
                </div>
            </div>

            <p><strong>ADMINISTRATIVE VERIFICATION:</strong></p>
            <p>Based on our comprehensive record review and sectoral reports, this office hereby confirms the following regarding the aforementioned resident:</p>
            <ol>
                <li class="mb-2"><strong>Social Standing:</strong> The resident has maintained an exemplary social record and has actively participated in community development initiatives. No reports of antisocial behavior have been recorded.</li>
                <li class="mb-2"><strong>Financial Obligations:</strong> All statutory administrative fees, community contributions, and local service payments have been fully settled as of the date of this issuance.</li>
                <li class="mb-2"><strong>Legal & Security Status:</strong> No pending administrative disciplinary actions, local civil disputes, or criminal involvements are registered against the resident within this Kebele's jurisdiction.</li>
            </ol>

            <p><strong>PURPOSE OF ISSUANCE:</strong></p>
            <p>This clearance is granted upon the formal request of the resident for the purpose of <span class="highlight"><?php echo $reason; ?></span>. It is specifically intended for use at <span class="highlight"><?php echo $destination; ?></span> and is not transferable for other purposes without further verification.</p>
            
            <?php if ($extra): ?>
                <p><strong>Additional Remarks:</strong> <em><?php echo $extra; ?></em></p>
            <?php endif; ?>

            <p class="mt-4">The administration of Ifa Bula Kebele requests all concerned authorities to accord the bearer the necessary assistance and recognition. This certificate remains valid for <span class="highlight">Six (6) Months</span> from the date of issuance.</p>
        </div>

        <div class="official-footer">
            <div class="signature-box" style="opacity: 0.1;">
                <div style="height: 100px; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center;">OFFICIAL SEAL / STAMP</div>
            </div>
            
            <div class="v-box-clear">
                <div id="qrcode-clear"></div>
                <svg id="barcode-clear"></svg>
            </div>

            <div class="signature-box">
                <div class="line"></div>
                <strong>Kebele Manager / Chairperson</strong><br>
                <span class="small text-muted">Legal Registry Section</span>
            </div>
        </div>

        <div class="mt-5 pt-4 border-top text-center small text-muted">
            Ifa Bula Kebele Digital Administration System | Verification ID: <?php echo sha1($c['cert_number']); ?>
        </div>
    </div>
        </div>
    </div>
    <script>
        // Generate QR Code
        const qrData = "CLEARANCE: <?php echo $c['cert_number']; ?>\nName: <?php echo "{$c['fname']} {$c['mname']} {$c['lname']}"; ?>\nPurpose: <?php echo $reason; ?>\nDate: <?php echo $c['issue_date']; ?>";
        new QRCode(document.getElementById("qrcode-clear"), {
            text: qrData,
            width: 80,
            height: 80,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });

        // Generate Barcode
        JsBarcode("#barcode-clear", "<?php echo $c['cert_number']; ?>", {
            format: "CODE128",
            width: 1.5,
            height: 40,
            displayValue: true,
            fontSize: 12,
            lineColor: "#2c3e50"
        });
    </script>
</body>
</html>
