<?php
// modules/health/safetynet_print.php — Printable PSNP ID Card (Green/Social theme)
session_start();
require_once __DIR__ . '/../../config/database.php';
if (!isset($_SESSION['user_id'])) exit('Unauthorized');

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("
    SELECT sc.*, sn.household_size, sn.transfer_type, sn.work_status,
           i.fname, i.mname, i.lname, i.phot, i.s, ag.bdate,
           ad.kebele, ad.city, ad.pho_no, h.hnum
    FROM safetynet_id_cards sc
    JOIN safetynet_records sn ON sc.safetynet_record_id = sn.id
    JOIN individuals i ON sn.individual_id = i.id
    LEFT JOIN ages ag ON i.id = ag.id
    LEFT JOIN addresses ad ON i.id = ad.id
    LEFT JOIN houses h ON h.owner_individual_id = i.id
    WHERE sc.id = ?
");
$stmt->execute([$id]);
$card = $stmt->fetch();
if (!$card) exit('Safety Net ID Card not found.');
?>
<!DOCTYPE html>
<html lang="om">
<head>
    <meta charset="UTF-8">
    <title>PSNP ID — <?php echo $card['id_num']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #fff; display: flex; flex-direction: column; align-items: center; padding: 20px; }
        .no-print { margin-bottom: 20px; display: flex; gap: 10px; }
        .btn { padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; border: none; text-decoration: none; }
        .btn-green { background: #16a34a; color: white; }

        /* PSNP ID - Green Card */
        .id-card { width: 640px; height: 400px; border-radius: 12px; overflow: hidden; background: #fff; border: 1px solid #ddd; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .card-header { background: #16a34a; height: 95px; position: relative; z-index: 10; display: flex; align-items: center; justify-content: center; border-bottom: 4px solid #fbbf24; }
        .card-header::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #000 33%, #d32f2f 33%, #d32f2f 66%, #fff 66%); }
        
        .flag-box { position: absolute; top: 50%; transform: translateY(-50%); padding: 0 15px; margin-top: 3px; }
        .flag-box.left { left: 0; } .flag-box.right { right: 0; }
        .flag-box img { height: 68px; width: 68px; object-fit: contain; background: #fff; border: 2px solid #fbbf24; border-radius: 50%; box-shadow: 0 4px 10px rgba(0,0,0,0.3); padding: 2px; }
        
        .header-text { text-align: center; color: #fff; margin-top: 5px; }
        .text-region { font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 13px; color: #fbbf24; margin-top: 2px; text-transform: uppercase; letter-spacing: 0.5px; }
        .text-region-sub { font-family: 'Outfit', sans-serif; font-size: 10px; font-weight: 700; color: #fff; text-transform: uppercase; }
        .text-type { font-size: 11px; font-weight: 800; margin-top: 4px; letter-spacing: 1.5px; color: #fff; text-transform: uppercase; background: rgba(0,0,0,0.2); display: inline-block; padding: 2px 10px; border-radius: 12px; }

        .body { display: flex; padding: 15px; height: calc(400px - 95px - 40px); background: url('data:image/svg+xml;utf8,<svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"><g fill="%2316a34a" fill-opacity="0.03" fill-rule="evenodd"><path d="M0 40L40 0H20L0 20M40 40V20L20 40"/></g></svg>'); }
        .photo-area { width: 130px; display: flex; flex-direction: column; align-items: center; }
        .photo-area img { width: 110px; height: 130px; object-fit: cover; border: 2px solid #16a34a; border-radius: 6px; }
        .tag { background: #16a34a; color: #fff; font-size: 8px; font-weight: 800; padding: 3px 10px; border-radius: 4px; margin-top: 8px; }

        .info-area { flex: 1; padding-left: 20px; display: flex; flex-direction: column; gap: 8px; }
        .row-item { border-bottom: 1px solid #f0f0f0; padding-bottom: 4px; }
        .label { font-size: 8px; color: #666; font-weight: 700; text-transform: uppercase; }
        .value { font-size: 13px; font-weight: 700; color: #111; }

        .footer { background: #064e3b; height: 40px; border-top: 2px solid #fbbf24; color: #fbbf24; display: flex; justify-content: space-between; align-items: center; padding: 0 20px; }
        .id-num { font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 16px; }
        .dates { font-size: 9px; font-weight: 600; opacity: 0.8; }

        .extra-stack { position: absolute; right: 25px; top: 100px; display: flex; flex-direction: column; align-items: center; gap: 10px; }
        #qrcode img { width: 60px !important; height: 60px !important; border: 1px solid #16a34a; padding: 2px; }
        #barcode { width: 100px; height: 35px; }

        @media print { .no-print { display: none; } body { padding: 0; } .id-card { box-shadow: none; border: 1px solid #000; } }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-green">🖨 Print PSNP ID</button>
        <a href="safetynet_list.php" class="btn" style="background:#eee;color:#333;">Close</a>
    </div>

    <div class="id-card">
        <div class="card-header">
            <div class="flag-box left"><img src="/Bosa Addis/assets/img/oromia.JPG" alt="Oromia Regional Logo"></div>
            <div class="header-text">
                <div class="text-region" style="font-size:15px;">BULCHIINSA MAGAALAA JIMMAA</div>
                <div class="text-region-sub">JIMMA CITY ADMINISTRATION</div>
                <div class="text-type">SAFETY NET (PSNP) ID CARD</div>
            </div>
            <div class="flag-box right"><img src="/Bosa Addis/assets/images/logo of bosa addis.jpg" alt="Health/Bosa Logo"></div>
        </div>
        <div class="body">
            <div class="photo-area">
                <img src="../../assets/images/<?php echo $card['phot'] ?: 'default_profile.png'; ?>" onerror="this.src='/Bosa Addis/assets/images/default_profile.png'">
                <div class="tag">PARTICIPANT</div>
            </div>
            <div class="info-area">
                <div class="row-item">
                    <div class="label">Full Name / Maqaa Guutuu:</div>
                    <div class="value" style="text-transform:uppercase;"><?php echo htmlspecialchars("{$card['fname']} {$card['mname']} {$card['lname']}"); ?></div>
                </div>
                <div class="row-item">
                    <div class="label">Work Status / Hojii:</div>
                    <div class="value" style="color:#16a34a;"><?php echo htmlspecialchars($card['work_status']); ?></div>
                </div>
                <div class="row-item">
                    <div class="label">Support Type / Gargaarsa:</div>
                    <div class="value"><?php echo htmlspecialchars($card['transfer_type']); ?></div>
                </div>
                <div class="row-item">
                    <div class="label">Household Size / Miseensa Maatii:</div>
                    <div class="value"><?php echo $card['household_size']; ?> Persons</div>
                </div>
                <div class="row-item">
                    <div class="label">Address / Teessoo:</div>
                    <div class="value"><?php echo ($card['kebele'] ? $card['kebele'].', ' : '') . ($card['city'] ?: 'Bosa Addis'); ?> (H.No: <?php echo $card['hnum'] ?: '—'; ?>)</div>
                </div>
                <div class="row-item">
                    <div class="label">Phone / Bilbila:</div>
                    <div class="value"><?php echo $card['pho_no'] ?: '—'; ?></div>
                </div>
            </div>
            <div class="extra-stack">
                <div id="qrcode"></div>
                <svg id="barcode"></svg>
            </div>
        </div>
        <div class="footer">
            <div class="id-num"><?php echo $card['id_num']; ?></div>
            <div class="dates">ISSUED: <?php echo date('d/m/Y', strtotime($card['issue_date'])); ?> | EXP: <?php echo date('d/m/Y', strtotime($card['expiry_date'])); ?></div>
        </div>
    </div>

    <script>
        new QRCode(document.getElementById("qrcode"), {
            text: "PSNP-ID: <?php echo $card['id_num']; ?>\nName: <?php echo $card['fname'].' '.$card['lname']; ?>\nHold: <?php echo $card['household_size']; ?>",
            width: 60, height: 60, colorDark: "#064e3b"
        });
        JsBarcode("#barcode", "<?php echo $card['id_num']; ?>", { format: "CODE128", width: 1, height: 35, displayValue: false, lineColor: "#064e3b" });
    </script>
</body>
</html>
