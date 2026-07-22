-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: pos_inventory_system
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
-- Table structure for table `item_variants`
--

DROP TABLE IF EXISTS `item_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `item_variants` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` int(10) unsigned NOT NULL,
  `color` varchar(100) NOT NULL,
  `size` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `fk_variants_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `item_variants`
--

LOCK TABLES `item_variants` WRITE;
/*!40000 ALTER TABLE `item_variants` DISABLE KEYS */;
INSERT INTO `item_variants` (`id`, `item_id`, `color`, `size`, `quantity`, `created_at`) VALUES (1,1,'White','US 9',8,'2026-07-22 19:59:18'),(2,1,'White','US 10',14,'2026-07-22 19:59:18'),(3,1,'White','US 11',4,'2026-07-22 19:59:18'),(4,1,'Black','US 9',5,'2026-07-22 19:59:18'),(5,1,'Black','US 10',6,'2026-07-22 19:59:18'),(6,2,'Red','US 8',10,'2026-07-22 19:59:18'),(7,2,'Red','US 9',12,'2026-07-22 19:59:18'),(8,2,'Red','US 10',8,'2026-07-22 19:59:18'),(9,2,'White','US 9',4,'2026-07-22 19:59:18'),(10,3,'Bred','US 10',6,'2026-07-22 19:59:18'),(11,3,'Bred','US 11',5,'2026-07-22 19:59:18'),(12,3,'Bred','US 12',4,'2026-07-22 19:59:18'),(13,3,'Shadow','US 10',3,'2026-07-22 19:59:18'),(14,3,'Shadow','US 11',3,'2026-07-22 19:59:18'),(15,4,'Navy','US 9',15,'2026-07-22 19:59:18'),(16,4,'Navy','US 10',12,'2026-07-22 19:59:18'),(17,4,'Navy','US 11',10,'2026-07-22 19:59:18'),(18,4,'Red','US 9',8,'2026-07-22 19:59:18'),(19,4,'Red','US 10',5,'2026-07-22 19:59:18'),(20,5,'White','US 9',10,'2026-07-22 19:59:18'),(21,5,'White','US 10',8,'2026-07-22 19:59:18'),(22,5,'White','US 11',7,'2026-07-22 19:59:18'),(23,5,'Black','US 10',5,'2026-07-22 19:59:18'),(24,6,'Black','US 9',2,'2026-07-22 19:59:18'),(25,6,'Grey','US 9.5',2,'2026-07-22 19:59:18'),(26,6,'Black','US 10',4,'2026-07-22 19:59:18');
/*!40000 ALTER TABLE `item_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `category` varchar(50) DEFAULT 'lifestyle',
  `supplier_id` int(10) unsigned DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `min_quantity` int(11) NOT NULL DEFAULT 0,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `color` varchar(100) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `items_ibfk_1` (`supplier_id`),
  CONSTRAINT `items_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`order_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `items`
--

