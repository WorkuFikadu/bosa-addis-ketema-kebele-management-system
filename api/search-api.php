<?php
// api/search-api.php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$query = trim($_GET['q'] ?? '');
if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$results = [];

// 1. Search Residents
$stmt = $pdo->prepare("
    SELECT i.id, i.fname, i.lname, i.mname, a.pho_no 
    FROM individuals i 
    LEFT JOIN addresses a ON i.id = a.id 
    WHERE i.fname LIKE ? OR i.lname LIKE ? OR a.pho_no LIKE ? LIMIT 5
");
$q = "%$query%";
$stmt->execute([$q, $q, $q]);
while ($row = $stmt->fetch()) {
    $results[] = [
        'title' => htmlspecialchars($row['fname'] . ' ' . $row['mname'] . ' ' . $row['lname']),
        'desc' => 'Resident | Phone: ' . htmlspecialchars($row['pho_no'] ?? 'N/A'),
        'url' => '/Bosa Addis/modules/residents/view.php?id=' . $row['id'],
        'icon' => 'fa-user'
    ];
}

// 2. Search ID Cards
$stmt = $pdo->prepare("
    SELECT i.id_num, ind.fname, ind.mname, ind.lname 
    FROM id_cards i 
    JOIN individuals ind ON i.resident_id = ind.id 
    WHERE i.id_num LIKE ? LIMIT 3
");
$stmt->execute([$q]);
while ($row = $stmt->fetch()) {
    $results[] = [
        'title' => 'ID: ' . htmlspecialchars($row['id_num']),
        'desc' => 'Card Holder: ' . htmlspecialchars($row['fname'] . ' ' . $row['lname']),
        'url' => '/Bosa Addis/modules/id_cards/',
        'icon' => 'fa-id-card'
    ];
}

// 3. Search Houses
$stmt = $pdo->prepare("SELECT hnum, block_no FROM houses WHERE hnum LIKE ? OR block_no LIKE ? LIMIT 3");
$stmt->execute([$q, $q]);
while ($row = $stmt->fetch()) {
    $results[] = [
        'title' => 'House #' . htmlspecialchars($row['hnum']),
        'desc' => 'Property / Block: ' . htmlspecialchars($row['block_no'] ?? 'N/A'),
        'url' => '/Bosa Addis/modules/houses/',
        'icon' => 'fa-home'
    ];
}

echo json_encode($results);
