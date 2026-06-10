<?php
require 'config/database.php';

try {
    // 1. Change role column to VARCHAR
    $pdo->exec("ALTER TABLE users MODIFY COLUMN role VARCHAR(50) DEFAULT 'staff'");
    echo "Users table modified.\n";

    // 2. Create system_roles table
    $pdo->exec("CREATE TABLE IF NOT EXISTS system_roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        role_name VARCHAR(100) NOT NULL,
        role_key VARCHAR(50) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "system_roles table created.\n";

    // 3. Seed default roles
    $roles = [
        ['Administrator', 'admin', 'Full system access'],
        ['Secretary', 'secretary', 'Standard office tasks and record management'],
        ['Data Clerk', 'clerk', 'Data entry and record verification'],
        ['Manager', 'manager', 'Supervisory role'],
        ['Security Committee', 'security', 'Access control and security monitoring']
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO system_roles (role_name, role_key, description) VALUES (?, ?, ?)");
    foreach ($roles as $r) {
        $stmt->execute($r);
    }
    echo "Default roles seeded.\n";

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
