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
    <style>
        @page { size: landscape; margin: 0; }
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; margin: 0; padding: 0; }
        
        .certificate-wrapper {
            width: 1120px;
            height: 790px;
            margin: 20px auto;
            background: white;
            padding: 15px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .border-outer {
            border: 12px solid #3b4486;
            height: 100%;
            padding: 20px;
            position: relative;
            background-image: 
                linear-gradient(45deg, #f8f9fa 25%, transparent 25%), 
                linear-gradient(-45deg, #f8f9fa 25%, transparent 25%), 
                linear-gradient(45deg, transparent 75%, #f8f9fa 75%), 
                linear-gradient(-45deg, transparent 75%, #f8f9fa 75%);
            background-size: 20px 20px;
            background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .resident-photo {
            width: 110px;
            height: 130px;
            border: 2px solid #3b4486;
            object-fit: cover;
            border-radius: 4px;
        }

        .flags-section {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .flag { width: 80px; height: 50px; border: 1px solid #ddd; }

        .header-text {
            text-align: center;
            flex-grow: 1;
        }

        .header-text h6 {
            color: #3b4486;
            font-weight: 700;
            margin-bottom: 2px;
            font-size: 14px;
        }

        .header-text h4 {
            color: #3b4486;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            margin-top: 10px;
            font-size: 24px;
            border-bottom: 2px solid #3b4486;
            display: inline-block;
            padding-bottom: 5px;
        }

        .serial-box {
            text-align: right;
            font-weight: bold;
            color: #3b4486;
        }

        .info-grid {
            margin-top: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px 30px;
        }

        .field {
            border-bottom: 1px dotted #888;
            padding-bottom: 2px;
            position: relative;
            margin-bottom: 10px;
        }

        .label-om { font-size: 11px; color: #555; font-weight: 600; display: block; }
        .label-am { font-size: 11px; color: #555; font-weight: 600; display: block; margin-top: -2px; }
        .value { 
            position: absolute; 
            bottom: 2px; 
            left: 170px; 
            font-weight: 700; 
            color: #111;
            font-size: 14px;
            color: #1a237e;
        }

        .field-full { grid-column: span 3; }
        .field-half { grid-column: span 1.5; }

        .footer-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .signature-area {
            width: 250px;
            text-align: center;
            border-top: 2px solid #3b4486;
            padding-top: 10px;
            font-size: 12px;
            color: #3b4486;
            font-weight: bold;
        }

        .stamp-circle {
            width: 120px;
            height: 120px;
            border: 2px dashed #3b4486;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #3b4486;
            font-size: 10px;
            opacity: 0.3;
            transform: rotate(-15deg);
        }

        @media print {
            body { background: white; }
            .certificate-wrapper { margin: 0; box-shadow: none; border: none; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print p-3 text-center bg-dark">
        <button onclick="window.print()" class="btn btn-primary px-5"><i class="fas fa-print me-2"></i>Print Official Birth Certificate</button>
        <a href="index.php" class="btn btn-outline-light ms-2">Back to Records</a>
    </div>

    <div class="certificate-wrapper">
        <div class="border-outer">
            <!-- Header Section -->
            <div class="header-content">
                <img src="../../assets/images/<?php echo $c['phot']; ?>" class="resident-photo" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($c['fname']); ?>&size=128'">
                
                <div class="header-text">
                    <div class="d-flex justify-content-between align-items-center mb-3 px-5">
                        <img src="/Ifa Bula/assets/img/ethiopia_flag.png" class="flag">
                        <img src="/Ifa Bula/assets/img/oromia_flag.png" class="flag">
                    </div>
                    <h6>Fedaraalawaa Dimokiraatawaa Rippaablika Itoophiyaatti Galmeessa Ragaalee Bu'uraa Hawaasummaa</h6>
                    <h6>በኢትዮጵያ ፌዴራላዊ ዲሞክራሲያዊ ሪፐብሊክ የወሳኝ ኩነቶች ምዝገባ ኤጀንሲ</h6>
                    <h4>Waraqaa Ragaa Dhalootaa / የልደት ምስክር ወረቀት</h4>
                </div>

                <div class="serial-box">
                    Serial No: <span class="text-danger"><?php echo str_replace('IB-BC', '', $c['cert_number']); ?></span>
                </div>
            </div>

            <!-- Fields Grid -->
            <div class="info-grid mt-4">
                <div class="field">
                    <span class="label-om">Maqaa Da'imaa</span>
                    <span class="label-am">የህፃኑ ስም</span>
                    <span class="value"><?php echo $c['fname']; ?></span>
                </div>
                <div class="field">
                    <span class="label-om">Maqaa Abbaa</span>
                    <span class="label-am">የአባት ስም</span>
                    <span class="value"><?php echo $c['mname']; ?></span>
                </div>
                <div class="field">
                    <span class="label-om">Maqaa Akaakayyuu</span>
                    <span class="label-am">የአያት ስም</span>
                    <span class="value"><?php echo $c['lname']; ?></span>
                </div>

                <div class="field">
                    <span class="label-om">Koorniyaa</span>
                    <span class="label-am">ጾታ</span>
                    <span class="value"><?php echo ($c['s'] == 'Male' ? 'Dhiira' : 'Dubartii'); ?> / <?php echo ($c['s'] == 'Male' ? 'ወንድ' : 'ሴት'); ?></span>
                </div>
                <div class="field" style="grid-column: span 2;">
                    <span class="label-om">Guyyaa Dhalootaa (Jii, Guy, Bar)</span>
                    <span class="label-am">የትውልድ ቀን (ወር፣ ቀን፣ ዓ.ም)</span>
                    <span class="value"><?php echo getOromoMonth($c['bdate']); ?> <?php echo date('d', strtotime($c['bdate'])); ?>, <?php echo date('Y', strtotime($c['bdate'])); ?> / <?php echo getAmharicMonth($c['bdate']); ?> <?php echo date('d', strtotime($c['bdate'])); ?>, <?php echo date('Y', strtotime($c['bdate'])); ?></span>
                </div>

                <div class="field field-full">
                    <span class="label-om">Iddoo Dhalootaa (Godi, Aanaa, Ganda)</span>
                    <span class="label-am">የትውልድ ቦታ (ዞን፣ ወረዳ፣ ቀበሌ)</span>
                    <span class="value"><?php echo "{$c['zone']}, {$c['city']}, {$c['kebele']}"; ?></span>
                </div>

                <div class="field field-full">
                    <span class="label-om">Lammummaa</span>
                    <span class="label-am">ዜግነት</span>
                    <span class="value"><?php echo $c['nat']; ?> / ኢትዮጵያዊ</span>
                </div>

                <div class="field field-full">
                    <span class="label-om">Maqaa Guutuu Haadhaa</span>
                    <span class="label-am">የእናት ሙሉ ስም</span>
                    <span class="value"><?php echo $c['mother_full_name'] ?: '---'; ?></span>
                </div>
                <div class="field field-full">
                    <span class="label-om">Lammummaa Haadhaa</span>
                    <span class="label-am">የእናት ዜግነት</span>
                    <span class="value"><?php echo $c['mother_nat'] ?: 'Itoophiyaa'; ?></span>
                </div>

                <div class="field field-full">
                    <span class="label-om">Maqaa Guutuu Abbaa</span>
                    <span class="label-am">የአባት ሙሉ ስም</span>
                    <span class="value"><?php echo $c['father_full_name'] ?: "{$c['mname']} {$c['lname']}"; ?></span>
                </div>

                <div class="field" style="grid-column: span 2;">
                    <span class="label-om">Dhalootichi Kan Galmeeffame (Jii, Guy, Bar)</span>
                    <span class="label-am">ልደቱ የተመዘገበበት (ወር፣ ቀን፣ ዓ.ም)</span>
                    <span class="value"><?php echo getOromoMonth($c['issue_date']); ?> <?php echo date('d', strtotime($c['issue_date'])); ?>, <?php echo date('Y', strtotime($c['issue_date'])); ?></span>
                </div>
                <div class="field">
                    <span class="label-om">Guyyaa Ragaa Kenname</span>
                    <span class="label-am">የምስክር ወረቀቱ የተሰጠበት ቀን</span>
                    <span class="value"><?php echo date('d/m/Y', strtotime($c['issue_date'])); ?></span>
                </div>
            </div>

            <!-- Footer Section -->
            <div class="footer-section">
                <div class="stamp-circle">KEBELE OFFICIAL SEAL</div>
                <div class="signature-area">
                    Maqaa fi Mallattoo Qondaala Galmeessaa<br>
                    የመዝጋቢው ሙሉ ስም እና ፊርማ
                </div>
            </div>
        </div>
    </div>
</body>
</html>
