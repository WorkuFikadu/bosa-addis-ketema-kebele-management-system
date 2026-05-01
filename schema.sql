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
    zon VARCHAR(45) NOT NULL,
    city VARCHAR(45) NOT NULL,
    keb VARCHAR(45) NOT NULL,
    pho_no VARCHAR(20) NOT NULL,
    email VARCHAR(50) NOT NULL,
    FOREIGN KEY (id) REFERENCES individuals(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS houses (
    hnum INT PRIMARY KEY,
    area DOUBLE NOT NULL,
    door INT NOT NULL,
    own_id INT NOT NULL,
    FOREIGN KEY (own_id) REFERENCES individuals(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS families (
    hnum INT PRIMARY KEY,
    lead_id INT NOT NULL,
    fam_no INT NOT NULL,
    FOREIGN KEY (hnum) REFERENCES houses(hnum) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES individuals(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS id_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL UNIQUE,
    id_number VARCHAR(50) NOT NULL UNIQUE,
    issue_date DATE NOT NULL,
    FOREIGN KEY (resident_id) REFERENCES individuals(id) ON DELETE CASCADE
);

-- Default admin: admin / admin123
INSERT IGNORE INTO users (username, password, role) VALUES ('admin', '$2y$10$wODZ6U.z7POrT.r2O8eLueHwK5tL5cIOGJ3W4E.QEZ1PzvO.Q5r/u', 'admin');
