<?php
// seed_data.php
require_once __DIR__ . '/config/database.php';

try {
    $pdo->exec("USE kebele_system");

    // Sample Residents
    $residents = [
        ['Estiphanos', 'Alemu', 'Abebe', 'Single', 'Male', 'Ethiopian', 'Degree', 'Orthodox', 'Software Engineer'],
        ['Felmeta', 'Tola', 'Gudina', 'Married', 'Male', 'Ethiopian', 'Degree', 'Protestant', 'Civil Engineer'],
        ['Fereha', 'Ali', 'Mohammed', 'Single', 'Female', 'Ethiopian', 'Masters', 'Muslim', 'Data Analyst'],
        ['Feyera', 'Ayalew', 'Bekele', 'Widowed', 'Male', 'Ethiopian', 'Grade 12', 'Orthodox', 'Merchant'],
        ['Fikru', 'Seli', 'Desta', 'Single', 'Male', 'Ethiopian', 'Diploma', 'Protestant', 'Technician']
    ];

    $stmt = $pdo->prepare("INSERT INTO individuals (fname, lname, mname, mar, s, nat, level_edu, relg, occ, phot) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'default.png')");
    
    foreach ($residents as $r) {
        $stmt->execute($r);
        $id = $pdo->lastInsertId();

        // Seed Age
        $bdate = '199' . mt_rand(0, 9) . '-' . str_pad(mt_rand(1, 12), 2, '0', STR_PAD_LEFT) . '-' . str_pad(mt_rand(1, 28), 2, '0', STR_PAD_LEFT);
        $age = date_diff(date_create($bdate), date_create('today'))->y;
        $pdo->prepare("INSERT INTO ages (id, bdate, age) VALUES (?, ?, ?)")->execute([$id, $bdate, $age]);

        // Seed Address
        $pdo->prepare("INSERT INTO addresses (id, region, zon, city, keb, pho_no, email) VALUES (?, 'Oromia', 'Jimma', 'Jimma City', 'Ifa Bula Kebele ', ?, ?)")
            ->execute([$id, '09' . mt_rand(11111111, 99999999), strtolower($r[0]) . '@example.com']);
    }

    // Seed some Houses
    $pdo->exec("INSERT IGNORE INTO houses (hnum, area, door, own_id) VALUES 
        (101, 150.5, 2, 1),
        (202, 200.0, 3, 2),
        (303, 120.0, 1, 3)");

    // Seed some Families
    $pdo->exec("INSERT IGNORE INTO families (hnum, lead_id, fam_no) VALUES 
        (101, 1, 4),
        (202, 2, 6)");

    echo "<h1>Seeding Successful!</h1>";
    echo "<p>Sample data has been added to the Kebele system.</p>";
    echo "<a href='dashboard.php'>Go to Dashboard</a>";

} catch (PDOException $e) {
    echo "Seeding failed: " . $e->getMessage();
}
?>
