# Ifa Bula Kebele  Resident Data Management System

This system is a comprehensive web-based solution designed to digitize the resident record-keeping process for Ifa Bula Kebele , replacing manual paper-based methods.

## 🚀 Key Features

### 1. Secure Authentication
-   **Admin Login**: Secure entry point for authorized personnel.
-   **Session Management**: Ensures data security and controlled access.
-   **Default Credentials**: 
    -   **Username**: `admin`
    -   **Password**: `admin123`

### 2. Comprehensive Dashboard
-   **Insights at a Glance**: Visual summary cards for total residents, houses, families, and issued ID cards.
-   **Recent Activity**: Quick view of the latest registered residents.

### 3. Resident Management (Individual)
-   **Full CRUD**: Create, Read, Update, and Delete resident profiles.
-   **Detailed Profiles**: Tracks everything from education and occupation to birth details and mother's name.
-   **Photo Support**: Upload and display resident photos.

### 4. Housing & Family Tracking
-   **House Management**: Register housing units with area and door details.
-   **Family Grouping**: Organize residents into family units with designated leaders.
-   **One-to-Many Relationships**: Residents are mapped to specific houses and families.

### 5. Automated ID Card System
-   **Eligibility Engine**: Automatically lists residents aged 18+ eligible for identification.
-   **Smart Generation**: Generates unique ID numbers (e.g., `IB-2026-XXXX`).
-   **Print-Ready Cards**: Modern ID card design with resident photo and details, ready to print directly from the browser.

### 6. Analytical Reports
-   **Data Visualization**: Reports on population by gender, age groups, and location.
-   **Export Options**:
    -   **PDF**: Via browser print functionality.
    -   **Excel**: Instant download of report data for administrative use.

---

## 🛠 Technology Stack
-   **Frontend**: HTML5, CSS3, BootStrap 5, FontAwesome 6, JavaScript.
-   **Backend**: PHP 8.
-   **Database**: MySQL with PDO (PHP Data Objects) for secure SQL interactions.

---

## ⚙️ Setup Instructions (Local Machine)

1.  **Install XAMPP**: Ensure you have XAMPP (or WAMP) installed with PHP 8+ and MySQL.
2.  **Move Files**: Place the `ifa-bula-system` folder inside your `C:\xampp\htdocs\` directory.
3.  **Database Setup**:
    -   Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
    -   Create a new database named `kebele_system`.
    -   Import the `database.sql` file provided in the project root.
4.  **Configuration**: Verify the database credentials in `config/database.php`.
5.  **Initial Data**:
    -   Run `http://localhost/ifa-bula-system/seed.php` once to populate the system with example data for testing.
6.  **Access System**:
    -   Visit `http://localhost/ifa-bula-system/` in your browser.   http://localhost/Ifa%20Bula/
    -   Log in with `admin` / `admin_password`.

---

## 📂 Project Structure
```text
/ifa-bula-system
├── /assets           # CSS, JS, and uploaded images
├── /auth             # Login and Logout logic
├── /config           # Database connection
├── /includes         # Header, Footer, and Sidebar templates
├── /modules          # Core system modules (Residents, Houses, etc.)
├── dashboard.php     # Main admin panel
├── database.sql      # SQL schema export
├── index.php         # Entry redirector
└── seed.php          # Data population utility
```
