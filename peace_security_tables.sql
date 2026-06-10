USE kebele_system;

-- 1. Police Registration Table
CREATE TABLE IF NOT EXISTS police_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    individual_id INT NOT NULL,
    badge_number VARCHAR(50) NOT NULL UNIQUE,
    rank VARCHAR(50) NOT NULL,
    station_assignment VARCHAR(100) NOT NULL,
    weapon_serial VARCHAR(100) DEFAULT NULL,
    status ENUM('Active', 'Suspended', 'Retired', 'Transferred') DEFAULT 'Active',
    joined_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (individual_id) REFERENCES individuals(id) ON DELETE CASCADE
);

-- 2. Milisha Registration Table
CREATE TABLE IF NOT EXISTS milisha_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    individual_id INT NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'Member',
    zone_assigned VARCHAR(100) NOT NULL,
    weapon_serial VARCHAR(100) DEFAULT NULL,
    status ENUM('Active', 'Inactive', 'Dismissed') DEFAULT 'Active',
    joined_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (individual_id) REFERENCES individuals(id) ON DELETE CASCADE
);

-- 3. Gachana Sirna Registration Table
CREATE TABLE IF NOT EXISTS gachana_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    individual_id INT NOT NULL,
    committee_role VARCHAR(50) NOT NULL DEFAULT 'Member',
    sector VARCHAR(100) NOT NULL,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    joined_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (individual_id) REFERENCES individuals(id) ON DELETE CASCADE
);

-- 4. Social Court Cases Table
CREATE TABLE IF NOT EXISTS court_cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_number VARCHAR(50) NOT NULL UNIQUE,
    plaintiff_name VARCHAR(100) NOT NULL,
    defendant_name VARCHAR(100) NOT NULL,
    plaintiff_id INT DEFAULT NULL,
    defendant_id INT DEFAULT NULL,
    case_type ENUM('Civil', 'Boundary', 'Family', 'Minor Criminal', 'Other') NOT NULL,
    description TEXT NOT NULL,
    status ENUM('Open', 'In Progress', 'Resolved', 'Appealed', 'Dismissed') DEFAULT 'Open',
    verdict TEXT DEFAULT NULL,
    filed_date DATE NOT NULL,
    resolved_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (plaintiff_id) REFERENCES individuals(id) ON DELETE SET NULL,
    FOREIGN KEY (defendant_id) REFERENCES individuals(id) ON DELETE SET NULL
);
