-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: kebele_system
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `region` varchar(50) NOT NULL,
  `zone` varchar(50) NOT NULL,
  `city` varchar(50) NOT NULL,
  `kebele` varchar(50) NOT NULL,
  `pho_no` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `kebele_zone` tinyint(3) unsigned DEFAULT NULL COMMENT 'Kebele Zone number (1-5)',
  `garee` varchar(100) DEFAULT NULL COMMENT 'Garee (community group) name',
  `block` varchar(50) DEFAULT NULL COMMENT 'Block identifier within the kebele',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addresses`
--

LOCK TABLES `addresses` WRITE;
/*!40000 ALTER TABLE `addresses` DISABLE KEYS */;
INSERT INTO `addresses` VALUES (1,'Oromia','Jimma','Jimma City','Bosa Addis','0911223344','abebe@example.com',NULL,NULL,NULL),(2,'Oromia','Jimma','Jimma City','Bosa Addis','0922334455','marta@example.com',NULL,NULL,NULL),(3,'Oromia','Jimma','Jimma City','Bosa Addis','0933445566','chala@example.com',NULL,NULL,NULL),(4,'Oromia','Jimma','Jimma City','Bosa Addis','0944556677','zenebe@example.com',NULL,NULL,NULL),(8,'Oromia','Jimma','Jimma','Bosa Addis','0934953593','worku@gmail.com',NULL,NULL,NULL),(9,'Oromia','Jimma','Jimma','Bosa Addis','0919639519','woor@gmail.com',NULL,NULL,NULL),(10,'Amhara','Central Gonder','Gonder','Arbeya','0992450553','wube@gmail.com',NULL,NULL,NULL),(11,'Oromia','Jimma','Jimma','Bosa Addis','0992450553','workufikadu643@gmail.com',NULL,NULL,NULL),(12,'Oromia','Jimma','Jimma','Bosa Addis','0992450553','worku@gmail.com',NULL,NULL,NULL),(17,'External','Other','Other','Other','N/A','',NULL,NULL,NULL),(18,'External','Other','Other','Other','N/A','',NULL,NULL,NULL),(19,'Oromia','Jimma','Jimma','Bosa Addis','0911000000','mulatu@example.com',NULL,NULL,NULL),(20,'Oromia','Jimma','Jimma','Bosa Addis Kebele','+251919639519','workufikadu643@gmail.com',NULL,NULL,NULL),(21,'Oromia','Jimma','Jimma','Bosa Addis Kebele','+251919639519','worku@gmail.com',NULL,NULL,NULL),(22,'Oromia','Jimma','Jimma','Bosa Addis ','+251919639519','workufikadu643@gmail.com',NULL,NULL,NULL),(23,'External','Other','Other','Other','N/A','',NULL,NULL,NULL),(24,'Oromia','Jimma','Jimma','Bosa Addis Kebele','+251919639519','workufikadu643@gmail.com',NULL,NULL,NULL),(25,'Oromia','Other','Other','Other','N/A','',NULL,NULL,NULL),(26,'Oromia','Other','Other','Other','N/A','',NULL,NULL,NULL),(27,'Oromia','Other','Other','Other','N/A','',NULL,NULL,NULL),(28,'Oromia','Other','Other','Other','N/A','',NULL,NULL,NULL),(29,'Oromia','Jimma','Jimma','Bosa Addis Kebele','+251919639519','workufikadu643@gmail.com',NULL,NULL,NULL),(30,'Oromia','Jimma','Jimma','Bosa Addis ','+251919639519','woor@gmail.com',NULL,NULL,NULL),(31,'Oromia','Jimma','Jimma','Bosa Addis ','+251968572434','workufikadu643@gmail.com',NULL,NULL,NULL);
/*!40000 ALTER TABLE `addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ages`
--

DROP TABLE IF EXISTS `ages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ages` (
  `id` int(11) NOT NULL,
  `bdate` date NOT NULL,
  `age` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `ages_ibfk_1` FOREIGN KEY (`id`) REFERENCES `individuals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ages`
--

LOCK TABLES `ages` WRITE;
/*!40000 ALTER TABLE `ages` DISABLE KEYS */;
INSERT INTO `ages` VALUES (8,'1980-04-08',46),(9,'2005-04-14',21),(10,'2004-05-09',21),(11,'2020-05-08',5),(12,'2014-05-14',11),(19,'1990-05-12',36),(20,'2000-05-08',26),(21,'2000-05-04',26),(22,'2005-04-27',21),(23,'2007-03-26',19),(24,'2007-04-06',19),(29,'2003-01-13',23),(30,'2004-05-13',22),(31,'2003-04-10',23);
/*!40000 ALTER TABLE `ages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `body` text NOT NULL,
  `category` varchar(50) DEFAULT 'General',
  `is_pinned` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `module` varchar(50) NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,'UPDATED','payments',37,'Transaction #37 was marked as Completed','2026-05-08 23:06:14'),(2,1,'CREATED','residents',20,'Registered new resident: Bereket  Tokuma','2026-05-09 06:00:40'),(3,1,'UPDATED','payments',29,'Transaction #29 was marked as Rejected','2026-05-09 08:47:59'),(4,1,'CREATED','residents',21,'Registered new resident: Gemechis  Gudeta','2026-05-10 12:17:27'),(5,1,'UPDATED','payments',38,'Transaction #38 was marked as Completed','2026-05-10 12:18:59'),(6,1,'UPDATED','payments',39,'Transaction #39 was marked as Completed','2026-05-10 12:21:15'),(7,1,'CREATED','residents',22,'Registered new resident: Falmata Dibaba','2026-05-12 19:49:10'),(8,1,'UPDATED','payments',42,'Transaction #42 was marked as Rejected','2026-05-12 19:51:12'),(9,1,'UPDATED','payments',41,'Transaction #41 was marked as Completed','2026-05-12 19:51:25'),(10,1,'UPDATED','payments',43,'Transaction #43 was marked as Completed','2026-05-12 20:02:09'),(11,1,'UPDATED','payments',44,'Transaction #44 was marked as Completed','2026-05-12 20:08:45'),(12,1,'CREATED','residents',24,'Registered new resident: Simbo Tola','2026-05-12 20:41:30'),(13,1,'UPDATED','payments',45,'Transaction #45 was marked as Completed','2026-05-12 20:42:49'),(14,1,'UPDATED','payments',47,'Transaction #47 was marked as Completed','2026-05-12 20:56:24'),(15,1,'UPDATED','payments',48,'Transaction #48 was marked as Completed','2026-05-13 23:22:31'),(16,1,'UPDATED','payments',49,'Transaction #49 was marked as Completed','2026-05-14 08:09:27'),(17,1,'UPDATED','payments',50,'Transaction #50 was marked as Completed','2026-05-14 08:25:37'),(18,1,'UPDATED','payments',51,'Transaction #51 was marked as Completed','2026-05-16 17:46:00'),(19,1,'UPDATED','payments',52,'Transaction #52 was marked as Completed','2026-05-16 18:50:02'),(20,1,'UPDATED','payments',53,'Transaction #53 was marked as Completed','2026-05-16 19:02:04'),(21,1,'UPDATED','payments',53,'Transaction #53 was marked as Completed','2026-05-16 19:02:15'),(22,1,'UPDATED','payments',54,'Transaction #54 was marked as Completed','2026-05-19 08:31:51'),(23,1,'UPDATED','payments',54,'Transaction #54 was marked as Completed','2026-05-19 08:32:02'),(24,1,'UPDATED','payments',55,'Transaction #55 was marked as Completed','2026-05-19 08:33:27'),(25,1,'DELETED','residents',26,'Deleted resident:  ','2026-05-19 11:29:15'),(26,1,'DELETED','residents',25,'Deleted resident:  ','2026-05-19 11:29:21'),(27,1,'DELETED','residents',27,'Deleted resident:  ','2026-05-19 11:29:27'),(28,1,'DELETED','residents',28,'Deleted resident:  ','2026-05-19 11:29:32'),(29,1,'CREATED','residents',29,'Registered new resident: Worku Irena','2026-05-20 04:01:11'),(30,1,'UPDATED','payments',56,'Transaction #56 was marked as Completed','2026-05-20 04:03:16'),(31,1,'CREATED','residents',30,'Registered new resident: Nafyad  Nezif','2026-05-20 04:38:00'),(32,1,'UPDATED','payments',57,'Transaction #57 was marked as Completed','2026-05-20 07:21:22'),(33,1,'CREATED','residents',31,'Registered new resident: Kirubel  Keti','2026-05-27 12:16:14'),(34,1,'UPDATED','payments',58,'Transaction #58 was marked as Completed','2026-05-27 12:18:00'),(35,1,'CREATE','',NULL,'Registered new Police Officer. Badge: 12','2026-05-27 19:21:46'),(36,1,'CREATE','',NULL,'Issued Police ID Card: POL0001','2026-05-27 19:22:06'),(37,1,'CREATE','',NULL,'Registered new Milisha Member. Role: Commander in zone 5','2026-05-27 19:29:08'),(38,1,'CREATE','',NULL,'Registered new Gachana Sirna Member. Role: Member in Ketena 5','2026-05-27 19:30:08'),(39,1,'CREATE','',NULL,'Issued Milisha ID Card: MIL0001','2026-05-27 20:12:04'),(40,1,'CREATE','',NULL,'Issued Gachana Sirna ID Card: GAC0001','2026-05-27 20:12:47'),(41,1,'CREATE','',NULL,'Issued Safety Net ID: PSNP0001','2026-05-27 20:30:56'),(42,1,'CREATE','',NULL,'Filed new court case: KBL/SC/05/2026/0101','2026-05-28 09:08:55'),(43,1,'UPDATE','',NULL,'Updated Court Case ID #1 to status: Open','2026-05-28 09:11:47'),(44,1,'CREATE','',NULL,'Filed new court case: KBL/SC/06/2026/0101','2026-06-04 08:20:11'),(45,1,'UPDATE','',NULL,'Quick status change on Case ID #2 → Resolved','2026-06-04 08:28:16');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `complaints`
