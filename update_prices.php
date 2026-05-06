<?php
require 'config/database.php';

try {
    // 1. Update ID Card price
    $stmt = $pdo->prepare("UPDATE service_prices SET price_etb = 500 WHERE service_key = 'id_card'");
    $stmt->execute();
    
    // 2. Update Certificate prices
    $certificates = ['birth_cert', 'death_cert', 'clearance_cert', 'marriage_cert', 'divorce_cert'];
    foreach ($certificates as $cert) {
        $stmt = $pdo->prepare("UPDATE service_prices SET price_etb = 400 WHERE service_key = ?");
        $stmt->execute([$cert]);
    }
    
    echo "Prices updated successfully! ID Cards: 500 ETB, Certificates: 400 ETB.";
} catch (PDOException $e) {
    echo "Error updating prices: " . $e->getMessage();
}
?>
