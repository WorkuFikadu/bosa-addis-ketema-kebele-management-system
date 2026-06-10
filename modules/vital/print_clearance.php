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
        @page { size: portrait; margin: 0; }
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f9ff; margin: 0; padding: 0; }

        .certificate-wrapper {
            width: 800px;
            min-height: 1100px;
            margin: 20px auto;
            background: #fff;
            position: relative;
            box-shadow: 0 25px 60px rgba(0,0,0,0.1);
            padding: 10px;
            overflow: hidden;
        }

        /* Decorative Borders */
        .cert-border {
            position: absolute;
            inset: 10px;
            border: 6px double #003366; /* Template Dark Blue */
            pointer-events: none;
            z-index: 5;
        }
        .cert-border::before {
            content: '';
            position: absolute;
            inset: 6px;
            border: 2px solid #FDB913;
        }
        .cert-border::after {
            content: '';
            position: absolute;
            inset: 15px;
            border: 1px solid rgba(0, 51, 102, 0.15);
        }

        /* Background Pattern */
        .cert-bg {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 10% 10%, rgba(0,51,102,0.03) 0%, transparent 40%),
                radial-gradient(circle at 90% 90%, rgba(0,51,102,0.03) 0%, transparent 40%),
                linear-gradient(135deg, rgba(253,185,19,0.01) 25%, transparent 25%),
                linear-gradient(-135deg, rgba(253,185,19,0.01) 25%, transparent 25%);
            z-index: 0;
        }

        .cert-header {
            position: relative;
            z-index: 10;
            text-align: center;
            padding: 20px 35px 12px;
            border-bottom: 2px solid #003366;
            margin: 0 28px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 18px;
        }

        .flag-img {
            width: 65px;
            height: 40px;
            object-fit: cover;
            border: 2px solid #FDB913;
            border-radius: 4px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.12);
        }

        .header-title-box { flex: 1; padding: 0 25px; }
        .gov-line { font-family: 'Outfit', sans-serif; font-size: 9px; color: #003366; font-weight: 900; letter-spacing: 0.5px; text-transform: uppercase; }
        .cert-main-title {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            font-weight: 700;
            color: #003366;
            margin: 6px 0 3px;
            letter-spacing: 1px;
            text-shadow: 1px 1px 1px rgba(0,0,0,0.05);
        }
        .cert-sub-title { font-size: 11px; color: #FDB913; font-weight: 700; font-family: 'Outfit', sans-serif; letter-spacing: 0.5px; }

        .cert-body {
            position: relative;
            z-index: 10;
            padding: 18px 40px;
            line-height: 1.6;
            font-size: 12px;
            color: #334155;
        }

        .ref-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-family: 'Outfit', sans-serif;
            font-size: 11px;
        }
        .ref-no { color: #dc2626; font-weight: 900; letter-spacing: 0.5px; }

        .resident-section {
            display: flex;
            gap: 20px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
            box-shadow: 0 3px 10px rgba(0,0,0,0.03);
        }
        .photo-box img {
            width: 100px;
            height: 125px;
            object-fit: cover;
            border: 2px solid #003366;
            border-radius: 8px;
            box-shadow: 0 5px 14px rgba(0,0,0,0.1);
        }
        .details-box { display: flex; flex-direction: column; justify-content: center; gap: 8px; }
        .details-box p { margin: 0; }
        .lbl-sm { font-size: 8px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.8px; display: block; margin-bottom: 2px; }
        .val-txt { font-size: 13px; font-weight: 700; color: #0f172a; font-family: 'Outfit', sans-serif; }

        .statutory-list {
            list-style: none;
            padding-left: 0;
            margin: 14px 0;
        }
        .statutory-list li {
            padding-left: 22px;
            position: relative;
            margin-bottom: 8px;
            font-style: italic;
            font-size: 11px;
        }
        .statutory-list li::before {
            content: '✦';
            position: absolute;
            left: 0;
            color: #003366;
            font-weight: 900;
        }

        .purpose-box {
            background: #fffdfa;
            border: 1.5px dashed #FDB913;
            padding: 12px;
            margin: 14px 0;
            border-radius: 8px;
            font-style: italic;
            font-size: 11px;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
        }

        .cert-footer { position: relative; z-index: 10; padding: 10px 70px 50px; display: flex; justify-content: space-between; align-items: flex-end; }

        .sig-block { text-align: center; width: 180px; }
        .sig-line { border-top: 2px solid #003366; margin-top: 30px; padding-top: 8px; }
        .sig-label { font-size: 10px; font-weight: 800; color: #003366; letter-spacing: 0.5px; }

        .stamp-area {
            width: 90px;
            height: 90px;
            border: 2px dashed #C9A84C;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            font-weight: 900;
            color: #FDB913;
            text-align: center;
            transform: rotate(-15deg);
            opacity: 0.35;
            line-height: 1.5;
            background: rgba(253,185,19,0.02);
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
            color: rgba(0, 51, 102, 0.015);
            white-space: nowrap;
            pointer-events: none;
        }

        .cert-watermark-img {
            position: absolute;
            top: 55%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 350px;
            height: auto;
            opacity: 0.04;
            z-index: 1;
            pointer-events: none;
            filter: grayscale(10%);
        }

        .v-box-clear { display: flex; flex-direction: column; align-items: center; gap: 8px; }
        #qrcode-clear img { 
            width: 55px !important; 
            height: 55px !important; 
            padding: 3px; 
            background: #fff; 
            border: 1.5px solid #003366; 
            border-radius: 4px;
        }
        #barcode-clear { height: 30px; }

        @media print {
            @page { size: portrait; margin: 0; }
            body, html { margin: 0 !important; padding: 0 !important; background: white !important; overflow: hidden !important; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="padding:12px; text-align:center; background:#1a1a2e;">
        <button onclick="window.print()" style="padding:10px 30px; background:#0d9488; color:white; border:none; border-radius:8px; font-weight:700; font-size:14px; cursor:pointer;">
            &#128424; Print Official Clearance
        </button>
        <a href="index.php" style="padding:10px 20px; color:#fff; text-decoration:none; margin-left:10px; border:1px solid #555; border-radius:8px;">Back</a>
    </div>

    <div class="certificate-wrapper">
        <div class="cert-border"></div>
        <div class="cert-bg"></div>
        <img src="/Bosa Addis/assets/images/logo of bosa addis.jpg" class="cert-watermark-img" alt="Emblem">
        <div class="watermark">OFFICIAL CLEARANCE</div>

        <!-- ═══ HEADER ═══ -->
        <div class="cert-header">
            <div class="header-top">
                <img src="/Bosa Addis/assets/img/ethiopia_flag.png" class="flag-img" alt="Ethiopia">
                <div class="header-title-box">
                    <div class="gov-line">Federal Democratic Republic of Ethiopia</div>
                    <div class="gov-line" style="margin-top:2px; font-weight:900;">የኢትዮጵያ ፌዴራላዊ ዲሞክራሲያዊ ሪፐብሊክ</div>
                    <div class="gov-line" style="margin-top:2px; font-size:10px; opacity:0.9; font-weight:800;">Rippaablika Federaalawa Dimokiraatawaa Itoophiyaa</div>
                    <div class="gov-line" style="margin-top:8px; color:#FDB913; font-weight:900; font-size:13px;">MOOTUMMAA NAANNOO OROMIYAA</div>
                    <div class="gov-line" style="margin-top:2px; color:#FDB913; font-weight:900; font-size:12px;">የኦሮሚያ ብሔራዊ ክልላዊ መንግሥት</div>
                    <div class="gov-line" style="margin-top:2px; color:#FDB913; font-weight:800; font-size:11px; opacity:0.9;">OROMIA NATIONAL REGIONAL STATE</div>
                    <div class="cert-main-title" style="margin-top:10px;">RESIDENCE CLEARANCE</div>
                    <div class="cert-sub-title">Waraqaa Qulqullinaa Fi Eenyummaa / የነዋሪነት ማረጋገጫ ምስክር ወረቀት</div>
                </div>
                <img src="/Bosa Addis/assets/img/oromia_flag.png" class="flag-img" alt="Oromia">
            </div>
        </div>

        <!-- ═══ BODY ═══ -->
        <div class="cert-body">
            <div class="ref-info">
                <span>Official Ref No: <span class="ref-no"><?php echo $c['cert_number']; ?></span></span>
                <span>Date: <strong><?php echo date('d/m/Y', strtotime($c['issue_date'])); ?></strong></span>
            </div>

            <p style="text-align: center; font-weight: 700; color: #0d9488; text-transform: uppercase; letter-spacing: 1px;">To Whom It May Concern</p>

            <p>This is to formally certify that the individual described below is a recognized and registered resident of <strong>Bosa Addis Kebele</strong>, Jimma Zone, Oromia National Regional State.</p>

            <div class="resident-section">
                <div class="photo-box">
                    <img src="../../assets/images/<?php echo $c['phot']; ?>" 
                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($c['fname']); ?>&size=150&background=0d9488&color=fff&bold=true'">
                </div>
                <div class="details-box">
                    <p><span class="lbl-sm">Full Name</span><span class="val-txt text-uppercase"><?php echo "{$c['fname']} {$c['mname']} {$c['lname']}"; ?></span></p>
                    <p><span class="lbl-sm">Resident ID</span><span class="val-txt">BA-<?php echo str_pad($c['resident_id'], 4, '0', STR_PAD_LEFT); ?></span></p>
                    <div style="display:flex; gap:40px;">
                        <p><span class="lbl-sm">Sex</span><span class="val-txt"><?php echo $c['s']; ?></span></p>
                        <p><span class="lbl-sm">Nationality</span><span class="val-txt"><?php echo $c['nat']; ?></span></p>
                    </div>
                </div>
            </div>

            <p><strong>Administrative Verification & Conduct:</strong></p>
            <p>Based on our comprehensive record review and sectoral community reports, this office hereby confirms:</p>
            <ul class="statutory-list">
                <li>The resident has maintained an exemplary social record and has no history of antisocial or illegal behavior within this jurisdiction.</li>
                <li>All statutory administrative obligations and community contributions have been fully settled as of the date of issuance.</li>
                <li>The resident is in good legal standing and is not currently under any administrative disciplinary action.</li>
            </ul>

            <div class="purpose-box">
                <strong>Purpose of Issuance:</strong> This clearance is granted for the purpose of <span style="color:#111; font-weight:700;"><?php echo $reason; ?></span> 
                to be presented at <span style="color:#111; font-weight:700;"><?php echo $destination; ?></span>.
            </div>

            <?php if (!empty(trim($extra))): ?>
            <div style="margin: 14px 0; padding: 12px; border-left: 4px solid #003366; background: #f8fafc; font-size: 11px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); border-radius: 4px;">
                <strong>Details / Remarks:</strong> <?php echo htmlspecialchars($extra); ?>
            </div>
            <?php endif; ?>

            <p>The administration of Bosa Addis Kebele requests all concerned authorities to accord the bearer the necessary assistance and recognition. This certificate remains valid for <strong>Six (6) Months</strong> from the date of issuance.</p>
        </div>

        <!-- ═══ FOOTER ═══ -->
        <div class="cert-footer">
            <div class="v-box-clear">
                <div id="qrcode-clear"></div>
                <svg id="barcode-clear"></svg>
            </div>

            <div class="stamp-area">OFFICIAL<br>KEBELE SEAL</div>

            <div class="sig-block">
                <div class="sig-line">
                    <span style="font-weight:800; color:#111; font-size:14px;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Official Registrar'); ?></span><br>
                    <span class="sig-label">Kebele Manager / Chairperson</span><br>
                    <span style="font-size:9px; color:#555;">Legal Registry Section</span>
                </div>
            </div>
        </div>
    </div>
        </div>
    </div>
    <script>
        // ── Robust Print Scaling (Portrait) ──
        (function () {
            var PAGE_W = 793, PAGE_H = 1122;
            var _saved = '';
            function applyScale() {
                var w = document.querySelector('.certificate-wrapper');
                if (!w) return;
                _saved = w.getAttribute('style') || '';
                w.style.width = '800px'; 
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
            lineColor: "#003366"
        });
    </script>
</body>
</html>
