<?php
// modules/idcards/print.php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT ic.*, i.*, ag.age, ag.bdate, ad.kebele, ad.city, ad.zone, ad.region, ad.pho_no, h.hnum 
                       FROM id_cards ic
                       JOIN individuals i ON ic.resident_id = i.id
                       LEFT JOIN ages ag ON i.id = ag.id
                       LEFT JOIN addresses ad ON i.id = ad.id
                       LEFT JOIN houses h ON h.owner_individual_id = i.id
                       WHERE ic.id = ?");
$stmt->execute([$id]);
$card = $stmt->fetch();

if (!$card) {
    exit('ID Card not found');
}
?>
<!DOCTYPE html>
<html lang="om">
<head>
    <meta charset="UTF-8">
    <title>Waraqaa Eenyummaa - <?php echo $card['id_num']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        @page { size: 85.6mm 54mm landscape; margin: 0; }
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px;
            gap: 30px;
        }

        .no-print { display: flex; gap: 15px; }
        .btn {
            padding: 12px 28px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-primary { background: #003366; color: white; box-shadow: 0 4px 12px rgba(0,51,102,0.3); }
        .btn-secondary { background: white; color: #374151; border: 1px solid #d1d5db; }

        /* ══ PREMIUM ID CARD SHELL ══ */
        .id-card {
            width: 640px;
            height: 400px;
            border-radius: 16px;
            overflow: hidden;
            background: #fff;
            position: relative;
            box-shadow: 0 20px 50px rgba(0,0,0,0.25);
            border: 1px solid rgba(0,0,0,0.1);
        }

        /* ══ DECORATIVE ELEMENTS ══ */
        .card-bg-overlay {
            position: absolute;
            inset: 0;
            background: 
                radial-gradient(circle at 10% 10%, rgba(0,51,102,0.03) 0%, transparent 40%),
                radial-gradient(circle at 90% 90%, rgba(253,185,19,0.03) 0%, transparent 40%);
            z-index: 0;
            pointer-events: none;
        }

        .card-watermark {
            position: absolute;
            top: 55%;
            left: 55%;
            transform: translate(-50%, -50%);
            width: 320px;
            height: auto;
            opacity: 0.06;
            z-index: 1;
            pointer-events: none;
            filter: grayscale(20%);
        }

        /* ══ HEADER ══ */
        .card-header {
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            height: 90px;
            position: relative;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 3px solid #FDB913;
        }

        .flag-box {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            padding: 0 15px;
        }
        .flag-box.left { left: 0; }
        .flag-box.right { right: 0; }
        .flag-box img {
            height: 52px;
            width: 80px;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,0.4);
            border-radius: 4px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }

        .header-text {
            text-align: center;
            color: #fff;
            line-height: 1.2;
        }
        .text-gov { font-weight: 800; font-size: 14px; letter-spacing: 0.5px; text-transform: uppercase; }
        .text-region { font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 12px; color: #FDB913; margin-top: 2px; text-transform: uppercase; }
        .text-type { font-size: 10px; font-weight: 600; opacity: 0.9; margin-top: 4px; letter-spacing: 1px; }

        /* ══ BODY ══ */
        .card-body {
            position: relative;
            z-index: 10;
            display: flex;
            padding: 15px;
            height: calc(400px - 90px - 35px); /* Header - Footer */
        }

        /* Photo Side */
        .left-col {
            width: 140px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        .photo-wrap {
            padding: 4px;
            background: #fff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .photo-wrap img {
            width: 110px;
            height: 140px;
            object-fit: cover;
            border-radius: 4px;
            display: block;
        }
        .id-label-tag {
            background: #003366;
            color: #fff;
            font-size: 8px;
            font-weight: 800;
            padding: 2px 10px;
            border-radius: 4px;
            text-transform: uppercase;
        }

        /* Info Side */
        .right-col {
            flex: 1;
            padding-left: 15px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .info-row {
            display: flex;
            border-bottom: 1px solid #f3f4f6;
            padding-bottom: 4px;
        }
        .info-lbl {
            width: 135px;
            font-size: 8px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
        }
        .info-val {
            font-size: 13px;
            font-weight: 700;
            color: #111;
        }

        .double-row { display: flex; gap: 15px; }

        /* ══ FOOTER ══ */
        .card-footer {
            height: 35px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 10;
        }
        .id-number-txt {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 14px;
            color: #003366;
            letter-spacing: 1px;
        }
        .validity-txt {
            font-size: 9px;
            font-weight: 600;
            color: #6b7280;
        }
        .validity-txt span { color: #111; font-weight: 700; }

        /* ══ VISUAL ASSETS ══ */
        .v-stack {
            position: absolute;
            right: 25px;
            top: 110px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }
        #qrcode img {
            width: 55px !important;
            height: 55px !important;
            padding: 3px;
            background: #fff;
            border: 1px solid #003366;
        }
        #barcode { width: 90px; height: 30px; }

        .sig-container {
            text-align: center;
            width: 100px;
            margin-top: 5px;
        }
        .sig-line-only {
            border-bottom: 1.5px solid #003366;
            width: 100%;
            margin-bottom: 4px;
        }
        .sig-name-below {
            font-family: 'Playfair Display', serif;
            font-size: 12px;
            color: #003366;
            font-style: italic;
            font-weight: 700;
        }
        .sig-lbl { font-size: 8px; font-weight: 700; color: #003366; text-transform: uppercase; margin-top: 2px; }

        @media print {
            @page { size: portrait; margin: 0; }
            body, html { margin: 0 !important; padding: 0 !important; background: white !important; overflow: hidden !important; }
            .no-print { display: none !important; }
        }
    </style>

</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn btn-primary">&#128424; Print Official ID Card</button>
        <a href="index.php" class="btn btn-secondary">Back to List</a>
    </div>

    <div class="id-card">
        <div class="card-bg-overlay"></div>
        <img src="/Bosa Addis/assets/images/logo of bosa addis.jpg" class="card-watermark" alt="Watermark">

        <!-- ══ HEADER ══ -->
        <div class="card-header">
            <div class="flag-box left">
                <img src="/Bosa Addis/assets/img/oromia_flag.png" alt="Oromia Flag">
            </div>
            <div class="header-text">
                <div class="text-region">MOOTUMMAA NAANNOO OROMIYAA</div>
                <div class="text-region" style="font-size:10px;">የኦሮሚያ ብሔራዊ ክልላዊ መንግሥት</div>
                <div class="text-region" style="font-size:9px; opacity:0.8;">OROMIA NATIONAL REGIONAL STATE</div>
                <div class="text-type">Identity Card | መታወቂያ | Waraqaa Eenyummaa</div>
            </div>
            <div class="flag-box right">
                <img src="/Bosa Addis/assets/img/ethiopia_flag.png" alt="Ethiopia Flag">
            </div>
        </div>

        <!-- ══ BODY ══ -->
        <div class="card-body">
            <div class="left-col">
                <div class="photo-wrap">
                    <img src="../../assets/images/<?php echo $card['phot']; ?>" 
                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($card['fname'].'+'.$card['lname']); ?>&size=200&background=003366&color=fff&bold=true'">
                </div>
                <div class="id-label-tag">Resident Photo</div>
            </div>

            <div class="right-col">
                <div class="info-row">
                    <div class="info-lbl">Maqaa / ስም / Name:</div>
                    <div class="info-val" style="text-transform:uppercase;"><?php echo "{$card['fname']} {$card['mname']} {$card['lname']}"; ?></div>
                </div>
                <div class="info-row">
                    <div class="info-lbl">Haadhaa / እናት / Mother:</div>
                    <div class="info-val"><?php echo $card['mother_full_name'] ?: '—'; ?></div>
                </div>
                <div class="double-row">
                    <div class="info-row" style="flex:1;">
                        <div class="info-lbl">Saala / ጾታ / Sex:</div>
                        <div class="info-val"><?php echo ($card['s'] == 'Male' ? 'Dhiira / ወንድ / M' : 'Dubartii / ሴት / F'); ?></div>
                    </div>
                    <div class="info-row" style="flex:1;">
                        <div class="info-lbl">G.Dhal / ልደት / DOB:</div>
                        <div class="info-val"><?php echo $card['bdate'] ? date('d/m/Y', strtotime($card['bdate'])) : '—'; ?></div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-lbl">Teessoo / አድራሻ / Addr:</div>
                    <div class="info-val"><?php echo ($card['kebele'] ? $card['kebele'].', ' : '') . ($card['city'] ?: 'Bosa Addis'); ?> (H.No: <?php echo $card['hnum'] ?: '—'; ?>)</div>
                </div>
                <div class="info-row">
                    <div class="info-lbl">Bilbila / ስልክ / Phone:</div>
                    <div class="info-val"><?php echo $card['pho_no'] ?: '—'; ?></div>
                </div>
                <div class="info-row">
                    <div class="info-lbl">Hojii / ስራ / Occ:</div>
                    <div class="info-val"><?php echo $card['occ']; ?></div>
                </div>
            </div>

            <!-- Visual Assets & Signature -->
            <div class="v-stack">
                <div id="qrcode"></div>
                <svg id="barcode"></svg>
                <div class="sig-container">
                    <div class="sig-line-only"></div>
                    <div class="sig-name-below"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Registrar'); ?></div>
                    <div class="sig-lbl">Authorized Signature</div>
                </div>
            </div>
        </div>

        <!-- ══ FOOTER ══ -->
        <div class="card-footer">
            <div class="id-number-txt"><?php echo $card['id_num']; ?></div>
            <div class="validity-txt">
                ISSUED: <span><?php echo date('d/m/Y', strtotime($card['issue_date'])); ?></span> &nbsp;|&nbsp; 
                EXPIRY: <span><?php echo date('d/m/Y', strtotime($card['expiry_date'])); ?></span>
            </div>
        </div>
    </div>


    <script>
        // ── Robust Print Scaling ──
        (function () {
            // Target scaling for A4 Portrait to ensure it fits regardless of orientation choice
            var PAGE_W = 793, PAGE_H = 1122; 
            var _saved = '';
            function applyScale() {
                var w = document.querySelector('.id-card');
                if (!w) return;
                _saved = w.getAttribute('style') || '';
                w.style.width = '640px'; 
                w.style.height = 'auto';
                w.style.transform = 'none';
                
                var cw = w.scrollWidth;
                var ch = w.scrollHeight;
                var scale = Math.min(PAGE_W / cw, PAGE_H / ch);
                
                w.style.cssText = [
                    'position:fixed', 'top:0', 'left:0',
                    'width:' + cw + 'px',
                    'height:' + ch + 'px',
                    'minHeight:unset',
                    'margin:0',
                    'box-shadow:none',
                    'overflow:hidden',
                    'transform-origin:top left',
                    'transform:scale(' + scale + ')'
                ].join('!important;') + '!important';
            }
            function removeScale() {
                var w = document.querySelector('.id-card');
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
        const qrData = "ID: <?php echo $card['id_num']; ?>\nName: <?php echo $card['fname'] . ' ' . $card['lname']; ?>\nKebele: <?php echo $card['kebele']; ?>\nValid: <?php echo $card['expiry_date']; ?>";
        new QRCode(document.getElementById("qrcode"), {
            text: qrData,
            width: 64,
            height: 64,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });

        // Generate Barcode
        JsBarcode("#barcode", "<?php echo $card['id_num']; ?>", {
            format: "CODE128",
            width: 1,
            height: 30,
            displayValue: false,
            margin: 0,
            lineColor: "#003366"
        });
    </script>
</body>
</html>
