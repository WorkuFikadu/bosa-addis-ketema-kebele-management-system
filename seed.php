<?php
require_once 'config/database.php';

echo "<h2>Database Seeding Utility</h2>";

try {
    // 1. Clear existing data (optional, but good for clean seed)
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("TRUNCATE TABLE id_cards");
    $pdo->exec("TRUNCATE TABLE residents");
    $pdo->exec("TRUNCATE TABLE families");
    $pdo->exec("TRUNCATE TABLE houses");
    $pdo->exec("TRUNCATE TABLE addresses");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "Existing data cleared.<br>";

    // 2. Seed Houses
    $houses = [
        ['hnum' => 101, 'area' => 120.5, 'door' => 2, 'owner_id' => 'OWN-001'],
        ['hnum' => 102, 'area' => 85.0, 'door' => 1, 'owner_id' => 'OWN-002'],
        ['hnum' => 103, 'area' => 200.0, 'door' => 4, 'owner_id' => 'OWN-003'],
        ['hnum' => 104, 'area' => 150.0, 'door' => 3, 'owner_id' => 'OWN-004'],
    ];

    $h_stmt = $pdo->prepare("INSERT INTO houses (hnum, area, door, owner_id) VALUES (?, ?, ?, ?)");
    foreach ($houses as $h) {
        $h_stmt->execute([$h['hnum'], $h['area'], $h['door'], $h['owner_id']]);
    }
    echo "Houses seeded.<br>";

    // 3. Seed Families
    $families = [
        ['fam_no' => 1, 'lead_id' => 'L-101', 'hnum' => 101],
        ['fam_no' => 2, 'lead_id' => 'L-102', 'hnum' => 102],
        ['fam_no' => 3, 'lead_id' => 'L-103', 'hnum' => 103],
    ];

    $f_stmt = $pdo->prepare("INSERT INTO families (fam_no, lead_id, hnum) VALUES (?, ?, ?)");
    foreach ($families as $f) {
        $f_stmt->execute([$f['fam_no'], $f['lead_id'], $f['hnum']]);
    }
    echo "Families seeded.<br>";

    // 4. Seed Residents
    $residents = [
        [
            'fname' => 'Abebe', 'lname' => 'Bikila', 'mname' => 'Sara', 'bdate' => '1985-05-12', 'age' => 39,
            'sex' => 'M', 'marital_status' => 'Married', 'level_edu' => 'Bachelor Degree', 'relg' => 'Orthodox',
            'nat' => 'Ethiopian', 'occ' => 'Engineer', 'pho_no' => '0911223344', 'email' => 'abebe@example.com',
            'hnum' => 101, 'fam_no' => 1
        ],
        [
            'fname' => 'Marta', 'lname' => 'Tola', 'mname' => 'Genet', 'bdate' => '1990-08-20', 'age' => 34,
            'sex' => 'F', 'marital_status' => 'Married', 'level_edu' => 'Master Degree', 'relg' => 'Protestant',
            'nat' => 'Ethiopian', 'occ' => 'Doctor', 'pho_no' => '0922334455', 'email' => 'marta@example.com',
            'hnum' => 101, 'fam_no' => 1
        ],
        [
            'fname' => 'Chala', 'lname' => 'Guta', 'mname' => 'Lemat', 'bdate' => '2005-01-15', 'age' => 19,
            'sex' => 'M', 'marital_status' => 'Single', 'level_edu' => 'High School', 'relg' => 'Muslim',
            'nat' => 'Ethiopian', 'occ' => 'Student', 'pho_no' => '0933445566', 'email' => 'chala@example.com',
            'hnum' => 102, 'fam_no' => 2
        ],
        [
            'fname' => 'Zenebe', 'lname' => 'Worku', 'mname' => 'Aster', 'bdate' => '1960-11-30', 'age' => 63,
            'sex' => 'M', 'marital_status' => 'Widowed', 'level_edu' => 'Elementary', 'relg' => 'Orthodox',
            'nat' => 'Ethiopian', 'occ' => 'Retired', 'pho_no' => '0944556677', 'email' => 'zenebe@example.com',
            'hnum' => 103, 'fam_no' => 3
        ]
    ];

    $addr_stmt = $pdo->prepare("INSERT INTO addresses (region, zone, city, kebele, pho_no, email) VALUES (?, ?, ?, ?, ?, ?)");
    $res_stmt = $pdo->prepare("INSERT INTO residents (fname, lname, mname, bdate, age, sex, marital_status, level_edu, relg, nat, occ, pho_no, email, address_id, hnum, fam_no) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($residents as $r) {
        $addr_stmt->execute(['Oromia', 'Jimma', 'Jimma City', 'Bosa Addis Kebele ', $r['pho_no'], $r['email']]);
        $address_id = $pdo->lastInsertId();
        
        $res_stmt->execute([
            $r['fname'], $r['lname'], $r['mname'], $r['bdate'], $r['age'], $r['sex'], $r['marital_status'],
            $r['level_edu'], $r['relg'], $r['nat'], $r['occ'], $r['pho_no'], $r['email'], $address_id, $r['hnum'], $r['fam_no']
        ]);
    }
    echo "Residents seeded.<br>";
    
    echo "<br><strong style='color:green;'>Seeding completed successfully!</strong>";
    echo "<br><a href='index.php'>Go to Dashboard</a>";

} catch (PDOException $e) {
    echo "<br><strong style='color:red;'>Error during seeding:</strong> " . $e->getMessage();
}
?>
