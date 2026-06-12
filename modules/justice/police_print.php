<?php
// modules/justice/police_print.php — Print Police ID Card (same format as resident ID)
session_start();
require_once __DIR__ . '/../../config/database.php';
if (!isset($_SESSION['user_id'])) exit('Unauthorized');

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("
    SELECT pic.*, pr.badge_number, pr.rank, pr.station_assignment, pr.weapon_serial,
           i.fname, i.mname, i.lname, i.phot, i.s, ag.bdate, ag.age,
           ad.kebele, ad.city, ad.pho_no, h.hnum
    FROM police_id_cards pic
    JOIN police_records pr ON pic.police_record_id = pr.id
    JOIN individuals i ON pr.individual_id = i.id
    LEFT JOIN ages ag ON i.id = ag.id
    LEFT JOIN addresses ad ON i.id = ad.id
    LEFT JOIN houses h ON h.owner_individual_id = i.id
    WHERE pic.id = ?
");
$stmt->execute([$id]);
$card = $stmt->fetch();
if (!$card) exit('Police ID Card not found.');
?>
<!DOCTYPE html>
<html lang="om">
<head>
    <meta charset="UTF-8">
    <title>Police ID — <?php echo $card['id_num']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; display: flex; flex-direction: column; align-items: center; padding: 40px; gap: 30px; }
        .no-print { display: flex; gap: 15px; }
        .btn { padding: 12px 28px; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer; border: none; text-decoration: none; transition: all 0.3s; }
        .btn-primary { background: #1b4332; color: white; box-shadow: 0 4px 12px rgba(27,67,50,0.35); }
        .btn-secondary { background: white; color: #374151; border: 1px solid #d1d5db; }

        /* ══ POLICE ID CARD SHELL ══ */
        .id-card { width: 640px; height: 400px; border-radius: 16px; overflow: hidden; background: #fff; position: relative; box-shadow: 0 20px 50px rgba(0,0,0,0.25); border: 1px solid rgba(0,0,0,0.1); }
        .card-bg-overlay { position: absolute; inset: 0; background: radial-gradient(circle at 10% 10%, rgba(27,67,50,0.04) 0%, transparent 40%), radial-gradient(circle at 90% 90%, rgba(253,185,19,0.04) 0%, transparent 40%); z-index: 0; pointer-events: none; }
        .card-watermark { position: absolute; top: 55%; left: 55%; transform: translate(-50%, -50%); width: 320px; height: auto; opacity: 0.06; z-index: 1; pointer-events: none; filter: grayscale(20%); }

        /* ══ POLICE HEADER ══ */
        .card-header { background: #1b4332; height: 95px; position: relative; z-index: 10; display: flex; align-items: center; justify-content: center; border-bottom: 4px solid #FDB913; }
        .card-header::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #000 33%, #d32f2f 33%, #d32f2f 66%, #fff 66%); } /* Oromia Flag top stripe */
        .flag-box { position: absolute; top: 50%; transform: translateY(-50%); padding: 0 15px; margin-top: 3px; }
        .flag-box.left { left: 0; }
        .flag-box.right { right: 0; }
        .flag-box img { height: 68px; width: 68px; object-fit: contain; background: #fff; border: 2px solid #FDB913; border-radius: 50%; box-shadow: 0 4px 10px rgba(0,0,0,0.3); padding: 2px; }
        .header-text { text-align: center; color: #fff; margin-top: 5px; }
        .text-region { font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 13px; color: #FDB913; margin-top: 2px; text-transform: uppercase; letter-spacing: 0.5px; }
        .text-region-sub { font-family: 'Outfit', sans-serif; font-size: 10px; font-weight: 700; color: #fff; text-transform: uppercase; }
        .text-type { font-size: 11px; font-weight: 800; margin-top: 4px; letter-spacing: 1.5px; color: #fff; text-transform: uppercase; background: rgba(0,0,0,0.2); display: inline-block; padding: 2px 10px; border-radius: 12px; }

        /* ══ BODY ══ */
        .card-body { position: relative; z-index: 10; display: flex; padding: 20px; height: calc(400px - 95px - 35px); background: url('data:image/svg+xml;utf8,<svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"><g fill="%231b4332" fill-opacity="0.03" fill-rule="evenodd"><path d="M0 40L40 0H20L0 20M40 40V20L20 40"/></g></svg>'); }
        .left-col { width: 140px; display: flex; flex-direction: column; align-items: center; gap: 10px; }
        .photo-wrap { padding: 4px; background: #fff; border: 2px solid #1b4332; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border-radius: 8px; }
        .photo-wrap img { width: 110px; height: 130px; object-fit: cover; border-radius: 4px; display: block; }
        .id-label-tag { background: #1b4332; color: #FDB913; font-size: 8px; font-weight: 800; padding: 2px 10px; border-radius: 4px; text-transform: uppercase; letter-spacing: 1px; }

        .right-col { flex: 1; padding-left: 15px; display: flex; flex-direction: column; gap: 7px; }
        .info-row { display: flex; border-bottom: 1px solid #f3f4f6; padding-bottom: 4px; }
        .info-lbl { width: 135px; font-size: 8px; font-weight: 700; color: #6b7280; text-transform: uppercase; }
        .info-val { font-size: 12px; font-weight: 700; color: #111; }
        .double-row { display: flex; gap: 15px; }

        /* ══ FOOTER ══ */
        .card-footer { height: 35px; background: #0a2e1a; border-top: 2px solid #FDB913; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; position: relative; z-index: 10; }
        .id-number-txt { font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 14px; color: #FDB913; letter-spacing: 1px; }
        .validity-txt { font-size: 9px; font-weight: 600; color: rgba(255,255,255,0.7); }
        .validity-txt span { color: #FDB913; font-weight: 700; }

        /* ══ VISUAL ASSETS ══ */
        .v-stack { position: absolute; right: 25px; top: 110px; display: flex; flex-direction: column; align-items: center; gap: 6px; }
        #qrcode img { width: 55px !important; height: 55px !important; padding: 3px; background: #fff; border: 1px solid #1b4332; }
        #barcode { width: 90px; height: 30px; }
        .sig-container { text-align: center; width: 100px; margin-top: 5px; }
        .sig-line-only { border-bottom: 1.5px solid #1b4332; width: 100%; margin-bottom: 4px; }
        .sig-lbl { font-size: 7px; font-weight: 700; color: #1b4332; text-transform: uppercase; margin-top: 2px; }

        @media print {
            @page { size: portrait; margin: 0; }
            body, html { margin: 0 !important; padding: 0 !important; background: white !important; overflow: hidden !important; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-primary">🖨 Print Police ID Card</button>
        <a href="police_list.php" class="btn btn-secondary">← Back to List</a>
    </div>

    <div class="id-card">
        <div class="card-bg-overlay"></div>
        <img src="/Bosa Addis/assets/images/logo of bosa addis.jpg" class="card-watermark" alt="Watermark">

        <!-- HEADER -->
        <div class="card-header">
            <div class="flag-box left">
                <img src="/Bosa Addis/assets/img/oromia police logo.jpg" alt="Police Logo">
            </div>
            <div class="header-text">
                <div class="text-region">MOOTUMMAA NAANNOO OROMIYAA</div>
                <div class="text-region-sub">የኦሮሚያ ብሔራዊ ክልላዊ መንግሥት</div>
                <div class="text-type">🛡 POLICE ID CARD 🛡</div>
                <div style="font-size:8px;color:rgba(255,255,255,0.85);letter-spacing:1px;margin-top:2px;text-transform:uppercase;">Waraqaa Eenyummaa Poolisii / የፖሊስ መታወቂያ</div>
            </div>
            <div class="flag-box right">
                <img src="/Bosa Addis/assets/img/oromia police logo.jpg" alt="Police Logo">
            </div>
        </div>

        <!-- BODY -->
        <div class="card-body">
            <div class="left-col">
                <div class="photo-wrap">
                    <img src="../../assets/images/<?php echo $card['phot']; ?>"
                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($card['fname'].'+'.$card['lname']); ?>&size=200&background=1b4332&color=FDB913&bold=true'">
                </div>
                <div class="id-label-tag">🔰 Police Photo</div>
            </div>

            <div class="right-col">
                <div class="info-row">
                    <div class="info-lbl">Maqaa / ስም / Name:</div>
                    <div class="info-val" style="text-transform:uppercase;"><?php echo htmlspecialchars("{$card['fname']} {$card['mname']} {$card['lname']}"); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-lbl">Badge / ባጅ:</div>
                    <div class="info-val" style="color:#1b4332;"><?php echo htmlspecialchars($card['badge_number']); ?></div>
                </div>
                <div class="double-row">
                    <div class="info-row" style="flex:1;">
                        <div class="info-lbl">Saala / ጾታ / Sex:</div>
                        <div class="info-val"><?php echo ($card['s'] == 'Male' ? 'Dhiira / M' : 'Dubartii / F'); ?></div>
                    </div>
                    <div class="info-row" style="flex:1;">
                        <div class="info-lbl">G.Dhal / DOB:</div>
                        <div class="info-val"><?php echo $card['bdate'] ? date('d/m/Y', strtotime($card['bdate'])) : '—'; ?></div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-lbl">Sadarkaa / Rank / ደረጃ:</div>
                    <div class="info-val" style="color:#1b4332;"><?php echo htmlspecialchars($card['rank']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-lbl">Station / Posting:</div>
                    <div class="info-val"><?php echo htmlspecialchars($card['station_assignment']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-lbl">Address / Teessoo:</div>
                    <div class="info-val"><?php echo ($card['kebele'] ? $card['kebele'].', ' : '') . ($card['city'] ?: 'Bosa Addis'); ?> (H.No: <?php echo $card['hnum'] ?: '—'; ?>)</div>
                </div>
                <div class="info-row">
                    <div class="info-lbl">Phone / Bilbila:</div>
                    <div class="info-val"><?php echo $card['pho_no'] ?: '—'; ?></div>
                </div>
            </div>

            <!-- QR, Barcode & Signature -->
            <div class="v-stack">
                <div id="qrcode"></div>
                <svg id="barcode"></svg>
                <div class="sig-container">
                    <div class="sig-line-only"></div>
                    <div style="font-style:italic;font-size:11px;color:#1b4332;font-weight:700;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Registrar'); ?></div>
                    <div class="sig-lbl">Authorized Signature</div>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="card-footer">
            <div class="id-number-txt"><?php echo $card['id_num']; ?></div>
            <div class="validity-txt">
                ISSUED: <span><?php echo date('d/m/Y', strtotime($card['issue_date'])); ?></span>
                &nbsp;|&nbsp;
                EXPIRY: <span><?php echo date('d/m/Y', strtotime($card['expiry_date'])); ?></span>
            </div>
        </div>
    </div>

    <script>
        new QRCode(document.getElementById("qrcode"), {
            text: "POLICE-ID: <?php echo $card['id_num']; ?>\nName: <?php echo $card['fname'].' '.$card['lname']; ?>\nBadge: <?php echo $card['badge_number']; ?>\nRank: <?php echo $card['rank']; ?>\nValid Until: <?php echo $card['expiry_date']; ?>",
            width: 64, height: 64,
            colorDark: "#1b4332", colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });

        JsBarcode("#barcode", "<?php echo $card['id_num']; ?>", {
            format: "CODE128", width: 1, height: 30,
            displayValue: false, margin: 0, lineColor: "#1b4332"
        });

        // Print scaling
        (function() {
            var _saved = '';
            function applyScale() {
                var w = document.querySelector('.id-card');
                if (!w) return;
                _saved = w.getAttribute('style') || '';
                var cw = w.scrollWidth, ch = w.scrollHeight;
                var scale = Math.min(793 / cw, 1122 / ch);
                w.style.cssText = ['position:fixed','top:0','left:0','width:'+cw+'px','height:'+ch+'px','margin:0','box-shadow:none','overflow:hidden','transform-origin:top left','transform:scale('+scale+')'].join('!important;')+'!important';
            }
            function removeScale() { var w = document.querySelector('.id-card'); if(w) w.setAttribute('style', _saved); }
            window.addEventListener('beforeprint', applyScale);
            window.addEventListener('afterprint', removeScale);
        })();
    </script>
</body>
</html>
