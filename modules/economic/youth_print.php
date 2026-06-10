<?php
// modules/economic/youth_print.php — Official Youth ID (Resident ID Format)
session_start();
require_once __DIR__ . '/../../config/database.php';
if (!isset($_SESSION['user_id'])) exit('Unauthorized');

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("
    SELECT yc.*, ey.education_level, ey.skills, ey.employment_status, ey.preferred_sector, ey.training_interests,
           i.fname, i.mname, i.lname, i.phot, i.s, i.mar, i.occ, i.id as i_id,
           ag.bdate, ag.age, ad.kebele, ad.city, ad.pho_no, h.hnum
    FROM youth_id_cards yc
    JOIN economic_youth_registry ey ON yc.youth_record_id = ey.id
    JOIN individuals i ON ey.individual_id = i.id
    LEFT JOIN ages ag ON i.id = ag.id
    LEFT JOIN addresses ad ON i.id = ad.id
    LEFT JOIN houses h ON h.owner_individual_id = i.id
    WHERE yc.id = ?
");
$stmt->execute([$id]);
$card = $stmt->fetch();
if (!$card) exit('Youth ID Card not found.');

$fullName = strtoupper($card['fname'] . ' ' . $card['mname'] . ' ' . $card['lname']);
?>
<!DOCTYPE html>
<html lang="om">
<head>
    <meta charset="UTF-8">
    <title>Youth ID - <?php echo $card['id_num']; ?></title>
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
        .btn-green { background: #16a34a; color: white; box-shadow: 0 4px 12px rgba(22,163,74,0.3); }
        .btn-secondary { background: white; color: #374151; border: 1px solid #d1d5db; }

        /* ══ ID CARD SHELL (RESIDENT FORMAT) ══ */
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

        .card-bg-overlay {
            position: absolute;
            inset: 0;
            background: 
                radial-gradient(circle at 10% 10%, rgba(22,163,74,0.03) 0%, transparent 40%),
                radial-gradient(circle at 90% 90%, rgba(22,163,74,0.03) 0%, transparent 40%);
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
            opacity: 0.05;
            z-index: 1;
            pointer-events: none;
        }

        /* ══ HEADER (GREEN THEME) ══ */
        .card-header {
            background: #16a34a;
            height: 95px;
            position: relative;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 4px solid #fbbf24;
        }
        .card-header::before { 
            content: ""; position: absolute; top: 0; left: 0; right: 0; height: 6px; 
            background: linear-gradient(90deg, #000 33%, #d32f2f 33%, #d32f2f 66%, #fff 66%); 
        }

        .flag-box {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            padding: 0 15px;
            margin-top: 3px;
        }
        .flag-box.left { left: 0; }
        .flag-box.right { right: 0; }
        .flag-box img {
            height: 68px;
            width: 68px;
            object-fit: contain;
            background: #fff;
            border: 2px solid #fbbf24;
            border-radius: 50%;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            padding: 2px;
        }

        .header-text {
            text-align: center;
            color: #fff;
            line-height: 1.2;
        }
        .text-region { font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 12px; color: #fff; text-transform: uppercase; }
        .text-type { font-size: 10px; font-weight: 600; opacity: 0.9; margin-top: 4px; letter-spacing: 1px; color: #dcfce7; }

        /* ══ BODY ══ */
        .card-body {
            position: relative;
            z-index: 10;
            display: flex;
            padding: 20px;
            height: calc(400px - 95px - 35px);
            background: url('data:image/svg+xml;utf8,<svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"><g fill="%2316a34a" fill-opacity="0.03" fill-rule="evenodd"><path d="M0 40L40 0H20L0 20M40 40V20L20 40"/></g></svg>');
        }

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
        .photo-wrap img { width: 110px; height: 140px; object-fit: cover; border-radius: 4px; }
        .id-label-tag {
            background: #16a34a;
            color: #fff;
            font-size: 8px;
            font-weight: 800;
            padding: 2px 10px;
            border-radius: 4px;
            text-transform: uppercase;
        }

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
            color: #065f46;
            text-transform: uppercase;
        }
        .info-val { font-size: 13px; font-weight: 700; color: #111; }

        .double-row { display: flex; gap: 15px; }

        /* ══ FOOTER ══ */
        .card-footer {
            height: 35px;
            background: #f0fdf4;
            border-top: 1px solid #dcfce7;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 10;
        }
        .id-number-txt { font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 14px; color: #16a34a; letter-spacing: 1px; }
        .validity-txt { font-size: 9px; font-weight: 600; color: #064e3b; }

        /* ══ ASSETS ══ */
        .v-stack {
            position: absolute;
            right: 25px;
            top: 110px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }
        #qrcode img { width: 55px !important; height: 55px !important; padding: 3px; background: #fff; border: 1px solid #16a34a; }
        #barcode { width: 90px; height: 30px; }

        .sig-container { text-align: center; width: 100px; margin-top: 5px; }
        .sig-line-only { border-bottom: 1.5px solid #16a34a; width: 100%; margin-bottom: 4px; }
        .sig-lbl { font-size: 8px; font-weight: 700; color: #064e3b; text-transform: uppercase; }

        @media print {
            body { background: none; padding: 0; }
            .no-print { display: none; }
            .id-card { box-shadow: none; border: 1px solid #000; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-green">🖨 Print Official Youth ID</button>
        <a href="youth_list.php" class="btn btn-secondary">Back to List</a>
    </div>

    <div class="id-card">
        <div class="card-bg-overlay"></div>
        <img src="/Bosa Addis/assets/images/logo of bosa addis.jpg" class="card-watermark">

        <div class="card-header">
            <div class="flag-box left"><img src="/Bosa Addis/assets/img/oromia.JPG" alt="Oromia State Logo"></div>
            <div class="header-text">
                <div class="text-region" style="font-size:11px; margin-top:3px;">WALDAA DARGAGGOO BULCHIINSA MAGAALAA JIMMAA</div>
                <div class="text-region" style="font-size:9px;">JIMMA CITY ADMINISTRATION YOUTH ASSOCIATION</div>
                <div class="text-type" style="background: rgba(0,0,0,0.2); padding: 2px 10px; border-radius: 12px; display: inline-block; margin-top:5px;">Youth Empowerment ID | Waraqaa Eenyummaa</div>
            </div>
            <div class="flag-box right"><img src="/Bosa Addis/assets/img/oromia youth logo.jpg" alt="Youth Logo"></div>
        </div>

        <div class="card-body">
            <div class="left-col">
                <div class="photo-wrap">
                    <img src="../../assets/images/<?php echo $card['phot'] ?: 'default_profile.png'; ?>" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($card['fname'].'+'.$card['lname']); ?>&size=200&background=dc2626&color=fff&bold=true'">
                </div>
                <div class="id-label-tag">Youth Member</div>
            </div>

            <div class="right-col">
                <div class="info-row">
                    <div class="info-lbl">Maqaa / Name:</div>
                    <div class="info-val" style="text-transform:uppercase;"><?php echo $fullName; ?></div>
                </div>
                <div class="info-row">
                    <div class="info-lbl">Gender / Saala:</div>
                    <div class="info-val"><?php echo ($card['s'] == 'Male' ? 'MALE / DHIIRA' : 'FEMALE / DUBARTII'); ?></div>
                </div>
                <div class="double-row">
                    <div class="info-row" style="flex:1;">
                        <div class="info-lbl">DOB / G.Dhal:</div>
                        <div class="info-val"><?php echo $card['bdate'] ? date('d/m/Y', strtotime($card['bdate'])) : '—'; ?></div>
                    </div>
                    <div class="info-row" style="flex:1;">
                        <div class="info-lbl">Resident ID:</div>
                        <div class="info-val">#<?php echo $card['i_id']; ?></div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-lbl">Address / Teessoo:</div>
                    <div class="info-val"><?php echo ($card['kebele'] ? $card['kebele'].', ' : '') . ($card['city'] ?: 'Bosa Addis'); ?> (H.No: <?php echo $card['hnum'] ?: '—'; ?>)</div>
                </div>
                <div class="info-row">
                    <div class="info-lbl">Phone / Bilbila:</div>
                    <div class="info-val"><?php echo $card['pho_no'] ?: '—'; ?></div>
                </div>
                <div class="info-row">
                    <div class="info-lbl">Target Sector / Sector Target:</div>
                    <div class="info-val"><?php echo $card['preferred_sector'] ?: 'General Economic'; ?></div>
                </div>
                <div class="info-row">
                    <div class="info-lbl">Skills / Ogummaa:</div>
                    <div class="info-val"><?php echo $card['skills'] ?: 'Unskilled'; ?></div>
                </div>
                <div class="info-row">
                    <div class="info-lbl">Training Goal / Galma:</div>
                    <div class="info-val"><?php echo $card['training_interests'] ?: 'Capacity Building'; ?></div>
                </div>
                <div class="info-row">
                    <div class="info-lbl">Education / Barnoota:</div>
                    <div class="info-val"><?php echo $card['education_level']; ?></div>
                </div>
            </div>

            <div class="v-stack">
                <div id="qrcode"></div>
                <svg id="barcode"></svg>
                <div class="sig-container">
                    <div class="sig-line-only"></div>
                    <div class="sig-lbl">Auth. Signature</div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <div class="id-number-txt"><?php echo $card['id_num']; ?></div>
            <div class="validity-txt">EXPIRY: <span><?php echo date('d/m/Y', strtotime($card['expiry_date'])); ?></span></div>
        </div>
    </div>

    <script>
        const qrData = "YOUTH-ID: <?php echo $card['id_num']; ?>\nName: <?php echo $fullName; ?>\nSkill: <?php echo $card['skills']; ?>";
        new QRCode(document.getElementById("qrcode"), { text: qrData, width: 60, height: 60, colorDark: "#16a34a" });
        JsBarcode("#barcode", "<?php echo $card['id_num']; ?>", { format: "CODE128", width: 1, height: 30, displayValue: false, lineColor: "#16a34a" });
    </script>
</body>
</html>
