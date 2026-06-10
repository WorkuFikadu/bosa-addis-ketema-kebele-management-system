CREATE DATABASE IF NOT EXISTS kebele_system;
USE kebele_system;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS individuals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fname VARCHAR(45) NOT NULL,
    lname VARCHAR(45) NOT NULL,
    mname VARCHAR(45) NOT NULL,
    mar VARCHAR(20) NOT NULL,
    s VARCHAR(6) NOT NULL,
    nat VARCHAR(30) NOT NULL,
    level_edu VARCHAR(45) NOT NULL,
    relg VARCHAR(30) NOT NULL,
    occ VARCHAR(50) NOT NULL,
    phot VARCHAR(255) DEFAULT 'default_profile.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ages (
    id INT PRIMARY KEY,
    bdate DATE NOT NULL,
    age INT NOT NULL,
    FOREIGN KEY (id) REFERENCES individuals(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS addresses (
    id INT PRIMARY KEY,
    region VARCHAR(45) NOT NULL,
    zone VARCHAR(45) NOT NULL,
    city VARCHAR(45) NOT NULL,
    kebele VARCHAR(45) NOT NULL,
    pho_no VARCHAR(20) NOT NULL,
    email VARCHAR(50) NOT NULL,
    FOREIGN KEY (id) REFERENCES individuals(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS houses (
    hnum INT PRIMARY KEY,
    area DOUBLE NOT NULL,
    door INT NOT NULL DEFAULT 1,
    owner_id VARCHAR(45) NOT NULL,
    owner_individual_id INT,
    house_type VARCHAR(50) DEFAULT 'Residential',
    construction_type VARCHAR(100) DEFAULT 'Wood and Mud',
    rooms_count INT DEFAULT 1,
    floor_type VARCHAR(50) DEFAULT 'Earth',
    roof_type VARCHAR(50) DEFAULT 'CIS',
    has_water ENUM('Yes', 'No') DEFAULT 'No',
    has_electricity ENUM('Yes', 'No') DEFAULT 'No',
    toilet_type VARCHAR(50) DEFAULT 'None',
    constructed_year INT DEFAULT NULL,
    block_no VARCHAR(50) DEFAULT NULL,
    FOREIGN KEY (owner_individual_id) REFERENCES individuals(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS families (
    hnum INT PRIMARY KEY,
    lead_id INT NOT NULL,
    fam_no INT NOT NULL,
    family_type VARCHAR(50) DEFAULT 'Nuclear',
    income_category VARCHAR(50) DEFAULT 'Low',
    social_status VARCHAR(50) DEFAULT 'Permanent Resident',
    total_males INT DEFAULT 0,
    total_females INT DEFAULT 0,
    disabled_members INT DEFAULT 0,
    orphans_count INT DEFAULT 0,
    has_pension ENUM('Yes', 'No') DEFAULT 'No',
    is_vulnerable ENUM('Yes', 'No') DEFAULT 'No',
    registration_date DATE DEFAULT NULL,
    FOREIGN KEY (hnum) REFERENCES houses(hnum) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES individuals(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS id_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL UNIQUE,
    id_num VARCHAR(50) NOT NULL UNIQUE,
    issue_date DATE NOT NULL,
    FOREIGN KEY (resident_id) REFERENCES individuals(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_number VARCHAR(20) NOT NULL UNIQUE,
    resident_id INT,
    subject VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES individuals(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    body TEXT NOT NULL,
    category VARCHAR(50) DEFAULT 'General',
    is_pinned BOOLEAN DEFAULT FALSE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    type VARCHAR(50) NOT NULL,
    message VARCHAR(255) NOT NULL,
    link VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Default admin: admin / admin123
INSERT IGNORE INTO users (username, password, role) VALUES ('admin', '$2y$10$wODZ6U.z7POrT.r2O8eLueHwK5tL5cIOGJ3W4E.QEZ1PzvO.Q5r/u', 'admin');