--

DROP TABLE IF EXISTS `complaints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `complaints` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_number` varchar(20) NOT NULL,
  `resident_id` int(11) DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `status` enum('open','in_progress','resolved','closed') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_number` (`ticket_number`),
  KEY `resident_id` (`resident_id`),
  CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `individuals` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `complaints`
--

LOCK TABLES `complaints` WRITE;
/*!40000 ALTER TABLE `complaints` DISABLE KEYS */;
/*!40000 ALTER TABLE `complaints` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `court_cases`
--

DROP TABLE IF EXISTS `court_cases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `court_cases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case_number` varchar(50) NOT NULL,
  `plaintiff_name` varchar(100) NOT NULL,
  `defendant_name` varchar(100) NOT NULL,
  `plaintiff_id` int(11) DEFAULT NULL,
  `defendant_id` int(11) DEFAULT NULL,
  `case_category` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `presiding_judge` varchar(100) DEFAULT NULL,
  `status` enum('Open','In Progress','Resolved','Appealed','Dismissed') DEFAULT 'Open',
  `verdict` text DEFAULT NULL,
  `filed_date` date NOT NULL,
  `resolved_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `case_number` (`case_number`),
  KEY `plaintiff_id` (`plaintiff_id`),
  KEY `defendant_id` (`defendant_id`),
  CONSTRAINT `court_cases_ibfk_1` FOREIGN KEY (`plaintiff_id`) REFERENCES `individuals` (`id`) ON DELETE SET NULL,
  CONSTRAINT `court_cases_ibfk_2` FOREIGN KEY (`defendant_id`) REFERENCES `individuals` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `court_cases`
--

LOCK TABLES `court_cases` WRITE;
/*!40000 ALTER TABLE `court_cases` DISABLE KEYS */;
INSERT INTO `court_cases` VALUES (1,'KBL/SC/05/2026/0101','Wubiye Ertibu Alemayehu','Simbo Miresa  Tola',10,24,'Family','wwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwww','worku','Open',NULL,'2026-05-28',NULL,'2026-05-28 09:08:54'),(2,'KBL/SC/06/2026/0101','Kirubel  Tesfaye  Keti','Bereket  Chala Tokuma',31,20,'Boundary','jkdsabhhhhhhhcwquiiiirrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrefg\n\n[CASE DETAILS]\n{\n    \"plaintiff_phone\": \"0919639519\",\n    \"plaintiff_id_num\": \"1234567812345612\",\n    \"plaintiff_address\": \"Bosa Addis \",\n    \"defendant_phone\": \"0934953593\",\n    \"defendant_id_num\": \"1221344345656767\",\n    \"defendant_address\": \"Bosa Kitto\",\n    \"incident_date\": \"2026-06-11\",\n    \"incident_time\": \"12:00\",\n    \"incident_location\": \"Matric\",\n    \"dispute_amount\": \"28000.00\",\n    \"urgency_level\": \"Normal\",\n    \"witnesses\": \"Wube\\r\\nHaile \\r\\nTola\",\n    \"evidence_list\": \"uddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddwe\",\n    \"prior_mediation\": \"Yes\",\n    \"mediation_details\": \"nnnnnnnnnnnnnnnnn\",\n    \"hearing_date\": \"2026-06-02\",\n    \"plaintiff_attorney\": \"no\",\n    \"defendant_attorney\": \"no\",\n    \"relief_sought\": \"okay\"\n}','worku','Resolved',NULL,'2026-06-04','2026-06-04','2026-06-04 08:20:11');
/*!40000 ALTER TABLE `court_cases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `divorce_details`
--

DROP TABLE IF EXISTS `divorce_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `divorce_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cert_id` int(11) NOT NULL,
  `husband_id` int(11) NOT NULL,
  `wife_id` int(11) NOT NULL,
  `divorce_date` date NOT NULL,
  `divorce_place` varchar(255) DEFAULT 'Bosa Addis Kebele, Jimma',
  `witness1_name` varchar(100) DEFAULT NULL,
  `witness2_name` varchar(100) DEFAULT NULL,
  `husband_photo` varchar(255) DEFAULT 'default_profile.png',
  `wife_photo` varchar(255) DEFAULT 'default_profile.png',
  PRIMARY KEY (`id`),
  KEY `fk_divorce_cert` (`cert_id`),
  KEY `fk_divorce_husband` (`husband_id`),
  KEY `fk_divorce_wife` (`wife_id`),
  CONSTRAINT `fk_divorce_cert` FOREIGN KEY (`cert_id`) REFERENCES `vital_certificates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_divorce_husband` FOREIGN KEY (`husband_id`) REFERENCES `individuals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_divorce_wife` FOREIGN KEY (`wife_id`) REFERENCES `individuals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `divorce_details`
--

