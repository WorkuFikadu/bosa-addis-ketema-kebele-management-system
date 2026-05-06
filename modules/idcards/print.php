<?php
// modules/idcards/print.php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT ic.*, i.*, ag.age, ag.bdate, ad.kebele, ad.city, ad.zone, ad.region, h.hnum 
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
            background: #e8ecf0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 100vh;
            padding: 30px;
            gap: 24px;
        }

        .no-print {
            display: flex;
            gap: 12px;
        }
        .btn {
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary { background: #006400; color: white; }
        .btn-secondary { background: #fff; color: #333; border: 1px solid #ccc; }

        /* ══ Card Shell ══ */
        .id-card {
            width: 640px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            border: 2px solid #2e7d32;
            font-size: 11px;
        }

        /* ══ Top Header Band ══ */
        .card-header {
            background: linear-gradient(135deg, #0d1b2a 0%, #1a2a4a 50%, #0f2040 100%);
            padding: 0;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 82px;
            border-bottom: 3px solid #2563eb;
        }

        /* Flag panels — absolute corners */
        .header-flag-panel {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            padding: 0 12px;
        }
        .header-flag-panel.left  { left: 0; }
        .header-flag-panel.right { right: 0; }
        .header-flag-panel img {
            height: 50px;
            border-radius: 3px;
            border: 2px solid rgba(255,255,255,0.35);
            box-shadow: 0 3px 12px rgba(0,0,0,0.5);
        }

        /* Center title */
        .header-center {
            text-align: center;
            padding: 12px 100px;
            line-height: 1.4;
        }
        .header-center .line-om {
            color: #ffffff;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 13.5px;
            letter-spacing: 0.5px;
        }
        .header-center .line-title-om {
            color: #93c5fd;
            font-size: 11px;
            font-weight: 600;
        }
        .header-center .line-am {
            color: #bfdbfe;
            font-size: 12px;
            font-weight: 700;
            margin-top: 2px;
        }
        .header-center .line-sub {
            color: #7dd3fc;
            font-size: 9px;
            font-weight: 500;
            margin-top: 3px;
        }
        .header-center .card-title-bar {
            background: rgba(37,99,235,0.4);
            border: 1px solid rgba(147,197,253,0.4);
            margin-top: 6px;
            padding: 3px 16px;
            border-radius: 20px;
            display: inline-block;
            color: #e0f2fe;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        /* ══ Card Body ══ */
        .card-body {
            display: flex;
            background: #fff;
        }

        /* Photo column */
        .photo-col {
            width: 120px;
            flex-shrink: 0;
            background: #f0f4f8;
            border-right: 2px solid #0d1b2a;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: flex-start;
            padding: 14px 10px 10px;
            gap: 6px;
        }
        .photo-col img {
            width: 90px;
            height: 110px;
            object-fit: cover;
            border-radius: 3px;
            border: 3px solid #0d1b2a;
            display: block;
        }
        .photo-label {
            font-size: 7.5px;
            color: #0d1b2a;
            text-align: left;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            border-top: 1.5px solid #0d1b2a;
            padding-top: 4px;
            width: 90px;
        }
        .v-box {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            width: 90px;
        }
        #qrcode img {
            width: 60px !important;
            height: 60px !important;
            padding: 2px;
            background: white;
            border: 1px solid #0d1b2a;
        }
        #barcode {
            width: 100px;
            height: 30px;
        }

        /* Info column */
        .info-col {
            flex: 1;
            padding: 12px 14px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .field-row {
            display: flex;
            align-items: baseline;
            border-bottom: 1px dotted #c8e6c9;
            padding-bottom: 5px;
            gap: 0;
        }
        .field-row:last-child { border-bottom: none; }

        .field-label {
            min-width: 110px;
        }
        .field-label .lbl-om {
            display: block;
            font-size: 9.5px;
            font-weight: 700;
            color: #1b5e20;
        }
        .field-label .lbl-am {
            display: block;
            font-size: 8.5px;
            color: #555;
            font-weight: 500;
        }
        .field-value {
            font-size: 12px;
            font-weight: 700;
            color: #111;
            padding-left: 6px;
        }

        /* Two-column sub-row */
        .row-double {
            display: flex;
            gap: 12px;
        }
        .row-double .field-row {
            flex: 1;
        }

        /* ══ Card Footer ══ */
        .card-footer {
            background: #1b5e20;
            padding: 7px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-number {
            font-family: 'Inter', monospace;
            font-size: 11px;
            font-weight: 800;
            color: #a5d6a7;
            letter-spacing: 1.5px;
        }
        .issue-info {
            font-size: 9px;
            color: #c8e6c9;
            text-align: right;
        }
        .issue-info span { font-weight: 700; color: #fff; }

        @media print {
            body { background: white; padding: 0; }
            .no-print { display: none !important; }
            .id-card { box-shadow: none; width: 100%; border-radius: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn btn-primary">&#128424; Waraqaa Maxxansi / Print ID</button>
        <a href="index.php" class="btn btn-secondary">&#8592; Deebi'i / Back</a>
    </div>

    <div class="id-card">

        <!-- ══ Header: Oromia flag LEFT · Title CENTER · Ethiopia flag RIGHT ══ -->
        <div class="card-header">
            <!-- Oromia flag — far left corner -->
            <div class="header-flag-panel left">
                <img src="/Ifa Bula/assets/img/oromia_flag.png" alt="Oromia">
            </div>

            <!-- Centered Title -->
            <div class="header-center">
                <div class="line-om">Bulchiinsa Mootummaa</div>
                <div class="line-om" style="font-size:17px;">Naannoo <em>Oromiyaa</em></div>
                <div class="line-am">የኦሮሚያ ብሔራዊ መንግሥት</div>
                <div class="line-sub">Waraqaa Eenyummaa Jiraattota &nbsp;|&nbsp; የነዋሪ መታወቂያ ምስክር ወረቀት</div>
                <span class="card-title-bar">Waraqaa Eenyummaa / መታወቂያ</span>
            </div>

            <!-- Ethiopia flag — far right corner -->
            <div class="header-flag-panel right">
                <img src="/Ifa Bula/assets/img/ethiopia_flag.png" alt="Itoophiyaa">
            </div>
        </div>

        <!-- ══ Body ══ -->
        <div class="card-body">

            <!-- Photo -->
            <div class="photo-col">
                <img
                    src="../../assets/images/<?php echo $card['phot']; ?>"
                    onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($card['fname'] . '+' . $card['lname']); ?>&size=200&background=2e7d32&color=fff&bold=true'"
                    alt="Suur'aa"
                >
                <div class="photo-label">Suur'aa / ፎቶ</div>
                <div class="v-box">
                    <div id="qrcode"></div>
                    <svg id="barcode"></svg>
                </div>
            </div>

            <!-- Fields -->
            <div class="info-col">

                <!-- Full Name -->
                <div class="field-row">
                    <div class="field-label">
                        <span class="lbl-om">Maqaa Guutuu</span>
                        <span class="lbl-am">ሙሉ ስም</span>
                    </div>
                    <div class="field-value"><?php echo $card['fname'] . ' ' . $card['mname'] . ' ' . $card['lname']; ?></div>
                </div>

                <!-- Mother's Name -->
                <div class="field-row">
                    <div class="field-label">
                        <span class="lbl-om">M. Haadhaa</span>
                        <span class="lbl-am">የእናት ስም</span>
                    </div>
                    <div class="field-value"><?php echo $card['mother_full_name'] ?: '—'; ?></div>
                </div>

                <!-- Sex + DOB side by side -->
                <div class="row-double">
                    <div class="field-row">
                        <div class="field-label">
                            <span class="lbl-om">Saala</span>
                            <span class="lbl-am">ጾታ</span>
                        </div>
                        <div class="field-value">
                            <?php echo $card['s'] == 'Male' ? 'Dhiira / ወንድ' : 'Dubartii / ሴት'; ?>
                        </div>
                    </div>
                    <div class="field-row">
                        <div class="field-label">
                            <span class="lbl-om">Guy. Dhalootaa</span>
                            <span class="lbl-am">የልደት ቀን</span>
                        </div>
                        <div class="field-value">
                            <?php echo $card['bdate'] ? date('d/m/Y', strtotime($card['bdate'])) : '—'; ?>
                        </div>
                    </div>
                </div>

                <!-- Kebele + House No -->
                <div class="row-double">
                    <div class="field-row">
                        <div class="field-label">
                            <span class="lbl-om">Magaalaa / Ganda</span>
                            <span class="lbl-am">ቀበሌ / አካባቢ</span>
                        </div>
                        <div class="field-value">
                            <?php echo ($card['city'] ?: '—') . ' / ' . ($card['kebele'] ?: '—'); ?>
                        </div>
                    </div>
                    <div class="field-row" style="min-width: 130px;">
                        <div class="field-label">
                            <span class="lbl-om">Lakk. M.</span>
                            <span class="lbl-am">የቤት ቁጥር</span>
                        </div>
                        <div class="field-value"><?php echo $card['hnum'] ?? '—'; ?></div>
                    </div>
                </div>

                <!-- Emergency Contact / Occupation -->
                <div class="field-row">
                    <div class="field-label">
                        <span class="lbl-om">Hojii / Nationaliti</span>
                        <span class="lbl-am">ሙያ / ዜግነት</span>
                    </div>
                    <div class="field-value"><?php echo $card['occ']; ?> · <?php echo $card['nat']; ?></div>
                </div>

            </div>
        </div>

        <!-- ══ Footer ══ -->
        <div class="card-footer">
            <div class="card-number"><?php echo $card['id_num']; ?></div>
            <div class="issue-info">
                Guyyaa Kenname: <span><?php echo date('d/m/Y', strtotime($card['issue_date'])); ?></span><br>
                Hanga: <span><?php echo date('d/m/Y', strtotime($card['expiry_date'])); ?></span>
            </div>
        </div>

        </div>

    </div>

    <script>
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
            height: 25,
            displayValue: false,
            margin: 0,
            lineColor: "#0d1b2a"
        });
    </script>

</body>
</html>
