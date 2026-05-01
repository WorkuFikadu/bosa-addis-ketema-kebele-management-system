-- fix_vital_module.sql
USE kebele_system;

-- 1. Create vital_certificates table
CREATE TABLE IF NOT EXISTS vital_certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    cert_type ENUM('birth', 'death') NOT NULL,
    cert_number VARCHAR(50) NOT NULL UNIQUE,
    issue_date DATE NOT NULL,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES individuals(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 2. Add missing columns to individuals table
ALTER TABLE individuals 
ADD COLUMN IF NOT EXISTS status ENUM('alive', 'deceased') DEFAULT 'alive',
ADD COLUMN IF NOT EXISTS death_date DATE NULL,
ADD COLUMN IF NOT EXISTS death_reason TEXT NULL;