LOCK TABLES `divorce_details` WRITE;
/*!40000 ALTER TABLE `divorce_details` DISABLE KEYS */;
INSERT INTO `divorce_details` VALUES (1,11,8,9,'2026-05-14','Bosa Addis Kebele, Jimma City','Mamo Chala Tola','Worku Fikadu Erena','husband_1777627876_69f472e490c2b.jpg','wife_1777627876_69f472e491a9e.jpg');
/*!40000 ALTER TABLE `divorce_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `economic_agriculture`
--

DROP TABLE IF EXISTS `economic_agriculture`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `economic_agriculture` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `land_owner_id` int(11) NOT NULL,
  `plot_number` varchar(100) DEFAULT NULL,
  `land_size_sqm` decimal(10,2) DEFAULT NULL,
  `land_use` enum('Residential','Farmland','Commercial','Mixed') DEFAULT 'Farmland',
  `plot_status` enum('Active','Idle','Disputed','Rented') DEFAULT 'Active',
  `main_crops` varchar(255) DEFAULT NULL,
  `livestock_summary` text DEFAULT NULL,
  `water_source` varchar(100) DEFAULT NULL,
  `soil_type` varchar(100) DEFAULT NULL,
  `fertilizer_received` decimal(10,2) DEFAULT 0.00,
  `seed_received` decimal(10,2) DEFAULT 0.00,
  `input_payment_status` enum('Fully Paid','Partial','Credit','Government Support') DEFAULT 'Government Support',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `land_owner_id` (`land_owner_id`),
  CONSTRAINT `economic_agriculture_ibfk_1` FOREIGN KEY (`land_owner_id`) REFERENCES `individuals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `economic_agriculture`
--

LOCK TABLES `economic_agriculture` WRITE;
/*!40000 ALTER TABLE `economic_agriculture` DISABLE KEYS */;
INSERT INTO `economic_agriculture` VALUES (1,8,'67',345.00,'Farmland','Active','wheat','sheep 5','River','black',1.24,0.23,'Fully Paid','2026-05-27 22:13:06');
/*!40000 ALTER TABLE `economic_agriculture` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `economic_enterprises`
--

DROP TABLE IF EXISTS `economic_enterprises`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `economic_enterprises` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `business_name` varchar(255) NOT NULL,
  `business_type` enum('Retail','Manufacturing','Services','Agriculture','Cooperatives') NOT NULL,
  `license_number` varchar(100) DEFAULT NULL,
  `registration_date` date NOT NULL,
  `status` enum('Active','Suspended','Closed') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  CONSTRAINT `economic_enterprises_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `individuals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `economic_enterprises`
--

LOCK TABLES `economic_enterprises` WRITE;
/*!40000 ALTER TABLE `economic_enterprises` DISABLE KEYS */;
INSERT INTO `economic_enterprises` VALUES (1,8,'Shop','Manufacturing','1111111111111111111','2026-05-27','Active','2026-05-27 20:38:45');
/*!40000 ALTER TABLE `economic_enterprises` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `economic_psnp`
--

DROP TABLE IF EXISTS `economic_psnp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `economic_psnp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resident_id` int(11) NOT NULL,
  `enrollment_date` date NOT NULL,
  `household_size` int(11) NOT NULL,
  `transfer_type` varchar(100) NOT NULL,
  `work_requirement` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `resident_id` (`resident_id`),
  CONSTRAINT `economic_psnp_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `individuals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `economic_psnp`
--

LOCK TABLES `economic_psnp` WRITE;
/*!40000 ALTER TABLE `economic_psnp` DISABLE KEYS */;
/*!40000 ALTER TABLE `economic_psnp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `economic_subsidies`
--

DROP TABLE IF EXISTS `economic_subsidies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `economic_subsidies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `individual_id` int(11) NOT NULL,
  `item_type` enum('Sugar','Oil','Wheat','Flour') NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit` enum('Kg','Liters','Quintal') DEFAULT 'Kg',
  `distribution_date` date NOT NULL,
  `collected_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `individual_id` (`individual_id`),
  CONSTRAINT `economic_subsidies_ibfk_1` FOREIGN KEY (`individual_id`) REFERENCES `individuals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `economic_subsidies`
--

LOCK TABLES `economic_subsidies` WRITE;
/*!40000 ALTER TABLE `economic_subsidies` DISABLE KEYS */;
INSERT INTO `economic_subsidies` VALUES (1,20,'Oil',200.00,'Liters','2026-05-27','2026-05-27 20:45:23');
/*!40000 ALTER TABLE `economic_subsidies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `economic_youth_registry`
--

DROP TABLE IF EXISTS `economic_youth_registry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `economic_youth_registry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `individual_id` int(11) NOT NULL,
  `education_level` varchar(100) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `training_history` text DEFAULT NULL,
  `training_interests` text DEFAULT NULL,
  `employment_status` enum('Unemployed','Self-employed','Employed','Student') DEFAULT 'Unemployed',
  `preferred_sector` varchar(100) DEFAULT NULL,
  `disability_status` enum('None','Physical','Visual','Hearing','Other') DEFAULT 'None',
  `dependency_count` int(11) DEFAULT 0,
  `registration_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `individual_id` (`individual_id`),
  CONSTRAINT `economic_youth_registry_ibfk_1` FOREIGN KEY (`individual_id`) REFERENCES `individuals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `economic_youth_registry`
--

LOCK TABLES `economic_youth_registry` WRITE;
/*!40000 ALTER TABLE `economic_youth_registry` DISABLE KEYS */;
INSERT INTO `economic_youth_registry` VALUES (1,22,'Bachelor Degree','Coding',NULL,NULL,'Student','Web developer ','None',0,'2026-05-27','2026-05-27 20:43:09'),(2,11,'Bachelor Degree','Coding','Web developer ','AI','Unemployed','Web developer','None',3,'2026-05-27','2026-05-27 21:04:29');
/*!40000 ALTER TABLE `economic_youth_registry` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `families`
--

DROP TABLE IF EXISTS `families`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `families` (
  `fam_no` int(11) NOT NULL,
  `lead_id` varchar(40) NOT NULL,
  `hnum` int(11) NOT NULL,
  `family_type` varchar(50) DEFAULT 'Nuclear',
  `income_category` varchar(50) DEFAULT 'Low',
  `social_status` varchar(50) DEFAULT 'Permanent Resident',
  `total_males` int(11) DEFAULT 0,
  `total_females` int(11) DEFAULT 0,
  `disabled_members` int(11) DEFAULT 0,
  `orphans_count` int(11) DEFAULT 0,
  `has_pension` enum('Yes','No') DEFAULT 'No',
  `is_vulnerable` enum('Yes','No') DEFAULT 'No',
  `registration_date` date DEFAULT NULL,
  PRIMARY KEY (`hnum`),
  UNIQUE KEY `lead_id` (`lead_id`),
  CONSTRAINT `fk_family_house` FOREIGN KEY (`hnum`) REFERENCES `houses` (`hnum`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `families`
--

LOCK TABLES `families` WRITE;
/*!40000 ALTER TABLE `families` DISABLE KEYS */;
INSERT INTO `families` VALUES (3,'9',100000,'Nuclear','Low','Permanent Resident',0,0,0,0,'No','No',NULL),(6,'20',100001,'Nuclear','Low','Permanent Resident',3,3,1,2,'No','No','2026-05-20'),(4,'10',100004,'Nuclear','Low','Permanent Resident',0,0,0,0,'No','No',NULL),(5,'8',100005,'Nuclear','Low','Permanent Resident',0,0,0,0,'No','No',NULL),(3,'29',100006,'Nuclear','Low','Permanent Resident',0,0,0,0,'No','No','2026-05-20');
/*!40000 ALTER TABLE `families` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gachana_id_cards`
--

DROP TABLE IF EXISTS `gachana_id_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gachana_id_cards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gachana_record_id` int(11) NOT NULL,
  `id_num` varchar(50) NOT NULL,
  `issue_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `status` enum('Active','Lost','Expired') DEFAULT 'Active',
  `transaction_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `gachana_record_id` (`gachana_record_id`),
  UNIQUE KEY `id_num` (`id_num`),
  CONSTRAINT `gachana_id_cards_ibfk_1` FOREIGN KEY (`gachana_record_id`) REFERENCES `gachana_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gachana_id_cards`
--

LOCK TABLES `gachana_id_cards` WRITE;
/*!40000 ALTER TABLE `gachana_id_cards` DISABLE KEYS */;
INSERT INTO `gachana_id_cards` VALUES (1,1,'GAC0001','2026-05-27','2028-05-27','Active',61,'2026-05-27 20:12:47');
/*!40000 ALTER TABLE `gachana_id_cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gachana_records`
--

DROP TABLE IF EXISTS `gachana_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gachana_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `individual_id` int(11) NOT NULL,
  `committee_role` varchar(50) NOT NULL DEFAULT 'Member',
  `sector` varchar(100) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `joined_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `individual_id` (`individual_id`),
  CONSTRAINT `gachana_records_ibfk_1` FOREIGN KEY (`individual_id`) REFERENCES `individuals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gachana_records`
--

LOCK TABLES `gachana_records` WRITE;
/*!40000 ALTER TABLE `gachana_records` DISABLE KEYS */;
INSERT INTO `gachana_records` VALUES (1,11,'Member','Ketena 5','Active','2026-05-27','2026-05-27 19:30:08');
/*!40000 ALTER TABLE `gachana_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `generated_letters`
--

DROP TABLE IF EXISTS `generated_letters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `generated_letters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resident_id` int(11) NOT NULL,
  `letter_type` enum('Residency','Conduct','Verification','Clearance') NOT NULL,
  `ref_number` varchar(50) NOT NULL,
  `purpose` text DEFAULT NULL,
  `issue_date` date NOT NULL,
  `issued_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ref_number` (`ref_number`),
  KEY `resident_id` (`resident_id`),
  CONSTRAINT `generated_letters_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `individuals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `generated_letters`
--

LOCK TABLES `generated_letters` WRITE;
/*!40000 ALTER TABLE `generated_letters` DISABLE KEYS */;
INSERT INTO `generated_letters` VALUES (1,21,'Verification','KBL/VER/2026/0001','yyyyyyyyyyyyyyyyyyyyyy','2026-05-27',1,'2026-05-27 20:49:52'),(2,21,'Verification','KBL/VER/2026/0002','yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy','2026-05-27',1,'2026-05-27 20:51:42'),(3,21,'Residency','KBL/RES/2026/0001','yyyyyyyyyyy','2026-05-27',1,'2026-05-27 20:52:10'),(4,11,'Verification','KBL/VER/2026/0003','','2026-06-03',1,'2026-06-03 08:22:12');
/*!40000 ALTER TABLE `generated_letters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `health_records`
--

DROP TABLE IF EXISTS `health_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `health_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `individual_id` int(11) NOT NULL,
  `service_type` enum('Vaccination','Maternal Health','General Checkup','Clinic Referral') NOT NULL,
  `service_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `staff_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `individual_id` (`individual_id`),
  CONSTRAINT `health_records_ibfk_1` FOREIGN KEY (`individual_id`) REFERENCES `individuals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `health_records`
--

LOCK TABLES `health_records` WRITE;
/*!40000 ALTER TABLE `health_records` DISABLE KEYS */;
INSERT INTO `health_records` VALUES (1,10,'Vaccination','2026-06-03','to ju hospital','Worku','2026-06-03 20:30:11');
/*!40000 ALTER TABLE `health_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `houses`
--

DROP TABLE IF EXISTS `houses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `houses` (
  `hnum` int(11) NOT NULL,
  `area` double NOT NULL,
  `door` int(11) NOT NULL,
  `owner_id` varchar(45) NOT NULL,
  `owner_individual_id` int(11) DEFAULT NULL,
  `house_type` varchar(50) DEFAULT 'Residential',
  `construction_type` varchar(100) DEFAULT 'Wood and Mud',
  `rooms_count` int(11) DEFAULT 1,
  `floor_type` varchar(50) DEFAULT 'Earth',
  `roof_type` varchar(50) DEFAULT 'CIS',
  `has_water` enum('Yes','No') DEFAULT 'No',
  `has_electricity` enum('Yes','No') DEFAULT 'No',
  `toilet_type` varchar(50) DEFAULT 'None',
  `constructed_year` int(11) DEFAULT NULL,
  `block_no` varchar(50) DEFAULT NULL,
  `plan_certificate` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`hnum`),
  KEY `fk_house_owner` (`owner_individual_id`),
  CONSTRAINT `fk_house_owner` FOREIGN KEY (`owner_individual_id`) REFERENCES `individuals` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `houses`
--

LOCK TABLES `houses` WRITE;
/*!40000 ALTER TABLE `houses` DISABLE KEYS */;
INSERT INTO `houses` VALUES (100000,345,7,'RES-9',9,'Residential','Stone and Cement',5,'Cement','CIS','Yes','Yes','Pit Latrine',2024,'008',NULL),(100001,1200,4,'RES-24',24,'Public','Wood and Mud',8,'Tiles','Tiles','Yes','Yes','Shared',2026,'002',NULL),(100002,567,7,'RES-8',8,'Commercial','Modern Concrete',6,'Earth','CIS','Yes','Yes','Shared',1900,'98',NULL),(100003,55,15,'RES-8',8,'Residential','Wood and Mud',1,'Earth','CIS','No','No','None',NULL,NULL,NULL),(100004,1000,22,'RES-10',10,'Residential','Wood and Mud',1,'Earth','CIS','No','No','None',NULL,NULL,NULL),(100005,987,3,'RES-8',8,'Residential','Wood and Mud',1,'Earth','CIS','No','No','None',NULL,NULL,NULL),(100006,340,3,'RES-29',29,'Residential','Stone and Cement',6,'Tiles','CIS','Yes','Yes','Pit Latrine',2025,'24',NULL),(100007,230,1,'RES-31',31,'Residential','Wood and Mud',1,'Earth','CIS','Yes','Yes','Pit Latrine',2012,'23',NULL);
/*!40000 ALTER TABLE `houses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `id_cards`
--

DROP TABLE IF EXISTS `id_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `id_cards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resident_id` int(11) NOT NULL,
  `id_num` varchar(50) NOT NULL,
  `issue_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `status` enum('Active','Expired','Lost') DEFAULT 'Active',
  `transaction_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_num` (`id_num`),
  KEY `id_cards_ibfk_1` (`resident_id`),
  CONSTRAINT `id_cards_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `individuals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `id_cards`
--

LOCK TABLES `id_cards` WRITE;
/*!40000 ALTER TABLE `id_cards` DISABLE KEYS */;
INSERT INTO `id_cards` VALUES (17,9,'BA0002','2026-05-01','2031-05-01','Lost',NULL,'2026-05-03 05:43:12'),(18,8,'BA0003','2026-05-01','2031-05-01','Lost',NULL,'2026-05-03 05:43:12'),(20,10,'BA0004','2026-05-01','2031-05-01','Lost',NULL,'2026-05-03 05:43:12'),(21,11,'BA0005','2026-05-01','2031-05-01','Lost',NULL,'2026-05-03 05:43:12'),(22,12,'BA0006','2026-05-02','2031-05-02','Lost',NULL,'2026-05-03 05:43:12'),(28,9,'BA0007','2026-05-03','2031-05-03','Active',28,'2026-05-03 06:06:48'),(29,8,'BA0008','2026-05-03','2031-05-03','Active',29,'2026-05-03 06:07:08'),(30,10,'BA0009','2026-05-03','2031-05-03','Lost',30,'2026-05-03 06:07:32'),(31,12,'BA0010','2026-05-04','2031-05-04','Active',32,'2026-05-04 08:48:01'),(32,11,'BA0011','2026-05-04','2031-05-04','Lost',35,'2026-05-04 20:42:13'),(33,19,'BA-9860100','2026-05-08','0000-00-00','Active',NULL,'2026-05-08 21:39:23'),(34,21,'BA0012','2026-05-10','2031-05-10','Lost',38,'2026-05-10 12:18:14'),(35,11,'BA0013','2026-05-10','2031-05-10','Lost',40,'2026-05-10 12:38:55'),(36,22,'BA0014','2026-05-12','2031-05-12','Lost',41,'2026-05-12 19:50:22'),(37,24,'BA0015','2026-05-12','2031-05-12','Active',45,'2026-05-12 20:42:35'),(38,23,'BA0016','2026-05-14','2031-05-14','Lost',49,'2026-05-14 08:09:03'),(39,22,'BA0017','2026-05-14','2031-05-14','Active',50,'2026-05-14 08:25:19'),(40,10,'BA0018','2026-05-16','2031-05-16','Active',51,'2026-05-16 17:45:39'),(41,23,'BA0019','2026-05-16','2031-05-16','Active',52,'2026-05-16 18:49:07'),(42,11,'BA0020','2026-05-19','2031-05-19','Active',54,'2026-05-19 08:31:05'),(43,29,'BA0021','2026-05-20','2031-05-20','Active',56,'2026-05-20 04:02:51'),(44,30,'BA0022','2026-05-20','2031-05-20','Active',57,'2026-05-20 07:21:02'),(45,31,'BA0023','2026-05-27','2031-05-27','Active',58,'2026-05-27 12:17:16');
/*!40000 ALTER TABLE `id_cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `individuals`
--

DROP TABLE IF EXISTS `individuals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `individuals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fname` varchar(45) NOT NULL,
  `lname` varchar(45) NOT NULL,
  `mname` varchar(45) NOT NULL,
  `mar` varchar(20) NOT NULL,
  `s` varchar(6) NOT NULL,
  `nat` varchar(30) NOT NULL,
  `level_edu` varchar(45) NOT NULL,
  `relg` varchar(30) NOT NULL,
  `occ` varchar(50) NOT NULL,
  `phot` varchar(255) DEFAULT 'default_profile.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('alive','deceased') DEFAULT 'alive',
  `death_date` date DEFAULT NULL,
  `death_reason` text DEFAULT NULL,
  `mother_full_name` varchar(100) DEFAULT NULL,
  `father_full_name` varchar(100) DEFAULT NULL,
  `mother_nat` varchar(50) DEFAULT 'Ethiopian',
  `father_nat` varchar(50) DEFAULT 'Ethiopian',
  `birth_place` varchar(100) DEFAULT NULL,
  `blood_type` varchar(5) DEFAULT NULL,
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `doc_birth_cert` varchar(255) DEFAULT NULL,
  `doc_clearance` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `individuals`
--

LOCK TABLES `individuals` WRITE;
/*!40000 ALTER TABLE `individuals` DISABLE KEYS */;
INSERT INTO `individuals` VALUES (8,'Abebe ','Erena','Fikadu','Divorced','Male','Ethiopian','','Protestant','Civil Engineer','1777622103_69f45c57360a0.jpg','2026-05-01 07:55:03','alive',NULL,NULL,'Mamitu Tafa Hunde','Fikadu Erena Melka','Itoophiyaa','Itoophiyaa',NULL,NULL,NULL,NULL,NULL,NULL),(9,'Chaltu','Tokuma','Tola','Married','Female','Ethiopian','B.S of Computer Science','Protestant','Web Developer','1777626032_69f46bb0adad2.jpg','2026-05-01 09:00:32','alive',NULL,NULL,'Toleshi Dhaba Oda','Belete Bereket Tulu','Itoophiyaa','Itoophiyaa',NULL,NULL,NULL,NULL,NULL,NULL),(10,'Wubiye','Alemayehu','Ertibu','Married','Male','Ethiopian','B.Sc Food Science','Orthodox','student','1777632823_69f4863782e62.jpg','2026-05-01 10:53:43','alive',NULL,NULL,'Wubit Baye Amberber','Ertibu Alemayehu Maru','Ethiopian','Ethiopian',NULL,NULL,NULL,NULL,NULL,NULL),(11,'Beka ','Gadisa','Chala','Single','Male','Ethiopian','Grade 5','Protestant','student','1777638881_69f49de189258.jpg','2026-05-01 12:34:41','alive',NULL,NULL,'Beza Tola Lata','Bora Doga Dega','Ethiopian','Ethiopian',NULL,NULL,NULL,NULL,NULL,NULL),(12,'Yeab','Kasa','Fikadu','Married','Male','Ethiopian','Grade 5','Orthodox','student','1777710494_69f5b59edbd43.jpg','2026-05-02 08:28:14','alive',NULL,NULL,'Simret S/Bizu Yadi','Fikadu Kasa Wake','Ethiopian','Ethiopian',NULL,NULL,NULL,NULL,NULL,NULL),(19,'Mulatu','Tadesse','Addis','Married','Male','Ethiopian','Degree','Orthodox','Civil Servant','default_profile.png','2026-05-08 21:39:23','deceased','2026-05-08','ggggggggggggg',NULL,NULL,'Ethiopian','Ethiopian',NULL,NULL,NULL,NULL,NULL,NULL),(20,'Bereket ','Tokuma','Chala','Single','Male','Ethiopian','B.S of Software Engineering','Protestant','Engineer','1778306440_photo_69fecd88d8135.jpg','2026-05-09 06:00:40','alive',NULL,NULL,'Toltu Badhasa Chala','Chala Tokuma Tolosa','Itoophiyaa','Itoophiyaa','Jimma','A+','Abebe Fikadu','+251938109833','',''),(21,'Gemechis ','Gudeta','Endalu','Married','Male','Ethiopian','B.Sc Health','Protestant','Barber','1778415447_photo_6a0077578ea3c.jpg','2026-05-10 12:17:27','alive',NULL,NULL,'Badhane Jirata','Endalu Gudeta','Itoophiyaa','Itoophiyaa','Wellega','A+','','','',''),(22,'Falmata','Dibaba','Tola','Married','Male','Ethiopian','Degree','Protestant','Web Developer','1778615350_photo_6a0384364ba4a.jpg','2026-05-12 19:49:10','alive',NULL,NULL,'Chaltu Beka Tolosa ','Tola Dibaba Oda','Itoophiyaa','Itoophiyaa','Finfinne','AB+','Chala Tola Dibaba','+251938109833','',''),(23,'Sifan','Worku','Chala ','Married','Female','Ethiopian','B.S of Computer Science','Orthodox','Other','1778616759_photo_6a0389b75cb81.jpg','2026-05-12 20:06:51','alive',NULL,NULL,'Chaltu Beka Tolosa','Belete Bereket Tulu','Ethiopian','Ethiopian','Finfinne','Unkno','','',NULL,NULL),(24,'Simbo','Tola','Miresa ','Married','Female','Ethiopian','Grade 12','Protestant','Student','1778618490_photo_6a03907a224d1.jpg','2026-05-12 20:41:30','alive',NULL,NULL,'Wube Badhasa Hirpha','Miresa Tola Melka','Itoophiyaa','Itoophiyaa','Jimma','AB+','Chala Tola Dibaba','+251938109833','',''),(29,'Worku','Irena','Fikadu','Single','Male','Ethiopian','Bachelor\'s Degree','Protestant','Student','1779249671_photo_6a0d32078fdef.jpg','2026-05-20 04:01:11','alive',NULL,NULL,'Mamitu Tafa',' Fikadu Irena','Itoophiyaa','Itoophiyaa','Jimma City','AB+','Abebe Fikadu','+251938109833','',''),(30,'Nafyad ','Nezif','Diriba','Single','Male','Ethiopian','Master\'s Degree','Orthodox','Merchant / Business Owner','1779251879_photo_6a0d3aa7aad83.jpg','2026-05-20 04:37:59','alive',NULL,NULL,'Beza Tola Lata','Diriba Lata Beka','Itoophiyaa','Itoophiyaa','Agaro Town','O+','Abebe Fikadu','+251938109833','',''),(31,'Kirubel ','Keti','Tesfaye ','Single','Male','Ethiopian','Primary School','Orthodox','Private Sector Employee','1779884174_photo_6a16e08e48cce.jpg','2026-05-27 12:16:14','alive',NULL,NULL,'Damenech Birhanu','Tesfaye Keti','Itoophiyaa','Itoophiyaa','Shebe Senbo','AB+','Tamrat Tesfaye ','+251938109833','','');
/*!40000 ALTER TABLE `individuals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marriage_details`
--

DROP TABLE IF EXISTS `marriage_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marriage_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cert_id` int(11) NOT NULL,
  `groom_id` int(11) NOT NULL,
  `bride_id` int(11) NOT NULL,
  `marriage_date` date NOT NULL,
  `marriage_place` varchar(255) DEFAULT 'Bosa Addis Kebele, Jimma',
  `witness1_name` varchar(100) DEFAULT NULL,
  `witness2_name` varchar(100) DEFAULT NULL,
  `groom_photo` varchar(255) DEFAULT 'default_profile.png',
  `bride_photo` varchar(255) DEFAULT 'default_profile.png',
  PRIMARY KEY (`id`),
  KEY `fk_marriage_cert` (`cert_id`),
  KEY `fk_marriage_groom` (`groom_id`),
  KEY `fk_marriage_bride` (`bride_id`),
  CONSTRAINT `fk_marriage_bride` FOREIGN KEY (`bride_id`) REFERENCES `individuals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_marriage_cert` FOREIGN KEY (`cert_id`) REFERENCES `vital_certificates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_marriage_groom` FOREIGN KEY (`groom_id`) REFERENCES `individuals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marriage_details`
--

LOCK TABLES `marriage_details` WRITE;
/*!40000 ALTER TABLE `marriage_details` DISABLE KEYS */;
INSERT INTO `marriage_details` VALUES (1,10,8,9,'2026-05-08','Bosa Addis Kebele, Jimma City','Abebe Fikdu Erena','Worku Fikadu Erena','groom_1777626245_69f46c8516e57.jpg','bride_1777626245_69f46c85178f8.jpg'),(2,13,10,9,'2026-05-08','Bosa Addis Kebele, Jimma City','Worku Fikadu Erena','Abebe Metu Bore','groom_1777633022_69f486fe6048e.jpg','bride_1777633022_69f486fe60993.jpg'),(3,17,12,9,'2026-05-04','Bosa Addis Kebele, Jimma City','Mehraf Samson Getachew','Yeabkal Niguse Fikadu','groom_1777710712_69f5b678b5cd7.jpg','bride_1777710712_69f5b678b664b.jpg'),(4,21,8,9,'2026-05-02','Bosa Addis Kebele, Jimma','','','default_profile.png','default_profile.png'),(6,28,22,23,'2026-05-12','Bosa Addis Kebele, Jimma','Worku Fikadu Erena','Mamo Wonde Yosef','cert_g_1778616411.jpg','cert_b_1778616411.jpg'),(7,29,21,24,'2026-05-12','Bosa Addis Kebele, Jimma','','','default_profile.png','default_profile.png');
/*!40000 ALTER TABLE `marriage_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `milisha_id_cards`
--

DROP TABLE IF EXISTS `milisha_id_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `milisha_id_cards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `milisha_record_id` int(11) NOT NULL,
  `id_num` varchar(50) NOT NULL,
  `issue_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `status` enum('Active','Lost','Expired') DEFAULT 'Active',
  `transaction_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `milisha_record_id` (`milisha_record_id`),
  UNIQUE KEY `id_num` (`id_num`),
  CONSTRAINT `milisha_id_cards_ibfk_1` FOREIGN KEY (`milisha_record_id`) REFERENCES `milisha_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `milisha_id_cards`
--

LOCK TABLES `milisha_id_cards` WRITE;
/*!40000 ALTER TABLE `milisha_id_cards` DISABLE KEYS */;
INSERT INTO `milisha_id_cards` VALUES (1,1,'MIL0001','2026-05-27','2028-05-27','Active',60,'2026-05-27 20:12:03');
/*!40000 ALTER TABLE `milisha_id_cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `milisha_records`
--

DROP TABLE IF EXISTS `milisha_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `milisha_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `individual_id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'Member',
  `zone_assigned` varchar(100) NOT NULL,
  `weapon_serial` varchar(100) DEFAULT NULL,
  `status` enum('Active','Inactive','Dismissed') DEFAULT 'Active',
  `joined_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `individual_id` (`individual_id`),
  CONSTRAINT `milisha_records_ibfk_1` FOREIGN KEY (`individual_id`) REFERENCES `individuals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `milisha_records`
--

LOCK TABLES `milisha_records` WRITE;
/*!40000 ALTER TABLE `milisha_records` DISABLE KEYS */;
INSERT INTO `milisha_records` VALUES (1,22,'Commander','zone 5','','Active','2026-05-27','2026-05-27 19:29:08');
/*!40000 ALTER TABLE `milisha_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `message` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `police_id_cards`
--

DROP TABLE IF EXISTS `police_id_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `police_id_cards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `police_record_id` int(11) NOT NULL,
  `id_num` varchar(50) NOT NULL,
  `issue_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `status` varchar(20) DEFAULT 'Active',
  `transaction_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `police_record_id` (`police_record_id`),
  UNIQUE KEY `id_num` (`id_num`),
  CONSTRAINT `police_id_cards_ibfk_1` FOREIGN KEY (`police_record_id`) REFERENCES `police_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `police_id_cards`
--

LOCK TABLES `police_id_cards` WRITE;
/*!40000 ALTER TABLE `police_id_cards` DISABLE KEYS */;
INSERT INTO `police_id_cards` VALUES (1,1,'POL0001','2026-05-27','2029-05-27','Active',59);
/*!40000 ALTER TABLE `police_id_cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `police_records`
--

DROP TABLE IF EXISTS `police_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `police_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `individual_id` int(11) NOT NULL,
  `badge_number` varchar(50) NOT NULL,
  `rank` varchar(50) NOT NULL,
  `station_assignment` varchar(100) NOT NULL,
  `weapon_serial` varchar(100) DEFAULT NULL,
  `status` enum('Active','Suspended','Retired','Transferred') DEFAULT 'Active',
  `joined_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `badge_number` (`badge_number`),
  KEY `individual_id` (`individual_id`),
  CONSTRAINT `police_records_ibfk_1` FOREIGN KEY (`individual_id`) REFERENCES `individuals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `police_records`
--

LOCK TABLES `police_records` WRITE;
/*!40000 ALTER TABLE `police_records` DISABLE KEYS */;
INSERT INTO `police_records` VALUES (1,8,'12','Inspector','zone 1','','Active','2026-05-27','2026-05-27 19:21:46');
/*!40000 ALTER TABLE `police_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `residents`
--

DROP TABLE IF EXISTS `residents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `residents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fname` varchar(45) NOT NULL,
  `lname` varchar(45) NOT NULL,
  `mname` varchar(45) NOT NULL,
  `bdate` date NOT NULL,
  `age` int(11) NOT NULL,
  `sex` enum('M','F') NOT NULL,
  `marital_status` varchar(20) NOT NULL,
  `level_edu` varchar(45) NOT NULL,
  `relg` varchar(30) NOT NULL,
  `nat` varchar(30) NOT NULL,
  `occ` varchar(50) NOT NULL,
  `pho_no` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `phot` varchar(255) DEFAULT 'default.png',
  `address_id` int(11) DEFAULT NULL,
  `hnum` int(11) DEFAULT NULL,
  `fam_no` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_resident_address` (`address_id`),
  KEY `fk_resident_family` (`fam_no`),
  KEY `fk_resident_house` (`hnum`),
  CONSTRAINT `fk_resident_address` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_resident_house` FOREIGN KEY (`hnum`) REFERENCES `houses` (`hnum`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `residents`
--

LOCK TABLES `residents` WRITE;
/*!40000 ALTER TABLE `residents` DISABLE KEYS */;
INSERT INTO `residents` VALUES (1,'Abebe','Bikila','Sara','1985-05-12',39,'M','Married','Bachelor Degree','Orthodox','Ethiopian','Engineer','0911223344','abebe@example.com','default.png',1,NULL,NULL),(2,'Marta','Tola','Genet','1990-08-20',34,'F','Married','Master Degree','Protestant','Ethiopian','Doctor','0922334455','marta@example.com','default.png',2,NULL,NULL),(3,'Chala','Guta','Lemat','2005-01-15',19,'M','Single','High School','Muslim','Ethiopian','Student','0933445566','chala@example.com','default.png',3,NULL,NULL),(4,'Zenebe','Worku','Aster','1960-11-30',63,'M','Widowed','Elementary','Orthodox','Ethiopian','Retired','0944556677','zenebe@example.com','default.png',4,NULL,NULL);
/*!40000 ALTER TABLE `residents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `safetynet_id_cards`
--

DROP TABLE IF EXISTS `safetynet_id_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `safetynet_id_cards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `safetynet_record_id` int(11) NOT NULL,
  `id_num` varchar(50) NOT NULL,
  `issue_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `status` enum('Active','Lost','Expired') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `safetynet_record_id` (`safetynet_record_id`),
  UNIQUE KEY `id_num` (`id_num`),
  CONSTRAINT `safetynet_id_cards_ibfk_1` FOREIGN KEY (`safetynet_record_id`) REFERENCES `safetynet_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `safetynet_id_cards`
--

LOCK TABLES `safetynet_id_cards` WRITE;
/*!40000 ALTER TABLE `safetynet_id_cards` DISABLE KEYS */;
INSERT INTO `safetynet_id_cards` VALUES (1,1,'PSNP0001','2026-05-27','2029-05-27','Active','2026-05-27 20:30:56');
/*!40000 ALTER TABLE `safetynet_id_cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `safetynet_records`
--

DROP TABLE IF EXISTS `safetynet_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `safetynet_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `individual_id` int(11) NOT NULL,
  `enrollment_date` date NOT NULL,
  `household_size` int(11) DEFAULT 1,
  `transfer_type` enum('Cash','Food','Mixed') NOT NULL,
  `work_status` enum('Public Work','Direct Support') NOT NULL,
  `payment_status` enum('Up to date','Pending','Overdue') DEFAULT 'Up to date',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `vulnerability_criteria` varchar(255) DEFAULT NULL,
  `proxy_name` varchar(100) DEFAULT NULL,
  `duty_station` varchar(100) DEFAULT NULL,
  `monthly_entitlement` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'Cash',
  PRIMARY KEY (`id`),
  KEY `individual_id` (`individual_id`),
  CONSTRAINT `safetynet_records_ibfk_1` FOREIGN KEY (`individual_id`) REFERENCES `individuals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `safetynet_records`
--

LOCK TABLES `safetynet_records` WRITE;
/*!40000 ALTER TABLE `safetynet_records` DISABLE KEYS */;
INSERT INTO `safetynet_records` VALUES (1,23,'2026-05-27',4,'Mixed','Public Work','Up to date','2026-05-27 20:30:30',NULL,NULL,NULL,NULL,'Cash');
/*!40000 ALTER TABLE `safetynet_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sanitation_campaigns`
--

DROP TABLE IF EXISTS `sanitation_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sanitation_campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_name` varchar(255) NOT NULL,
  `campaign_date` date NOT NULL,
  `zone` varchar(100) DEFAULT NULL,
  `status` enum('Planned','In Progress','Completed','Cancelled') DEFAULT 'Planned',
  `participants_est` int(11) DEFAULT 0,
  `impact_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sanitation_campaigns`
--

LOCK TABLES `sanitation_campaigns` WRITE;
/*!40000 ALTER TABLE `sanitation_campaigns` DISABLE KEYS */;
/*!40000 ALTER TABLE `sanitation_campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_prices`
--

DROP TABLE IF EXISTS `service_prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_prices` (
  `service_key` varchar(50) NOT NULL,
  `service_name_en` varchar(100) NOT NULL,
  `price_etb` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`service_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_prices`
--

LOCK TABLES `service_prices` WRITE;
/*!40000 ALTER TABLE `service_prices` DISABLE KEYS */;
INSERT INTO `service_prices` VALUES ('birth_cert','Birth Certificate',400.00),('clearance_cert','Administrative Clearance',400.00),('death_cert','Death Certificate',400.00),('divorce_cert','Divorce Certificate',400.00),('gachana_id','Gachana Sirna ID Card Issuance',15.00),('house_reg','Property Registration Fee',500.00),('id_card','Kebele Resident ID Card',500.00),('letter_clearance','Clearance Letter',20.00),('letter_conduct','Good Conduct Letter',15.00),('letter_residency','Residency Letter',10.00),('letter_verification','Verification Letter',10.00),('marriage_cert','Marriage Certificate',400.00),('milisha_id','Milisha ID Card Issuance',20.00),('police_id','Police ID Card Issuance',30.00),('safetynet_id','Safety Net (PSNP) ID Card',5.00),('youth_id','Youth Empowerment ID Card',5.00);
/*!40000 ALTER TABLE `service_prices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_announcements`
--

DROP TABLE IF EXISTS `staff_announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `priority` enum('normal','urgent','critical') DEFAULT 'normal',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_announcements`
--

LOCK TABLES `staff_announcements` WRITE;
/*!40000 ALTER TABLE `staff_announcements` DISABLE KEYS */;
INSERT INTO `staff_announcements` VALUES (1,1,'hhhhhhhhh','jjjjjjjjjjjjjj','urgent','2026-06-05 20:39:08'),(2,1,'hhhhhhhhh','jjjjjjjjjjjjjj','urgent','2026-06-05 20:39:11'),(3,1,'hhhhhhhhh','jjjjjjjjjjjjjj','urgent','2026-06-05 20:39:14'),(4,1,'hhhhhhhhh','jjjjjjjjjjjjjj','urgent','2026-06-05 20:39:16'),(5,1,'hhhhhhhhh','jjjjjjjjjjjjjj','urgent','2026-06-05 20:39:16'),(6,1,'hhhhhhhhh','jjjjjjjjjjjjjj','urgent','2026-06-05 20:39:17'),(7,1,'gggggggg','gggggggggg','normal','2026-06-06 10:35:05'),(8,1,'gggggggg','gggggggggg','normal','2026-06-06 10:35:10'),(9,1,'gggggggg','gggggggggg','normal','2026-06-06 10:35:11'),(10,1,'gggggggg','gggggggggg','normal','2026-06-06 10:35:12'),(11,1,'gggggggg','gggggggggg','normal','2026-06-06 10:35:12'),(12,1,'Hello All','Tomorrow at 2:00 LT we will have short meeting at vice administrator','urgent','2026-06-06 10:47:06'),(13,1,'Hello All','Tomorrow at 2:00 LT we will have short meeting at vice administrator','urgent','2026-06-06 10:47:07'),(14,1,'Hello All','Tomorrow at 2:00 LT we will have short meeting at vice administrator','urgent','2026-06-06 10:47:08'),(15,1,'Hello All','Tomorrow at 2:00 LT we will have short meeting at vice administrator','urgent','2026-06-06 10:47:08'),(16,1,'Hello All','Tomorrow at 2:00 LT we will have short meeting at vice administrator','urgent','2026-06-06 10:51:29'),(17,1,'Hello All','Tomorrow at 2:00 LT we will have short meeting at vice administrator','urgent','2026-06-06 10:51:31'),(18,1,'hhrrh','eee','normal','2026-06-06 10:51:45'),(19,1,'hhrrh','eee','normal','2026-06-06 10:51:46');
/*!40000 ALTER TABLE `staff_announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_messages`
--

DROP TABLE IF EXISTS `staff_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `channel` varchar(50) DEFAULT 'general',
  `message` text NOT NULL,
  `msg_type` enum('text','image','file','announcement') DEFAULT 'text',
  `file_path` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reply_to` int(11) DEFAULT NULL,
  `is_edited` tinyint(1) DEFAULT 0,
  `is_deleted` tinyint(1) DEFAULT 0,
  `is_pinned` tinyint(1) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `channel` (`channel`),
  KEY `sender_id` (`sender_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_messages`
--

LOCK TABLES `staff_messages` WRITE;
/*!40000 ALTER TABLE `staff_messages` DISABLE KEYS */;
INSERT INTO `staff_messages` VALUES (1,1,NULL,'random','hello','text',NULL,0,'2026-06-05 20:38:02',NULL,0,0,0,'2026-06-06 10:45:46'),(2,1,NULL,'general','hello','text',NULL,0,'2026-06-05 20:38:19',NULL,0,0,0,'2026-06-06 10:45:46'),(3,1,NULL,'general','???? [urgent] hhhhhhhhh: jjjjjjjjjjjjjj','announcement',NULL,0,'2026-06-05 20:39:08',NULL,0,0,0,'2026-06-06 10:45:46'),(4,1,NULL,'general','???? [urgent] hhhhhhhhh: jjjjjjjjjjjjjj','announcement',NULL,0,'2026-06-05 20:39:11',NULL,0,0,0,'2026-06-06 10:45:46'),(5,1,NULL,'general','???? [urgent] hhhhhhhhh: jjjjjjjjjjjjjj','announcement',NULL,0,'2026-06-05 20:39:14',NULL,0,0,0,'2026-06-06 10:45:46'),(6,1,NULL,'general','???? [urgent] hhhhhhhhh: jjjjjjjjjjjjjj','announcement',NULL,0,'2026-06-05 20:39:16',NULL,0,0,0,'2026-06-06 10:45:46'),(7,1,NULL,'general','???? [urgent] hhhhhhhhh: jjjjjjjjjjjjjj','announcement',NULL,0,'2026-06-05 20:39:16',NULL,0,0,0,'2026-06-06 10:45:46'),(8,1,NULL,'general','???? [urgent] hhhhhhhhh: jjjjjjjjjjjjjj','announcement',NULL,0,'2026-06-05 20:39:17',NULL,0,0,0,'2026-06-06 10:45:46'),(9,1,NULL,'general','???? [normal] gggggggg: gggggggggg','announcement',NULL,0,'2026-06-06 10:35:06',NULL,0,0,0,'2026-06-06 10:45:46'),(10,1,NULL,'general','???? [normal] gggggggg: gggggggggg','announcement',NULL,0,'2026-06-06 10:35:10',NULL,0,0,0,'2026-06-06 10:45:46'),(11,1,NULL,'general','???? [normal] gggggggg: gggggggggg','announcement',NULL,0,'2026-06-06 10:35:11',NULL,0,0,0,'2026-06-06 10:45:46'),(12,1,NULL,'general','???? [normal] gggggggg: gggggggggg','announcement',NULL,0,'2026-06-06 10:35:12',NULL,0,0,0,'2026-06-06 10:45:46'),(13,1,NULL,'general','???? [normal] gggggggg: gggggggggg','announcement',NULL,0,'2026-06-06 10:35:12',NULL,0,0,0,'2026-06-06 10:45:46'),(14,1,NULL,'general','hello','text',NULL,0,'2026-06-06 10:45:26',NULL,0,0,0,'2026-06-06 10:45:46'),(15,1,NULL,'general','???? [urgent] Hello All: Tomorrow at 2:00 LT we will have short meeting at vice administrator','announcement',NULL,0,'2026-06-06 10:47:06',NULL,0,0,0,'2026-06-06 10:47:06'),(16,1,NULL,'general','???? [urgent] Hello All: Tomorrow at 2:00 LT we will have short meeting at vice administrator','announcement',NULL,0,'2026-06-06 10:47:07',NULL,0,0,0,'2026-06-06 10:47:07'),(17,1,NULL,'general','???? [urgent] Hello All: Tomorrow at 2:00 LT we will have short meeting at vice administrator','announcement',NULL,0,'2026-06-06 10:47:08',NULL,0,0,0,'2026-06-06 10:47:08'),(18,1,NULL,'general','???? [urgent] Hello All: Tomorrow at 2:00 LT we will have short meeting at vice administrator','announcement',NULL,0,'2026-06-06 10:47:08',NULL,0,0,0,'2026-06-06 10:47:08'),(19,1,NULL,'general','???? [urgent] Hello All: Tomorrow at 2:00 LT we will have short meeting at vice administrator','announcement',NULL,0,'2026-06-06 10:51:30',NULL,0,0,0,'2026-06-06 10:51:30'),(20,1,NULL,'general','???? [urgent] Hello All: Tomorrow at 2:00 LT we will have short meeting at vice administrator','announcement',NULL,0,'2026-06-06 10:51:31',NULL,0,0,0,'2026-06-06 10:51:31'),(21,1,NULL,'general','???? [normal] hhrrh: eee','announcement',NULL,0,'2026-06-06 10:51:45',NULL,0,0,0,'2026-06-06 10:51:45'),(22,1,NULL,'general','???? [normal] hhrrh: eee','announcement',NULL,0,'2026-06-06 10:51:46',NULL,0,0,0,'2026-06-06 10:51:46'),(23,1,NULL,'general','hee','text',NULL,0,'2026-06-06 10:51:55',NULL,0,0,0,'2026-06-06 10:51:55'),(24,1,6,'dm_6','hhh','text',NULL,0,'2026-06-06 10:53:24',NULL,0,0,0,'2026-06-06 10:53:24');
/*!40000 ALTER TABLE `staff_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_online`
--

DROP TABLE IF EXISTS `staff_online`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_online` (
  `user_id` int(11) NOT NULL,
  `last_seen` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_online`
--

LOCK TABLES `staff_online` WRITE;
/*!40000 ALTER TABLE `staff_online` DISABLE KEYS */;
INSERT INTO `staff_online` VALUES (1,'2026-06-06 17:40:40'),(6,'2026-06-06 11:02:26');
/*!40000 ALTER TABLE `staff_online` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_reactions`
--

DROP TABLE IF EXISTS `staff_reactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_reactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `emoji` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_reaction` (`message_id`,`user_id`,`emoji`),
  KEY `message_id` (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_reactions`
--

LOCK TABLES `staff_reactions` WRITE;
/*!40000 ALTER TABLE `staff_reactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff_reactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_read_receipts`
--

DROP TABLE IF EXISTS `staff_read_receipts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_read_receipts` (
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `read_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`message_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_read_receipts`
--

LOCK TABLES `staff_read_receipts` WRITE;
/*!40000 ALTER TABLE `staff_read_receipts` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff_read_receipts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_typing`
--

DROP TABLE IF EXISTS `staff_typing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_typing` (
  `user_id` int(11) NOT NULL,
  `channel` varchar(50) NOT NULL,
  `last_typed` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`,`channel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_typing`
--

LOCK TABLES `staff_typing` WRITE;
/*!40000 ALTER TABLE `staff_typing` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff_typing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_roles`
--

DROP TABLE IF EXISTS `system_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(100) NOT NULL,
  `role_key` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_key` (`role_key`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_roles`
--

LOCK TABLES `system_roles` WRITE;
/*!40000 ALTER TABLE `system_roles` DISABLE KEYS */;
INSERT INTO `system_roles` VALUES (1,'System Administrator(Worku)','admin','Full system access','2026-05-19 12:01:24'),(2,'Land and house manager','controller and manager','Standard office tasks and record management','2026-05-19 12:01:24'),(3,'Prosperity Party Manager','Managing all prosperity party','Managing all prosperity party , Data entry and record verification','2026-05-19 12:01:24'),(4,'Kebele Administrator','manager ','Supervisory role','2026-05-19 12:01:24'),(5,'Vice Administrator','security','Access control and security monitoring','2026-05-19 12:01:24'),(6,'secretary','secretary','','2026-06-05 19:55:33'),(7,'Kebele Linker With Jimma City Administration','linker_with_jimma_city_administration','','2026-06-06 11:06:10');
/*!40000 ALTER TABLE `system_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) DEFAULT 'general',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES ('contact_email','info@bosaaddis.gov.et','contact','2026-06-06 17:41:37'),('contact_phone','+251 900 000 000','contact','2026-05-02 17:54:56'),('currency','ETB','general','2026-05-08 19:25:25'),('currency_symbol','ETB','finance','2026-05-02 17:54:56'),('dark_mode','0','appearance','2026-05-20 06:25:14'),('default_language','en','general','2026-05-08 19:25:25'),('enable_public_registration','1','general','2026-05-08 19:25:25'),('kebele_name','Bosa Addis','general','2026-05-20 08:33:32'),('maintenance_mode','0','system','2026-05-02 17:54:57'),('max_file_upload_mb','5','upload','2026-05-08 19:25:25'),('office_address','Bosa Addis Kebele, Jimma City','contact','2026-05-20 08:33:32'),('region_name','Oromia','general','2026-05-02 17:54:56'),('require_email_verification','0','general','2026-05-08 19:25:25'),('system_name','Bosa Addis Kebele Management System','general','2026-05-20 08:43:00'),('zone_name','Jimma','general','2026-05-06 06:41:43');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resident_id` int(11) NOT NULL,
  `service_type` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('Telebirr','CBE Birr','Sinqe Bank','Coop Bank','Cash','Other') DEFAULT 'Telebirr',
  `transaction_ref` varchar(100) NOT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `screenshot_path` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Completed','Failed') DEFAULT 'Completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_ref` (`transaction_ref`),
  KEY `transactions_ibfk_1` (`resident_id`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `individuals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (4,9,'birth_cert',30.00,'Cash','CASH-9131',NULL,NULL,'Completed','2026-05-02 13:00:06'),(5,11,'clearance_cert',40.00,'CBE Birr','TXN-BIR-1777726818-569',NULL,NULL,'Completed','2026-05-02 13:00:43'),(6,9,'id_card',500.00,'CBE Birr','TXN-ID_-1777727928-544','proof_1777727957_203.png',NULL,'Completed','2026-05-02 13:19:17'),(7,9,'id_card',500.00,'CBE Birr','TXN-ID_-1777727957-303','proof_1777727976_597.png',NULL,'Completed','2026-05-02 13:19:36'),(8,9,'id_card',500.00,'CBE Birr','TXN-ID_-1777727976-350','proof_1777728011_580.png',NULL,'Completed','2026-05-02 13:20:11'),(9,12,'birth_cert',400.00,'CBE Birr','TXN-BIR-1777728140-396','proof_1777728166_843.png',NULL,'Completed','2026-05-02 13:22:46'),(10,8,'birth_cert',400.00,'Cash','CASH-6224',NULL,NULL,'Completed','2026-05-02 13:45:01'),(11,8,'marriage_cert',400.00,'Telebirr','TXN-MAR-1777743295-312','proof_1777743359_346.jpg',NULL,'','2026-05-02 17:35:59'),(12,8,'id_card',500.00,'Telebirr','TXN-ID_-1777743421-147','proof_1777743451_344.jpg',NULL,'','2026-05-02 17:37:31'),(13,8,'id_card',500.00,'Telebirr','TXN-ID_-1777743451-462','proof_1777743481_702.jpg',NULL,'Completed','2026-05-02 17:38:01'),(14,10,'id_card',500.00,'Coop Bank','TXN-ID_-1777743551-287','proof_1777743573_744.jpg',NULL,'Completed','2026-05-02 17:39:33'),(15,8,'marriage_cert',400.00,'Sinqe Bank','TXN-MAR-1777748879-165','proof_1777748918_871.png',NULL,'Completed','2026-05-02 19:08:38'),(16,12,'id_card',500.00,'Telebirr','TXN-ID_-1777786301-869','proof_1777786366_794.jpg',NULL,'Completed','2026-05-03 05:32:46'),(28,9,'id_card',500.00,'Telebirr','TXN-ID_-1777788288-467','proof_1777788408_947.png',NULL,'Pending','2026-05-03 06:06:48'),(29,8,'id_card',500.00,'Telebirr','TXN-ID_-1777788413-776','proof_1777788428_475.png',NULL,'','2026-05-03 06:07:08'),(30,10,'id_card',500.00,'Telebirr','TXN-ID_-1777788437-543','proof_1777788452_747.png',NULL,'Completed','2026-05-03 06:07:32'),(31,8,'id_card',500.00,'CBE Birr','TXN-ID_-1777876878-841','proof_1777876940_980.jpg',NULL,'Completed','2026-05-04 06:42:20'),(32,12,'id_card',500.00,'CBE Birr','TXN-ID_-1777884444-332','proof_1777884481_590.jpg',NULL,'Completed','2026-05-04 08:48:01'),(33,8,'divorce_cert',400.00,'Cash','CASH-9622',NULL,NULL,'Completed','2026-05-04 08:49:41'),(34,9,'id_card',500.00,'Sinqe Bank','TXN-ID_-1777893359-311','proof_1777893395_898.png',NULL,'Pending','2026-05-04 11:16:35'),(35,11,'id_card',500.00,'Sinqe Bank','TXN-ID_-1777927290-673','proof_1777927333_200.jpg',NULL,'Completed','2026-05-04 20:42:13'),(36,9,'id_card',500.00,'Sinqe Bank','TXN-ID_-1777970927-926','proof_1777970987_100.png',NULL,'Completed','2026-05-05 08:49:47'),(37,19,'death_cert',400.00,'Telebirr','TXN-DEA-1778281534-994','proof_1778281554_770.jpg',NULL,'Completed','2026-05-08 23:05:54'),(38,21,'id_card',500.00,'Sinqe Bank','TXN-ID_-1778415459-209','proof_1778415494_206.png',NULL,'Completed','2026-05-10 12:18:14'),(39,21,'birth_cert',400.00,'CBE Birr','TXN-BIR-1778415630-795','proof_1778415652_186.jpg',NULL,'Completed','2026-05-10 12:20:52'),(40,11,'id_card',500.00,'Cash','CASH-3488',NULL,NULL,'Completed','2026-05-10 12:38:55'),(41,22,'id_card',500.00,'Telebirr','TXN-ID_-1778615385-877','proof_1778615421_711.jpg',NULL,'Completed','2026-05-12 19:50:21'),(42,22,'id_card',500.00,'Telebirr','TXN-ID_-1778615422-212','proof_1778615452_165.jpg',NULL,'','2026-05-12 19:50:52'),(43,22,'birth_cert',400.00,'CBE Birr','TXN-BIR-1778616085-711','proof_1778616107_298.jpg',NULL,'Completed','2026-05-12 20:01:47'),(44,22,'marriage_cert',400.00,'Sinqe Bank','TXN-MAR-1778616448-225','proof_1778616500_618.jpg',NULL,'Completed','2026-05-12 20:08:20'),(45,24,'id_card',500.00,'CBE Birr','TXN-ID_-1778618507-364','proof_1778618555_200.jpg',NULL,'Completed','2026-05-12 20:42:35'),(46,21,'marriage_cert',400.00,'Cash','CASH-4924',NULL,NULL,'Completed','2026-05-12 20:49:56'),(47,24,'birth_cert',400.00,'Sinqe Bank','TXN-BIR-1778619352-328','proof_1778619375_762.jpg',NULL,'Completed','2026-05-12 20:56:15'),(48,9,'clearance_cert',400.00,'CBE Birr','TXN-CLE-1778714513-864','proof_1778714537_349.jpg',NULL,'Completed','2026-05-13 23:22:17'),(49,23,'id_card',500.00,'Sinqe Bank','TXN-ID_-1778746105-601','proof_1778746142_972.jpg',NULL,'Completed','2026-05-14 08:09:02'),(50,22,'id_card',500.00,'CBE Birr','TXN-ID_-1778747097-544','proof_1778747119_683.jpg',NULL,'Completed','2026-05-14 08:25:19'),(51,10,'id_card',500.00,'Sinqe Bank','TXN-ID_-1778953516-388','proof_1778953539_445.png',NULL,'Completed','2026-05-16 17:45:39'),(52,23,'id_card',500.00,'Sinqe Bank','TXN-ID_-1778957266-137','proof_1778957347_183.jpg',NULL,'Completed','2026-05-16 18:49:07'),(53,8,'clearance_cert',400.00,'CBE Birr','TXN-CLE-1778958073-696','proof_1778958093_865.png',NULL,'Completed','2026-05-16 19:01:33'),(54,11,'id_card',500.00,'Sinqe Bank','TXN-ID_-1779179418-238','proof_1779179465_952.png',NULL,'Completed','2026-05-19 08:31:05'),(55,20,'birth_cert',400.00,'CBE Birr','TXN-BIR-1779179559-853','proof_1779179586_199.png',NULL,'Completed','2026-05-19 08:33:06'),(56,29,'id_card',500.00,'Telebirr','TXN-ID_-1779249730-978','proof_1779249771_647.jpg',NULL,'Completed','2026-05-20 04:02:51'),(57,30,'id_card',500.00,'Telebirr','TXN-ID_-1779261639-363','proof_1779261662_449.jpg',NULL,'Completed','2026-05-20 07:21:02'),(58,31,'id_card',500.00,'Sinqe Bank','TXN-ID_-1779884193-668','proof_1779884236_993.jpg',NULL,'Completed','2026-05-27 12:17:16'),(59,8,'police_id',30.00,'Cash','CASH-687',NULL,NULL,'Completed','2026-05-27 19:22:06'),(60,22,'milisha_id',20.00,'Cash','CASH-4115',NULL,NULL,'Completed','2026-05-27 20:12:03'),(61,11,'gachana_id',15.00,'Cash','CASH-2236',NULL,NULL,'Completed','2026-05-27 20:12:47'),(62,23,'safetynet_id',5.00,'Cash','CASH-68',NULL,NULL,'Completed','2026-05-27 20:30:56'),(63,22,'youth_id',5.00,'Cash','CASH-3713',NULL,NULL,'Completed','2026-05-27 20:43:26'),(64,21,'letter_verification',10.00,'Cash','CASH-228',NULL,NULL,'Completed','2026-05-27 20:49:52'),(65,21,'letter_verification',10.00,'Cash','CASH-2451',NULL,NULL,'Completed','2026-05-27 20:51:42'),(66,21,'letter_residency',10.00,'Cash','CASH-5833',NULL,NULL,'Completed','2026-05-27 20:52:10'),(67,11,'youth_id',5.00,'Cash','CASH-6498',NULL,NULL,'Completed','2026-05-27 21:04:46'),(68,11,'letter_verification',10.00,'Cash','CASH-7974',NULL,NULL,'Completed','2026-06-03 08:22:12');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `photo` varchar(255) DEFAULT 'default_admin.png',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','Worku Fikadu','nafyadfikaduerena@gmail.com','+251919639519','$2y$10$GvefVia6w7DiSQQLeXvJceXkIQrIgJnqYmvBmQuSf/OOaxnloa1s.','admin','2026-04-30 11:26:07','default_admin.png'),(3,'Hawi','Hawi','','','$2y$10$jlS4gf7yBMCUeO00mgvDouW4V5SjzFCUqd2QnRmCTCI2AF9VY7WpC','security','2026-05-01 08:14:14','default_admin.png'),(4,'manager','Nezif Teleha','','','$2y$10$WE.rw2J1itFeTQIE.0sx1.UbKLymSUSi7BcT9BsTR7NZS2z8/6.Pa','manager ','2026-05-02 18:26:36','default_admin.png'),(5,'Hirpha','Hirphaasa Gudeta','hirpha@gmail.com','+251919639519','$2y$10$NzV3n74F8IpEpdawowO0buNTYfDoFM1Ymg5p307NkgYGgXBhp8qm6','Managing all prosperity party','2026-05-20 11:53:59','profile_5_1779278090.jpg'),(6,'Gosaye','Gosaye Diriba','gosaye@gmail.com','+251919639519','$2y$10$V8iXbrSup.hFqoC0Fbcyj.TAzhl1Q0/eoI7UZKplyWDxwgjqJdaEK','controller and manager','2026-06-05 19:42:00','default_admin.png'),(7,'Jihad','Jihad Husen ','','+251919639519','$2y$10$gOIjM2auWHET4fw1T/OKcuxt1uU1OLCnTx3zcVUY44y0RB/IS.a9C','linker_with_jimma_city_administration','2026-06-06 11:04:15','default_admin.png');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vital_certificates`
--

DROP TABLE IF EXISTS `vital_certificates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vital_certificates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resident_id` int(11) NOT NULL,
  `cert_type` enum('birth','death','clearance','marriage','divorce') NOT NULL,
  `cert_number` varchar(50) NOT NULL,
  `issue_date` date NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `cert_number` (`cert_number`),
  KEY `resident_id` (`resident_id`),
  CONSTRAINT `vital_certificates_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `individuals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vital_certificates`
--

LOCK TABLES `vital_certificates` WRITE;
/*!40000 ALTER TABLE `vital_certificates` DISABLE KEYS */;
INSERT INTO `vital_certificates` VALUES (10,8,'marriage','BA-MR001','2026-05-01','Have a nice marriage','2026-05-01 09:04:05'),(11,8,'divorce','BA-DV001','2026-05-01','Per Article of the Revised Family Code of Ethiopia, divorce must be performed with the full, informed, and voluntary consent of both spouses or by court decision. ','2026-05-01 09:31:16'),(12,10,'birth','BA-BC01','2026-05-01','','2026-05-01 10:54:11'),(13,10,'marriage','BA-MR002','2026-05-01','','2026-05-01 10:57:02'),(14,10,'clearance','BA-CL01','2026-05-01','Destination: Gonder University | Reason: employment | Extra: ','2026-05-01 11:03:15'),(15,11,'birth','BA-BC02','2026-05-01','','2026-05-01 12:36:40'),(16,12,'birth','BA-BC03','2026-05-02','','2026-05-02 08:29:27'),(17,12,'marriage','BA-MR003','2026-05-02','','2026-05-02 08:31:52'),(18,11,'clearance','BA-CL02','2026-05-02','Destination: jimma university | Reason: employment | Extra: ','2026-05-02 11:21:59'),(19,9,'birth','BA-BC04','2026-05-02','','2026-05-02 12:48:13'),(20,8,'birth','BA-BC05','2026-05-02','','2026-05-02 13:43:30'),(21,8,'marriage','BA-MR004','2026-05-02','','2026-05-02 13:45:45'),(25,19,'death','BA-DC00','2026-05-09','Reason: ggggggggggggg','2026-05-08 23:02:55'),(26,21,'birth','BA-BC06','2026-05-10','','2026-05-10 12:20:28'),(27,22,'birth','BA-BC07','2026-05-12','','2026-05-12 20:01:15'),(28,22,'marriage','BA-MR005','2026-05-12','','2026-05-12 20:06:51'),(29,21,'marriage','BA-MR006','2026-05-12','','2026-05-12 20:49:36'),(30,24,'birth','BA-BC08','2026-05-12','','2026-05-12 20:55:44'),(31,9,'clearance','BA-CL03','2026-05-14','Destination: Finfinne | Reason: Education  | Extra: I\'m transfering from Jimma high school to Gara Guri School ( Finfinne)','2026-05-13 23:21:50'),(33,8,'clearance','BA-CL04','2026-05-16','Destination: gffttt | Reason: t6ytdtdf | Extra: ','2026-05-16 19:01:10'),(35,20,'birth','BA-BC09','2026-05-19','','2026-05-19 08:32:36'),(36,19,'birth','BA-BC10','2026-06-03','','2026-06-03 02:09:06');
/*!40000 ALTER TABLE `vital_certificates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `welfare_records`
--

DROP TABLE IF EXISTS `welfare_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `welfare_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `individual_id` int(11) NOT NULL,
  `vulnerability_type` enum('Elderly','Disabled','Orphan','Low Income','Displaced') NOT NULL,
  `disability_details` varchar(255) DEFAULT NULL,
  `aid_status` enum('Registered','Receiving Aid','Waitlist','Graduated') DEFAULT 'Registered',
  `aid_type` varchar(100) DEFAULT NULL,
  `next_review_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `individual_id` (`individual_id`),
  CONSTRAINT `welfare_records_ibfk_1` FOREIGN KEY (`individual_id`) REFERENCES `individuals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `welfare_records`
--

LOCK TABLES `welfare_records` WRITE;
/*!40000 ALTER TABLE `welfare_records` DISABLE KEYS */;
/*!40000 ALTER TABLE `welfare_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `youth_id_cards`
--

DROP TABLE IF EXISTS `youth_id_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `youth_id_cards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `youth_record_id` int(11) NOT NULL,
  `id_num` varchar(50) NOT NULL,
  `issue_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `status` enum('Active','Lost','Expired') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `youth_record_id` (`youth_record_id`),
  UNIQUE KEY `id_num` (`id_num`),
  CONSTRAINT `youth_id_cards_ibfk_1` FOREIGN KEY (`youth_record_id`) REFERENCES `economic_youth_registry` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `youth_id_cards`
--

LOCK TABLES `youth_id_cards` WRITE;
/*!40000 ALTER TABLE `youth_id_cards` DISABLE KEYS */;
INSERT INTO `youth_id_cards` VALUES (1,1,'YOUTH0001','2026-05-27','2031-05-27','Active','2026-05-27 20:43:26'),(2,2,'YOUTH0002','2026-05-27','2031-05-27','Active','2026-05-27 21:04:46');
/*!40000 ALTER TABLE `youth_id_cards` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-10 22:08:54
