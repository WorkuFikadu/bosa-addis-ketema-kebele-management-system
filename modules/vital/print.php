<?php
// modules/vital/print.php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$id = $_GET['id'] ?? '';
$query = "SELECT vc.*, i.*, vc.id AS cert_id, vc.resident_id, ag.bdate, a.region, a.zone, a.city, a.kebele 
          FROM vital_certificates vc 
          JOIN individuals i ON vc.resident_id = i.id 
          LEFT JOIN ages ag ON i.id = ag.id
          LEFT JOIN addresses a ON i.id = a.id
          WHERE vc.id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id]);
$c = $stmt->fetch();

if (!$c) die("Record not found.");

$is_birth = ($c['cert_type'] === 'birth');
$is_death = ($c['cert_type'] === 'death');
$is_clearance = ($c['cert_type'] === 'clearance');
$is_marriage = ($c['cert_type'] === 'marriage');
$is_divorce = ($c['cert_type'] === 'divorce');

if ($is_death) {
    include 'print_death_cert.php';
    exit;
}

if ($is_clearance) {
    include 'print_clearance.php';
    exit;
}

if ($is_marriage) {
    include 'print_marriage_cert.php';
    exit;
}

if ($is_divorce) {
    include 'print_divorce_cert.php';
    exit;
}

if (!$is_birth) {
    die("Unsupported certificate type.");
}

// Function to convert date to Amharic month name (simplified)
function getAmharicMonth($date) {
    $months = [
        1 => 'መስከረም', 2 => 'ጥቅምት', 3 => 'ኅዳር', 4 => 'ታኅሣሥ', 5 => 'ጥር', 6 => 'የካቲት',
        7 => 'መጋቢት', 8 => 'ሚያዝያ', 9 => 'ግንቦት', 10 => 'ሰኔ', 11 => 'ሐምሌ', 12 => 'ነሐሴ'
    ];
    // This is a rough estimation as GC to EC conversion is complex. 
    // For a demo, we use the month number directly.
    $m = (int)date('m', strtotime($date));
    return $months[$m] ?? '';
}

