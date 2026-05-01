-- Create Database
CREATE DATABASE IF NOT EXISTS kebele_system;
USE kebele_system;

-- Clear existing tables if they exist (in correct order)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS id_cards;
DROP TABLE IF EXISTS residents;
DROP TABLE IF EXISTS families;
DROP TABLE IF EXISTS houses;
DROP TABLE IF EXISTS addresses;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Address Table
CREATE TABLE addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    region VARCHAR(50) NOT NULL,
    zone VARCHAR(50) NOT NULL,
    city VARCHAR(50) NOT NULL,
    kebele VARCHAR(50) NOT NULL,
    pho_no VARCHAR(20),
    email VARCHAR(100)
) ENGINE=InnoDB;

-- House Table
CREATE TABLE houses (
    hnum INT PRIMARY KEY,
    area DOUBLE NOT NULL,
    door INT NOT NULL,
    owner_id VARCHAR(45) NOT NULL
) ENGINE=InnoDB;

-- Family Table
CREATE TABLE families (
    fam_no INT PRIMARY KEY,
    lead_id VARCHAR(40) NOT NULL,
    hnum INT NOT NULL,
    CONSTRAINT fk_family_house FOREIGN KEY (hnum) REFERENCES houses(hnum) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Residents Table
CREATE TABLE residents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fname VARCHAR(45) NOT NULL,
    lname VARCHAR(45) NOT NULL,
    mname VARCHAR(45) NOT NULL,
    bdate DATE NOT NULL,
    age INT NOT NULL,
    sex ENUM('M', 'F') NOT NULL,
    marital_status VARCHAR(20) NOT NULL,
    level_edu VARCHAR(45) NOT NULL,
    relg VARCHAR(30) NOT NULL,
    nat VARCHAR(30) NOT NULL,
    occ VARCHAR(50) NOT NULL,
    pho_no VARCHAR(20) NOT NULL,
    email VARCHAR(50) NOT NULL,
    phot VARCHAR(255) DEFAULT 'default.png',
    address_id INT,
    hnum INT,
    fam_no INT,
    CONSTRAINT fk_resident_address FOREIGN KEY (address_id) REFERENCES addresses(id) ON DELETE SET NULL,
    CONSTRAINT fk_resident_house FOREIGN KEY (hnum) REFERENCES houses(hnum) ON DELETE SET NULL,
    CONSTRAINT fk_resident_family FOREIGN KEY (fam_no) REFERENCES families(fam_no) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ID Cards Table
CREATE TABLE id_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL UNIQUE,
    id_num VARCHAR(50) NOT NULL UNIQUE,
    issue_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    CONSTRAINT fk_id_resident FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Insert Default Admin (Password: admin123)
INSERT INTO users (username, password, role) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

