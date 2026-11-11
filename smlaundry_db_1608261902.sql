-- ======================================================
-- Database Backup: smlaundry_db
-- Generated: 2026-08-16 19:02:23
-- File: smlaundry_db_1608261902.sql
-- ======================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;


-- ------------------------------------------------------
-- Table: institutions
-- ------------------------------------------------------

DROP TABLE IF EXISTS `institutions`;
CREATE TABLE `institutions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `institution_name` varchar(255) NOT NULL,
  `mobile_number` varchar(20) NOT NULL DEFAULT '',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_institution_name` (`institution_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `institutions` (`id`, `institution_name`, `mobile_number`, `status`, `created_at`, `updated_at`) VALUES ('1', 'SREEKRISHNA HOTEL', '8907277471', 'Active', '2026-08-16 20:25:08', '2026-08-16 20:33:27');
INSERT INTO `institutions` (`id`, `institution_name`, `mobile_number`, `status`, `created_at`, `updated_at`) VALUES ('2', 'LAKSHMI LODGE', '7907169675', 'Active', '2026-08-16 20:26:27', '2026-08-16 20:33:00');
INSERT INTO `institutions` (`id`, `institution_name`, `mobile_number`, `status`, `created_at`, `updated_at`) VALUES ('3', 'PANJAJANYAM', '9496047137', 'Active', '2026-08-16 20:26:33', '2026-08-16 20:33:16');
INSERT INTO `institutions` (`id`, `institution_name`, `mobile_number`, `status`, `created_at`, `updated_at`) VALUES ('4', 'KRISHNA LODGE', '9446510012', 'Active', '2026-08-16 22:19:12', '2026-08-16 22:19:12');


-- ------------------------------------------------------
-- Table: orders
-- ------------------------------------------------------

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` varchar(50) NOT NULL,
  `date` varchar(20) DEFAULT NULL,
  `isoDate` varchar(20) DEFAULT NULL,
  `time` varchar(20) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `count` int(11) DEFAULT NULL,
  `service` varchar(100) DEFAULT NULL,
  `items` text DEFAULT NULL,
  `extraCharge` decimal(10,2) DEFAULT 0.00,
  `collection` decimal(10,2) NOT NULL DEFAULT 0.00,
  `received_amount` decimal(10,2) DEFAULT NULL,
  `received_date` date DEFAULT NULL,
  `instrument_type` varchar(20) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `collection` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` varchar(50) NOT NULL,
  `phone` varchar(30) NOT NULL DEFAULT '',
  `received_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `received_date` date NOT NULL,
  `instrument_type` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_collection_order` (`order_id`),
  KEY `idx_collection_received_date` (`received_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `orders` (`id`, `date`, `isoDate`, `time`, `name`, `phone`, `count`, `service`, `items`, `extraCharge`, `collection`, `details`, `amount`, `status`, `created_at`) VALUES ('160826001', '16/8/2026', '2026-08-16', '10:05:39 PM', 'VISHNU', '9061616676', '16', 'Steam Ironing', '[{\"name\":\"Skirt\",\"qty\":6,\"rate\":15,\"amount\":90},{\"name\":\"Dhothi\",\"qty\":6,\"rate\":20,\"amount\":120},{\"name\":\"Curtain (Small)\",\"qty\":4,\"rate\":60,\"amount\":240}]', '0.00', '450.00', 'Skirt (6) @ ₹15.00, Dhothi (6) @ ₹20.00, Curtain (Small) (4) @ ₹60.00', '450.00', 'Completed', '2026-08-16 22:12:48');
INSERT INTO `orders` (`id`, `date`, `isoDate`, `time`, `name`, `phone`, `count`, `service`, `items`, `extraCharge`, `collection`, `details`, `amount`, `status`, `created_at`) VALUES ('160826002', '16/8/2026', '2026-08-16', '10:13:36 PM', 'AISWARYA K', '9074475422', '11', 'Steam Ironing', '[{\"name\":\"Saree\",\"qty\":4,\"rate\":40,\"amount\":160},{\"name\":\"Shirt\",\"qty\":7,\"rate\":20,\"amount\":140}]', '0.00', '300.00', 'Saree (4) @ ₹40.00, Shirt (7) @ ₹20.00', '300.00', 'Completed', '2026-08-16 22:14:32');
INSERT INTO `orders` (`id`, `date`, `isoDate`, `time`, `name`, `phone`, `count`, `service`, `items`, `extraCharge`, `collection`, `details`, `amount`, `status`, `created_at`) VALUES ('160826003', '16/8/2026', '2026-08-16', '10:18:42 PM', 'PANJAJANYAM', '9496047137', '17', 'Laundry', '[{\"name\":\"Bedsheet King Size\",\"qty\":7,\"rate\":120,\"amount\":840},{\"name\":\"Curtain (Small)\",\"qty\":5,\"rate\":60,\"amount\":300},{\"name\":\"Curtain (Long)\",\"qty\":5,\"rate\":80,\"amount\":400}]', '120.00', '1780.00', 'Bedsheet King Size (7), Curtain (Small) (5), Curtain (Long) (5) + Extra (₹120)', '1660.00', 'Completed', '2026-08-16 22:20:20');
INSERT INTO `orders` (`id`, `date`, `isoDate`, `time`, `name`, `phone`, `count`, `service`, `items`, `extraCharge`, `collection`, `details`, `amount`, `status`, `created_at`) VALUES ('160826004', '16/8/2026', '2026-08-16', '10:29:48 PM', 'PANJAJANYAM', '9496047137', '1', 'Steam Ironing', '[{\"name\":\"Saree\",\"qty\":1,\"rate\":50,\"amount\":50}]', '50.00', '0.00', 'Saree (1) + Extra (₹50)', '100.00', 'Pending', '2026-08-16 22:29:48');


-- ------------------------------------------------------
-- Table: rates
-- ------------------------------------------------------

DROP TABLE IF EXISTS `rates`;
CREATE TABLE `rates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_type` varchar(100) NOT NULL,
  `item_name` varchar(150) NOT NULL,
  `rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_service_item` (`service_type`,`item_name`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('1', 'Steam Ironing', 'Saree', '50.00', '2026-08-16 22:23:41', '2026-08-16 22:29:16');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('2', 'Steam Ironing', 'Blouse', '15.00', '2026-08-16 22:23:41', '2026-08-16 22:23:51');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('3', 'Steam Ironing', 'Churidar set', '45.00', '2026-08-16 22:23:41', '2026-08-16 22:23:51');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('4', 'Steam Ironing', 'Jeans', '25.00', '2026-08-16 22:23:42', '2026-08-16 22:23:51');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('5', 'Steam Ironing', 'T Shirt', '20.00', '2026-08-16 22:23:42', '2026-08-16 22:23:51');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('6', 'Steam Ironing', 'Dress long', '30.00', '2026-08-16 22:23:42', '2026-08-16 22:23:51');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('7', 'Steam Ironing', 'Skirt', '15.00', '2026-08-16 22:23:42', '2026-08-16 22:23:51');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('8', 'Steam Ironing', 'Dhothi', '20.00', '2026-08-16 22:23:42', '2026-08-16 22:23:51');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('9', 'Steam Ironing', 'Shirt', '20.00', '2026-08-16 22:23:42', '2026-08-16 22:23:51');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('10', 'Steam Ironing', 'Pant', '20.00', '2026-08-16 22:23:42', '2026-08-16 22:23:51');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('11', 'Steam Ironing', 'Jubba', '20.00', '2026-08-16 22:23:42', '2026-08-16 22:23:51');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('12', 'Steam Ironing', 'Blanket', '250.00', '2026-08-16 22:23:42', '2026-08-16 22:23:51');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('13', 'Steam Ironing', 'Curtain (Small)', '60.00', '2026-08-16 22:23:42', '2026-08-16 22:23:51');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('14', 'Steam Ironing', 'Curtain (Long)', '80.00', '2026-08-16 22:23:42', '2026-08-16 22:23:51');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('15', 'Steam Ironing', 'Bedsheet', '100.00', '2026-08-16 22:23:42', '2026-08-16 22:23:51');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('16', 'Steam Ironing', 'Bedsheet King Size', '120.00', '2026-08-16 22:23:42', '2026-08-16 22:23:51');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('17', 'Steam Ironing', 'Pillow Cover', '20.00', '2026-08-16 22:23:42', '2026-08-16 22:23:51');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('18', 'Laundry', 'Pattusaree', '250.00', '2026-08-16 22:23:42', '2026-08-16 22:23:59');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('19', 'Laundry', 'Saree', '100.00', '2026-08-16 22:23:42', '2026-08-16 22:23:59');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('20', 'Laundry', 'Blouse', '30.00', '2026-08-16 22:23:42', '2026-08-16 22:23:59');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('21', 'Laundry', 'Churidar Set', '90.00', '2026-08-16 22:23:42', '2026-08-16 22:23:59');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('22', 'Laundry', 'Jeans', '50.00', '2026-08-16 22:23:42', '2026-08-16 22:23:59');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('23', 'Laundry', 'T Shirt', '40.00', '2026-08-16 22:23:42', '2026-08-16 22:23:59');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('24', 'Laundry', 'Dress long', '50.00', '2026-08-16 22:23:42', '2026-08-16 22:23:59');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('25', 'Laundry', 'Skirt', '30.00', '2026-08-16 22:23:42', '2026-08-16 22:23:59');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('26', 'Laundry', 'Dhothi', '60.00', '2026-08-16 22:23:42', '2026-08-16 22:23:59');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('27', 'Laundry', 'Shirt', '50.00', '2026-08-16 22:23:42', '2026-08-16 22:23:59');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('28', 'Laundry', 'Pant', '60.00', '2026-08-16 22:23:42', '2026-08-16 22:23:59');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('29', 'Laundry', 'Jubba', '60.00', '2026-08-16 22:23:42', '2026-08-16 22:23:59');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('30', 'Laundry', 'Baniyan', '30.00', '2026-08-16 22:23:42', '2026-08-16 22:23:59');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('31', 'Laundry', 'Coat', '250.00', '2026-08-16 22:23:42', '2026-08-16 22:23:59');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('32', 'Laundry', 'Blanket', '250.00', '2026-08-16 22:23:42', '2026-08-16 22:23:59');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('33', 'Laundry', 'Curtain (Small)', '60.00', '2026-08-16 22:23:42', '2026-08-16 22:23:59');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('34', 'Laundry', 'Curtain (Long)', '80.00', '2026-08-16 22:23:42', '2026-08-16 22:23:59');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('35', 'Laundry', 'Bedsheet', '100.00', '2026-08-16 22:23:42', '2026-08-16 22:23:59');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('36', 'Laundry', 'Bedsheet King Size', '120.00', '2026-08-16 22:23:42', '2026-08-16 22:23:59');
INSERT INTO `rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('37', 'Laundry', 'Pillow Cover', '20.00', '2026-08-16 22:23:42', '2026-08-16 22:23:59');


-- ------------------------------------------------------
-- Table: service_rates
-- ------------------------------------------------------

DROP TABLE IF EXISTS `service_rates`;
CREATE TABLE `service_rates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_type` varchar(50) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_service_item` (`service_type`,`item_name`)
) ENGINE=InnoDB AUTO_INCREMENT=734 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('1', 'Steam Ironing', 'Saree', '40.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('2', 'Steam Ironing', 'Blouse', '15.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('3', 'Steam Ironing', 'Churidar set', '45.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('4', 'Steam Ironing', 'Jeans', '25.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('5', 'Steam Ironing', 'T Shirt', '20.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('6', 'Steam Ironing', 'Dress long', '30.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('7', 'Steam Ironing', 'Skirt', '15.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('8', 'Steam Ironing', 'Dhothi', '20.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('9', 'Steam Ironing', 'Shirt', '20.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('10', 'Steam Ironing', 'Pant', '20.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('11', 'Steam Ironing', 'Jubba', '20.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('12', 'Steam Ironing', 'Blanket', '250.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('13', 'Steam Ironing', 'Curtain (Small)', '60.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('14', 'Steam Ironing', 'Curtain (Long)', '80.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('15', 'Steam Ironing', 'Bedsheet', '100.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('16', 'Steam Ironing', 'Bedsheet King Size', '120.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('17', 'Steam Ironing', 'Pillow Cover', '20.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('18', 'Laundry', 'Pattusaree', '250.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('19', 'Laundry', 'Saree', '100.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('20', 'Laundry', 'Blouse', '30.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('21', 'Laundry', 'Churidar Set', '90.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('22', 'Laundry', 'Jeans', '50.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('23', 'Laundry', 'T Shirt', '40.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('24', 'Laundry', 'Dress long', '50.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('25', 'Laundry', 'Skirt', '30.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('26', 'Laundry', 'Dhothi', '60.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('27', 'Laundry', 'Shirt', '50.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('28', 'Laundry', 'Pant', '60.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('29', 'Laundry', 'Jubba', '60.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('30', 'Laundry', 'Baniyan', '30.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('31', 'Laundry', 'Coat', '250.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('32', 'Laundry', 'Blanket', '250.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('33', 'Laundry', 'Curtain (Small)', '60.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('34', 'Laundry', 'Curtain (Long)', '80.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('35', 'Laundry', 'Bedsheet', '100.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('36', 'Laundry', 'Bedsheet King Size', '120.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');
INSERT INTO `service_rates` (`id`, `service_type`, `item_name`, `rate`, `created_at`, `updated_at`) VALUES ('37', 'Laundry', 'Pillow Cover', '20.00', '2026-08-16 20:57:45', '2026-08-16 20:57:45');


SET FOREIGN_KEY_CHECKS = 1;
-- End of backup
