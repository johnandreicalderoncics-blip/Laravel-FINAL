-- =====================================================================
-- Ordering System database export (schema + sample data)
--
-- Import into an EMPTY database named `ordering_system`:
--   phpMyAdmin: select the database -> Import -> choose this file
--   CLI:        mysql -u root -p ordering_system < database/ordering_system.sql
--
-- Works on MySQL 8.0.16+ and MariaDB 10.4+. Built from the migrations and
-- seeders with `php artisan migrate:fresh --seed`; see README.md.
-- All seeded accounts use the password: password
-- =====================================================================



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
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cart_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cart_items` (
  `cart_item_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `quantity` int unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`cart_item_id`),
  UNIQUE KEY `cart_items_user_id_product_id_unique` (`user_id`,`product_id`),
  KEY `cart_items_product_id_foreign` (`product_id`),
  CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `chk_cart_items_quantity` CHECK ((`quantity` >= 1))
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cart_items` WRITE;
/*!40000 ALTER TABLE `cart_items` DISABLE KEYS */;
INSERT INTO `cart_items` (`cart_item_id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES (1,3,12,1,'2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `cart_items` (`cart_item_id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES (2,3,8,3,'2026-11-10 22:28:01','2026-11-10 22:28:01');
/*!40000 ALTER TABLE `cart_items` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `category_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `categories_category_name_unique` (`category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` (`category_id`, `category_name`, `description`, `created_at`, `updated_at`) VALUES (1,'Electronics','Computer accessories and small gadgets','2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `categories` (`category_id`, `category_name`, `description`, `created_at`, `updated_at`) VALUES (2,'Home & Kitchen','Drinkware, cookware and everyday home items','2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `categories` (`category_id`, `category_name`, `description`, `created_at`, `updated_at`) VALUES (3,'School & Office','Notebooks, pens and desk supplies','2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `categories` (`category_id`, `category_name`, `description`, `created_at`, `updated_at`) VALUES (4,'Apparel','Clothing and wearable accessories','2026-11-10 22:28:01','2026-11-10 22:28:01');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `inventories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventories` (
  `inventory_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `quantity_in_stock` int unsigned NOT NULL DEFAULT '0',
  `last_stock_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`inventory_id`),
  UNIQUE KEY `inventories_product_id_unique` (`product_id`),
  CONSTRAINT `inventories_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `inventories` WRITE;
/*!40000 ALTER TABLE `inventories` DISABLE KEYS */;
INSERT INTO `inventories` (`inventory_id`, `product_id`, `quantity_in_stock`, `last_stock_update`) VALUES (1,1,45,'2026-11-10 22:28:01');
INSERT INTO `inventories` (`inventory_id`, `product_id`, `quantity_in_stock`, `last_stock_update`) VALUES (2,2,8,'2026-11-10 22:28:01');
INSERT INTO `inventories` (`inventory_id`, `product_id`, `quantity_in_stock`, `last_stock_update`) VALUES (3,3,120,'2026-11-10 22:28:01');
INSERT INTO `inventories` (`inventory_id`, `product_id`, `quantity_in_stock`, `last_stock_update`) VALUES (4,4,0,'2026-11-10 22:28:01');
INSERT INTO `inventories` (`inventory_id`, `product_id`, `quantity_in_stock`, `last_stock_update`) VALUES (5,5,60,'2026-11-10 22:28:01');
INSERT INTO `inventories` (`inventory_id`, `product_id`, `quantity_in_stock`, `last_stock_update`) VALUES (6,6,12,'2026-11-10 22:28:01');
INSERT INTO `inventories` (`inventory_id`, `product_id`, `quantity_in_stock`, `last_stock_update`) VALUES (7,7,25,'2026-11-10 22:28:01');
INSERT INTO `inventories` (`inventory_id`, `product_id`, `quantity_in_stock`, `last_stock_update`) VALUES (8,8,200,'2026-11-10 22:28:01');
INSERT INTO `inventories` (`inventory_id`, `product_id`, `quantity_in_stock`, `last_stock_update`) VALUES (9,9,5,'2026-11-10 22:28:01');
INSERT INTO `inventories` (`inventory_id`, `product_id`, `quantity_in_stock`, `last_stock_update`) VALUES (10,10,30,'2026-11-10 22:28:01');
INSERT INTO `inventories` (`inventory_id`, `product_id`, `quantity_in_stock`, `last_stock_update`) VALUES (11,11,75,'2026-11-10 22:28:01');
INSERT INTO `inventories` (`inventory_id`, `product_id`, `quantity_in_stock`, `last_stock_update`) VALUES (12,12,40,'2026-11-10 22:28:01');
INSERT INTO `inventories` (`inventory_id`, `product_id`, `quantity_in_stock`, `last_stock_update`) VALUES (13,13,20,'2026-11-10 22:28:01');
/*!40000 ALTER TABLE `inventories` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `logs` (
  `log_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `logs_user_id_foreign` (`user_id`),
  KEY `logs_timestamp_index` (`timestamp`),
  CONSTRAINT `logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `logs` WRITE;
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
INSERT INTO `logs` (`log_id`, `user_id`, `action`, `timestamp`) VALUES (1,1,'Logged in','2026-10-28 22:28:01');
INSERT INTO `logs` (`log_id`, `user_id`, `action`, `timestamp`) VALUES (2,1,'Added product: Wireless Mouse','2026-10-28 22:28:01');
INSERT INTO `logs` (`log_id`, `user_id`, `action`, `timestamp`) VALUES (3,2,'Logged in','2026-10-31 22:28:01');
INSERT INTO `logs` (`log_id`, `user_id`, `action`, `timestamp`) VALUES (4,2,'Updated stock: Ceramic Coffee Mug','2026-10-31 22:28:01');
INSERT INTO `logs` (`log_id`, `user_id`, `action`, `timestamp`) VALUES (5,3,'Registered an account','2026-10-29 22:28:01');
INSERT INTO `logs` (`log_id`, `user_id`, `action`, `timestamp`) VALUES (6,3,'Placed order #1','2026-10-29 22:28:01');
INSERT INTO `logs` (`log_id`, `user_id`, `action`, `timestamp`) VALUES (7,4,'Placed order #2','2026-11-01 22:28:01');
INSERT INTO `logs` (`log_id`, `user_id`, `action`, `timestamp`) VALUES (8,1,'Marked order #1 as completed','2026-11-02 22:28:01');
INSERT INTO `logs` (`log_id`, `user_id`, `action`, `timestamp`) VALUES (9,4,'Cancelled order #4','2026-11-06 22:28:01');
INSERT INTO `logs` (`log_id`, `user_id`, `action`, `timestamp`) VALUES (10,1,'Deactivated user: inactive@example.com','2026-11-08 22:28:01');
INSERT INTO `logs` (`log_id`, `user_id`, `action`, `timestamp`) VALUES (11,1,'Logged in','2026-11-10 22:28:01');
/*!40000 ALTER TABLE `logs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000003_create_sessions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2026_10_15_060350_categories',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2026_10_15_060355_products',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2026_10_15_060400_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2026_10_15_060450_orders',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2026_10_15_060536_order_items',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2026_10_15_060612_inventories',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2026_11_11_040000_cart_items',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2026_11_11_040100_logs',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2026_11_11_040200_check_constraints',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `order_item_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `quantity` int unsigned NOT NULL,
  `item_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`order_item_id`),
  UNIQUE KEY `order_items_order_id_product_id_unique` (`order_id`,`product_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE RESTRICT,
  CONSTRAINT `chk_order_items_item_price` CHECK ((`item_price` >= 0)),
  CONSTRAINT `chk_order_items_quantity` CHECK ((`quantity` >= 1))
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `item_price`, `created_at`, `updated_at`) VALUES (1,1,1,1,499.00,'2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `item_price`, `created_at`, `updated_at`) VALUES (2,1,3,2,199.00,'2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `item_price`, `created_at`, `updated_at`) VALUES (3,2,5,2,349.00,'2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `item_price`, `created_at`, `updated_at`) VALUES (4,2,6,4,149.00,'2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `item_price`, `created_at`, `updated_at`) VALUES (5,3,2,1,1899.00,'2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `item_price`, `created_at`, `updated_at`) VALUES (6,4,4,1,1299.00,'2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `item_price`, `created_at`, `updated_at`) VALUES (7,5,8,5,59.00,'2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `item_price`, `created_at`, `updated_at`) VALUES (8,5,9,2,129.00,'2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `item_price`, `created_at`, `updated_at`) VALUES (9,5,10,1,279.00,'2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `item_price`, `created_at`, `updated_at`) VALUES (10,6,11,3,249.00,'2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `item_price`, `created_at`, `updated_at`) VALUES (11,6,12,1,199.00,'2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `item_price`, `created_at`, `updated_at`) VALUES (12,7,7,1,799.00,'2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `item_price`, `created_at`, `updated_at`) VALUES (13,7,6,2,149.00,'2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `item_price`, `created_at`, `updated_at`) VALUES (14,8,1,1,499.00,'2026-11-10 22:28:01','2026-11-10 22:28:01');
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `order_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `order_date` date NOT NULL,
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `order_status` enum('pending','processing','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `shipping_address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_method` enum('cash_on_delivery','gcash','bank_transfer','card') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash_on_delivery',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  KEY `orders_user_id_foreign` (`user_id`),
  KEY `orders_order_status_order_date_index` (`order_status`,`order_date`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT,
  CONSTRAINT `chk_orders_total_price` CHECK ((`total_price` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `total_price`, `order_status`, `shipping_address`, `contact_number`, `payment_method`, `created_at`, `updated_at`) VALUES (1,3,'2026-10-30',897.00,'completed','789 Customer Lane, Customer City','+639170000003','cash_on_delivery','2026-10-29 22:28:01','2026-11-10 22:28:01');
INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `total_price`, `order_status`, `shipping_address`, `contact_number`, `payment_method`, `created_at`, `updated_at`) VALUES (2,4,'2026-11-02',1294.00,'completed','321 Buyer Blvd, Buyer Town','+639170000004','gcash','2026-11-01 22:28:01','2026-11-10 22:28:01');
INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `total_price`, `order_status`, `shipping_address`, `contact_number`, `payment_method`, `created_at`, `updated_at`) VALUES (3,3,'2026-11-05',1899.00,'completed','789 Customer Lane, Customer City','+639170000003','card','2026-11-04 22:28:01','2026-11-10 22:28:01');
INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `total_price`, `order_status`, `shipping_address`, `contact_number`, `payment_method`, `created_at`, `updated_at`) VALUES (4,4,'2026-11-07',1299.00,'cancelled','321 Buyer Blvd, Buyer Town','+639170000004','cash_on_delivery','2026-11-06 22:28:01','2026-11-10 22:28:01');
INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `total_price`, `order_status`, `shipping_address`, `contact_number`, `payment_method`, `created_at`, `updated_at`) VALUES (5,3,'2026-11-08',832.00,'completed','789 Customer Lane, Customer City','+639170000003','gcash','2026-11-07 22:28:01','2026-11-10 22:28:01');
INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `total_price`, `order_status`, `shipping_address`, `contact_number`, `payment_method`, `created_at`, `updated_at`) VALUES (6,4,'2026-11-10',946.00,'processing','321 Buyer Blvd, Buyer Town','+639170000004','bank_transfer','2026-11-09 22:28:01','2026-11-10 22:28:01');
INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `total_price`, `order_status`, `shipping_address`, `contact_number`, `payment_method`, `created_at`, `updated_at`) VALUES (7,3,'2026-11-11',1097.00,'pending','789 Customer Lane, Customer City','+639170000003','cash_on_delivery','2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `total_price`, `order_status`, `shipping_address`, `contact_number`, `payment_method`, `created_at`, `updated_at`) VALUES (8,4,'2026-11-11',499.00,'pending','321 Buyer Blvd, Buyer Town','+639170000004','gcash','2026-11-10 22:28:01','2026-11-10 22:28:01');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `product_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned DEFAULT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `available_quantity` int unsigned NOT NULL DEFAULT '0',
  `reorder_level` int unsigned NOT NULL DEFAULT '10',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`product_id`),
  KEY `products_category_id_foreign` (`category_id`),
  KEY `products_status_index` (`status`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE RESTRICT,
  CONSTRAINT `chk_products_price` CHECK ((`price` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `description`, `price`, `available_quantity`, `reorder_level`, `image`, `status`, `created_at`, `updated_at`) VALUES (1,1,'Wireless Mouse','2.4 GHz optical mouse with USB receiver',499.00,45,10,NULL,'active','2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `description`, `price`, `available_quantity`, `reorder_level`, `image`, `status`, `created_at`, `updated_at`) VALUES (2,1,'Mechanical Keyboard','Full-size keyboard with blue switches',1899.00,8,10,NULL,'active','2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `description`, `price`, `available_quantity`, `reorder_level`, `image`, `status`, `created_at`, `updated_at`) VALUES (3,1,'USB-C Charging Cable 1m','Braided fast-charging cable',199.00,120,20,NULL,'active','2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `description`, `price`, `available_quantity`, `reorder_level`, `image`, `status`, `created_at`, `updated_at`) VALUES (4,1,'Bluetooth Earbuds','True wireless earbuds with charging case',1299.00,0,5,NULL,'active','2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `description`, `price`, `available_quantity`, `reorder_level`, `image`, `status`, `created_at`, `updated_at`) VALUES (5,2,'Stainless Steel Tumbler 500ml','Double-wall insulated tumbler',349.00,60,15,NULL,'active','2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `description`, `price`, `available_quantity`, `reorder_level`, `image`, `status`, `created_at`, `updated_at`) VALUES (6,2,'Ceramic Coffee Mug','350ml glazed ceramic mug',149.00,12,15,NULL,'active','2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `description`, `price`, `available_quantity`, `reorder_level`, `image`, `status`, `created_at`, `updated_at`) VALUES (7,2,'Non-stick Frying Pan 24cm','Aluminium pan with heat-resistant handle',799.00,25,5,NULL,'active','2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `description`, `price`, `available_quantity`, `reorder_level`, `image`, `status`, `created_at`, `updated_at`) VALUES (8,3,'Spiral Notebook A5','80 ruled leaves',59.00,200,30,NULL,'active','2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `description`, `price`, `available_quantity`, `reorder_level`, `image`, `status`, `created_at`, `updated_at`) VALUES (9,3,'Gel Pen Set (10 pcs)','0.5mm assorted colors',129.00,5,20,NULL,'active','2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `description`, `price`, `available_quantity`, `reorder_level`, `image`, `status`, `created_at`, `updated_at`) VALUES (10,3,'Desk Organizer','Five-compartment mesh organizer',279.00,30,10,NULL,'active','2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `description`, `price`, `available_quantity`, `reorder_level`, `image`, `status`, `created_at`, `updated_at`) VALUES (11,4,'Cotton T-Shirt (Plain White)','Unisex round-neck shirt, 100% cotton',249.00,75,15,NULL,'active','2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `description`, `price`, `available_quantity`, `reorder_level`, `image`, `status`, `created_at`, `updated_at`) VALUES (12,4,'Canvas Tote Bag','Heavy-duty canvas bag with inner pocket',199.00,40,10,NULL,'active','2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `description`, `price`, `available_quantity`, `reorder_level`, `image`, `status`, `created_at`, `updated_at`) VALUES (13,4,'Baseball Cap','Adjustable cap, discontinued design',229.00,20,10,NULL,'inactive','2026-11-10 22:28:01','2026-11-10 22:28:01');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','employee','customer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `status`, `phone_number`, `address`, `email_verified_at`, `remember_token`, `created_at`, `updated_at`) VALUES (1,'Admin User','admin@example.com','$2y$12$GiRYNy/j4A659C9vx0xKHuR0QZLxTi39XRVR0ENDkBUmw4e6rKX5O','admin','active','+639170000001','123 Admin Street, Admin City',NULL,NULL,'2026-11-10 22:28:00','2026-11-10 22:28:00');
INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `status`, `phone_number`, `address`, `email_verified_at`, `remember_token`, `created_at`, `updated_at`) VALUES (2,'Employee User','employee@example.com','$2y$12$pjHHfrcDCz.lrutx7MtpSO70at1akhaRUCUCzp5WvCOLhd6gMolKq','employee','active','+639170000002','456 Employee Ave, Employee City',NULL,NULL,'2026-11-10 22:28:00','2026-11-10 22:28:00');
INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `status`, `phone_number`, `address`, `email_verified_at`, `remember_token`, `created_at`, `updated_at`) VALUES (3,'John Customer','customer1@example.com','$2y$12$8IoAlBiOJhDz0uuQLNf5POAPUxYRRH7fsTpE5bLqWOgXgs0Y2dwEO','customer','active','+639170000003','789 Customer Lane, Customer City',NULL,NULL,'2026-11-10 22:28:00','2026-11-10 22:28:00');
INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `status`, `phone_number`, `address`, `email_verified_at`, `remember_token`, `created_at`, `updated_at`) VALUES (4,'Jane Customer','customer2@example.com','$2y$12$ZEK31n7JUHmCRsxSLsWlH.smWC7b9sGTUOlYHbhcvGf6myUTohAPO','customer','active','+639170000004','321 Buyer Blvd, Buyer Town',NULL,NULL,'2026-11-10 22:28:01','2026-11-10 22:28:01');
INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `status`, `phone_number`, `address`, `email_verified_at`, `remember_token`, `created_at`, `updated_at`) VALUES (5,'Inactive Customer','inactive@example.com','$2y$12$O91VmdYCC.jpigC.eQDatOTmZTa4LTI1LXL4XB4EFY2GXmsLHtwIC','customer','inactive','+639170000005','654 Former Road, Old Town',NULL,NULL,'2026-11-10 22:28:01','2026-11-10 22:28:01');
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

