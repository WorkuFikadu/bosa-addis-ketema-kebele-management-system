<?php
// modules/vital/print_divorce_cert.php
// Included by print.php when cert_type is 'divorce'
// $c already has the cert + husband individual data

// Fetch full divorce details
$md_stmt = $pdo->prepare("SELECT md.*, 
    g.fname as g_fname, g.lname as g_lname, g.mname as g_mname, g.s as g_sex, g.nat as g_nat, g.occ as g_occ, g.relg as g_relg, g.phot as g_phot_profile,
    g.mother_full_name as g_mother, g.father_full_name as g_father,
    b.fname as b_fname, b.lname as b_lname, b.mname as b_mname, b.s as b_sex, b.nat as b_nat, b.occ as b_occ, b.relg as b_relg, b.phot as b_phot_profile,
    b.mother_full_name as b_mother, b.father_full_name as b_father,
    ga.bdate as g_bdate, ga.age as g_age,
    ba.bdate as b_bdate, ba.age as b_age,
    gad.region as g_region, gad.zone as g_zone, gad.city as g_city, gad.kebele as g_kebele,
    bad.region as b_region, bad.zone as b_zone, bad.city as b_city, bad.kebele as b_kebele
    FROM divorce_details md
    JOIN individuals g ON md.husband_id = g.id
    JOIN individuals b ON md.wife_id = b.id
    LEFT JOIN ages ga ON g.id = ga.id
    LEFT JOIN ages ba ON b.id = ba.id
    LEFT JOIN addresses gad ON g.id = gad.id
    LEFT JOIN addresses bad ON b.id = bad.id
    WHERE md.cert_id = ?");
$md_stmt->execute([$c['cert_id']]);
$m = $md_stmt->fetch();

if (!$m) die("divorce details not found.");

// Use uploaded photos if they exist, fallback to profile photo
$husband_photo = ($m['husband_photo'] && $m['husband_photo'] !== 'default_profile.png') ? $m['husband_photo'] : $m['g_phot_profile'];
$wife_photo = ($m['wife_photo'] && $m['wife_photo'] !== 'default_profile.png') ? $m['wife_photo'] : $m['b_phot_profile'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Divorce Certificate - <?php echo $c['cert_number']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Outfit:wght@700;800;900&family=Playfair+Display:ital,wght@0,700;1,600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        @page { size: landscape; margin: 0; }
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #e5e7eb; margin: 0; padding: 0; }

        .certificate-wrapper {
            width: 1140px;
            height: 800px;
            margin: 20px auto;
            background: #fff;
            position: relative;
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            overflow: hidden;
        }

        /* Decorative outer border */
        .cert-border {
            position: absolute;
            inset: 8px;
            border: 3px solid #8B6914;
            pointer-events: none;
            z-index: 5;
        }
        .cert-border::before {
            content: '';
            position: absolute;
            inset: 4px;
            border: 1px solid #C9A84C;
        }

        /* Background pattern */
        .cert-bg {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 15% 15%, rgba(139,105,20,0.04) 0%, transparent 40%),
                radial-gradient(circle at 85% 85%, rgba(139,105,20,0.04) 0%, transparent 40%),
                linear-gradient(135deg, rgba(255,215,0,0.02) 25%, transparent 25%),
                linear-gradient(-135deg, rgba(255,215,0,0.02) 25%, transparent 25%);
            z-index: 0;
        }

        /* Header section */
        .cert-header {
            position: relative;
            z-index: 10;
            text-align: center;
            padding: 24px 40px 12px;
            border-bottom: 2px solid #8B6914;
        }

        .header-flags {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 6px;
        }

        .flag-img {
            width: 90px;
            height: 58px;
            object-fit: cover;
            border: 2px solid #C9A84C;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .header-title {
            flex: 1;
            padding: 0 20px;
        }

        .header-title .line-gov {
            font-size: 11px;
            color: #555;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .header-title .line-am {
            font-size: 13px;
            color: #333;
            font-weight: 700;
        }
        .header-title .cert-main-title {
            font-family: 'Playfair Display', serif;
            font-size: 30px;
            font-weight: 700;
            color: #8B6914;
            margin: 8px 0 2px;
            letter-spacing: 1px;
        }
        .header-title .cert-sub-title {
            font-size: 14px;
            color: #6B5B00;
            font-weight: 600;
        }
        .header-title .cert-sub-am {
            font-size: 13px;
            color: #555;
            font-weight: 600;
        }
        .serial-no {
            margin-top: 4px;
            font-size: 11px;
            color: #8B6914;
            font-weight: 700;
            letter-spacing: 1px;
        }

        /* Body */
        .cert-body {
            position: relative;
            z-index: 10;
            padding: 16px 50px 10px;
        }

        /* Couple photos row */
        .couple-row {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 80px;
            margin-bottom: 14px;
        }

        .person-card {
            text-align: center;
        }
        .person-card img {
            width: 100px;
            height: 120px;
            object-fit: cover;
            border: 3px solid #8B6914;
            border-radius: 6px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.12);
        }
        .person-card .person-label {
            font-size: 10px;
            font-weight: 800;
            color: #8B6914;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-top: 6px;
        }
        .person-card .person-name {
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            font-weight: 700;
            color: #1a1a1a;
            margin-top: 2px;
        }

        .heart-divider {
            font-size: 36px;
            color: #c0392b;
            text-shadow: 0 2px 8px rgba(192,57,43,0.3);
            align-self: center;
            margin-top: -20px;
        }

        /* Legal text */
        .legal-preamble {
            text-align: center;
            font-size: 11.5px;
            color: #444;
            line-height: 1.7;
            margin-bottom: 14px;
            max-width: 900px;
            margin: 0 auto 14px;
        }
        .legal-preamble strong { color: #1a1a1a; }
        .highlight { color: #8B6914; font-weight: 700; }

        /* Info grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px 40px;
        }
        .info-section-title {
            grid-column: span 1;
            font-size: 11px;
            font-weight: 800;
            color: #8B6914;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #C9A84C;
            padding-bottom: 3px;
            margin-bottom: 3px;
            margin-top: 4px;
        }
        .info-field {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            border-bottom: 1px dotted #ddd;
            padding: 2px 0;
            font-size: 11px;
        }
        .info-label {
            color: #666;
            font-weight: 600;
            min-width: 110px;
        }
        .info-value {
            font-weight: 700;
            color: #111;
            text-align: right;
        }

        /* divorce details */
        .divorce-details-row {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin: 10px 0;
            padding: 8px 0;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
        }
        .md-item {
            text-align: center;
        }
        .md-item .md-label { font-size: 9px; color: #888; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .md-item .md-value { font-size: 12px; font-weight: 800; color: #1a1a1a; }

        /* Footer */
        .cert-footer {
            position: relative;
            z-index: 10;
            padding: 8px 50px 18px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .signature-block {
            text-align: center;
            width: 200px;
        }
        .signature-block .sig-line {
            border-top: 2px solid #8B6914;
            padding-top: 6px;
            font-size: 10px;
            color: #8B6914;
            font-weight: 700;
        }

        .stamp-area {
            width: 100px;
            height: 100px;
            border: 2px dashed #C9A84C;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            color: #C9A84C;
            font-weight: 800;
            text-align: center;
            transform: rotate(-15deg);
            opacity: 0.5;
            line-height: 1.3;
        }
        .v-box-divorce {
            position: absolute;
            bottom: 20px;
            left: 50px;
            display: flex;
            align-items: center;
            gap: 15px;
            z-index: 20;
        }
        #qrcode-divorce img {
            width: 70px !important;
            height: 70px !important;
            padding: 4px;
            background: white;
            border: 1px solid #8B6914;
        }
        #barcode-divorce {
            width: 120px;
            height: 40px;
        }

        .watermark {
            position: absolute;
            z-index: 2;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-family: 'Outfit', sans-serif;
            font-size: 70px;
            font-weight: 900;
            color: rgba(139, 105, 20, 0.03);
            white-space: nowrap;
            pointer-events: none;
        }

        @media print {
            body { background: white; }
            .certificate-wrapper { margin: 0; box-shadow: none; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="padding:12px; text-align:center; background:#1a1a2e;">
        <button onclick="window.print()" style="padding:10px 30px; background:#8B6914; color:white; border:none; border-radius:8px; font-weight:700; font-size:14px; cursor:pointer;">
            &#128424; Print Divorce Certificate
        </button>
        <a href="index.php" style="padding:10px 20px; color:#fff; text-decoration:none; margin-left:10px; border:1px solid #555; border-radius:8px;">Back to Records</a>
    </div>

    <div class="certificate-wrapper">
        <div class="cert-border"></div>
        <div class="cert-bg"></div>
        <div class="watermark">DIVORCE CERTIFICATE</div>

        <div class="v-box-divorce">
            <div id="qrcode-divorce"></div>
            <svg id="barcode-divorce"></svg>
        </div>

        <!-- ═══ HEADER ═══ -->
        <div class="cert-header">
            <div class="header-flags">
                <img src="/Ifa Bula/assets/img/ethiopia_flag.png" class="flag-img" alt="Ethiopia">
                <div class="header-title">
                    <div class="line-gov">FEDERAL DEMOCRATIC REPUBLIC OF ETHIOPIA</div>
                    <div class="line-am">የኢትዮጵያ ፌደራላዊ ዴሞክራሲያዊ ሪፐብሊክ</div>
                    <div class="line-gov" style="margin-top:2px;">Rippaablika Federaalawa Dimokiraatawaa Itoophiyaa</div>
                    <div class="cert-main-title">DIVORCE CERTIFICATE</div>
                    <div class="cert-sub-title">Waraqaa Ragaa Hiikaa</div>
                    <div class="cert-sub-am">የፍቺ ምስክር ወረቀት</div>
                    <div class="serial-no">Registration No: <?php echo $c['cert_number']; ?></div>
                </div>
                <img src="/Ifa Bula/assets/img/oromia_flag.png" class="flag-img" alt="Oromia">
            </div>
        </div>

        <!-- ═══ BODY ═══ -->
        <div class="cert-body">
            <!-- Couple Photos -->
            <div class="couple-row">
                <div class="person-card">
                    <img src="../../assets/images/<?php echo htmlspecialchars($husband_photo); ?>"
                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($m['g_fname'].' '.$m['g_lname']); ?>&size=200&background=1e3a5f&color=fff&bold=true'">
                    <div class="person-label">Husband / ባል</div>
                    <div class="person-name"><?php echo htmlspecialchars("{$m['g_fname']} {$m['g_mname']} {$m['g_lname']}"); ?></div>
                </div>

                <div class="heart-divider">&#10084;</div>

                <div class="person-card">
                    <img src="../../assets/images/<?php echo htmlspecialchars($wife_photo); ?>"
                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($m['b_fname'].' '.$m['b_lname']); ?>&size=200&background=9d174d&color=fff&bold=true'">
                    <div class="person-label">Wife / ሚስት</div>
                    <div class="person-name"><?php echo htmlspecialchars("{$m['b_fname']} {$m['b_mname']} {$m['b_lname']}"); ?></div>
                </div>
            </div>

            <!-- Legal Preamble -->
            <div class="legal-preamble">
                This is to certify that, in accordance with the <strong>Revised Family Code of the Federal Democratic Republic of Ethiopia (Proclamation No. 213/2000)</strong>,
                and under the lawful authority of <span class="highlight">Ifa Bula Kebele Administration</span>, Jimma Zone, Oromia National Regional State,
                the following individuals have entered into a <strong>lawful divorce</strong> by their own free will and mutual consent.
            </div>

            <!-- divorce Details -->
            <div class="divorce-details-row">
                <div class="md-item">
                    <div class="md-label">Date of Divorce</div>
                    <div class="md-value"><?php echo date('F d, Y', strtotime($m['divorce_date'])); ?></div>
                </div>
                <div class="md-item">
                    <div class="md-label">Place of Divorce</div>
                    <div class="md-value"><?php echo htmlspecialchars($m['divorce_place']); ?></div>
                </div>
                <div class="md-item">
                    <div class="md-label">Certificate Issued</div>
                    <div class="md-value"><?php echo date('F d, Y', strtotime($c['issue_date'])); ?></div>
                </div>
            </div>

            <!-- husband + wife Details Grid -->
            <div class="info-grid">
                <div class="info-section-title"><i>&#9794;</i> Husband Details / የባል መረጃ</div>
                <div class="info-section-title"><i>&#9792;</i> Wife Details / የሚስት መረጃ</div>

                <div class="info-field"><span class="info-label">Full Name:</span> <span class="info-value"><?php echo htmlspecialchars("{$m['g_fname']} {$m['g_mname']} {$m['g_lname']}"); ?></span></div>
                <div class="info-field"><span class="info-label">Full Name:</span> <span class="info-value"><?php echo htmlspecialchars("{$m['b_fname']} {$m['b_mname']} {$m['b_lname']}"); ?></span></div>

                <div class="info-field"><span class="info-label">Date of Birth:</span> <span class="info-value"><?php echo $m['g_bdate'] ? date('d/m/Y', strtotime($m['g_bdate'])) : '—'; ?></span></div>
                <div class="info-field"><span class="info-label">Date of Birth:</span> <span class="info-value"><?php echo $m['b_bdate'] ? date('d/m/Y', strtotime($m['b_bdate'])) : '—'; ?></span></div>

                <div class="info-field"><span class="info-label">Age:</span> <span class="info-value"><?php echo $m['g_age'] ?? '—'; ?> yrs</span></div>
                <div class="info-field"><span class="info-label">Age:</span> <span class="info-value"><?php echo $m['b_age'] ?? '—'; ?> yrs</span></div>

                <div class="info-field"><span class="info-label">Nationality:</span> <span class="info-value"><?php echo htmlspecialchars($m['g_nat']); ?></span></div>
                <div class="info-field"><span class="info-label">Nationality:</span> <span class="info-value"><?php echo htmlspecialchars($m['b_nat']); ?></span></div>

                <div class="info-field"><span class="info-label">Father:</span> <span class="info-value"><?php echo htmlspecialchars($m['g_father'] ?: "{$m['g_mname']} {$m['g_lname']}"); ?></span></div>
                <div class="info-field"><span class="info-label">Father:</span> <span class="info-value"><?php echo htmlspecialchars($m['b_father'] ?: "{$m['b_mname']} {$m['b_lname']}"); ?></span></div>

                <div class="info-field"><span class="info-label">Mother:</span> <span class="info-value"><?php echo htmlspecialchars($m['g_mother'] ?: '—'); ?></span></div>
                <div class="info-field"><span class="info-label">Mother:</span> <span class="info-value"><?php echo htmlspecialchars($m['b_mother'] ?: '—'); ?></span></div>

                <div class="info-field"><span class="info-label">Address:</span> <span class="info-value"><?php echo htmlspecialchars(($m['g_kebele'] ?: '') . ', ' . ($m['g_city'] ?: '')); ?></span></div>
                <div class="info-field"><span class="info-label">Address:</span> <span class="info-value"><?php echo htmlspecialchars(($m['b_kebele'] ?: '') . ', ' . ($m['b_city'] ?: '')); ?></span></div>
            </div>

            <!-- Witnesses -->
            <?php if ($m['witness1_name'] || $m['witness2_name']): ?>
            <div style="display:flex; justify-content:center; gap:60px; margin-top:8px; padding-top:6px; border-top:1px dotted #ccc;">
                <?php if ($m['witness1_name']): ?>
                <div style="text-align:center; font-size:10px;">
                    <div style="color:#888; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Witness 1 / ምስክር ፩</div>
                    <div style="font-weight:700; color:#111; font-size:12px; margin-top:2px;"><?php echo htmlspecialchars($m['witness1_name']); ?></div>
                </div>
                <?php endif; ?>
                <?php if ($m['witness2_name']): ?>
                <div style="text-align:center; font-size:10px;">
                    <div style="color:#888; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Witness 2 / ምስክር ፪</div>
                    <div style="font-weight:700; color:#111; font-size:12px; margin-top:2px;"><?php echo htmlspecialchars($m['witness2_name']); ?></div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- ═══ FOOTER ═══ -->
        <div class="cert-footer">
            <div class="signature-block">
                <div class="sig-line">
                    Husband's Signature<br>
                    <span style="font-size:9px; color:#555;">የባል ፊርማ</span>
                </div>
            </div>

            <div class="stamp-area">KEBELE<br>OFFICIAL<br>SEAL</div>

            <div class="signature-block">
                <div class="sig-line">
                    Registrar / Authorized Officer<br>
                    <span style="font-size:9px; color:#555;">የመዝጋቢው ፊርማ</span>
                </div>
            </div>

            <div class="signature-block">
                <div class="sig-line">
                    Wife's Signature<br>
                    <span style="font-size:9px; color:#555;">የሚስት ፊርማ</span>
                </div>
            </div>
        </div>

        </div>

    </div>
    <script>
        // Generate QR Code
        const qrData = "DIVORCE: <?php echo $c['cert_number']; ?>\nHusband: <?php echo "{$m['g_fname']} {$m['g_lname']}"; ?>\nWife: <?php echo "{$m['b_fname']} {$m['b_lname']}"; ?>\nDate: <?php echo $m['divorce_date']; ?>";
        new QRCode(document.getElementById("qrcode-divorce"), {
            text: qrData,
            width: 70,
            height: 70,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });

        // Generate Barcode
        JsBarcode("#barcode-divorce", "<?php echo $c['cert_number']; ?>", {
            format: "CODE128",
            width: 1,
            height: 30,
            displayValue: true,
            fontSize: 10,
            lineColor: "#8B6914"
        });
    </script>
</body>
</html>
