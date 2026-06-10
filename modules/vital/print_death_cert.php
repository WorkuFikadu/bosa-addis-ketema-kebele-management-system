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
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        @page { size: landscape; margin: 0; }
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #fff1f2; margin: 0; padding: 0; }
        
        .certificate-wrapper {
            width: 1140px;
            min-height: 800px;
            margin: 20px auto;
            background: #fff;
            position: relative;
            box-shadow: 0 25px 60px rgba(0,0,0,0.12);
            padding: 10px;
            overflow: hidden;
        }

        /* Premium Decorative Borders */
        .cert-border {
            position: absolute;
            inset: 8px;
            border: 5px double #003366;
            pointer-events: none;
            z-index: 5;
        }
        .cert-border::before {
            content: '';
            position: absolute;
            inset: 5px;
            border: 1.5px solid #FDB913;
        }
        .cert-border::after {
            content: '';
            position: absolute;
            inset: 12px;
            border: 1px solid rgba(0, 51, 102, 0.15);
        }

        /* Subtle Background Polish */
        .cert-bg {
            position: absolute;
            inset: 0;
            background: 
                radial-gradient(circle at 10% 10%, rgba(0,51,102,0.04) 0%, transparent 40%),
                radial-gradient(circle at 90% 90%, rgba(0,51,102,0.04) 0%, transparent 40%),
                linear-gradient(135deg, rgba(253,185,19,0.02) 25%, transparent 25%),
                linear-gradient(-135deg, rgba(253,185,19,0.02) 25%, transparent 25%);
            z-index: 0;
        }

        .header-section { position: relative; z-index: 10; text-align: center; padding: 40px 60px 20px; border-bottom: 3px solid #003366; margin: 0 50px; }
        .header-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 18px; }
        .flag-img { width: 100px; height: 62px; object-fit: cover; border: 2px solid #FDB913; border-radius: 6px; box-shadow: 0 4px 15px rgba(0,0,0,0.12); }
        .header-title-box { flex: 1; padding: 0 50px; }
        .gov-line { font-family: 'Outfit', sans-serif; font-size: 11px; color: #003366; font-weight: 900; letter-spacing: 0.5px; text-transform: uppercase; }
        .cert-main-title { font-family: 'Playfair Display', serif; font-size: 38px; font-weight: 700; color: #003366; margin: 10px 0 4px; letter-spacing: 2px; text-shadow: 1px 1px 1px rgba(0,0,0,0.05); }
        .cert-sub-title { font-size: 16px; color: #FDB913; font-weight: 700; font-family: 'Outfit', sans-serif; letter-spacing: 0.5px; }
        .cert-body { position: relative; z-index: 10; padding: 35px 70px; }
        .serial-info { text-align: center; margin-bottom: 35px; }
        .serial-label { font-size: 11px; color: #999; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; }
        .serial-value { font-family: 'Outfit', sans-serif; font-size: 24px; color: #003366; font-weight: 900; display: block; margin-top: 5px; }
        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px 80px; margin-top: 15px; }
        .info-field { border-bottom: 1.5px solid #fef2f2; padding-bottom: 10px; position: relative; }
        .info-field.full { grid-column: span 2; }
        .lbl-eng { font-size: 11px; font-weight: 800; color: #003366; display: block; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
        .lbl-local { font-size: 10px; font-weight: 600; color: #6b7280; display: block; font-style: italic; margin-bottom: 5px; }
        .val-text { font-size: 17px; font-weight: 700; color: #111; display: block; font-family: 'Outfit', sans-serif; }
        .legal-note { margin-top: 50px; text-align: center; font-size: 12.5px; color: #6b7280; font-style: italic; max-width: 850px; margin: 50px auto 0; line-height: 1.8; padding: 0 40px; }

        .footer-section { position: relative; z-index: 10; padding: 20px 70px 45px; display: flex; justify-content: space-between; align-items: flex-end; }
        .sig-block { text-align: center; width: 260px; }
        .sig-line { border-top: 2px solid #003366; margin-top: 50px; padding-top: 12px; }
        .sig-label { font-size: 12px; font-weight: 800; color: #003366; letter-spacing: 0.5px; }
        .stamp-area { width: 140px; height: 140px; border: 2px dashed #C9A84C; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 900; color: #FDB913; text-align: center; transform: rotate(-15deg); opacity: 0.35; line-height: 1.6; background: rgba(253,185,19,0.02); }
        .watermark { position: absolute; z-index: 2; top: 55%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-family: 'Outfit', sans-serif; font-size: 110px; font-weight: 900; color: rgba(0, 51, 102, 0.015); white-space: nowrap; pointer-events: none; }
        .cert-watermark-img { position: absolute; top: 55%; left: 50%; transform: translate(-50%, -50%); width: 500px; height: auto; opacity: 0.04; z-index: 1; pointer-events: none; filter: grayscale(10%); }
        .qr-bar-box { display: flex; flex-direction: column; align-items: center; gap: 12px; }
        #qrcode-cert img { width: 85px !important; height: 85px !important; padding: 5px; background: #fff; border: 1.5px solid #003366; border-radius: 4px; }
        #barcode-cert { height: 45px; }

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
    <div class="no-print p-3 text-center bg-dark">
        <button onclick="window.print()" class="btn btn-primary px-5"><i class="fas fa-print me-2"></i>Print Official Death Certificate</button>
        <a href="index.php" class="btn btn-outline-light ms-2">Back to Records</a>
    </div>

    <div class="certificate-wrapper">
        <div class="cert-border"></div>
        <div class="cert-bg"></div>
        <img src="/Bosa Addis/assets/images/logo of bosa addis.jpg" class="cert-watermark-img" alt="Emblem">
        <div class="watermark">DEATH CERTIFICATE</div>

        <!-- ═══ HEADER ═══ -->
        <div class="header-section">
            <div class="header-top">
                <img src="../../assets/img/ethiopia_flag.png" class="flag-img" alt="Ethiopia">
                <div class="header-title-box">
                    <div class="gov-line">Federal Democratic Republic of Ethiopia</div>
                    <div class="gov-line" style="margin-top:2px; font-weight:900;">የኢትዮጵያ ፌዴራላዊ ዲሞክራሲያዊ ሪፐብሊክ</div>
                    <div class="gov-line" style="margin-top:2px; font-size:10px; opacity:0.9; font-weight:800;">Rippaablika Federaalawa Dimokiraatawaa Itoophiyaa</div>
                    <div class="gov-line" style="margin-top:8px; color:#FDB913; font-weight:900; font-size:13px;">MOOTUMMAA NAANNOO OROMIYAA</div>
                    <div class="gov-line" style="margin-top:2px; color:#FDB913; font-weight:900; font-size:12px;">የኦሮሚያ ብሔራዊ ክልላዊ መንግሥት</div>
                    <div class="gov-line" style="margin-top:2px; color:#FDB913; font-weight:800; font-size:11px; opacity:0.9;">OROMIA NATIONAL REGIONAL STATE</div>
                    <div class="cert-main-title" style="margin-top:10px;">DEATH CERTIFICATE</div>
                    <div class="cert-sub-title">Waraqaa Ragaa Du'aa / የሞት ምስክር ወረቀት</div>
                </div>
                <img src="../../assets/img/oromia_flag.png" class="flag-img" alt="Oromia">
            </div>
        </div>

        <!-- ═══ BODY ═══ -->
        <div class="cert-body">
            <div class="serial-info">
                <span class="serial-label">Official Registration No</span>
                <span class="serial-value"><?php echo $c['cert_number']; ?></span>
            </div>

            <div class="info-grid">
                <div class="info-field">
                    <span class="lbl-eng">Full Name</span>
                    <span class="lbl-local">የሟቹ ሙሉ ስም</span>
                    <span class="val-text"><?php echo "{$c['fname']} {$c['mname']} {$c['lname']}"; ?></span>
                </div>
                <div class="info-field">
                    <span class="lbl-eng">Date of Death</span>
                    <span class="lbl-local">የሞተበት ቀን</span>
                    <span class="val-text"><?php echo date('F d, Y', strtotime($c['death_date'])); ?></span>
                </div>
                <div class="info-field">
                    <span class="lbl-eng">Place of Birth</span>
                    <span class="lbl-local">የትውልድ ቦታ</span>
                    <span class="val-text"><?php echo "{$c['city']}, {$c['zone']}"; ?></span>
                </div>
                <div class="info-field">
                    <span class="lbl-eng">Nationality</span>
                    <span class="lbl-local">ዜግነት</span>
                    <span class="val-text"><?php echo $c['nat']; ?></span>
                </div>
                <div class="info-field full">
                    <span class="lbl-eng">Cause of Death</span>
                    <span class="lbl-local">የሞት ምክንያት</span>
                    <span class="val-text"><?php echo $c['death_reason']; ?></span>
                </div>
                <div class="info-field">
                    <span class="lbl-eng">Issued Date</span>
                    <span class="lbl-local">የተሰጠበት ቀን</span>
                    <span class="val-text"><?php echo date('d/m/Y', strtotime($c['issue_date'])); ?></span>
                </div>
                <div class="info-field">
                    <span class="lbl-eng">Registrar</span>
                    <span class="lbl-local">የመዝጋቢው ስም</span>
                    <span class="val-text"><?php echo $_SESSION['username']; ?></span>
                </div>
            </div>

            <p class="legal-note">
                This document serves as the official legal record of death as registered in the Kebele archives of Bosa Addis Administration. 
                Any unauthorized alterations, deletions, or fraudulent use of this document render it null and void and are punishable by law.
            </p>
        </div>

        <!-- ═══ FOOTER ═══ -->
        <div class="footer-section">
            <div class="qr-bar-box">
                <div id="qrcode-cert"></div>
                <svg id="barcode-cert"></svg>
            </div>

            <div class="stamp-area">OFFICIAL<br>KEBELE<br>SEAL</div>

            <div class="sig-block">
                <div class="sig-line">
                    <span style="font-weight:800; color:#111; font-size:14px;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Official Registrar'); ?></span><br>
                    <span class="sig-label">Authorized Signature & Title</span><br>
                    <span style="font-size:9x; color:#666;">ህጋዊ ፊርማ እና ማህተም</span>
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
                // Clear dimensions to get natural content size
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
        const qrData = "CERT: <?php echo $c['cert_number']; ?>\nType: Death\nName: <?php echo "{$c['fname']} {$c['mname']} {$c['lname']}"; ?>\nDate: <?php echo $c['issue_date']; ?>";
        new QRCode(document.getElementById("qrcode-cert"), {
            text: qrData,
            width: 80,
            height: 80,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });

        // Generate Barcode
        JsBarcode("#barcode-cert", "<?php echo $c['cert_number']; ?>", {
            format: "CODE128",
            width: 1.5,
            height: 40,
            displayValue: true,
            fontSize: 12,
            lineColor: "#003366"
        });
    </script>
</body>
</html>
