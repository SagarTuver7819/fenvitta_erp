
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
DROP TABLE IF EXISTS `batch_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `batch_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_id` int(11) NOT NULL,
  `entry_date` date NOT NULL,
  `stage` varchar(80) DEFAULT NULL,
  `activity` varchar(150) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `parameter` varchar(100) DEFAULT NULL,
  `observed_value` varchar(100) DEFAULT NULL,
  `done_by` varchar(80) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `batch_id` (`batch_id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `batch_data` WRITE;
/*!40000 ALTER TABLE `batch_data` DISABLE KEYS */;
INSERT INTO `batch_data` VALUES (1,1,'2026-04-12','Raw Material Charging','Charged Pyridine & CSA in reactor R-101','09:00:00','12:30:00','Temperature (°C)','5-10','Druvin G.',NULL,'2026-10-15 07:10:56'),(2,1,'2026-04-14','Reaction','Sulphation reaction with sucrose','10:00:00','13:30:00','Temperature (°C)','62','Mayur R.',NULL,'2026-10-15 07:10:56'),(3,1,'2026-04-16','Neutralisation','Neutralised with Soda Ash solution','11:00:00','14:30:00','pH','7.2','Altitude Shift-A',NULL,'2026-10-15 07:10:56'),(4,1,'2026-04-18','Distillation / Solvent Recovery','Pyridine recovery under vacuum','12:00:00','15:30:00','Vacuum (mmHg)','680','Druvin G.',NULL,'2026-10-15 07:10:56'),(5,1,'2026-04-20','Centrifuge','Wet cake centrifuged & washed','09:00:00','16:30:00','Weight (kg)','1460','Mayur R.',NULL,'2026-10-15 07:10:56'),(6,1,'2026-04-22','Drying','Dried in FBD','10:00:00','12:30:00','LOD (%)','3.8','Altitude Shift-A',NULL,'2026-10-15 07:10:56'),(7,1,'2026-04-22','Milling / Sifting','Milled & sifted through 80 mesh','11:00:00','13:30:00','Weight (kg)','982','Druvin G.',NULL,'2026-10-15 07:10:56'),(8,1,'2026-04-22','QC Sampling','Sample sent to QC','12:00:00','14:30:00','Assay (%)','99.1','Mayur R.',NULL,'2026-10-15 07:10:56'),(9,2,'2026-05-05','Raw Material Charging','Charged Pyridine & CSA in reactor R-101','09:00:00','12:30:00','Temperature (°C)','5-10','Druvin G.',NULL,'2026-10-15 07:10:56'),(10,2,'2026-05-07','Reaction','Sulphation reaction with sucrose','10:00:00','13:30:00','Temperature (°C)','62','Mayur R.',NULL,'2026-10-15 07:10:56'),(11,2,'2026-05-09','Neutralisation','Neutralised with Soda Ash solution','11:00:00','14:30:00','pH','7.2','Altitude Shift-A',NULL,'2026-10-15 07:10:56'),(12,2,'2026-05-11','Distillation / Solvent Recovery','Pyridine recovery under vacuum','12:00:00','15:30:00','Vacuum (mmHg)','680','Druvin G.',NULL,'2026-10-15 07:10:56'),(13,2,'2026-05-13','Centrifuge','Wet cake centrifuged & washed','09:00:00','16:30:00','Weight (kg)','1460','Mayur R.',NULL,'2026-10-15 07:10:56'),(14,2,'2026-05-15','Drying','Dried in FBD','10:00:00','12:30:00','LOD (%)','3.8','Altitude Shift-A',NULL,'2026-10-15 07:10:56'),(15,2,'2026-05-15','Milling / Sifting','Milled & sifted through 80 mesh','11:00:00','13:30:00','Weight (kg)','975','Druvin G.',NULL,'2026-10-15 07:10:56'),(16,2,'2026-05-15','QC Sampling','Sample sent to QC','12:00:00','14:30:00','Assay (%)','99.1','Mayur R.',NULL,'2026-10-15 07:10:56'),(17,3,'2026-05-22','Raw Material Charging','Charged Pyridine & CSA in reactor R-101','09:00:00','12:30:00','Temperature (°C)','5-10','Druvin G.',NULL,'2026-10-15 07:10:56'),(18,3,'2026-05-24','Reaction','Sulphation reaction with sucrose','10:00:00','13:30:00','Temperature (°C)','62','Mayur R.',NULL,'2026-10-15 07:10:56'),(19,3,'2026-05-26','Neutralisation','Neutralised with Soda Ash solution','11:00:00','14:30:00','pH','7.2','Altitude Shift-A',NULL,'2026-10-15 07:10:56'),(20,3,'2026-05-28','Distillation / Solvent Recovery','Pyridine recovery under vacuum','12:00:00','15:30:00','Vacuum (mmHg)','680','Druvin G.',NULL,'2026-10-15 07:10:56'),(21,3,'2026-05-30','Centrifuge','Wet cake centrifuged & washed','09:00:00','16:30:00','Weight (kg)','1460','Mayur R.',NULL,'2026-10-15 07:10:56'),(22,3,'2026-06-01','Drying','Dried in FBD','10:00:00','12:30:00','LOD (%)','3.8','Altitude Shift-A',NULL,'2026-10-15 07:10:56'),(23,3,'2026-06-01','Milling / Sifting','Milled & sifted through 80 mesh','11:00:00','13:30:00','Weight (kg)','988','Druvin G.',NULL,'2026-10-15 07:10:56'),(24,3,'2026-06-01','QC Sampling','Sample sent to QC','12:00:00','14:30:00','Assay (%)','99.1','Mayur R.',NULL,'2026-10-15 07:10:56'),(25,4,'2026-06-20','Raw Material Charging','Charged Pyridine & CSA in reactor R-101','09:00:00','12:30:00','Temperature (°C)','5-10','Druvin G.',NULL,'2026-10-15 07:10:56'),(26,4,'2026-06-22','Reaction','Sulphation reaction with sucrose','10:00:00','13:30:00','Temperature (°C)','62','Mayur R.',NULL,'2026-10-15 07:10:56'),(27,4,'2026-06-24','Neutralisation','Neutralised with Soda Ash solution','11:00:00','14:30:00','pH','7.2','Altitude Shift-A',NULL,'2026-10-15 07:10:56'),(28,4,'2026-06-26','Distillation / Solvent Recovery','Pyridine recovery under vacuum','12:00:00','15:30:00','Vacuum (mmHg)','680','Druvin G.',NULL,'2026-10-15 07:10:56'),(29,4,'2026-06-28','Centrifuge','Wet cake centrifuged & washed','09:00:00','16:30:00','Weight (kg)','1460','Mayur R.',NULL,'2026-10-15 07:10:56'),(30,4,'2026-06-30','Drying','Dried in FBD','10:00:00','12:30:00','LOD (%)','3.8','Altitude Shift-A',NULL,'2026-10-15 07:10:56'),(31,4,'2026-06-30','Milling / Sifting','Milled & sifted through 80 mesh','11:00:00','13:30:00','Weight (kg)','969','Druvin G.',NULL,'2026-10-15 07:10:56'),(32,4,'2026-06-30','QC Sampling','Sample sent to QC','12:00:00','14:30:00','Assay (%)','99.1','Mayur R.',NULL,'2026-10-15 07:10:56'),(33,5,'2026-07-16','Raw Material Charging','Charged Pyridine & CSA in reactor R-101','09:00:00','12:30:00','Temperature (°C)','5-10','Druvin G.',NULL,'2026-10-15 07:10:56'),(34,5,'2026-07-18','Reaction','Sulphation reaction with sucrose','10:00:00','13:30:00','Temperature (°C)','62','Mayur R.',NULL,'2026-10-15 07:10:56'),(35,5,'2026-07-20','Neutralisation','Neutralised with Soda Ash solution','11:00:00','14:30:00','pH','7.2','Altitude Shift-A',NULL,'2026-10-15 07:10:56'),(36,5,'2026-07-22','Distillation / Solvent Recovery','Pyridine recovery under vacuum','12:00:00','15:30:00','Vacuum (mmHg)','680','Druvin G.',NULL,'2026-10-15 07:10:56'),(37,5,'2026-07-24','Centrifuge','Wet cake centrifuged & washed','09:00:00','16:30:00','Weight (kg)','1460','Mayur R.',NULL,'2026-10-15 07:10:56'),(38,5,'2026-07-26','Drying','Dried in FBD','10:00:00','12:30:00','LOD (%)','3.8','Altitude Shift-A',NULL,'2026-10-15 07:10:56'),(39,5,'2026-07-26','Milling / Sifting','Milled & sifted through 80 mesh','11:00:00','13:30:00','Weight (kg)','990','Druvin G.',NULL,'2026-10-15 07:10:56'),(40,5,'2026-07-26','QC Sampling','Sample sent to QC','12:00:00','14:30:00','Assay (%)','99.1','Mayur R.',NULL,'2026-10-15 07:10:56'),(41,6,'2026-08-21','Raw Material Charging','Charged Pyridine & CSA in reactor R-101','09:00:00','12:30:00','Temperature (°C)','5-10','Druvin G.',NULL,'2026-10-15 07:10:56'),(42,6,'2026-08-23','Reaction','Sulphation reaction with sucrose','10:00:00','13:30:00','Temperature (°C)','62','Mayur R.',NULL,'2026-10-15 07:10:56'),(43,6,'2026-08-25','Neutralisation','Neutralised with Soda Ash solution','11:00:00','14:30:00','pH','7.2','Altitude Shift-A',NULL,'2026-10-15 07:10:56'),(44,6,'2026-08-27','Distillation / Solvent Recovery','Pyridine recovery under vacuum','12:00:00','15:30:00','Vacuum (mmHg)','680','Druvin G.',NULL,'2026-10-15 07:10:56'),(45,6,'2026-08-29','Centrifuge','Wet cake centrifuged & washed','09:00:00','16:30:00','Weight (kg)','1460','Mayur R.',NULL,'2026-10-15 07:10:56'),(46,6,'2026-08-31','Drying','Dried in FBD','10:00:00','12:30:00','LOD (%)','3.8','Altitude Shift-A',NULL,'2026-10-15 07:10:56'),(47,6,'2026-08-31','Milling / Sifting','Milled & sifted through 80 mesh','11:00:00','13:30:00','Weight (kg)','978','Druvin G.',NULL,'2026-10-15 07:10:56'),(48,6,'2026-08-31','QC Sampling','Sample sent to QC','12:00:00','14:30:00','Assay (%)','99.1','Mayur R.',NULL,'2026-10-15 07:10:56'),(49,7,'2026-09-16','Raw Material Charging','Charged Pyridine & CSA in reactor R-101','09:00:00','12:30:00','Temperature (°C)','5-10','Druvin G.',NULL,'2026-10-15 07:10:56'),(50,7,'2026-09-18','Reaction','Sulphation reaction with sucrose','10:00:00','13:30:00','Temperature (°C)','62','Mayur R.',NULL,'2026-10-15 07:10:56'),(51,7,'2026-09-20','Neutralisation','Neutralised with Soda Ash solution','11:00:00','14:30:00','pH','7.2','Altitude Shift-A',NULL,'2026-10-15 07:10:56'),(52,7,'2026-09-22','Distillation / Solvent Recovery','Pyridine recovery under vacuum','12:00:00','15:30:00','Vacuum (mmHg)','680','Druvin G.',NULL,'2026-10-15 07:10:56'),(53,7,'2026-09-24','Centrifuge','Wet cake centrifuged & washed','09:00:00','16:30:00','Weight (kg)','1460','Mayur R.',NULL,'2026-10-15 07:10:56'),(54,7,'2026-09-26','Drying','Dried in FBD','10:00:00','12:30:00','LOD (%)','3.8','Altitude Shift-A',NULL,'2026-10-15 07:10:56'),(55,7,'2026-09-26','Milling / Sifting','Milled & sifted through 80 mesh','11:00:00','13:30:00','Weight (kg)','985','Druvin G.',NULL,'2026-10-15 07:10:56'),(56,7,'2026-09-26','QC Sampling','Sample sent to QC','12:00:00','14:30:00','Assay (%)','99.1','Mayur R.',NULL,'2026-10-15 07:10:56'),(57,8,'2026-10-08','Raw Material Charging','Charged Pyridine & CSA in reactor R-101','09:00:00','12:30:00','Temperature (°C)','5-10','Druvin G.',NULL,'2026-10-15 07:10:56'),(58,8,'2026-10-10','Reaction','Sulphation reaction with sucrose','10:00:00','13:30:00','Temperature (°C)','62','Mayur R.',NULL,'2026-10-15 07:10:56'),(59,8,'2026-10-12','Neutralisation','Neutralised with Soda Ash solution','11:00:00','14:30:00','pH','7.2','Altitude Shift-A',NULL,'2026-10-15 07:10:56');
/*!40000 ALTER TABLE `batch_data` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `batch_outputs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `batch_outputs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_id` int(11) NOT NULL,
  `output_date` date NOT NULL,
  `qty` decimal(14,3) NOT NULL DEFAULT 0.000,
  `grade` varchar(60) DEFAULT NULL,
  `containers` int(11) NOT NULL DEFAULT 0,
  `pack_type` varchar(60) DEFAULT NULL,
  `qc_status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Approved',
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `batch_id` (`batch_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `batch_outputs` WRITE;
/*!40000 ALTER TABLE `batch_outputs` DISABLE KEYS */;
INSERT INTO `batch_outputs` VALUES (1,1,'2026-04-24',982.000,'IP/USP',40,'HDPE Drum','Approved',NULL,'2026-10-15 07:10:56'),(2,2,'2026-05-16',975.000,'IP/USP',39,'HDPE Drum','Approved',NULL,'2026-10-15 07:10:56'),(3,3,'2026-06-02',988.000,'IP/USP',40,'HDPE Drum','Approved',NULL,'2026-10-15 07:10:56'),(4,4,'2026-07-01',969.000,'IP/USP',39,'HDPE Drum','Approved',NULL,'2026-10-15 07:10:56'),(5,5,'2026-07-28',990.000,'IP/USP',40,'HDPE Drum','Approved',NULL,'2026-10-15 07:10:56'),(6,6,'2026-09-01',978.000,'IP/USP',40,'HDPE Drum','Approved',NULL,'2026-10-15 07:10:56'),(7,7,'2026-09-27',985.000,'IP/USP',40,'HDPE Drum','Approved',NULL,'2026-10-15 07:10:56'),(8,9,'2026-10-03',42.500,'IH',2,'Fibre Drum','Pending','Awaiting COA','2026-10-15 07:10:56');
/*!40000 ALTER TABLE `batch_outputs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `batch_rm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `batch_rm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_id` int(11) NOT NULL,
  `issue_date` date NOT NULL,
  `item_id` int(11) NOT NULL,
  `qty` decimal(14,3) NOT NULL DEFAULT 0.000,
  `rate` decimal(14,2) NOT NULL DEFAULT 0.00,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `batch_id` (`batch_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `batch_rm` WRITE;
/*!40000 ALTER TABLE `batch_rm` DISABLE KEYS */;
INSERT INTO `batch_rm` VALUES (1,1,'2026-04-12',5,385.000,327.25,NULL,'2026-10-15 07:10:56'),(2,1,'2026-04-12',6,123.000,301.00,NULL,'2026-10-15 07:10:56'),(3,1,'2026-04-12',7,659.000,80.92,NULL,'2026-10-15 07:10:56'),(4,1,'2026-04-12',8,325.000,66.57,NULL,'2026-10-15 07:10:56'),(5,1,'2026-04-12',9,953.000,31.92,NULL,'2026-10-15 07:10:56'),(6,1,'2026-04-12',10,565.000,14.00,NULL,'2026-10-15 07:10:56'),(7,1,'2026-04-12',12,473.000,92.38,NULL,'2026-10-15 07:10:56'),(8,2,'2026-05-05',5,386.000,327.25,NULL,'2026-10-15 07:10:56'),(9,2,'2026-05-05',6,117.000,301.00,NULL,'2026-10-15 07:10:56'),(10,2,'2026-05-05',7,628.000,80.92,NULL,'2026-10-15 07:10:56'),(11,2,'2026-05-05',8,335.000,66.57,NULL,'2026-10-15 07:10:56'),(12,2,'2026-05-05',9,975.000,31.92,NULL,'2026-10-15 07:10:56'),(13,2,'2026-05-05',10,546.000,14.00,NULL,'2026-10-15 07:10:56'),(14,2,'2026-05-05',12,475.000,92.38,NULL,'2026-10-15 07:10:56'),(15,3,'2026-05-22',5,377.000,327.25,NULL,'2026-10-15 07:10:56'),(16,3,'2026-05-22',6,116.000,301.00,NULL,'2026-10-15 07:10:56'),(17,3,'2026-05-22',7,657.000,80.92,NULL,'2026-10-15 07:10:56'),(18,3,'2026-05-22',8,330.000,66.57,NULL,'2026-10-15 07:10:56'),(19,3,'2026-05-22',9,999.000,31.92,NULL,'2026-10-15 07:10:56'),(20,3,'2026-05-22',10,570.000,14.00,NULL,'2026-10-15 07:10:56'),(21,3,'2026-05-22',12,458.000,92.38,NULL,'2026-10-15 07:10:56'),(22,4,'2026-06-20',5,370.000,327.25,NULL,'2026-10-15 07:10:56'),(23,4,'2026-06-20',6,121.000,301.00,NULL,'2026-10-15 07:10:56'),(24,4,'2026-06-20',7,659.000,80.92,NULL,'2026-10-15 07:10:56'),(25,4,'2026-06-20',8,324.000,66.57,NULL,'2026-10-15 07:10:56'),(26,4,'2026-06-20',9,1007.000,31.92,NULL,'2026-10-15 07:10:56'),(27,4,'2026-06-20',10,567.000,14.00,NULL,'2026-10-15 07:10:56'),(28,4,'2026-06-20',12,469.000,92.38,NULL,'2026-10-15 07:10:56'),(29,5,'2026-07-16',5,372.000,327.25,NULL,'2026-10-15 07:10:56'),(30,5,'2026-07-16',6,122.000,301.00,NULL,'2026-10-15 07:10:56'),(31,5,'2026-07-16',7,638.000,80.92,NULL,'2026-10-15 07:10:56'),(32,5,'2026-07-16',8,338.000,66.57,NULL,'2026-10-15 07:10:56'),(33,5,'2026-07-16',9,1007.000,31.92,NULL,'2026-10-15 07:10:56'),(34,5,'2026-07-16',10,569.000,14.00,NULL,'2026-10-15 07:10:56'),(35,5,'2026-07-16',12,466.000,92.38,NULL,'2026-10-15 07:10:56'),(36,6,'2026-08-21',5,384.000,327.25,NULL,'2026-10-15 07:10:56'),(37,6,'2026-08-21',6,122.000,301.00,NULL,'2026-10-15 07:10:56'),(38,6,'2026-08-21',7,632.000,80.92,NULL,'2026-10-15 07:10:56'),(39,6,'2026-08-21',8,324.000,66.57,NULL,'2026-10-15 07:10:56'),(40,6,'2026-08-21',9,989.000,31.92,NULL,'2026-10-15 07:10:56'),(41,6,'2026-08-21',10,555.000,14.00,NULL,'2026-10-15 07:10:56'),(42,6,'2026-08-21',12,470.000,92.38,NULL,'2026-10-15 07:10:56'),(43,7,'2026-09-16',5,378.000,327.25,NULL,'2026-10-15 07:10:56'),(44,7,'2026-09-16',6,121.000,301.00,NULL,'2026-10-15 07:10:56'),(45,7,'2026-09-16',7,650.000,80.92,NULL,'2026-10-15 07:10:56'),(46,7,'2026-09-16',8,326.000,66.57,NULL,'2026-10-15 07:10:56'),(47,7,'2026-09-16',9,979.000,31.92,NULL,'2026-10-15 07:10:56'),(48,7,'2026-09-16',10,558.000,14.00,NULL,'2026-10-15 07:10:56'),(49,7,'2026-09-16',12,471.000,92.38,NULL,'2026-10-15 07:10:56'),(50,8,'2026-10-08',5,387.000,327.25,NULL,'2026-10-15 07:10:56'),(51,8,'2026-10-08',6,123.000,301.00,NULL,'2026-10-15 07:10:56'),(52,8,'2026-10-08',7,639.000,80.92,NULL,'2026-10-15 07:10:56'),(53,8,'2026-10-08',8,340.000,66.57,NULL,'2026-10-15 07:10:56'),(54,8,'2026-10-08',9,983.000,31.92,NULL,'2026-10-15 07:10:56'),(55,8,'2026-10-08',10,557.000,14.00,NULL,'2026-10-15 07:10:56'),(56,8,'2026-10-08',12,476.000,92.38,NULL,'2026-10-15 07:10:56');
/*!40000 ALTER TABLE `batch_rm` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `dispatch_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dispatch_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dispatch_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `grade` varchar(60) DEFAULT NULL,
  `qty` decimal(14,3) NOT NULL DEFAULT 0.000,
  `packs` int(11) NOT NULL DEFAULT 0,
  `pack_type` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dispatch_id` (`dispatch_id`),
  KEY `batch_id` (`batch_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `dispatch_items` WRITE;
/*!40000 ALTER TABLE `dispatch_items` DISABLE KEYS */;
INSERT INTO `dispatch_items` VALUES (1,1,1,1,'IP/USP',600.000,24,'HDPE Drum'),(2,2,1,1,'IP/USP',300.000,12,'HDPE Drum'),(3,3,1,1,'USP',50.000,2,'HDPE Drum'),(4,4,1,1,'IP/USP',32.000,2,'HDPE Drum'),(5,4,1,2,'IP/USP',268.000,11,'HDPE Drum'),(6,5,1,2,'IP/USP',100.000,4,'HDPE Drum'),(7,6,1,2,'USP',500.000,20,'HDPE Drum'),(8,7,1,2,'IP/USP',107.000,5,'HDPE Drum'),(9,7,1,3,'IP/USP',193.000,8,'HDPE Drum'),(10,8,1,3,'IP/USP',600.000,24,'HDPE Drum'),(11,9,1,3,'USP',195.000,8,'HDPE Drum'),(12,9,1,4,'USP',205.000,9,'HDPE Drum'),(13,10,1,4,'IP/USP',500.000,20,'HDPE Drum'),(14,11,1,4,'IP/USP',50.000,2,'HDPE Drum'),(15,12,1,4,'USP',214.000,9,'HDPE Drum'),(16,12,1,5,'USP',636.000,26,'HDPE Drum'),(17,13,1,5,'IP/USP',100.000,4,'HDPE Drum'),(18,14,1,5,'IP/USP',254.000,11,'HDPE Drum'),(19,14,1,6,'IP/USP',96.000,4,'HDPE Drum'),(20,15,1,6,'USP',700.000,28,'HDPE Drum'),(21,16,1,6,'IP/USP',182.000,8,'HDPE Drum'),(22,16,1,7,'IP/USP',118.000,5,'HDPE Drum'),(23,17,1,7,'IP/USP',150.000,6,'HDPE Drum'),(24,18,1,7,'USP',400.000,16,'HDPE Drum');
/*!40000 ALTER TABLE `dispatch_items` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `dispatches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dispatches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dispatch_no` varchar(40) NOT NULL,
  `dispatch_date` date NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `party_id` int(11) NOT NULL,
  `invoice_no` varchar(60) DEFAULT NULL,
  `destination` varchar(150) DEFAULT NULL,
  `transporter` varchar(120) DEFAULT NULL,
  `vehicle_no` varchar(40) DEFAULT NULL,
  `lr_no` varchar(60) DEFAULT NULL,
  `driver_name` varchar(80) DEFAULT NULL,
  `driver_mobile` varchar(30) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `qr_token` varchar(40) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `dispatch_no` (`dispatch_no`),
  KEY `dispatch_date` (`dispatch_date`),
  KEY `party_id` (`party_id`),
  KEY `sale_id` (`sale_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `dispatches` WRITE;
/*!40000 ALTER TABLE `dispatches` DISABLE KEYS */;
INSERT INTO `dispatches` VALUES (1,'DSP/26-27/001','2026-04-29',1,14,'FNV/1/26-27','Karnal','Bhavna Roadways','GJ03BC1655','LR57492','Suresh','9821932734',NULL,'613a52c284b5b0ae',1,'2026-10-15 07:10:56'),(2,'DSP/26-27/002','2026-05-01',2,15,'FNV/2/26-27','Mumbai','Shree Maruti Courier','GJ03CD6637','LR29662','Bharat','9845243384',NULL,'caf8149d2ab57753',1,'2026-10-15 07:10:56'),(3,'DSP/26-27/003','2026-05-07',3,16,'FNV/3/26-27','Ahmedabad','Gati Ltd','GJ03DE7290','LR54056','Kishor','9897617305',NULL,'801d6fa0f6332744',1,'2026-10-15 07:10:56'),(4,'DSP/26-27/004','2026-05-20',4,15,'FNV/4/26-27','Mumbai','VRL Logistics','GJ03EF2763','LR94962','Ramesh','9882953072',NULL,'c976b725988a4d26',1,'2026-10-15 07:10:56'),(5,'DSP/26-27/005','2026-05-26',5,17,'FNV/5/26-27','Sirmour','Bhavna Roadways','GJ03FG7942','LR84886','Suresh','9812789640',NULL,'231f6229c13fa13b',1,'2026-10-15 07:10:56'),(6,'DSP/26-27/006','2026-06-06',6,14,'FNV/6/26-27','Karnal','Shree Maruti Courier','GJ03GH5294','LR18367','Bharat','9853413205',NULL,'761ddc7ec1e369f1',1,'2026-10-15 07:10:56'),(7,'DSP/26-27/007','2026-06-13',7,27,'FNV/7/26-27','Ahmedabad','Gati Ltd','GJ03HI9160','LR90708','Kishor','9840782139',NULL,'35ae4a9f45faefc8',1,'2026-10-15 07:10:56'),(8,'DSP/26-27/008','2026-06-25',8,18,'FNV/8/26-27','Surat','VRL Logistics','GJ03IJ1291','LR14916','Ramesh','9827499145',NULL,'cda0e258eba5847b',1,'2026-10-15 07:10:56'),(9,'DSP/26-27/009','2026-07-05',9,15,'FNV/9/26-27','Mumbai','Bhavna Roadways','GJ03JK6482','LR35431','Suresh','9829090766',NULL,'423b257c31c2069d',1,'2026-10-15 07:10:56'),(10,'DSP/26-27/010','2026-07-19',10,22,'FNV/10/26-27','Ahmedabad','Shree Maruti Courier','GJ03KL2891','LR87410','Bharat','9868274264',NULL,'41c0b85f990277dc',1,'2026-10-15 07:10:56'),(11,'DSP/26-27/011','2026-07-31',11,26,'FNV/11/26-27','Baddi','Gati Ltd','GJ03LM7069','LR65113','Kishor','9897385892',NULL,'fc6acac008886a14',1,'2026-10-15 07:10:56'),(12,'DSP/26-27/012','2026-08-08',12,18,'FNV/12/26-27','Surat','VRL Logistics','GJ03MN9549','LR76256','Ramesh','9888012825',NULL,'e991520ce8508842',1,'2026-10-15 07:10:56'),(13,'DSP/26-27/013','2026-08-22',13,19,'FNV/13/26-27','Ahmedabad','Bhavna Roadways','GJ03NO3252','LR34036','Suresh','9894229382',NULL,'a1882329bb393f05',1,'2026-10-15 07:10:56'),(14,'DSP/26-27/014','2026-09-04',14,15,'FNV/14/26-27','Mumbai','Shree Maruti Courier','GJ03OP1692','LR11367','Bharat','9893649548',NULL,'e19e0341219ab8b7',1,'2026-10-15 07:10:56'),(15,'DSP/26-27/015','2026-09-29',15,20,'FNV/15/26-27','Vapi','Gati Ltd','GJ03PQ5588','LR58371','Kishor','9815469484',NULL,'91e751a709aed469',1,'2026-10-15 07:10:56'),(16,'DSP/26-27/016','2026-10-01',16,21,'FNV/16/26-27','Hyderabad','VRL Logistics','GJ03QR6967','LR57109','Ramesh','9851485531',NULL,'95402a14ff47410c',1,'2026-10-15 07:10:56'),(17,'DSP/26-27/017','2026-10-03',17,17,'FNV/17/26-27','Sirmour','Bhavna Roadways','GJ03RS3687','LR32458','Suresh','9846417598',NULL,'ae264115fb7ff01f',1,'2026-10-15 07:10:56'),(18,'DSP/26-27/018','2026-10-06',18,14,'FNV/18/26-27','Karnal','Shree Maruti Courier','GJ03ST1971','LR90241','Bharat','9861027608',NULL,'9c990e02004709e3',1,'2026-10-15 07:10:56');
/*!40000 ALTER TABLE `dispatches` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_type` enum('RM','PM','FG','CONSUMABLE','SERVICE') NOT NULL DEFAULT 'RM',
  `code` varchar(40) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `cas_no` varchar(40) DEFAULT NULL,
  `grade` varchar(60) DEFAULT NULL,
  `hsn` varchar(20) DEFAULT NULL,
  `unit` varchar(15) NOT NULL DEFAULT 'kg',
  `gst_rate` decimal(5,2) NOT NULL DEFAULT 18.00,
  `std_rate` decimal(14,2) NOT NULL DEFAULT 0.00,
  `reorder_level` decimal(14,3) NOT NULL DEFAULT 0.000,
  `opening_qty` decimal(14,3) NOT NULL DEFAULT 0.000,
  `opening_rate` decimal(14,2) NOT NULL DEFAULT 0.00,
  `opening_date` date DEFAULT NULL,
  `shelf_life_months` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `item_type` (`item_type`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `items` WRITE;
/*!40000 ALTER TABLE `items` DISABLE KEYS */;
INSERT INTO `items` VALUES (1,'FG','FG001','Sucralfate','54182-58-0','IP/USP','29400000','kg',18.00,925.00,500.000,0.000,0.00,NULL,60,1,'2026-10-15 07:04:38'),(2,'FG','FG002','Vonoprazan Fumarate','1260141-27-2','IH','29339900','kg',18.00,0.00,0.000,0.000,0.00,NULL,36,1,'2026-10-15 07:04:38'),(3,'FG','FG003','Bempedoic Acid','738606-46-7','IH','29181900','kg',18.00,0.00,0.000,0.000,0.00,NULL,36,1,'2026-10-15 07:04:38'),(4,'FG','FG004','Ferric Carboxymaltose','9007-72-1','IH','29400000','kg',18.00,0.00,0.000,0.000,0.00,NULL,36,1,'2026-10-15 07:04:38'),(5,'RM','RM001','Pyridine (Fresh)','110-86-1','','29333100','kg',18.00,338.50,500.000,1000.000,338.50,'2026-04-01',0,1,'2026-10-15 07:04:38'),(6,'RM','RM002','Pyridine (Recovered)','110-86-1','','29333100','kg',18.00,0.00,0.000,1031.000,301.00,'2026-04-01',0,1,'2026-10-15 07:04:38'),(7,'RM','RM003','Chloro Sulphonic Acid (CSA)','7790-94-5','','28061000','kg',18.00,81.00,1000.000,1830.000,73.00,'2026-04-01',0,1,'2026-10-15 07:04:38'),(8,'RM','RM004','Sucrose [Multi-compendial Grade] #20','57-50-1','','17019990','kg',5.00,63.00,500.000,375.000,59.00,'2026-04-01',0,1,'2026-10-15 07:04:38'),(9,'RM','RM005','Soda Ash (Sodium Carbonate)','497-19-8','','28362010','kg',18.00,32.00,2000.000,2000.000,32.00,'2026-04-01',0,1,'2026-10-15 07:04:38'),(10,'RM','RM006','Chloroform (Fresh)','67-66-3','','29031300','kg',18.00,14.00,1000.000,5100.000,14.00,'2026-04-01',0,1,'2026-10-15 07:04:38'),(11,'RM','RM007','Chloroform (Recovered)','67-66-3','','29031300','kg',18.00,0.00,0.000,0.000,0.00,NULL,0,1,'2026-10-15 07:04:38'),(12,'RM','RM008','Aluminium Chloride (AlCl3)','7446-70-0','','28273200','kg',18.00,90.00,1000.000,1000.000,94.00,'2026-04-01',0,1,'2026-10-15 07:04:38'),(13,'PM','PM001','50 Ltr HDPE Carboy (Open Mouth, Metal Ring)','','','39233090','Nos',18.00,290.00,50.000,120.000,290.00,'2026-04-01',0,1,'2026-10-15 07:04:38'),(14,'PM','PM002','Fibre Drum 25 kg','','','48191010','Nos',18.00,0.00,50.000,80.000,210.00,'2026-04-01',0,1,'2026-10-15 07:04:38'),(15,'PM','PM003','Sticker Sheet with Lamination','','','49119990','Nos',18.00,2.00,500.000,1500.000,2.00,'2026-04-01',0,1,'2026-10-15 07:04:38'),(16,'SERVICE','SV001','Transport / Freight','','','996511','Nos',18.00,0.00,0.000,0.000,0.00,NULL,0,1,'2026-10-15 07:04:38'),(17,'SERVICE','SV002','Analysis / Testing Charges','','','998346','Nos',18.00,0.00,0.000,0.000,0.00,NULL,0,1,'2026-10-15 07:04:38'),(18,'SERVICE','SV003','Job Work Charges','','','998842','kg',18.00,0.00,0.000,0.000,0.00,NULL,0,1,'2026-10-15 07:04:38'),(19,'CONSUMABLE','CN001','Machinery / Equipment','','','84798999','Nos',18.00,0.00,0.000,0.000,0.00,NULL,0,1,'2026-10-15 07:04:38');
/*!40000 ALTER TABLE `items` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `packing_labels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `packing_labels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_id` int(11) NOT NULL,
  `label_count` int(11) NOT NULL DEFAULT 1,
  `start_no` int(11) NOT NULL DEFAULT 1,
  `net_wt` decimal(10,3) NOT NULL DEFAULT 0.000,
  `tare_wt` decimal(10,3) NOT NULL DEFAULT 0.000,
  `pack_type` varchar(60) DEFAULT NULL,
  `storage` varchar(200) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `batch_id` (`batch_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `packing_labels` WRITE;
/*!40000 ALTER TABLE `packing_labels` DISABLE KEYS */;
INSERT INTO `packing_labels` VALUES (1,1,40,1,25.000,1.500,'HDPE Drum','Store in a well closed container at a temperature not exceeding 30Â°C. Protect from light & moisture.',1,'2026-10-15 07:10:56'),(2,2,39,1,25.000,1.500,'HDPE Drum','Store in a well closed container at a temperature not exceeding 30Â°C. Protect from light & moisture.',1,'2026-10-15 07:10:56'),(3,3,40,1,25.000,1.500,'HDPE Drum','Store in a well closed container at a temperature not exceeding 30Â°C. Protect from light & moisture.',1,'2026-10-15 07:10:56'),(4,4,39,1,25.000,1.500,'HDPE Drum','Store in a well closed container at a temperature not exceeding 30Â°C. Protect from light & moisture.',1,'2026-10-15 07:10:56'),(5,5,40,1,25.000,1.500,'HDPE Drum','Store in a well closed container at a temperature not exceeding 30Â°C. Protect from light & moisture.',1,'2026-10-15 07:10:56'),(6,6,40,1,25.000,1.500,'HDPE Drum','Store in a well closed container at a temperature not exceeding 30Â°C. Protect from light & moisture.',1,'2026-10-15 07:10:56'),(7,7,40,1,25.000,1.500,'HDPE Drum','Store in a well closed container at a temperature not exceeding 30Â°C. Protect from light & moisture.',1,'2026-10-15 07:10:56');
/*!40000 ALTER TABLE `packing_labels` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `parties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `party_type` enum('Supplier','Customer','Both','Job Worker','Transporter') NOT NULL DEFAULT 'Supplier',
  `name` varchar(150) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(80) DEFAULT NULL,
  `state` varchar(80) DEFAULT 'Gujarat',
  `gstin` varchar(20) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `payment_terms` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `party_type` (`party_type`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `parties` WRITE;
/*!40000 ALTER TABLE `parties` DISABLE KEYS */;
INSERT INTO `parties` VALUES (1,'Supplier','Pramukh Chemicals','Plot No 3817, Opp. ETL Plant, GIDC Estate','Ankleshwar','Gujarat',NULL,'','96241 04560','',60,1,'2026-10-15 07:04:38'),(2,'Supplier','Rudra Chemicals','','Ankleshwar','Gujarat',NULL,'','97277 85247','',60,1,'2026-10-15 07:04:38'),(3,'Supplier','Nirva Speciality Chemicals','65-Haridham Society, Near Children Park, Ambatalavadi','Surat','Gujarat',NULL,'','95375 68204','info.nirvachemicals@gmail.com',90,1,'2026-10-15 07:04:38'),(4,'Supplier','Dhanraj Sugars Pvt Ltd','238-242, 2nd Floor, Mega Mall','Surendranagar','Gujarat',NULL,'Mr. Ashik Mirani','98750 89790','support@dhanrajsugar.com',60,1,'2026-10-15 07:04:38'),(5,'Supplier','Dev Polymers & Containers','','Surat','Gujarat',NULL,'','94145 11811','vikaschachan@yahoo.co.in',45,1,'2026-10-15 07:04:38'),(6,'Supplier','True Chem Enterprise','Plot No. 1411, Office No S27, Global Trade Center, GIDC','Ankleshwar','Gujarat',NULL,'Mr. Ankit','74900 56818','contact@truchementerprise.com',60,1,'2026-10-15 07:04:38'),(7,'Supplier','Aceto Chem Agencies','212, 2nd Floor, Konark Complex, Nr. Ambalal Park Char Rasta, Water Tank Road, Karelibaug','Vadodara','Gujarat',NULL,'Mr. Kalpesh Shah','98795 12726','office@acetogroup.com',0,1,'2026-10-15 07:04:38'),(8,'Supplier','Shree Bhagavati Pharma Machinery Company','','Bharuch','Gujarat',NULL,'','70690 01605','contact@bhagwatipharma.co.in',0,1,'2026-10-15 07:04:38'),(9,'Supplier','Kavya Chemical','Plot No. 621-P/23, GIDC Panoli, Ankleshwar Panoli Umarwada Alonj Road','Panoli','Gujarat',NULL,'Mr. Hiren','81406 21181','',60,1,'2026-10-15 07:04:38'),(10,'Supplier','Tivan Sciences Pvt Ltd','Plot 1, S.R. 117, Village Amreli, Taluka Padadhari','Rajkot','Gujarat',NULL,'Mr. Sanjay Biswal','92279 90975','busdev3@tivansciences.com',30,1,'2026-10-15 07:04:38'),(11,'Supplier','Accuprec Research Labs','','Ahmedabad','Gujarat',NULL,'','','',15,1,'2026-10-15 07:04:38'),(12,'Transporter','Bhavna Roadways','','','Gujarat',NULL,'','','',0,1,'2026-10-15 07:04:38'),(13,'Job Worker','Altitude Interchem LLP','Plot No 2222, Nr. Luna Chowkadi, GIDC Estate','Panoli','Gujarat',NULL,'','99096 53330','',0,1,'2026-10-15 07:04:38'),(14,'Customer','JRS Oryx Pharma','','Karnal','Haryana',NULL,'','','',30,1,'2026-10-15 07:04:38'),(15,'Customer','Farma Fusion','','Mumbai','Maharashtra',NULL,'','','',20,1,'2026-10-15 07:04:38'),(16,'Customer','RX Pharmachem','','Ahmedabad','Gujarat',NULL,'','','',30,1,'2026-10-15 07:04:38'),(17,'Customer','Sahika Pharma','','Sirmour','Himachal Pradesh',NULL,'','','',60,1,'2026-10-15 07:04:38'),(18,'Both','Believe Pharma Pvt Ltd','','Surat','Gujarat',NULL,'','','',15,1,'2026-10-15 07:04:38'),(19,'Customer','Jain Pharma','','','Gujarat',NULL,'','','',90,1,'2026-10-15 07:04:38'),(20,'Customer','Aurachem Life Sciences','','','Gujarat',NULL,'','','',100,1,'2026-10-15 07:04:38'),(21,'Customer','HL Healthcare','','','Gujarat',NULL,'','','',39,1,'2026-10-15 07:04:38'),(22,'Customer','Ariheet Pharmachem','','','Gujarat',NULL,'','','',30,1,'2026-10-15 07:04:38'),(23,'Customer','Regal Laboratories','','','Gujarat',NULL,'','','',30,1,'2026-10-15 07:04:38'),(24,'Customer','Zigamed','','','Gujarat',NULL,'','','',30,1,'2026-10-15 07:04:38'),(25,'Customer','Mednova Pharma','','','Gujarat',NULL,'','','',30,1,'2026-10-15 07:04:38'),(26,'Customer','Walter Healthcare','','','Gujarat',NULL,'','','',30,1,'2026-10-15 07:04:38'),(27,'Customer','Zee Laboratories','','','Gujarat',NULL,'','','',30,1,'2026-10-15 07:04:38'),(28,'Customer','Shree Raj Chem','','','Gujarat',NULL,'','','',30,1,'2026-10-15 07:04:38'),(29,'Customer','Maan Pharma','','','Gujarat',NULL,'','','',30,1,'2026-10-15 07:04:38'),(30,'Customer','Solunex Healthcare','','','Gujarat',NULL,'','','',30,1,'2026-10-15 07:04:38');
/*!40000 ALTER TABLE `parties` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pay_type` enum('RECEIPT','PAYMENT') NOT NULL,
  `ref_id` int(11) NOT NULL,
  `pay_date` date NOT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `tds` decimal(14,2) NOT NULL DEFAULT 0.00,
  `mode` varchar(20) NOT NULL DEFAULT 'Bank',
  `ref_no` varchar(80) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pay_type` (`pay_type`,`ref_id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,'PAYMENT',1,'2026-06-03',54516.00,0.00,'Bank','NEFT266316',NULL,1,'2026-10-15 07:10:56'),(2,'PAYMENT',2,'2026-04-08',43365.00,0.00,'Bank','NEFT559881',NULL,1,'2026-10-15 07:10:56'),(3,'PAYMENT',3,'2026-06-08',297360.00,0.00,'Bank','NEFT571388',NULL,1,'2026-10-15 07:10:56'),(4,'PAYMENT',4,'2026-04-10',798860.00,0.00,'Bank','NEFT517239',NULL,1,'2026-10-15 07:10:56'),(5,'PAYMENT',5,'2026-04-18',3648.00,0.00,'Bank','NEFT410803',NULL,1,'2026-10-15 07:10:56'),(6,'PAYMENT',6,'2026-07-01',435420.00,0.00,'Bank','NEFT770738',NULL,1,'2026-10-15 07:10:56'),(7,'PAYMENT',7,'2026-06-25',113280.00,0.00,'Bank','NEFT194061',NULL,1,'2026-10-15 07:10:56'),(8,'PAYMENT',8,'2026-05-20',72765.00,0.00,'Bank','NEFT799123',NULL,1,'2026-10-15 07:10:56'),(9,'PAYMENT',9,'2026-06-11',17700.00,0.00,'Bank','NEFT125478',NULL,1,'2026-10-15 07:10:56'),(10,'PAYMENT',10,'2026-08-02',191160.00,0.00,'Bank','NEFT162728',NULL,1,'2026-10-15 07:10:56'),(11,'PAYMENT',11,'2026-08-17',422558.00,0.00,'Bank','NEFT615082',NULL,1,'2026-10-15 07:10:56'),(12,'PAYMENT',12,'2026-06-30',363440.00,0.00,'Bank','NEFT124051',NULL,1,'2026-10-15 07:10:56'),(13,'PAYMENT',13,'2026-09-12',148680.00,0.00,'Bank','NEFT291733',NULL,1,'2026-10-15 07:10:56'),(14,'PAYMENT',14,'2026-09-05',59885.00,0.00,'Bank','NEFT176244',NULL,1,'2026-10-15 07:10:56'),(15,'PAYMENT',16,'2026-10-10',108675.00,0.00,'Bank','NEFT589456',NULL,1,'2026-10-15 07:10:56'),(16,'RECEIPT',1,'2026-05-25',658440.00,0.00,'Bank','UTR7186718',NULL,1,'2026-10-15 07:10:56'),(17,'RECEIPT',2,'2026-05-17',336300.00,0.00,'Bank','UTR9930399',NULL,1,'2026-10-15 07:10:56'),(18,'RECEIPT',3,'2026-06-02',54575.00,0.00,'Bank','UTR6664281',NULL,1,'2026-10-15 07:10:56'),(19,'RECEIPT',4,'2026-06-05',327450.00,0.00,'Bank','UTR4099447',NULL,1,'2026-10-15 07:10:56'),(20,'RECEIPT',5,'2026-07-21',107380.00,0.00,'PDC','UTR2737395',NULL,1,'2026-10-15 07:10:56'),(21,'RECEIPT',6,'2026-07-02',548700.00,0.00,'Bank','UTR6103754',NULL,1,'2026-10-15 07:10:56'),(22,'RECEIPT',7,'2026-07-09',325680.00,0.00,'Bank','UTR5388431',NULL,1,'2026-10-15 07:10:56'),(23,'RECEIPT',8,'2026-07-06',601800.00,0.00,'Bank','UTR9007088',NULL,1,'2026-10-15 07:10:56'),(24,'RECEIPT',9,'2026-07-21',436600.00,0.00,'Bank','UTR6166986',NULL,1,'2026-10-15 07:10:56'),(25,'RECEIPT',10,'2026-08-14',539850.00,0.00,'Bank','UTR6846118',NULL,1,'2026-10-15 07:10:56'),(26,'RECEIPT',11,'2026-08-26',55460.00,0.00,'Bank','UTR7892451',NULL,1,'2026-10-15 07:10:56'),(27,'RECEIPT',12,'2026-08-19',511530.00,0.00,'Bank','UTR4528925',NULL,1,'2026-10-15 07:10:56'),(28,'RECEIPT',14,'2026-09-20',362924.00,0.00,'Bank','UTR2196638',NULL,1,'2026-10-15 07:10:56');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `production_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `production_batches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_no` varchar(40) NOT NULL,
  `item_id` int(11) NOT NULL,
  `plant` varchar(100) DEFAULT NULL,
  `batch_size` decimal(14,3) NOT NULL DEFAULT 0.000,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `mfg_date` date DEFAULT NULL,
  `exp_date` date DEFAULT NULL,
  `status` enum('Planned','In Process','Completed','Cancelled') NOT NULL DEFAULT 'Planned',
  `remarks` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `batch_no` (`batch_no`),
  KEY `item_id` (`item_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `production_batches` WRITE;
/*!40000 ALTER TABLE `production_batches` DISABLE KEYS */;
INSERT INTO `production_batches` VALUES (1,'FV26SLF001',1,'Altitude Interchem - Panoli (Job Work)',1000.000,'2026-04-12','2026-04-24','2026-04-24','2031-04-23','Completed',NULL,1,'2026-10-15 07:10:56'),(2,'FV26SLF002',1,'Altitude Interchem - Panoli (Job Work)',1000.000,'2026-05-05','2026-05-16','2026-05-16','2031-05-15','Completed',NULL,1,'2026-10-15 07:10:56'),(3,'FV26SLF003',1,'Altitude Interchem - Panoli (Job Work)',1000.000,'2026-05-22','2026-06-02','2026-06-02','2031-06-01','Completed',NULL,1,'2026-10-15 07:10:56'),(4,'FV26SLF004',1,'Altitude Interchem - Panoli (Job Work)',1000.000,'2026-06-20','2026-07-01','2026-07-01','2031-06-30','Completed',NULL,1,'2026-10-15 07:10:56'),(5,'FV26SLF005',1,'Altitude Interchem - Panoli (Job Work)',1000.000,'2026-07-16','2026-07-28','2026-07-28','2031-07-27','Completed',NULL,1,'2026-10-15 07:10:56'),(6,'FV26SLF006',1,'Altitude Interchem - Panoli (Job Work)',1000.000,'2026-08-21','2026-09-01','2026-09-01','2031-08-31','Completed',NULL,1,'2026-10-15 07:10:56'),(7,'FV26SLF007',1,'Altitude Interchem - Panoli (Job Work)',1000.000,'2026-09-16','2026-09-27','2026-09-27','2031-09-26','Completed',NULL,1,'2026-10-15 07:10:56'),(8,'FV26SLF008',1,'Altitude Interchem - Panoli (Job Work)',1000.000,'2026-10-08',NULL,NULL,NULL,'In Process',NULL,1,'2026-10-15 07:10:56'),(9,'FV26VPF001',2,'Fenvitta - Kothariya, Rajkot',50.000,'2026-09-20','2026-10-03','2026-10-03','2029-10-02','Completed','Trial batch for customer sample',1,'2026-10-15 07:10:56');
/*!40000 ALTER TABLE `production_batches` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `purchase_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `purchase_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `qty` decimal(14,3) NOT NULL DEFAULT 0.000,
  `unit` varchar(15) DEFAULT NULL,
  `rate` decimal(14,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `gst_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `gst_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total` decimal(14,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `purchase_id` (`purchase_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `purchase_items` WRITE;
/*!40000 ALTER TABLE `purchase_items` DISABLE KEYS */;
INSERT INTO `purchase_items` VALUES (1,1,10,'',3300.000,'kg',14.00,46200.00,18.00,8316.00,54516.00),(2,2,8,'',700.000,'kg',59.00,41300.00,5.00,2065.00,43365.00),(3,3,9,'',2000.000,'kg',32.00,64000.00,18.00,11520.00,75520.00),(4,3,12,'',2000.000,'kg',94.00,188000.00,18.00,33840.00,221840.00),(5,4,5,'',2000.000,'kg',338.50,677000.00,18.00,121860.00,798860.00),(6,5,16,'',1.000,'Nos',3648.00,3648.00,0.00,0.00,3648.00),(7,6,12,'',3000.000,'kg',91.00,273000.00,18.00,49140.00,322140.00),(8,6,9,'',3000.000,'kg',32.00,96000.00,18.00,17280.00,113280.00),(9,7,13,'',300.000,'Nos',320.00,96000.00,18.00,17280.00,113280.00),(10,8,8,'',1100.000,'kg',63.00,69300.00,5.00,3465.00,72765.00),(11,9,17,'',1.000,'Nos',15000.00,15000.00,18.00,2700.00,17700.00),(12,10,7,'',2000.000,'kg',81.00,162000.00,18.00,29160.00,191160.00),(13,11,7,'',2100.000,'kg',81.00,170100.00,18.00,30618.00,200718.00),(14,11,12,'',2000.000,'kg',94.00,188000.00,18.00,33840.00,221840.00),(15,12,5,'',1000.000,'kg',308.00,308000.00,18.00,55440.00,363440.00),(16,13,9,'',4000.000,'kg',31.50,126000.00,18.00,22680.00,148680.00),(17,14,13,'',350.000,'Nos',290.00,101500.00,18.00,18270.00,119770.00),(18,15,7,'',2000.000,'kg',88.00,176000.00,18.00,31680.00,207680.00),(19,15,9,'',2000.000,'kg',32.50,65000.00,18.00,11700.00,76700.00),(20,16,8,'',1500.000,'kg',69.00,103500.00,5.00,5175.00,108675.00),(21,17,5,'',2000.000,'kg',320.00,640000.00,18.00,115200.00,755200.00),(22,18,8,'',1000.000,'kg',75.00,75000.00,5.00,3750.00,78750.00),(23,19,12,'',3000.000,'kg',95.00,285000.00,18.00,51300.00,336300.00),(24,20,12,'',2000.000,'kg',86.50,173000.00,18.00,31140.00,204140.00),(25,20,10,'',2000.000,'kg',14.00,28000.00,18.00,5040.00,33040.00),(28,21,5,'',10.000,'kg',338.50,3385.00,18.00,609.30,3994.30);
/*!40000 ALTER TABLE `purchase_items` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `po_no` varchar(40) DEFAULT NULL,
  `invoice_no` varchar(60) DEFAULT NULL,
  `purchase_date` date NOT NULL,
  `party_id` int(11) NOT NULL,
  `category` varchar(40) NOT NULL DEFAULT 'Raw Material',
  `payment_terms` int(11) NOT NULL DEFAULT 0,
  `due_date` date DEFAULT NULL,
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `gst_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `freight` decimal(14,2) NOT NULL DEFAULT 0.00,
  `other_charges` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `delivery_at` varchar(120) DEFAULT NULL,
  `transport` varchar(120) DEFAULT NULL,
  `freight_terms` varchar(60) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `purchase_date` (`purchase_date`),
  KEY `party_id` (`party_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `purchases` WRITE;
/*!40000 ALTER TABLE `purchases` DISABLE KEYS */;
INSERT INTO `purchases` VALUES (1,'FNV/26-27/001','26-27/10','2026-04-04',9,'Raw Material',60,'2026-06-03',46200.00,8316.00,0.00,0.00,54516.00,'Panoli 394116','By Private Transport','Paid',NULL,1,'2026-10-15 07:10:56'),(2,'FNV/26-27/002','2627/DSPL/D-93','2026-04-08',4,'Raw Material',0,'2026-04-08',41300.00,2065.00,0.00,0.00,43365.00,'Panoli 394116','By Private Transport','Paid',NULL,1,'2026-10-15 07:10:56'),(3,'FNV/26-27/003','260024','2026-04-09',6,'Raw Material',60,'2026-06-08',252000.00,45360.00,0.00,0.00,297360.00,'Panoli 394116','By Private Transport','Paid',NULL,1,'2026-10-15 07:10:56'),(4,'FNV/26-27/004','J0078','2026-04-10',7,'Raw Material',0,'2026-04-10',677000.00,121860.00,0.00,0.00,798860.00,'Panoli 394116','By Private Transport','Paid',NULL,1,'2026-10-15 07:10:56'),(5,'','9644461','2026-04-15',12,'Transport',0,'2026-04-15',3648.00,0.00,0.00,0.00,3648.00,'Panoli 394116','By Private Transport','Paid',NULL,1,'2026-10-15 07:10:56'),(6,'FNV/26-27/006','260051','2026-05-02',6,'Raw Material',60,'2026-07-01',369000.00,66420.00,0.00,0.00,435420.00,'Panoli 394116','By Private Transport','Paid',NULL,1,'2026-10-15 07:10:56'),(7,'FNV/26-27/007','DPC/112','2026-05-11',5,'Packing Material',45,'2026-06-25',96000.00,17280.00,0.00,0.00,113280.00,'Panoli 394116','By Private Transport','Paid',NULL,1,'2026-10-15 07:10:56'),(8,'FNV/26-27/008','D-141','2026-05-20',4,'Raw Material',0,'2026-05-20',69300.00,3465.00,0.00,0.00,72765.00,'Panoli 394116','By Private Transport','Paid',NULL,1,'2026-10-15 07:10:56'),(9,'','ARL/26-27/0223','2026-05-27',11,'Analysis',15,'2026-06-11',15000.00,2700.00,0.00,0.00,17700.00,'Panoli 394116','By Private Transport','Paid',NULL,1,'2026-10-15 07:10:56'),(10,'FNV/26-27/011','NSC/44/2627','2026-06-03',3,'Raw Material',60,'2026-08-02',162000.00,29160.00,0.00,0.00,191160.00,'Panoli 394116','By Private Transport','Paid',NULL,1,'2026-10-15 07:10:56'),(11,'FNV/26-27/013','NSC/71/2627','2026-06-18',3,'Raw Material',60,'2026-08-17',358100.00,64458.00,0.00,0.00,422558.00,'Panoli 394116','By Private Transport','Paid',NULL,1,'2026-10-15 07:10:56'),(12,'FNV/26-27/014','J0131','2026-06-30',7,'Raw Material',0,'2026-06-30',308000.00,55440.00,0.00,0.00,363440.00,'Panoli 394116','By Private Transport','Paid',NULL,1,'2026-10-15 07:10:56'),(13,'FNV/26-27/016','260177','2026-07-14',6,'Raw Material',60,'2026-09-12',126000.00,22680.00,0.00,0.00,148680.00,'Panoli 394116','By Private Transport','Paid',NULL,1,'2026-10-15 07:10:56'),(14,'FNV/26-27/018','DPC/188','2026-07-22',5,'Packing Material',45,'2026-09-05',101500.00,18270.00,0.00,0.00,119770.00,'Panoli 394116','By Private Transport','Paid',NULL,1,'2026-10-15 07:10:56'),(15,'FNV/26-27/020','NSC/102/2627','2026-08-05',3,'Raw Material',90,'2026-11-03',241000.00,43380.00,0.00,0.00,284380.00,'Panoli 394116','By Private Transport','Paid',NULL,1,'2026-10-15 07:10:56'),(16,'FNV/26-27/022','D-266','2026-08-19',4,'Raw Material',60,'2026-10-18',103500.00,5175.00,0.00,0.00,108675.00,'Panoli 394116','By Private Transport','Paid',NULL,1,'2026-10-15 07:10:56'),(17,'FNV/26-27/024','NSC/131/2627','2026-09-02',3,'Raw Material',90,'2026-12-01',640000.00,115200.00,0.00,0.00,755200.00,'Panoli 394116','By Private Transport','Paid',NULL,1,'2026-10-15 07:10:56'),(18,'FNV/26-27/025','D-301','2026-09-14',4,'Raw Material',60,'2026-11-13',75000.00,3750.00,0.00,0.00,78750.00,'Panoli 394116','By Private Transport','Paid',NULL,1,'2026-10-15 07:10:56'),(19,'FNV/26-27/027','RC/0091','2026-09-25',2,'Raw Material',60,'2026-11-24',285000.00,51300.00,0.00,0.00,336300.00,'Panoli 394116','By Private Transport','Paid',NULL,1,'2026-10-15 07:10:56'),(20,'FNV/26-27/028','PC/2627/044','2026-10-06',1,'Raw Material',60,'2026-12-05',201000.00,36180.00,0.00,0.00,237180.00,'Panoli 394116','By Private Transport','Paid',NULL,1,'2026-10-15 07:10:56'),(21,'FNV/26-27/029','101','2026-10-15',7,'Raw Material',0,'2026-10-15',3385.00,609.30,0.00,0.00,3994.30,'Panoli 394116','By Private Transport','Paid','test',1,'2026-10-15 07:25:01');
/*!40000 ALTER TABLE `purchases` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `rm_utilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rm_utilities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entry_date` date NOT NULL,
  `item_id` int(11) NOT NULL,
  `entry_type` varchar(30) NOT NULL,
  `qty` decimal(14,3) NOT NULL DEFAULT 0.000,
  `rate` decimal(14,2) NOT NULL DEFAULT 0.00,
  `area` varchar(100) DEFAULT NULL,
  `ref_no` varchar(60) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `entry_date` (`entry_date`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `rm_utilities` WRITE;
/*!40000 ALTER TABLE `rm_utilities` DISABLE KEYS */;
INSERT INTO `rm_utilities` VALUES (1,'2026-04-26',6,'Solvent Recovery',310.000,301.00,'Solvent Recovery Plant','FV26SLF001',NULL,1,'2026-10-15 07:10:56'),(2,'2026-05-18',11,'Solvent Recovery',420.000,0.00,'Solvent Recovery Plant','FV26SLF002',NULL,1,'2026-10-15 07:10:56'),(3,'2026-06-04',6,'Solvent Recovery',295.000,301.00,'Solvent Recovery Plant','FV26SLF003',NULL,1,'2026-10-15 07:10:56'),(4,'2026-07-03',6,'Solvent Recovery',305.000,301.00,'Solvent Recovery Plant','FV26SLF004',NULL,1,'2026-10-15 07:10:56'),(5,'2026-07-10',9,'Utility Consumption',150.000,31.92,'Effluent Treatment','',NULL,1,'2026-10-15 07:10:56'),(6,'2026-08-02',8,'Lab / QC Use',5.000,66.57,'Lab / QC','',NULL,1,'2026-10-15 07:10:56'),(7,'2026-08-30',6,'Solvent Recovery',300.000,301.00,'Solvent Recovery Plant','FV26SLF006',NULL,1,'2026-10-15 07:10:56'),(8,'2026-09-12',15,'Utility Consumption',400.000,2.00,'Packing','',NULL,1,'2026-10-15 07:10:56'),(9,'2026-09-29',11,'Solvent Recovery',380.000,0.00,'Solvent Recovery Plant','FV26SLF007',NULL,1,'2026-10-15 07:10:56'),(10,'2026-10-01',13,'Wastage / Loss',6.000,301.69,'Warehouse','Damaged drums',NULL,1,'2026-10-15 07:10:56');
/*!40000 ALTER TABLE `rm_utilities` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `sale_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `grade` varchar(60) DEFAULT NULL,
  `qty` decimal(14,3) NOT NULL DEFAULT 0.000,
  `unit` varchar(15) DEFAULT NULL,
  `rate` decimal(14,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `gst_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `gst_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total` decimal(14,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `sale_id` (`sale_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `sale_items` WRITE;
/*!40000 ALTER TABLE `sale_items` DISABLE KEYS */;
INSERT INTO `sale_items` VALUES (1,1,1,'IP/USP',600.000,'kg',930.00,558000.00,18.00,100440.00,658440.00),(2,2,1,'IP/USP',300.000,'kg',950.00,285000.00,18.00,51300.00,336300.00),(3,3,1,'USP',50.000,'kg',925.00,46250.00,18.00,8325.00,54575.00),(4,4,1,'IP/USP',300.000,'kg',925.00,277500.00,18.00,49950.00,327450.00),(5,5,1,'IP/USP',100.000,'kg',910.00,91000.00,18.00,16380.00,107380.00),(6,6,1,'USP',500.000,'kg',930.00,465000.00,18.00,83700.00,548700.00),(7,7,1,'IP/USP',300.000,'kg',920.00,276000.00,18.00,49680.00,325680.00),(8,8,1,'IP/USP',600.000,'kg',850.00,510000.00,18.00,91800.00,601800.00),(9,9,1,'USP',400.000,'kg',925.00,370000.00,18.00,66600.00,436600.00),(10,10,1,'IP/USP',500.000,'kg',915.00,457500.00,18.00,82350.00,539850.00),(11,11,1,'IP/USP',50.000,'kg',940.00,47000.00,18.00,8460.00,55460.00),(12,12,1,'USP',850.000,'kg',850.00,722500.00,18.00,130050.00,852550.00),(13,13,1,'IP/USP',100.000,'kg',900.00,90000.00,18.00,16200.00,106200.00),(14,14,1,'IP/USP',350.000,'kg',925.00,323750.00,18.00,58275.00,382025.00),(15,15,1,'USP',700.000,'kg',920.00,644000.00,18.00,115920.00,759920.00),(16,16,1,'IP/USP',300.000,'kg',925.00,277500.00,18.00,49950.00,327450.00),(17,17,1,'IP/USP',150.000,'kg',945.00,141750.00,18.00,25515.00,167265.00),(18,18,1,'USP',400.000,'kg',935.00,374000.00,18.00,67320.00,441320.00),(19,19,1,'IP/USP',25.000,'kg',960.00,24000.00,18.00,4320.00,28320.00),(20,20,1,'IP/USP',200.000,'kg',930.00,186000.00,18.00,33480.00,219480.00);
/*!40000 ALTER TABLE `sale_items` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_no` varchar(40) NOT NULL,
  `sale_date` date NOT NULL,
  `party_id` int(11) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `agent_name` varchar(100) DEFAULT NULL,
  `po_ref` varchar(60) DEFAULT NULL,
  `payment_terms` int(11) NOT NULL DEFAULT 0,
  `due_date` date DEFAULT NULL,
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `freight` decimal(14,2) NOT NULL DEFAULT 0.00,
  `gst_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_no` (`invoice_no`),
  KEY `sale_date` (`sale_date`),
  KEY `party_id` (`party_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `sales` WRITE;
/*!40000 ALTER TABLE `sales` DISABLE KEYS */;
INSERT INTO `sales` VALUES (1,'FNV/1/26-27','2026-04-28',14,'Karnal','NA',NULL,30,'2026-05-28',558000.00,0.00,100440.00,658440.00,NULL,1,'2026-10-15 07:10:56'),(2,'FNV/2/26-27','2026-04-30',15,'Mumbai','NA',NULL,20,'2026-05-20',285000.00,0.00,51300.00,336300.00,NULL,1,'2026-10-15 07:10:56'),(3,'FNV/3/26-27','2026-05-06',16,'Ahmedabad','NA',NULL,30,'2026-06-05',46250.00,0.00,8325.00,54575.00,NULL,1,'2026-10-15 07:10:56'),(4,'FNV/4/26-27','2026-05-19',15,'Mumbai','NA',NULL,20,'2026-06-08',277500.00,0.00,49950.00,327450.00,NULL,1,'2026-10-15 07:10:56'),(5,'FNV/5/26-27','2026-05-25',17,'Sirmour','NA',NULL,60,'2026-07-24',91000.00,0.00,16380.00,107380.00,NULL,1,'2026-10-15 07:10:56'),(6,'FNV/6/26-27','2026-06-05',14,'Karnal','NA',NULL,30,'2026-07-05',465000.00,0.00,83700.00,548700.00,NULL,1,'2026-10-15 07:10:56'),(7,'FNV/7/26-27','2026-06-12',27,'Ahmedabad','NA',NULL,30,'2026-07-12',276000.00,0.00,49680.00,325680.00,NULL,1,'2026-10-15 07:10:56'),(8,'FNV/8/26-27','2026-06-24',18,'Surat','NA',NULL,15,'2026-07-09',510000.00,0.00,91800.00,601800.00,NULL,1,'2026-10-15 07:10:56'),(9,'FNV/9/26-27','2026-07-04',15,'Mumbai','NA',NULL,20,'2026-07-24',370000.00,0.00,66600.00,436600.00,NULL,1,'2026-10-15 07:10:56'),(10,'FNV/10/26-27','2026-07-18',22,'Ahmedabad','NA',NULL,30,'2026-08-17',457500.00,0.00,82350.00,539850.00,NULL,1,'2026-10-15 07:10:56'),(11,'FNV/11/26-27','2026-07-30',26,'Baddi','NA',NULL,30,'2026-08-29',47000.00,0.00,8460.00,55460.00,NULL,1,'2026-10-15 07:10:56'),(12,'FNV/12/26-27','2026-08-07',18,'Surat','NA',NULL,15,'2026-08-22',722500.00,0.00,130050.00,852550.00,NULL,1,'2026-10-15 07:10:56'),(13,'FNV/13/26-27','2026-08-21',19,'Ahmedabad','NA',NULL,90,'2026-11-19',90000.00,0.00,16200.00,106200.00,NULL,1,'2026-10-15 07:10:56'),(14,'FNV/14/26-27','2026-09-03',15,'Mumbai','NA',NULL,20,'2026-09-23',323750.00,0.00,58275.00,382025.00,NULL,1,'2026-10-15 07:10:56'),(15,'FNV/15/26-27','2026-09-28',20,'Vapi','NA',NULL,100,'2027-01-06',644000.00,0.00,115920.00,759920.00,NULL,1,'2026-10-15 07:10:56'),(16,'FNV/16/26-27','2026-09-30',21,'Hyderabad','NA',NULL,39,'2026-11-08',277500.00,0.00,49950.00,327450.00,NULL,1,'2026-10-15 07:10:56'),(17,'FNV/17/26-27','2026-10-02',17,'Sirmour','NA',NULL,60,'2026-12-01',141750.00,0.00,25515.00,167265.00,NULL,1,'2026-10-15 07:10:56'),(18,'FNV/18/26-27','2026-10-05',14,'Karnal','NA',NULL,30,'2026-11-04',374000.00,0.00,67320.00,441320.00,NULL,1,'2026-10-15 07:10:56'),(19,'FNV/19/26-27','2026-10-09',25,'Vadodara','NA',NULL,30,'2026-11-08',24000.00,0.00,4320.00,28320.00,NULL,1,'2026-10-15 07:10:56'),(20,'FNV/20/26-27','2026-10-13',23,'Rajkot','NA',NULL,30,'2026-11-12',186000.00,0.00,33480.00,219480.00,NULL,1,'2026-10-15 07:10:56');
/*!40000 ALTER TABLE `sales` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `skey` varchar(60) NOT NULL,
  `svalue` text DEFAULT NULL,
  PRIMARY KEY (`skey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES ('company_name','Fenvitta Lifesciences LLP'),('company_tagline','The Core of Every Cure'),('dispatch_prefix','DSP'),('email','fenvitta@fenvitta.com'),('gstin',''),('label_storage','Store in a well closed container at a temperature not exceeding 30Â°C. Protect from light & moisture.'),('mfg_address','Survey No. 346/P3, Vill. Kothariya, Hadala-Kothariya Road, Rajkot 360003, Gujarat, India'),('office_address','Patel Nagar, Street No. 2, Behind Ramji Mandir, Opp. Divyang Ind., Rajkot 360002, Gujarat, India'),('phone','+91 9558406695'),('po_prefix','FNV'),('po_terms','Material shelf life should not be more than 3 months. Invoice, Packing list, LR copy, original COA with sign and stamp must come with material. Packing label must contain Product Name, CAS No., Batch No., Mfg. Date, Exp. Date, Net Wt., Tare Wt., Gross Wt. & QR Code. All terms and conditions as per Rajkot jurisdiction.'),('sale_prefix','FNV'),('website','www.fenvitta.com');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `stock_ledger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_ledger` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trans_date` date NOT NULL,
  `item_id` int(11) NOT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `ref_type` varchar(20) NOT NULL,
  `ref_id` int(11) NOT NULL DEFAULT 0,
  `qty_in` decimal(14,3) NOT NULL DEFAULT 0.000,
  `qty_out` decimal(14,3) NOT NULL DEFAULT 0.000,
  `rate` decimal(14,2) NOT NULL DEFAULT 0.00,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `batch_id` (`batch_id`),
  KEY `ref_type` (`ref_type`,`ref_id`),
  KEY `trans_date` (`trans_date`)
) ENGINE=InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `stock_ledger` WRITE;
/*!40000 ALTER TABLE `stock_ledger` DISABLE KEYS */;
INSERT INTO `stock_ledger` VALUES (1,'2026-04-01',5,NULL,'OPENING',5,1000.000,0.000,338.50,'Opening Stock','2026-10-15 07:10:56'),(2,'2026-04-01',6,NULL,'OPENING',6,1031.000,0.000,301.00,'Opening Stock','2026-10-15 07:10:56'),(3,'2026-04-01',7,NULL,'OPENING',7,1830.000,0.000,73.00,'Opening Stock','2026-10-15 07:10:56'),(4,'2026-04-01',8,NULL,'OPENING',8,375.000,0.000,59.00,'Opening Stock','2026-10-15 07:10:56'),(5,'2026-04-01',9,NULL,'OPENING',9,2000.000,0.000,32.00,'Opening Stock','2026-10-15 07:10:56'),(6,'2026-04-01',10,NULL,'OPENING',10,5100.000,0.000,14.00,'Opening Stock','2026-10-15 07:10:56'),(7,'2026-04-01',12,NULL,'OPENING',12,1000.000,0.000,94.00,'Opening Stock','2026-10-15 07:10:56'),(8,'2026-04-01',13,NULL,'OPENING',13,120.000,0.000,290.00,'Opening Stock','2026-10-15 07:10:56'),(9,'2026-04-01',14,NULL,'OPENING',14,80.000,0.000,210.00,'Opening Stock','2026-10-15 07:10:56'),(10,'2026-04-01',15,NULL,'OPENING',15,1500.000,0.000,2.00,'Opening Stock','2026-10-15 07:10:56'),(11,'2026-04-04',10,NULL,'PURCHASE',1,3300.000,0.000,14.00,'Purchase 26-27/10','2026-10-15 07:10:56'),(12,'2026-04-08',8,NULL,'PURCHASE',2,700.000,0.000,59.00,'Purchase 2627/DSPL/D-93','2026-10-15 07:10:56'),(13,'2026-04-09',9,NULL,'PURCHASE',3,2000.000,0.000,32.00,'Purchase 260024','2026-10-15 07:10:56'),(14,'2026-04-09',12,NULL,'PURCHASE',3,2000.000,0.000,94.00,'Purchase 260024','2026-10-15 07:10:56'),(15,'2026-04-10',5,NULL,'PURCHASE',4,2000.000,0.000,338.50,'Purchase J0078','2026-10-15 07:10:56'),(16,'2026-05-02',12,NULL,'PURCHASE',6,3000.000,0.000,91.00,'Purchase 260051','2026-10-15 07:10:56'),(17,'2026-05-02',9,NULL,'PURCHASE',6,3000.000,0.000,32.00,'Purchase 260051','2026-10-15 07:10:56'),(18,'2026-05-11',13,NULL,'PURCHASE',7,300.000,0.000,320.00,'Purchase DPC/112','2026-10-15 07:10:56'),(19,'2026-05-20',8,NULL,'PURCHASE',8,1100.000,0.000,63.00,'Purchase D-141','2026-10-15 07:10:56'),(20,'2026-06-03',7,NULL,'PURCHASE',10,2000.000,0.000,81.00,'Purchase NSC/44/2627','2026-10-15 07:10:56'),(21,'2026-06-18',7,NULL,'PURCHASE',11,2100.000,0.000,81.00,'Purchase NSC/71/2627','2026-10-15 07:10:56'),(22,'2026-06-18',12,NULL,'PURCHASE',11,2000.000,0.000,94.00,'Purchase NSC/71/2627','2026-10-15 07:10:56'),(23,'2026-06-30',5,NULL,'PURCHASE',12,1000.000,0.000,308.00,'Purchase J0131','2026-10-15 07:10:56'),(24,'2026-07-14',9,NULL,'PURCHASE',13,4000.000,0.000,31.50,'Purchase 260177','2026-10-15 07:10:56'),(25,'2026-07-22',13,NULL,'PURCHASE',14,350.000,0.000,290.00,'Purchase DPC/188','2026-10-15 07:10:56'),(26,'2026-08-05',7,NULL,'PURCHASE',15,2000.000,0.000,88.00,'Purchase NSC/102/2627','2026-10-15 07:10:56'),(27,'2026-08-05',9,NULL,'PURCHASE',15,2000.000,0.000,32.50,'Purchase NSC/102/2627','2026-10-15 07:10:56'),(28,'2026-08-19',8,NULL,'PURCHASE',16,1500.000,0.000,69.00,'Purchase D-266','2026-10-15 07:10:56'),(29,'2026-09-02',5,NULL,'PURCHASE',17,2000.000,0.000,320.00,'Purchase NSC/131/2627','2026-10-15 07:10:56'),(30,'2026-09-14',8,NULL,'PURCHASE',18,1000.000,0.000,75.00,'Purchase D-301','2026-10-15 07:10:56'),(31,'2026-09-25',12,NULL,'PURCHASE',19,3000.000,0.000,95.00,'Purchase RC/0091','2026-10-15 07:10:56'),(32,'2026-10-06',12,NULL,'PURCHASE',20,2000.000,0.000,86.50,'Purchase PC/2627/044','2026-10-15 07:10:56'),(33,'2026-10-06',10,NULL,'PURCHASE',20,2000.000,0.000,14.00,'Purchase PC/2627/044','2026-10-15 07:10:56'),(34,'2026-04-12',5,NULL,'BATCH_RM',1,0.000,385.000,327.25,'Issued to batch FV26SLF001','2026-10-15 07:10:56'),(35,'2026-04-12',6,NULL,'BATCH_RM',2,0.000,123.000,301.00,'Issued to batch FV26SLF001','2026-10-15 07:10:56'),(36,'2026-04-12',7,NULL,'BATCH_RM',3,0.000,659.000,80.92,'Issued to batch FV26SLF001','2026-10-15 07:10:56'),(37,'2026-04-12',8,NULL,'BATCH_RM',4,0.000,325.000,66.57,'Issued to batch FV26SLF001','2026-10-15 07:10:56'),(38,'2026-04-12',9,NULL,'BATCH_RM',5,0.000,953.000,31.92,'Issued to batch FV26SLF001','2026-10-15 07:10:56'),(39,'2026-04-12',10,NULL,'BATCH_RM',6,0.000,565.000,14.00,'Issued to batch FV26SLF001','2026-10-15 07:10:56'),(40,'2026-04-12',12,NULL,'BATCH_RM',7,0.000,473.000,92.38,'Issued to batch FV26SLF001','2026-10-15 07:10:56'),(41,'2026-04-24',1,1,'BATCH_OUTPUT',1,982.000,0.000,0.00,'Output of batch FV26SLF001','2026-10-15 07:10:56'),(42,'2026-05-05',5,NULL,'BATCH_RM',8,0.000,386.000,327.25,'Issued to batch FV26SLF002','2026-10-15 07:10:56'),(43,'2026-05-05',6,NULL,'BATCH_RM',9,0.000,117.000,301.00,'Issued to batch FV26SLF002','2026-10-15 07:10:56'),(44,'2026-05-05',7,NULL,'BATCH_RM',10,0.000,628.000,80.92,'Issued to batch FV26SLF002','2026-10-15 07:10:56'),(45,'2026-05-05',8,NULL,'BATCH_RM',11,0.000,335.000,66.57,'Issued to batch FV26SLF002','2026-10-15 07:10:56'),(46,'2026-05-05',9,NULL,'BATCH_RM',12,0.000,975.000,31.92,'Issued to batch FV26SLF002','2026-10-15 07:10:56'),(47,'2026-05-05',10,NULL,'BATCH_RM',13,0.000,546.000,14.00,'Issued to batch FV26SLF002','2026-10-15 07:10:56'),(48,'2026-05-05',12,NULL,'BATCH_RM',14,0.000,475.000,92.38,'Issued to batch FV26SLF002','2026-10-15 07:10:56'),(49,'2026-05-16',1,2,'BATCH_OUTPUT',2,975.000,0.000,0.00,'Output of batch FV26SLF002','2026-10-15 07:10:56'),(50,'2026-05-22',5,NULL,'BATCH_RM',15,0.000,377.000,327.25,'Issued to batch FV26SLF003','2026-10-15 07:10:56'),(51,'2026-05-22',6,NULL,'BATCH_RM',16,0.000,116.000,301.00,'Issued to batch FV26SLF003','2026-10-15 07:10:56'),(52,'2026-05-22',7,NULL,'BATCH_RM',17,0.000,657.000,80.92,'Issued to batch FV26SLF003','2026-10-15 07:10:56'),(53,'2026-05-22',8,NULL,'BATCH_RM',18,0.000,330.000,66.57,'Issued to batch FV26SLF003','2026-10-15 07:10:56'),(54,'2026-05-22',9,NULL,'BATCH_RM',19,0.000,999.000,31.92,'Issued to batch FV26SLF003','2026-10-15 07:10:56'),(55,'2026-05-22',10,NULL,'BATCH_RM',20,0.000,570.000,14.00,'Issued to batch FV26SLF003','2026-10-15 07:10:56'),(56,'2026-05-22',12,NULL,'BATCH_RM',21,0.000,458.000,92.38,'Issued to batch FV26SLF003','2026-10-15 07:10:56'),(57,'2026-06-02',1,3,'BATCH_OUTPUT',3,988.000,0.000,0.00,'Output of batch FV26SLF003','2026-10-15 07:10:56'),(58,'2026-06-20',5,NULL,'BATCH_RM',22,0.000,370.000,327.25,'Issued to batch FV26SLF004','2026-10-15 07:10:56'),(59,'2026-06-20',6,NULL,'BATCH_RM',23,0.000,121.000,301.00,'Issued to batch FV26SLF004','2026-10-15 07:10:56'),(60,'2026-06-20',7,NULL,'BATCH_RM',24,0.000,659.000,80.92,'Issued to batch FV26SLF004','2026-10-15 07:10:56'),(61,'2026-06-20',8,NULL,'BATCH_RM',25,0.000,324.000,66.57,'Issued to batch FV26SLF004','2026-10-15 07:10:56'),(62,'2026-06-20',9,NULL,'BATCH_RM',26,0.000,1007.000,31.92,'Issued to batch FV26SLF004','2026-10-15 07:10:56'),(63,'2026-06-20',10,NULL,'BATCH_RM',27,0.000,567.000,14.00,'Issued to batch FV26SLF004','2026-10-15 07:10:56'),(64,'2026-06-20',12,NULL,'BATCH_RM',28,0.000,469.000,92.38,'Issued to batch FV26SLF004','2026-10-15 07:10:56'),(65,'2026-07-01',1,4,'BATCH_OUTPUT',4,969.000,0.000,0.00,'Output of batch FV26SLF004','2026-10-15 07:10:56'),(66,'2026-07-16',5,NULL,'BATCH_RM',29,0.000,372.000,327.25,'Issued to batch FV26SLF005','2026-10-15 07:10:56'),(67,'2026-07-16',6,NULL,'BATCH_RM',30,0.000,122.000,301.00,'Issued to batch FV26SLF005','2026-10-15 07:10:56'),(68,'2026-07-16',7,NULL,'BATCH_RM',31,0.000,638.000,80.92,'Issued to batch FV26SLF005','2026-10-15 07:10:56'),(69,'2026-07-16',8,NULL,'BATCH_RM',32,0.000,338.000,66.57,'Issued to batch FV26SLF005','2026-10-15 07:10:56'),(70,'2026-07-16',9,NULL,'BATCH_RM',33,0.000,1007.000,31.92,'Issued to batch FV26SLF005','2026-10-15 07:10:56'),(71,'2026-07-16',10,NULL,'BATCH_RM',34,0.000,569.000,14.00,'Issued to batch FV26SLF005','2026-10-15 07:10:56'),(72,'2026-07-16',12,NULL,'BATCH_RM',35,0.000,466.000,92.38,'Issued to batch FV26SLF005','2026-10-15 07:10:56'),(73,'2026-07-28',1,5,'BATCH_OUTPUT',5,990.000,0.000,0.00,'Output of batch FV26SLF005','2026-10-15 07:10:56'),(74,'2026-08-21',5,NULL,'BATCH_RM',36,0.000,384.000,327.25,'Issued to batch FV26SLF006','2026-10-15 07:10:56'),(75,'2026-08-21',6,NULL,'BATCH_RM',37,0.000,122.000,301.00,'Issued to batch FV26SLF006','2026-10-15 07:10:56'),(76,'2026-08-21',7,NULL,'BATCH_RM',38,0.000,632.000,80.92,'Issued to batch FV26SLF006','2026-10-15 07:10:56'),(77,'2026-08-21',8,NULL,'BATCH_RM',39,0.000,324.000,66.57,'Issued to batch FV26SLF006','2026-10-15 07:10:56'),(78,'2026-08-21',9,NULL,'BATCH_RM',40,0.000,989.000,31.92,'Issued to batch FV26SLF006','2026-10-15 07:10:56'),(79,'2026-08-21',10,NULL,'BATCH_RM',41,0.000,555.000,14.00,'Issued to batch FV26SLF006','2026-10-15 07:10:56'),(80,'2026-08-21',12,NULL,'BATCH_RM',42,0.000,470.000,92.38,'Issued to batch FV26SLF006','2026-10-15 07:10:56'),(81,'2026-09-01',1,6,'BATCH_OUTPUT',6,978.000,0.000,0.00,'Output of batch FV26SLF006','2026-10-15 07:10:56'),(82,'2026-09-16',5,NULL,'BATCH_RM',43,0.000,378.000,327.25,'Issued to batch FV26SLF007','2026-10-15 07:10:56'),(83,'2026-09-16',6,NULL,'BATCH_RM',44,0.000,121.000,301.00,'Issued to batch FV26SLF007','2026-10-15 07:10:56'),(84,'2026-09-16',7,NULL,'BATCH_RM',45,0.000,650.000,80.92,'Issued to batch FV26SLF007','2026-10-15 07:10:56'),(85,'2026-09-16',8,NULL,'BATCH_RM',46,0.000,326.000,66.57,'Issued to batch FV26SLF007','2026-10-15 07:10:56'),(86,'2026-09-16',9,NULL,'BATCH_RM',47,0.000,979.000,31.92,'Issued to batch FV26SLF007','2026-10-15 07:10:56'),(87,'2026-09-16',10,NULL,'BATCH_RM',48,0.000,558.000,14.00,'Issued to batch FV26SLF007','2026-10-15 07:10:56'),(88,'2026-09-16',12,NULL,'BATCH_RM',49,0.000,471.000,92.38,'Issued to batch FV26SLF007','2026-10-15 07:10:56'),(89,'2026-09-27',1,7,'BATCH_OUTPUT',7,985.000,0.000,0.00,'Output of batch FV26SLF007','2026-10-15 07:10:56'),(90,'2026-10-08',5,NULL,'BATCH_RM',50,0.000,387.000,327.25,'Issued to batch FV26SLF008','2026-10-15 07:10:56'),(91,'2026-10-08',6,NULL,'BATCH_RM',51,0.000,123.000,301.00,'Issued to batch FV26SLF008','2026-10-15 07:10:56'),(92,'2026-10-08',7,NULL,'BATCH_RM',52,0.000,639.000,80.92,'Issued to batch FV26SLF008','2026-10-15 07:10:56'),(93,'2026-10-08',8,NULL,'BATCH_RM',53,0.000,340.000,66.57,'Issued to batch FV26SLF008','2026-10-15 07:10:56'),(94,'2026-10-08',9,NULL,'BATCH_RM',54,0.000,983.000,31.92,'Issued to batch FV26SLF008','2026-10-15 07:10:56'),(95,'2026-10-08',10,NULL,'BATCH_RM',55,0.000,557.000,14.00,'Issued to batch FV26SLF008','2026-10-15 07:10:56'),(96,'2026-10-08',12,NULL,'BATCH_RM',56,0.000,476.000,92.38,'Issued to batch FV26SLF008','2026-10-15 07:10:56'),(97,'2026-04-26',6,NULL,'RM_UTILITY',1,310.000,0.000,301.00,'Solvent Recovery Solvent Recovery Plant','2026-10-15 07:10:56'),(98,'2026-05-18',11,NULL,'RM_UTILITY',2,420.000,0.000,0.00,'Solvent Recovery Solvent Recovery Plant','2026-10-15 07:10:56'),(99,'2026-06-04',6,NULL,'RM_UTILITY',3,295.000,0.000,301.00,'Solvent Recovery Solvent Recovery Plant','2026-10-15 07:10:56'),(100,'2026-07-03',6,NULL,'RM_UTILITY',4,305.000,0.000,301.00,'Solvent Recovery Solvent Recovery Plant','2026-10-15 07:10:56'),(101,'2026-07-10',9,NULL,'RM_UTILITY',5,0.000,150.000,31.92,'Utility Consumption Effluent Treatment','2026-10-15 07:10:56'),(102,'2026-08-02',8,NULL,'RM_UTILITY',6,0.000,5.000,66.57,'Lab / QC Use Lab / QC','2026-10-15 07:10:56'),(103,'2026-08-30',6,NULL,'RM_UTILITY',7,300.000,0.000,301.00,'Solvent Recovery Solvent Recovery Plant','2026-10-15 07:10:56'),(104,'2026-09-12',15,NULL,'RM_UTILITY',8,0.000,400.000,2.00,'Utility Consumption Packing','2026-10-15 07:10:56'),(105,'2026-09-29',11,NULL,'RM_UTILITY',9,380.000,0.000,0.00,'Solvent Recovery Solvent Recovery Plant','2026-10-15 07:10:56'),(106,'2026-10-01',13,NULL,'RM_UTILITY',10,0.000,6.000,301.69,'Wastage / Loss Warehouse','2026-10-15 07:10:56'),(107,'2026-04-29',1,1,'DISPATCH',1,0.000,600.000,0.00,'Dispatch to JRS Oryx Pharma','2026-10-15 07:10:56'),(108,'2026-05-01',1,1,'DISPATCH',2,0.000,300.000,0.00,'Dispatch to Farma Fusion','2026-10-15 07:10:56'),(109,'2026-05-07',1,1,'DISPATCH',3,0.000,50.000,0.00,'Dispatch to RX Pharmachem','2026-10-15 07:10:56'),(110,'2026-05-20',1,1,'DISPATCH',4,0.000,32.000,0.00,'Dispatch to Farma Fusion','2026-10-15 07:10:56'),(111,'2026-05-20',1,2,'DISPATCH',4,0.000,268.000,0.00,'Dispatch to Farma Fusion','2026-10-15 07:10:56'),(112,'2026-05-26',1,2,'DISPATCH',5,0.000,100.000,0.00,'Dispatch to Sahika Pharma','2026-10-15 07:10:56'),(113,'2026-06-06',1,2,'DISPATCH',6,0.000,500.000,0.00,'Dispatch to JRS Oryx Pharma','2026-10-15 07:10:56'),(114,'2026-06-13',1,2,'DISPATCH',7,0.000,107.000,0.00,'Dispatch to Zee Laboratories','2026-10-15 07:10:56'),(115,'2026-06-13',1,3,'DISPATCH',7,0.000,193.000,0.00,'Dispatch to Zee Laboratories','2026-10-15 07:10:56'),(116,'2026-06-25',1,3,'DISPATCH',8,0.000,600.000,0.00,'Dispatch to Believe Pharma Pvt Ltd','2026-10-15 07:10:56'),(117,'2026-07-05',1,3,'DISPATCH',9,0.000,195.000,0.00,'Dispatch to Farma Fusion','2026-10-15 07:10:56'),(118,'2026-07-05',1,4,'DISPATCH',9,0.000,205.000,0.00,'Dispatch to Farma Fusion','2026-10-15 07:10:56'),(119,'2026-07-19',1,4,'DISPATCH',10,0.000,500.000,0.00,'Dispatch to Ariheet Pharmachem','2026-10-15 07:10:56'),(120,'2026-07-31',1,4,'DISPATCH',11,0.000,50.000,0.00,'Dispatch to Walter Healthcare','2026-10-15 07:10:56'),(121,'2026-08-08',1,4,'DISPATCH',12,0.000,214.000,0.00,'Dispatch to Believe Pharma Pvt Ltd','2026-10-15 07:10:56'),(122,'2026-08-08',1,5,'DISPATCH',12,0.000,636.000,0.00,'Dispatch to Believe Pharma Pvt Ltd','2026-10-15 07:10:56'),(123,'2026-08-22',1,5,'DISPATCH',13,0.000,100.000,0.00,'Dispatch to Jain Pharma','2026-10-15 07:10:56'),(124,'2026-09-04',1,5,'DISPATCH',14,0.000,254.000,0.00,'Dispatch to Farma Fusion','2026-10-15 07:10:56'),(125,'2026-09-04',1,6,'DISPATCH',14,0.000,96.000,0.00,'Dispatch to Farma Fusion','2026-10-15 07:10:56'),(126,'2026-09-29',1,6,'DISPATCH',15,0.000,700.000,0.00,'Dispatch to Aurachem Life Sciences','2026-10-15 07:10:56'),(127,'2026-10-01',1,6,'DISPATCH',16,0.000,182.000,0.00,'Dispatch to HL Healthcare','2026-10-15 07:10:56'),(128,'2026-10-01',1,7,'DISPATCH',16,0.000,118.000,0.00,'Dispatch to HL Healthcare','2026-10-15 07:10:56'),(129,'2026-10-03',1,7,'DISPATCH',17,0.000,150.000,0.00,'Dispatch to Sahika Pharma','2026-10-15 07:10:56'),(130,'2026-10-06',1,7,'DISPATCH',18,0.000,400.000,0.00,'Dispatch to JRS Oryx Pharma','2026-10-15 07:10:56'),(133,'2026-10-15',5,NULL,'PURCHASE',21,10.000,0.000,338.50,'Purchase 101','2026-10-15 07:25:29');
/*!40000 ALTER TABLE `stock_ledger` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Administrator','admin','$2y$10$U5PgxkxufaMcw9.IVASCnO0eaE90lPccPjG.hlQWZT13otW.sbwHq','admin',1,'2026-10-15 07:04:38');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

