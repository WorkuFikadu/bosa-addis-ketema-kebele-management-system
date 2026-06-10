USE kebele_system;

CREATE TABLE IF NOT EXISTS milisha_id_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    milisha_record_id INT NOT NULL UNIQUE,
    id_num VARCHAR(50) NOT NULL UNIQUE,
    issue_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    status ENUM('Active','Lost','Expired') DEFAULT 'Active',
    transaction_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (milisha_record_id) REFERENCES milisha_records(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS gachana_id_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gachana_record_id INT NOT NULL UNIQUE,
    id_num VARCHAR(50) NOT NULL UNIQUE,
    issue_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    status ENUM('Active','Lost','Expired') DEFAULT 'Active',
    transaction_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gachana_record_id) REFERENCES gachana_records(id) ON DELETE CASCADE
);

INSERT IGNORE INTO service_prices (service_key, service_name_en, price_etb) VALUES 
    ('milisha_id', 'Milisha ID Card Issuance', 20.00),
    ('gachana_id', 'Gachana Sirna ID Card Issuance', 15.00);
