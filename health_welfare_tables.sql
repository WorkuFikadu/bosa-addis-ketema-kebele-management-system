USE kebele_system;

-- 1. Health Extension Records
CREATE TABLE IF NOT EXISTS health_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    individual_id INT NOT NULL,
    service_type ENUM('Vaccination', 'Maternal Health', 'General Checkup', 'Clinic Referral') NOT NULL,
    service_date DATE NOT NULL,
    notes TEXT,
    staff_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (individual_id) REFERENCES individuals(id) ON DELETE CASCADE
);

-- 2. Social Welfare Records
CREATE TABLE IF NOT EXISTS welfare_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    individual_id INT NOT NULL,
    vulnerability_type ENUM('Elderly', 'Disabled', 'Orphan', 'Low Income', 'Displaced') NOT NULL,
    disability_details VARCHAR(255),
    aid_status ENUM('Registered', 'Receiving Aid', 'Waitlist', 'Graduated') DEFAULT 'Registered',
    aid_type VARCHAR(100), -- e.g., Monthly stipend, Food basket
    next_review_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (individual_id) REFERENCES individuals(id) ON DELETE CASCADE
);

-- 3. Sanitation Campaigns
CREATE TABLE IF NOT EXISTS sanitation_campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_name VARCHAR(255) NOT NULL,
    campaign_date DATE NOT NULL,
    zone VARCHAR(100),
    status ENUM('Planned', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Planned',
    participants_est INT DEFAULT 0,
    impact_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. PSNP (Safety Net) Records
CREATE TABLE IF NOT EXISTS safetynet_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    individual_id INT NOT NULL,
    enrollment_date DATE NOT NULL,
    household_size INT DEFAULT 1,
    transfer_type ENUM('Cash', 'Food', 'Mixed') NOT NULL,
    work_status ENUM('Public Work', 'Direct Support') NOT NULL,
    payment_status ENUM('Up to date', 'Pending', 'Overdue') DEFAULT 'Up to date',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (individual_id) REFERENCES individuals(id) ON DELETE CASCADE
);