function getOromoMonth($date) {
    $months = [
        1 => 'Ammajjii', 2 => 'Guraandhala', 3 => 'Bitootessa', 4 => 'Eebila', 5 => 'Caamsaa', 6 => 'Waxabajjii',
        7 => 'Adooleessa', 8 => 'Hagayya', 9 => 'Fuulbana', 10 => 'Onkololeessa', 11 => 'Sadaasa', 12 => 'Muddee'
    ];
    $m = (int)date('m', strtotime($date));
    return $months[$m] ?? '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Birth Certificate - <?php echo $c['cert_number']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Outfit:wght@800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        @page { size: landscape; margin: 0; }
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #e2e8f0; margin: 0; padding: 0; }
        
        .certificate-wrapper {
            width: 1140px;
            min-height: 800px;
            margin: 20px auto;
            background: #fff;
            position: relative;
            box-shadow: 0 40px 100px rgba(0,0,0,0.1);
            padding: 40px;
            overflow: hidden;
        }

        /* Diagonal Corner Accents (Image Reference Style) */
        .corner-accent {
            position: absolute;
            width: 300px;
            height: 300px;
            z-index: 1;
        }
        .top-right {
            top: -100px;
            right: -100px;
            background: linear-gradient(135deg, #003366 33%, #FDB913 33%, #FDB913 66%, #1D4E89 66%);
            transform: rotate(45deg);
        }
        .bottom-left {
            bottom: -100px;
            left: -100px;
            background: linear-gradient(135deg, #1D4E89 33%, #FDB913 33%, #FDB913 66%, #003366 66%);
            transform: rotate(45deg);
        }

        /* Inner White Border with Shadow */
        .inner-border {
            position: absolute;
            inset: 20px;
            border: 15px solid white;
            background: transparent;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            z-index: 2;
            pointer-events: none;
        }
        .outer-line {
            position: absolute;
            inset: 10px;
            border: 2px solid #003366;
            z-index: 3;
            pointer-events: none;
        }

        .header-section {
            position: relative;
            z-index: 10;
            text-align: center;
            padding: 20px 60px 20px;
            margin-bottom: 20px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .flag-img {
            width: 90px;
            height: 55px;
            object-fit: cover;
            border: 2px solid #f9b933;
            border-radius: 4px;
        }

        .header-title-box { flex: 1; padding: 0 40px; }
        .gov-line { font-family: 'Outfit', sans-serif; font-size: 11px; color: #003366; font-weight: 900; letter-spacing: 0.5px; text-transform: uppercase; }
        .cert-main-title { 
            font-family: 'Playfair Display', serif; 
            font-size: 42px; 
            font-weight: 700; 
            color: #003366; 
            margin: 10px 0 5px; 
            letter-spacing: 3px;
        }
        .cert-sub-title { font-size: 16px; color: #FDB913; font-weight: 700; font-family: 'Outfit', sans-serif; letter-spacing: 1px; }

        .cert-body {
            position: relative;
            z-index: 10;
            padding: 10px 80px;
        }

        .photo-and-serial {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .resident-photo-frame {
            padding: 8px;
            background: white;
            border: 3px solid #003366;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .resident-photo-frame img {
            width: 120px;
            height: 145px;
            object-fit: cover;
            display: block;
            border-radius: 6px;
        }

        .serial-no-box {
            text-align: right;
            border-right: 5px solid #FDB913;
            padding-right: 20px;
        }
        .serial-label { font-size: 11px; color: #999; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; }
        .serial-value { font-family: 'Outfit', sans-serif; font-size: 24px; color: #003366; font-weight: 900; display: block; margin-top: 5px; }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px 50px;
            margin-top: 20px;
        }

        .info-field {
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 10px;
            position: relative;
        }
        .info-field.full { grid-column: span 3; }
        .info-field.double { grid-column: span 2; }

        .lbl-o { font-size: 11px; font-weight: 800; color: #003366; display: block; text-transform: uppercase; letter-spacing: 1px; }
        .lbl-a { font-size: 10px; font-weight: 600; color: #94a3b8; display: block; font-style: italic; }
        .val-text { font-size: 16px; font-weight: 700; color: #1e293b; margin-top: 6px; display: block; font-family: 'Outfit', sans-serif; }

        .footer-section {
            position: relative;
            z-index: 10;
            padding: 30px 80px 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .sig-block { text-align: center; width: 260px; }
        .sig-line { border-top: 3px solid #003366; margin-top: 50px; padding-top: 15px; }
        .sig-label { font-size: 13px; font-weight: 800; color: #003366; text-transform: uppercase; }

        .seal-medal {
            position: relative;
            width: 120px;
            height: 120px;
            background: #003366;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 10px;
            font-weight: 900;
            text-align: center;
            border: 4px solid #FDB913;
            box-shadow: 0 10px 25px rgba(0,51,102,0.3);
            z-index: 15;
            margin: 0 40px;
        }
        .seal-medal::before, .seal-medal::after {
            content: '';
            position: absolute;
            bottom: -30px;
            width: 30px;
            height: 60px;
            background: #FDB913;
            clip-path: polygon(0 0, 100% 0, 100% 100%, 50% 80%, 0 100%);
            z-index: -1;
        }
        .seal-medal::before { left: 20px; transform: rotate(-10deg); }
        .seal-medal::after { right: 20px; transform: rotate(10deg); }

        .qr-bar-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }
        #qrcode-cert img { 
            width: 80px !important; 
            height: 80px !important; 
            padding: 5px; 
            background: #fff; 
            border: 2px solid #003366; 
            border-radius: 6px;
        }
        #barcode-cert { height: 45px; }

        .cert-watermark-img {
            position: absolute;
            top: 55%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 540px;
            height: auto;
            opacity: 0.04;
            z-index: 1;
            pointer-events: none;
            filter: grayscale(10%);
        }

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
        <button onclick="window.print()" class="btn btn-primary px-5"><i class="fas fa-print me-2"></i>Print Official Birth Certificate</button>
        <a href="index.php" class="btn btn-outline-light ms-2">Back to Records</a>
    </div>

    <div class="certificate-wrapper">
        <!-- Decorative Elements -->
        <div class="corner-accent top-right"></div>
        <div class="corner-accent bottom-left"></div>
        <div class="inner-border"></div>
        <div class="outer-line"></div>
        <img src="/Bosa Addis/assets/images/logo of bosa addis.jpg" class="cert-watermark-img" alt="Emblem">

        <!-- ═══ HEADER ═══ -->
        <div class="header-section">
            <div class="header-top">
                <img src="/Bosa Addis/assets/img/ethiopia_flag.png" class="flag-img" alt="Ethiopia">
                <div class="header-title-box">
                    <div class="gov-line">Federal Democratic Republic of Ethiopia</div>
                    <div class="gov-line" style="margin-top:2px; font-weight:900;">የኢትዮጵያ ፌዴራላዊ ዲሞክራሲያዊ ሪፐብሊክ</div>
                    <div class="gov-line" style="margin-top:2px; font-size:10px; opacity:0.9; font-weight:900;">Rippaablika Federaalawa Dimokiraatawaa Itoophiyaa</div>
                    <div class="gov-line" style="margin-top:8px; color:#FDB913; font-weight:900; font-size:13px;">MOOTUMMAA NAANNOO OROMIYAA</div>
                    <div class="gov-line" style="margin-top:2px; color:#FDB913; font-weight:900; font-size:12px;">የኦሮሚያ ብሔራዊ ክልላዊ መንግሥት</div>
                    <div class="gov-line" style="margin-top:2px; color:#FDB913; font-weight:800; font-size:11px; opacity:0.9;">OROMIA NATIONAL REGIONAL STATE</div>
                    <div class="cert-main-title" style="margin-top:10px;">BIRTH CERTIFICATE</div>
                    <div class="cert-sub-title">Waraqaa Ragaa Dhalootaa / የልደት ምስክር ወረቀት</div>
                </div>
                <img src="/Bosa Addis/assets/img/oromia_flag.png" class="flag-img" style="border-color: #FDB913;" alt="Oromia">
            </div>
        </div>

        <!-- ═══ BODY ═══ -->
        <div class="cert-body">
            <div class="photo-and-serial">
                <div class="resident-photo-frame">
                    <img src="../../assets/images/<?php echo $c['phot']; ?>" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($c['fname']); ?>&size=128&background=1a237e&color=fff'">
                </div>
                <div class="serial-no-box">
                    <span class="serial-label">Registration Number</span>
                    <span class="serial-value"><?php echo $c['cert_number']; ?></span>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-field">
                    <div class="label-group"><span class="lbl-o">Maqaa Da'imaa / የህፃኑ ስም</span><span class="lbl-a">Child's Name</span></div>
                    <span class="val-text"><?php echo $c['fname']; ?></span>
                </div>
                <div class="info-field">
                    <div class="label-group"><span class="lbl-o">Maqaa Abbaa / የአባት ስም</span><span class="lbl-a">Father's Name</span></div>
                    <span class="val-text"><?php echo $c['mname']; ?></span>
                </div>
                <div class="info-field">
                    <div class="label-group"><span class="lbl-o">Maqaa Akaakayyuu / የአያት ስም</span><span class="lbl-a">Grandfather's Name</span></div>
                    <span class="val-text"><?php echo $c['lname']; ?></span>
                </div>

                <div class="info-field">
                    <div class="label-group"><span class="lbl-o">Koorniyaa / ጾታ</span><span class="lbl-a">Sex</span></div>
                    <span class="val-text"><?php echo ($c['s'] == 'Male' ? 'Dhiira / ወንድ' : 'Dubartii / ሴት'); ?></span>
                </div>
                <div class="info-field double">
                    <div class="label-group"><span class="lbl-o">Guyyaa Dhalootaa / የልደት ቀን</span><span class="lbl-a">Date of Birth (Month, Day, Year)</span></div>
                    <span class="val-text">
                        <?php echo getOromoMonth($c['bdate']); ?> <?php echo date('d', strtotime($c['bdate'])); ?>, <?php echo date('Y', strtotime($c['bdate'])); ?> / 
                        <?php echo getAmharicMonth($c['bdate']); ?> <?php echo date('d', strtotime($c['bdate'])); ?>, <?php echo date('Y', strtotime($c['bdate'])); ?>
                    </span>
                </div>

                <div class="info-field full">
                    <div class="label-group"><span class="lbl-o">Iddoo Dhalootaa / የትውልድ ቦታ</span><span class="lbl-a">Place of Birth (Zone, City, Kebele)</span></div>
                    <span class="val-text"><?php echo "{$c['zone']}, {$c['city']}, {$c['kebele']}"; ?></span>
                </div>

                <div class="info-field">
                    <div class="label-group"><span class="lbl-o">Lammummaa / ዜግነት</span><span class="lbl-a">Nationality</span></div>
                    <span class="val-text"><?php echo $c['nat']; ?> / ኢትዮጵያዊ</span>
                </div>
                <div class="info-field double">
                    <div class="label-group"><span class="lbl-o">Maqaa Guutuu Haadhaa / የእናት ሙሉ ስም</span><span class="lbl-a">Mother's Full Name</span></div>
                    <span class="val-text"><?php echo $c['mother_full_name'] ?: '---'; ?></span>
                </div>

                <div class="info-field full">
                    <div class="label-group"><span class="lbl-o">Maqaa Guutuu Abbaa / የአባት ሙሉ ስም</span><span class="lbl-a">Father's Full Name</span></div>
                    <span class="val-text"><?php echo $c['father_full_name'] ?: "{$c['mname']} {$c['lname']}"; ?></span>
                </div>

                <div class="info-field double">
                    <div class="label-group"><span class="lbl-o">Guyyaa Galmeeffame / የተመዘገበበት ቀን</span><span class="lbl-a">Date Registered</span></div>
                    <span class="val-text"><?php echo getOromoMonth($c['issue_date']); ?> <?php echo date('d', strtotime($c['issue_date'])); ?>, <?php echo date('Y', strtotime($c['issue_date'])); ?></span>
                </div>
                <div class="info-field">
                    <div class="label-group"><span class="lbl-o">Guyyaa Kenname / የተሰጠበት ቀን</span><span class="lbl-a">Date Issued</span></div>
                    <span class="val-text"><?php echo date('d/m/Y', strtotime($c['issue_date'])); ?></span>
                </div>
            </div>
        </div>

        <!-- ═══ FOOTER ═══ -->
        <div class="footer-section">
            <div class="qr-bar-box">
                <div id="qrcode-cert"></div>
                <svg id="barcode-cert"></svg>
            </div>

            <div class="stamp-area">KEBELE<br>OFFICIAL<br>SEAL</div>

            <div class="sig-block">
                <div class="sig-line">
                    <span style="font-weight:800; color:#111; font-size:13px;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Official Registrar'); ?></span><br>
                    <span class="sig-label">Registrar / Authorized Officer</span><br>
                    <span style="font-size:9px; color:#666;">የመዝጋቢው ሙሉ ስም እና ፊርማ</span>
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
        const qrData = "CERT: <?php echo $c['cert_number']; ?>\nType: Birth\nName: <?php echo $c['fname'] . ' ' . $c['mname'] . ' ' . $c['lname']; ?>\nDate: <?php echo $c['issue_date']; ?>\nKebele: <?php echo $c['kebele']; ?>";
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
