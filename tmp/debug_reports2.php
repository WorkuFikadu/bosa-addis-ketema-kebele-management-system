<?php
require_once __DIR__ . '/../config/database.php';

$queries = array(
    '1. Sex Distribution' => "SELECT s, COUNT(*) as count FROM individuals GROUP BY s",
    '2. Age Distribution' => "SELECT CASE WHEN age < 18 THEN 'Under 18' WHEN age BETWEEN 18 AND 35 THEN '18-35' WHEN age BETWEEN 36 AND 60 THEN '36-60' ELSE '60+' END as age_group, COUNT(*) as count FROM ages GROUP BY age_group",
    '3. Status Stats' => "SELECT status, COUNT(*) as count FROM individuals GROUP BY status",
    '4. House Stats' => "SELECT COUNT(*) as total_houses, AVG(area) as avg_area FROM houses",
    '5. Family Stats' => "SELECT COUNT(*) as total_families, SUM(fam_no) as total_people FROM families",
    '6. Vital Stats' => "SELECT cert_type, COUNT(*) as count FROM vital_certificates GROUP BY cert_type",
    '7. ID Stats' => "SELECT COUNT(*) as total_ids, SUM(CASE WHEN expiry_date >= CURDATE() THEN 1 ELSE 0 END) as active_ids, SUM(CASE WHEN expiry_date < CURDATE() THEN 1 ELSE 0 END) as expired_ids FROM id_cards",
    '8. Financial Stats' => "SELECT payment_method, COUNT(*) as count, SUM(amount) as total FROM transactions GROUP BY payment_method",
    '9. Education' => "SELECT level_edu, COUNT(*) as count FROM individuals GROUP BY level_edu ORDER BY count DESC",
    '10. Religion' => "SELECT relg, COUNT(*) as count FROM individuals GROUP BY relg ORDER BY count DESC",
    '11. House Type' => "SELECT house_type, COUNT(*) as count FROM houses GROUP BY house_type",
    '12. Water Stats' => "SELECT has_water, COUNT(*) as count FROM houses GROUP BY has_water",
);

foreach ($queries as $name => $sql) {
    try {
        $result = $pdo->query($sql)->fetchAll();
        echo "OK: $name (" . count($result) . " rows)\n";
    } catch (PDOException $e) {
        echo "FAIL: $name => " . $e->getMessage() . "\n";
    }
}