LOCK TABLES `items` WRITE;
/*!40000 ALTER TABLE `items` DISABLE KEYS */;
INSERT INTO `items` (`id`, `name`, `category`, `supplier_id`, `quantity`, `min_quantity`, `price`, `color`, `size`, `image`, `created_at`) VALUES (1,'Nike Air Max 90 Essentials','sport',1,37,10,130.00,'White/Black','US 10','uploads/items/airmax90.png','2026-05-29 19:20:01'),(2,'onitsuka','lifestyle',6,34,5,30.00,'Red','US 9','uploads/items/onitsuka.jpg','2026-06-01 00:51:37'),(3,'Air Jordan 1 Retro High OG','classic',1,21,5,180.00,'Bred','US 11','uploads/items/airjordan1.jpg','2026-05-29 19:20:01'),(4,'Puma Clyde Classic Suede','classic',2,50,5,85.00,'Navy','US 9','uploads/items/suede-classic.png','2026-05-29 19:20:01'),(5,'Air Force 1','sport',1,30,5,50.00,'White','US 10','uploads/items/airforce1.jpg','2026-05-29 20:00:33'),(6,'Nike Pegasus 42','lifestyle',1,8,5,131.25,'Grey','US 9.5','uploads/items/pegasus.png','2026-07-19 06:30:25');
/*!40000 ALTER TABLE `items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock`
--

DROP TABLE IF EXISTS `stock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` int(10) unsigned NOT NULL,
  `category` varchar(100) DEFAULT 'Shoes',
  `supplier_id` int(10) unsigned DEFAULT NULL,
  `current_qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_threshold` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit` varchar(20) DEFAULT 'pairs',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_stock_items` (`item_id`),
  KEY `fk_stock_suppliers` (`supplier_id`),
  CONSTRAINT `fk_stock_items` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_stock_suppliers` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`order_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock`
--

LOCK TABLES `stock` WRITE;
/*!40000 ALTER TABLE `stock` DISABLE KEYS */;
INSERT INTO `stock` (`id`, `item_id`, `category`, `supplier_id`, `current_qty`, `min_threshold`, `unit`, `last_updated`) VALUES (1,1,'Shoes',1,37.00,10.00,'pairs','2026-07-22 21:11:57'),(3,3,'Shoes',1,21.00,5.00,'pairs','2026-07-22 14:13:29'),(4,4,'Shoes',2,50.00,5.00,'pairs','2026-07-22 09:10:49'),(5,5,'Shoes',1,30.00,5.00,'pairs','2026-07-19 06:34:11'),(7,2,'Shoes',6,34.00,5.00,'pairs','2026-07-22 13:44:42'),(8,6,'Shoes',1,8.00,5.00,'pairs','2026-07-22 21:06:25');
/*!40000 ALTER TABLE `stock` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliers` (
  `order_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_name` varchar(255) NOT NULL,
  `contact_person` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `phone_email` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` (`order_id`, `company_name`, `contact_person`, `category`, `phone_email`, `status`, `created_at`) VALUES (1,'Nike Southeast Asia','John Doe','Athletic Footwear','orders@nike-dist.com','Active','2026-05-29 19:17:35'),(2,'Adidas Distribution','Jane Smith','Sports Apparel & Shoes','+63 917 123 4567','Active','2026-05-29 19:17:35'),(3,'Puma woohoo','Mark','asd','asd@gm.com','Active','2026-05-29 20:00:55'),(6,'sc','Mark','Rubber Shoes','09124081259','Active','2026-06-01 00:52:20'),(7,'NIke Asia','Juan Dela Cruz','Rubber Shoes','09123456789','Active','2026-07-19 06:33:03');
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(10) unsigned NOT NULL,
  `transaction_type` varchar(20) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `transaction_date` datetime DEFAULT current_timestamp(),
  `reason` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` (`id`, `item_id`, `transaction_type`, `quantity`, `user_id`, `created_at`, `transaction_date`, `reason`) VALUES (5,3,'Sold',5,7,'2026-05-31 22:38:48','2026-06-01 14:20:00','Online order'),(6,1,'Restock',44,NULL,'2026-05-31 23:44:51','2026-06-01 07:44:51','asd'),(7,2,'Restock',34,7,'2026-06-01 00:53:07','2026-06-01 08:53:07','Critical Stock / Low Stock'),(17,1,'Restock',3,18,'2026-07-22 21:07:31','2026-07-23 05:07:31','Stock adjustment via admin'),(18,1,'Sold',3,18,'2026-07-22 21:07:31','2026-07-23 05:07:31','Stock adjustment via admin'),(19,1,'Restock',4,18,'2026-07-22 21:11:57','2026-07-23 05:11:57','Stock adjustment via admin'),(20,1,'Sold',4,18,'2026-07-22 21:11:57','2026-07-23 05:11:57','Stock adjustment via admin');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` varchar(50) NOT NULL DEFAULT 'user',
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Active',
  `provider` enum('local','google','facebook') NOT NULL DEFAULT 'local',
  `provider_id` varchar(190) DEFAULT NULL,
  `avatar` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_provider` (`provider`,`provider_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `username`, `password_hash`, `created_at`, `role`, `name`, `email`, `status`, `provider`, `provider_id`, `avatar`) VALUES (7,'mark','$2y$10$K2scmmI8OWu48pbISjwVrOHfMApfFTZTErsZmt77xHCwEM3Bl8vuC','2026-05-29 10:51:38','Staff','mark','nrqzmrk@asf.com','active','local',NULL,NULL),(14,'','','2026-07-09 10:37:40','user','Mark Anthony Enriquez','markanthonyenriquezzz@gmail.com','Active','facebook','1311847634445784','https://platform-lookaside.fbsbx.com/platform/profilepic/?asid=1311847634445784&height=50&width=50&ext=1786185461&hash=Aful-GOW8W2_ZhG7MKG54BTJ'),(16,'beandr','$2y$10$PF.T0/34pbqnAmW6/n1GFOWKEkhmgsChnyDP0NY/KnJ1l2jA7x5J6','2026-07-21 02:26:12','Staff','bea','beandr@gmail.com','Active','local',NULL,NULL),(18,'admin','$2y$10$QIrFPRj8M34winZFkY2zDOCadHVNu40EueH9isnPc/ztLrmy7mxDS','2026-07-22 08:57:29','Admin','Admin','admin@inventory.com','Active','local',NULL,NULL),(19,'bAsto','$2y$10$MOMtxxNtPH7g2F2y130S5uEnjHWB4xQ6uLB0eg1vMVDeSuKdzxCJi','2026-07-22 10:19:44','Staff','Prince Christian Basto','princebasto123@gmail.com','active','local',NULL,NULL);
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

-- Dump completed on 2026-07-23  5:15:00
