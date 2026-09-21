-- MySQL dump 10.13  Distrib 8.0.46, for Linux (x86_64)
-- Host: localhost    Database: pyros
-- ------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `buildings`
--

DROP TABLE IF EXISTS `buildings`;
CREATE TABLE `buildings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `json_data` json DEFAULT NULL,
  `calculated_values` json DEFAULT NULL,
  `qf` float DEFAULT NULL,
  `heat_loss` float DEFAULT NULL,
  `image_id` varchar(255) DEFAULT NULL,
  `complex` int DEFAULT NULL,
  `project_id` varchar(255) DEFAULT NULL,
  `size` float DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_complex_id` (`complex`),
  CONSTRAINT `fk_complex_id` FOREIGN KEY (`complex`) REFERENCES `complex` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buildings`
--

LOCK TABLES `buildings` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `complex`
--

DROP TABLE IF EXISTS `complex`;
CREATE TABLE `complex` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `postal` varchar(10) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `parcelNumber` varchar(255) DEFAULT NULL,
  `project_id` varchar(255) DEFAULT NULL,
  `complex_json` json DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complex`
--

LOCK TABLES `complex` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `heating_systems`
--

DROP TABLE IF EXISTS `heating_systems`;
CREATE TABLE `heating_systems` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `purpose` enum('HEAT','COOL','BOTH') DEFAULT NULL,
  `regulation` enum('NONE','REG_A','REG_B','REG_C','REG_D') DEFAULT NULL,
  `description` enum('NONE','REGDESC_A','REGDESC_B','REGDESC_C','REGDESC_D') DEFAULT NULL,
  `heaters` json DEFAULT NULL,
  `pumps` json DEFAULT NULL,
  `emitters` json DEFAULT NULL,
  `project_id` varchar(255) DEFAULT NULL,
  `complex` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `heating_systems`
--

LOCK TABLES `heating_systems` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `image_info`
--

DROP TABLE IF EXISTS `image_info`;
CREATE TABLE `image_info` (
  `id` int NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) DEFAULT NULL,
  `reference_type` enum('BUILDING','HEATER','PUMP','EMITTER','VENTILATION') DEFAULT NULL,
  `reference_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `image_info`
--

LOCK TABLES `image_info` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `lighting_systems`
--

DROP TABLE IF EXISTS `lighting_systems`;
CREATE TABLE `lighting_systems` (
  `link` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `size` float DEFAULT NULL,
  `solution` varchar(255) DEFAULT NULL,
  `dim` varchar(255) DEFAULT NULL,
  `zone_usage` varchar(255) DEFAULT NULL,
  `regulation` varchar(255) DEFAULT NULL,
  `natural_light` varchar(255) DEFAULT NULL,
  `emergency` tinyint(1) DEFAULT '0',
  `standby` tinyint(1) DEFAULT '0',
  `specific_sum` float DEFAULT NULL,
  `yearly_sum` float DEFAULT NULL,
  `id` int NOT NULL AUTO_INCREMENT,
  `project_id` varchar(255) DEFAULT NULL,
  `standing` varchar(255) DEFAULT NULL,
  `complex` varchar(255) DEFAULT NULL,
  `building` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lighting_systems`
--

LOCK TABLES `lighting_systems` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `product`
--

