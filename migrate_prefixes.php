<?php
require 'config/database.php';

try {
    $pdo->beginTransaction();

    // 1. Update id_cards (HM/2026/0001 -> IB0001)
    $stmt = $pdo->query("SELECT id, id_num FROM id_cards WHERE id_num LIKE 'HM/%'");
    while ($row = $stmt->fetch()) {
        $parts = explode('/', $row['id_num']);
        $num = intval(end($parts));
        $new_id = "IB" . str_pad($num - 1, 4, '0', STR_PAD_LEFT);
        
        $upd = $pdo->prepare("UPDATE id_cards SET id_num = ? WHERE id = ?");
        $upd->execute([$new_id, $row['id']]);
        echo "Updated ID Card: {$row['id_num']} -> $new_id\n";
    }

    // 2. Update vital_certificates - birth (BC/2026/0001 -> IB-BC00)
    $stmt = $pdo->query("SELECT id, cert_number FROM vital_certificates WHERE cert_type = 'birth' AND cert_number LIKE 'BC/%'");
    while ($row = $stmt->fetch()) {
        $parts = explode('/', $row['cert_number']);
        $num = intval(end($parts));
        $new_cert = "IB-BC" . str_pad($num - 1, 2, '0', STR_PAD_LEFT);
        
        $upd = $pdo->prepare("UPDATE vital_certificates SET cert_number = ? WHERE id = ?");
        $upd->execute([$new_cert, $row['id']]);
        echo "Updated Birth Cert: {$row['cert_number']} -> $new_cert\n";
    }

    // 3. Update vital_certificates - death (DC/2026/0001 -> IB-DC00)
    $stmt = $pdo->query("SELECT id, cert_number FROM vital_certificates WHERE cert_type = 'death' AND cert_number LIKE 'DC/%'");
    while ($row = $stmt->fetch()) {
        $parts = explode('/', $row['cert_number']);
        $num = intval(end($parts));
        $new_cert = "IB-DC" . str_pad($num - 1, 2, '0', STR_PAD_LEFT);
        
        $upd = $pdo->prepare("UPDATE vital_certificates SET cert_number = ? WHERE id = ?");
        $upd->execute([$new_cert, $row['id']]);
        echo "Updated Death Cert: {$row['cert_number']} -> $new_cert\n";
    }

    $pdo->commit();
    echo "Migration completed successfully.\n";
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "Error during migration: " . $e->getMessage() . "\n";
}
?>
