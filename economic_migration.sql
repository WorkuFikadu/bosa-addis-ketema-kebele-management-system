USE kebele_system;

-- 1. Micro Enterprises (SME)
CREATE TABLE IF NOT EXISTS economic_enterprises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    business_name VARCHAR(255) NOT NULL,
    business_type ENUM('Retail', 'Manufacturing', 'Services', 'Agriculture', 'Cooperatives') NOT NULL,
    license_number VARCHAR(100),
    registration_date DATE NOT NULL,
    status ENUM('Active', 'Suspended', 'Closed') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES individuals(id) ON DELETE CASCADE
);

-- 2. Youth & Job Registry
CREATE TABLE IF NOT EXISTS economic_youth_registry (
    id INT AUTO_INCREMENT PRIMARY KEY,
    individual_id INT NOT NULL,
    education_level VARCHAR(100),
    skills TEXT,
    employment_status ENUM('Unemployed', 'Self-employed', 'Employed', 'Student') DEFAULT 'Unemployed',
    preferred_sector VARCHAR(100),
    registration_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (individual_id) REFERENCES individuals(id) ON DELETE CASCADE
);

-- 3. Subsidized Goods Distribution
CREATE TABLE IF NOT EXISTS economic_subsidies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    individual_id INT NOT NULL,
    item_type ENUM('Sugar', 'Oil', 'Wheat', 'Flour') NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit ENUM('Kg', 'Liters', 'Quintal') DEFAULT 'Kg',
    distribution_date DATE NOT NULL,
    collected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (individual_id) REFERENCES individuals(id) ON DELETE CASCADE
);

-- 4. Agriculture & Land Registry
CREATE TABLE IF NOT EXISTS economic_agriculture (
    id INT AUTO_INCREMENT PRIMARY KEY,
    land_owner_id INT NOT NULL,
    plot_number VARCHAR(100),
    land_size_sqm DECIMAL(10,2),
    land_use ENUM('Residential', 'Farmland', 'Commercial', 'Mixed') DEFAULT 'Farmland',
    fertilizer_received DECIMAL(10,2) DEFAULT 0.00, -- In Quintals
    seed_received DECIMAL(10,2) DEFAULT 0.00,       -- In Kg
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (land_owner_id) REFERENCES individuals(id) ON DELETE CASCADE
);