DROP TABLE IF EXISTS `product`;
CREATE TABLE `product` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_name` varchar(255) DEFAULT NULL,
  `metric` varchar(255) DEFAULT NULL,
  `json` json DEFAULT NULL,
  `project_id` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

LOCK TABLES `product` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `standings`
--

DROP TABLE IF EXISTS `standings`;
CREATE TABLE `standings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `measurement_type` enum('MAIN','SUB','VIRTUAL') DEFAULT NULL,
  `sub_to` int DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `measurement` varchar(50) DEFAULT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `consumption` json DEFAULT NULL,
  `project_id` varchar(255) DEFAULT NULL,
  `purpose` enum('BUILDING','SERVICE','CARRY') DEFAULT NULL,
  `pod` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_standings_sub_to` (`sub_to`),
  CONSTRAINT `fk_standings_sub_to` FOREIGN KEY (`sub_to`) REFERENCES `standings` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `standings`
--

LOCK TABLES `standings` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `standings_to_other`
--

DROP TABLE IF EXISTS `standings_to_other`;
CREATE TABLE `standings_to_other` (
  `standing` int DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `type` enum('BUILDING','COMPLEX','SYSTEM','HEATER','TECHNOLOGY','VEHICLE','LIGHTING') DEFAULT NULL,
  KEY `fk_standings_uid` (`standing`),
  CONSTRAINT `fk_standings_uid` FOREIGN KEY (`standing`) REFERENCES `standings` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `standings_to_other`
--

LOCK TABLES `standings_to_other` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `technology`
--

DROP TABLE IF EXISTS `technology`;
CREATE TABLE `technology` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `json` json DEFAULT NULL,
  `technology_type` enum('COMPRESSED_AIR','STEAM','COOLING','OTHER') DEFAULT NULL,
  `project_id` varchar(255) DEFAULT NULL,
  `complex` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `technology`
--

LOCK TABLES `technology` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(10) DEFAULT NULL,
  `password` text,
  `cu_id` varchar(30) DEFAULT NULL,
  `cu_uname` varchar(30) DEFAULT NULL,
  `last_joined` date DEFAULT NULL,
  `role` enum('ADMIN','OPERATOR') DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES 
(1,'admin','$2y$10$9yds2gSXMcLtGZsknvg2Z.RadFtdUPX2ZuGfPDAOc/1pVZNCsz7Ky','88439708','Zétény Nagy','2026-02-26','ADMIN'),
(2,'kk','$2y$10$5rh9.0varVszIKZ.7M.VmurfwAvY3xiPyiO8mY./wkCHVRFRFkeXW','248473479','Kismárton Krisztián',NULL,'OPERATOR'),
(3,'hg','$2y$10$AtXYnChm768wvq0x7ZJueObPJ1SZSeoPysQLH92twyQuBsFksiI82','94201273','Huszárik György','2026-02-23','OPERATOR'),
(4,'at','$2y$10$avZPapEVaaMEz735lukVsu/X78KGcG./YbufxpKAZ.oVve0Hoj/RC','88434710','Almási Tamás','2026-03-16','OPERATOR'),
(5,'bzs','$2y$10$K2uEPTyhQrQnMOr7bSgeEeOI7F/R1qE1amJzMrhdHq5ZVN8mxpEd6','224532850','Balajti Zsolt','2026-02-06','ADMIN'),
(6,'ks','$2y$10$Mybr0F6XdVGaci2KAaumEuvpRgSzYmI8FD9CzC9IGZ4Fb5ai5GZ0S','88422344','Sanyi és Robi',NULL,'OPERATOR'),
(7,'kb','$2y$10$w1DxfoxnTCEjh2yhpDDiJu68wgyBzN/3ZvakU12.r7K9R2oyN3LiC','94204925','Koffán Balázs','2026-02-11','OPERATOR'),
(8,'bd','$2y$10$fbQo1d6KvE14Gh2VPLdFbePjjJ5IMPQZGLG8rLVLLsMYcDZIFdGg2','94209674','Berki Dávid','2026-02-09','OPERATOR'),
(9,'ka','$2y$10$xLdKgH5M3TddtYZjE2QJ3usKDP6iBB8W4t3XkOFA8EhWWkHfCTZiC','94214549','Attila Kondricz','2026-02-23','OPERATOR'),
(10,'bz','$2y$10$5FeJiYLwh6sL8dYERI.FYeohdfnYuHT2Lw2ltlsZARdIZEJqdvYmi','94214548','Bonta Zoltán','2026-02-12','OPERATOR'),
(11,'st','$2y$10$P0BXrKKP.QUci35QRI8wYuDii83IgmvgsTWSLRLral5xELSU8yBe2','94215912','Tamás Szőke','2026-03-06','OPERATOR'),
(12,'sa','$2y$10$EUOzSsrQ0oM9/RDYClKH5u0ZVc944g4N43sPts3H1TgZgxWTDDhC.','94425482','Serfőző Attila','2026-03-26','OPERATOR');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `variables`
--

DROP TABLE IF EXISTS `variables`;
CREATE TABLE `variables` (
  `id` int NOT NULL AUTO_INCREMENT,
  `project_id` varchar(255) DEFAULT NULL,
  `json` json DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `variables`
--

LOCK TABLES `variables` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `vehicles`
--

DROP TABLE IF EXISTS `vehicles`;
CREATE TABLE `vehicles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `project_id` varchar(255) DEFAULT NULL,
  `complex_id` int DEFAULT NULL,
  `standing_id` int DEFAULT NULL,
  `usage_metric` varchar(255) DEFAULT NULL,
  `usage_value` float DEFAULT NULL,
  `usage_value2` float DEFAULT NULL,
  `fuel` varchar(255) DEFAULT NULL,
  `hibrid` tinyint(1) DEFAULT NULL,
  `motor_size` int DEFAULT NULL,
  `capacity` float DEFAULT NULL,
  `vehicle_category` varchar(255) DEFAULT NULL,
  `chargeable` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

LOCK TABLES `vehicles` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `ventilation_systems`
--

DROP TABLE IF EXISTS `ventilation_systems`;
CREATE TABLE `ventilation_systems` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `json` json DEFAULT NULL,
  `sfp` float DEFAULT NULL,
  `category` varchar(10) DEFAULT NULL,
  `first_image` varchar(255) DEFAULT NULL,
  `second_image` varchar(255) DEFAULT NULL,
  `third_image` varchar(255) DEFAULT NULL,
  `project_id` varchar(255) DEFAULT NULL,
  `complex` varchar(255) DEFAULT NULL,
  `building` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ventilation_systems`
--

LOCK TABLES `ventilation_systems` WRITE;
UNLOCK TABLES;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;