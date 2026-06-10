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
        body { font-family: 'Inter', sans-serif; background: #f8fafc; margin: 0; padding: 0; }

        .certificate-wrapper {
            width: 1140px;
            min-height: 800px;
            margin: 20px auto;
            background: #fff;
            position: relative;
            box-shadow: 0 25px 60px rgba(0,0,0,0.1);
            padding: 10px;
            overflow: hidden;
        }

        /* Decorative Borders */
        .cert-border { position: absolute; inset: 10px; border: 6px double #003366; pointer-events: none; z-index: 5; }
        .cert-border::before { content: ''; position: absolute; inset: 6px; border: 2px solid #FDB913; }
        .cert-border::after { content: ''; position: absolute; inset: 15px; border: 1px solid rgba(0, 51, 102, 0.15); }

        /* Background pattern */
        .cert-bg { position: absolute; inset: 0; background: radial-gradient(circle at 10% 10%, rgba(0,51,102,0.03) 0%, transparent 40%), radial-gradient(circle at 90% 90%, rgba(0,51,102,0.03) 0%, transparent 40%), linear-gradient(135deg, rgba(253,185,19,0.01) 25%, transparent 25%), linear-gradient(-135deg, rgba(253,185,19,0.01) 25%, transparent 25%); z-index: 0; }

        .header-section { position: relative; z-index: 10; text-align: center; padding: 40px 60px 20px; border-bottom: 3px solid #003366; margin: 0 50px; }
        .header-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 18px; }
        .flag-img { width: 100px; height: 62px; object-fit: cover; border: 2px solid #FDB913; border-radius: 6px; box-shadow: 0 4px 15px rgba(0,0,0,0.12); }
        .header-title-box { flex: 1; padding: 0 50px; }
        .gov-line { font-family: 'Outfit', sans-serif; font-size: 11px; color: #003366; font-weight: 900; letter-spacing: 0.5px; text-transform: uppercase; }
        .cert-main-title { font-family: 'Playfair Display', serif; font-size: 38px; font-weight: 700; color: #003366; margin: 10px 0 4px; letter-spacing: 2px; text-shadow: 1px 1px 1px rgba(0,0,0,0.05); }
        .cert-sub-title { font-size: 16px; color: #FDB913; font-weight: 700; font-family: 'Outfit', sans-serif; letter-spacing: 0.5px; }

        .cert-body { position: relative; z-index: 10; padding: 25px 70px; }
        .serial-info { text-align: center; margin-bottom: 25px; }
        .serial-label { font-size: 11px; color: #999; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }
        .serial-value { font-family: 'Outfit', sans-serif; font-size: 18px; color: #dc2626; font-weight: 900; display: block; margin-top: 4px; }

        .couple-row { display: flex; justify-content: center; align-items: center; gap: 90px; margin-bottom: 25px; }
        .person-card { text-align: center; }
        .person-card img { width: 110px; height: 140px; object-fit: cover; border: 3px solid #FDB913; border-radius: 8px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .person-card .person-label { font-size: 10px; font-weight: 900; color: #003366; text-transform: uppercase; letter-spacing: 1.5px; margin-top: 10px; }
        .person-card .person-name { font-family: 'Outfit', sans-serif; font-size: 16px; font-weight: 700; color: #111; margin-top: 4px; }

        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px 80px; margin-top: 15px; }
        .info-section-title { grid-column: span 1; font-size: 12px; font-weight: 900; color: #003366; text-transform: uppercase; letter-spacing: 1.5px; border-bottom: 2px solid #FDB913; padding-bottom: 6px; margin-bottom: 12px; }
        .info-field { display: flex; justify-content: space-between; align-items: baseline; border-bottom: 1.5px solid #f1f5f9; padding: 6px 0; font-size: 13.5px; }
        .info-label { color: #64748b; font-weight: 600; font-style: italic; }
        .info-value { font-weight: 700; color: #111; text-align: right; font-family: 'Outfit', sans-serif; }

        .legal-preamble { text-align: center; font-size: 14px; color: #475569; max-width: 900px; margin: 25px auto; line-height: 1.8; padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0; background: #fdfcfb; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); }

        .cert-footer { position: relative; z-index: 10; padding: 20px 70px 45px; display: flex; justify-content: space-between; align-items: flex-end; }

        .sig-block { text-align: center; width: 240px; }
        .sig-line { border-top: 2px solid #003366; margin-top: 50px; padding-top: 12px; }
        .sig-label { font-size: 12px; font-weight: 800; color: #003366; letter-spacing: 0.5px; }

        .stamp-area { width: 135px; height: 135px; border: 2px dashed #C9A84C; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 900; color: #FDB913; text-align: center; transform: rotate(-15deg); opacity: 0.35; line-height: 1.6; background: rgba(253,185,19,0.02); }

        .watermark { position: absolute; z-index: 2; top: 55%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-family: 'Outfit', sans-serif; font-size: 110px; font-weight: 900; color: rgba(0, 51, 102, 0.015); white-space: nowrap; pointer-events: none; }
        .cert-watermark-img { position: absolute; top: 55%; left: 50%; transform: translate(-50%, -50%); width: 500px; height: auto; opacity: 0.04; z-index: 1; pointer-events: none; filter: grayscale(10%); }

        .v-box-divorce { display: flex; flex-direction: column; align-items: center; gap: 12px; }
        #qrcode-divorce img { width: 80px !important; height: 80px !important; padding: 5px; background: #fff; border: 1.5px solid #003366; border-radius: 4px; }
        #barcode-divorce { height: 45px; }

        @media print {
            @page { size: landscape; margin: 0; }
            html, body {
                margin: 0 !important; padding: 0 !important;
                background: white !important; overflow: hidden !important;
            }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="padding:12px; text-align:center; background:#1a1a2e;">
        <button onclick="window.print()" style="padding:10px 30px; background:#003366; color:white; border:none; border-radius:8px; font-weight:700; font-size:14px; cursor:pointer;">
            &#128424; Print Divorce Certificate
        </button>
        <a href="index.php" style="padding:10px 20px; color:#fff; text-decoration:none; margin-left:10px; border:1px solid #555; border-radius:8px;">Back to Records</a>
    </div>

    <div class="certificate-wrapper">
        <div class="cert-border"></div>
        <div class="cert-bg"></div>
        <img src="/Bosa Addis/assets/images/logo of bosa addis.jpg" class="cert-watermark-img" alt="Emblem">
        <div class="watermark">DIVORCE CERTIFICATE</div>

        <!-- ═══ HEADER ═══ -->
        <div class="header-section">
            <div class="header-top">
                <img src="/Bosa Addis/assets/img/ethiopia_flag.png" class="flag-img" alt="Ethiopia">
                <div class="header-title-box">
                    <div class="gov-line">Federal Democratic Republic of Ethiopia</div>
                    <div class="gov-line" style="margin-top:2px; font-weight:900;">የኢትዮጵያ ፌዴራላዊ ዲሞክራሲያዊ ሪፐብሊክ</div>
                    <div class="gov-line" style="margin-top:2px; font-size:10px; opacity:0.9; font-weight:800;">Rippaablika Federaalawa Dimokiraatawaa Itoophiyaa</div>
                    <div class="gov-line" style="margin-top:8px; color:#FDB913; font-weight:900; font-size:13px;">MOOTUMMAA NAANNOO OROMIYAA</div>
                    <div class="gov-line" style="margin-top:2px; color:#FDB913; font-weight:900; font-size:12px;">የኦሮሚያ ብሔራዊ ክልላዊ መንግሥት</div>
                    <div class="gov-line" style="margin-top:2px; color:#FDB913; font-weight:800; font-size:11px; opacity:0.9;">OROMIA NATIONAL REGIONAL STATE</div>
                    <div class="cert-main-title" style="margin-top:10px;">DIVORCE CERTIFICATE</div>
                    <div class="cert-sub-title">Waraqaa Ragaa Hiikaa / የፍቺ ምስክር ወረቀት</div>
                    <div class="serial-value" style="font-size:13px; color:#d32f2f; margin-top:5px;">Reg No: <?php echo $c['cert_number']; ?></div>
                </div>
                <img src="/Bosa Addis/assets/img/oromia_flag.png" class="flag-img" alt="Oromia">
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

                <div class="heart-divider" style="color:#374151; font-size:40px; transform: rotate(180deg); margin-top:0;">&#9986;</div>

                <div class="person-card">
                    <img src="../../assets/images/<?php echo htmlspecialchars($wife_photo); ?>"
                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($m['b_fname'].' '.$m['b_lname']); ?>&size=200&background=9d174d&color=fff&bold=true'">
                    <div class="person-label">Wife / ሚስት</div>
                    <div class="person-name"><?php echo htmlspecialchars("{$m['b_fname']} {$m['b_mname']} {$m['b_lname']}"); ?></div>
                </div>
            </div>

            <!-- Legal Preamble -->
            <div class="legal-preamble">
                This document officially certifies that the marriage previously existing between the individuals named below 
                has been <strong>legally dissolved</strong>. This dissolution is registered under the authority of
                <span style="color:#FDB913; font-weight:800;">Bosa Addis Kebele Administration</span> in accordance with the 
                Revised Family Code and relevant legal proclamations.
            </div>

            <!-- Husband + Wife Details Grid -->
            <div class="info-grid">
                <div class="info-section-title">Husband Details / የባል መረጃ</div>
                <div class="info-section-title">Wife Details / የሚስት መረጃ</div>

                <div class="info-field"><span class="info-label">Full Name:</span> <span class="info-value"><?php echo htmlspecialchars("{$m['g_fname']} {$m['g_mname']} {$m['g_lname']}"); ?></span></div>
                <div class="info-field"><span class="info-label">Full Name:</span> <span class="info-value"><?php echo htmlspecialchars("{$m['b_fname']} {$m['b_mname']} {$m['b_lname']}"); ?></span></div>

                <div class="info-field"><span class="info-label">Nationality:</span> <span class="info-value"><?php echo htmlspecialchars($m['g_nat']); ?></span></div>
                <div class="info-field"><span class="info-label">Nationality:</span> <span class="info-value"><?php echo htmlspecialchars($m['b_nat']); ?></span></div>

                <div class="info-field"><span class="info-label">Divorce Date:</span> <span class="info-value"><?php echo date('F d, Y', strtotime($m['divorce_date'])); ?></span></div>
                <div class="info-field"><span class="info-label">Address:</span> <span class="info-value"><?php echo htmlspecialchars(($m['b_kebele'] ?: '') . ' ' . ($m['b_city'] ?: '')); ?></span></div>
            </div>
        </div>

        <!-- ═══ FOOTER ═══ -->
        <div class="cert-footer">
            <div class="qr-bar-box">
                <div id="qrcode-divorce"></div>
                <svg id="barcode-divorce"></svg>
            </div>

            <div class="stamp-area">OFFICIAL<br>KEBELE<br>DIVORCE SEAL</div>

            <div class="sig-block">
                <div class="sig-line">
                    <span style="font-weight:800; color:#111; font-size:14px;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Official Registrar'); ?></span><br>
                    <span class="sig-label">Authorized Registrar</span><br>
                    <span style="font-size:9px; color:#555;">የመዝጋቢው ሙሉ ስም እና ፊርማ</span>
                </div>
            </div>
        </div>

    </div>
    <script>
        // ── Robust Print Scaling ──
        (function () {
            var PAGE_W = 1122, PAGE_H = 793;
            var _saved = '';
            function applyScale() {
                var w = document.querySelector('.certificate-wrapper');
                if (!w) return;
                _saved = w.getAttribute('style') || '';
                w.style.width = '1140px'; 
                w.style.height = 'auto';
                w.style.minHeight = 'unset';
                w.style.transform = 'none';
                
                var cw = w.scrollWidth;
                var ch = w.scrollHeight;
                var scale = Math.min(PAGE_W / cw, PAGE_H / ch);
                
                w.style.cssText = [
                    'position:fixed', 'top:0', 'left:0',
                    'width:' + cw + 'px',
                    'height:' + ch + 'px',
                    'min-height:unset',
                    'margin:0',
                    'box-shadow:none',
                    'overflow:hidden',
                    'transform-origin:top left',
                    'transform:scale(' + scale + ')'
                ].join('!important;') + '!important';
            }
            function removeScale() {
                var w = document.querySelector('.certificate-wrapper');
                if (w) w.setAttribute('style', _saved);
            }
            window.addEventListener('beforeprint', applyScale);
            window.addEventListener('afterprint', removeScale);
            if (window.matchMedia) {
                var mq = window.matchMedia('print');
                mq.addListener(function(e){ e.matches ? applyScale() : removeScale(); });
            }
        })();

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
            lineColor: "#003366"
        });
    </script>
</body>
</html>
