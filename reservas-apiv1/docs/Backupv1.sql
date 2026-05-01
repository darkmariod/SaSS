-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.0.30 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para curso_reserva
CREATE DATABASE IF NOT EXISTS `curso_reserva` /*!40100 DEFAULT CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `curso_reserva`;

-- Volcando estructura para tabla curso_reserva.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla curso_reserva.failed_jobs: ~0 rows (aproximadamente)
DELETE FROM `failed_jobs`;

-- Volcando estructura para tabla curso_reserva.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla curso_reserva.migrations: ~12 rows (aproximadamente)
DELETE FROM `migrations`;
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '2014_10_12_000000_create_users_table', 1),
	(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
	(3, '2019_08_19_000000_create_failed_jobs_table', 1),
	(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
	(5, '2024_09_18_023828_create_roles_table', 2),
	(6, '2024_09_18_024310_add_rol_id_to_users_table', 3),
	(7, '2024_09_18_024826_add_fields_to_users_table', 4),
	(8, '2024_09_18_025212_remove_name_from_users_table', 5),
	(9, '2024_09_18_025635_add_delete_at_to_users_table', 6),
	(10, '2024_09_18_025917_create_reservations_table', 7),
	(11, '2024_09_18_173122_rename_status_in_reservations_table', 8),
	(12, '2024_09_18_173520_create_reservations_details_table', 9),
	(13, '2024_09_27_034556_rename_consultand_id_to_consultant_id_in_reservartion_table', 10);

-- Volcando estructura para tabla curso_reserva.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla curso_reserva.password_reset_tokens: ~0 rows (aproximadamente)
DELETE FROM `password_reset_tokens`;

-- Volcando estructura para tabla curso_reserva.personal_access_tokens
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla curso_reserva.personal_access_tokens: ~0 rows (aproximadamente)
DELETE FROM `personal_access_tokens`;

-- Volcando estructura para tabla curso_reserva.reservations
CREATE TABLE IF NOT EXISTS `reservations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `consultant_id` bigint unsigned NOT NULL,
  `reservation_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `reservation_status` enum('pendiente','confirmada','cancelada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `total_amount` decimal(8,2) DEFAULT NULL,
  `payment_status` enum('pendiente','pagado','fallido') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reservations_user_id_foreign` (`user_id`),
  KEY `reservations_consultand_id_foreign` (`consultant_id`),
  CONSTRAINT `reservations_consultand_id_foreign` FOREIGN KEY (`consultant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla curso_reserva.reservations: ~58 rows (aproximadamente)
DELETE FROM `reservations`;
INSERT INTO `reservations` (`id`, `user_id`, `consultant_id`, `reservation_date`, `start_time`, `end_time`, `reservation_status`, `total_amount`, `payment_status`, `cancellation_reason`, `created_at`, `updated_at`) VALUES
	(1, 9, 5, '2024-10-16', '12:00:00', '13:00:00', 'cancelada', 50.00, 'fallido', NULL, '2024-09-27 04:59:54', '2024-09-27 04:59:54'),
	(2, 17, 6, '2024-10-15', '09:00:00', '10:00:00', 'confirmada', 50.00, 'pendiente', NULL, '2024-09-27 04:59:54', '2024-09-27 04:59:54'),
	(3, 12, 7, '2024-10-25', '10:00:00', '11:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-09-27 04:59:54', '2024-09-27 04:59:54'),
	(4, 15, 6, '2024-10-18', '14:00:00', '15:00:00', 'pendiente', 50.00, 'fallido', NULL, '2024-09-27 04:59:54', '2024-09-27 04:59:54'),
	(5, 11, 6, '2024-10-13', '14:00:00', '15:00:00', 'pendiente', 50.00, 'fallido', NULL, '2024-09-27 04:59:54', '2024-09-27 04:59:54'),
	(6, 11, 7, '2024-10-17', '11:00:00', '12:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-09-27 04:59:54', '2024-09-27 04:59:54'),
	(7, 9, 5, '2024-10-26', '14:00:00', '15:00:00', 'pendiente', 50.00, 'pendiente', NULL, '2024-09-27 04:59:54', '2024-09-27 04:59:54'),
	(8, 11, 6, '2024-10-23', '13:00:00', '14:00:00', 'cancelada', 50.00, 'pendiente', NULL, '2024-09-27 04:59:54', '2024-09-27 04:59:54'),
	(9, 14, 5, '2024-10-06', '10:00:00', '11:00:00', 'confirmada', 50.00, 'pendiente', NULL, '2024-09-27 04:59:54', '2024-09-27 04:59:54'),
	(10, 12, 5, '2024-10-06', '09:00:00', '10:00:00', 'confirmada', 50.00, 'fallido', NULL, '2024-09-27 04:59:54', '2024-09-27 04:59:54'),
	(11, 13, 6, '2024-10-12', '15:00:00', '16:00:00', 'confirmada', 50.00, 'fallido', NULL, '2024-09-27 04:59:54', '2024-09-27 04:59:54'),
	(12, 13, 5, '2024-10-02', '09:00:00', '10:00:00', 'cancelada', 50.00, 'pagado', NULL, '2024-09-27 04:59:54', '2024-09-27 04:59:54'),
	(13, 11, 7, '2024-10-03', '15:00:00', '16:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-09-27 04:59:54', '2024-09-27 04:59:54'),
	(14, 10, 7, '2024-10-02', '09:00:00', '10:00:00', 'cancelada', 50.00, 'pagado', 'test', '2024-09-27 04:59:54', '2024-10-01 23:05:16'),
	(15, 10, 6, '2024-10-23', '10:00:00', '11:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-09-27 04:59:54', '2024-09-27 04:59:54'),
	(16, 10, 6, '2024-10-05', '10:00:00', '11:00:00', 'confirmada', 50.00, 'pendiente', NULL, '2024-09-27 04:59:54', '2024-09-27 04:59:54'),
	(17, 17, 6, '2024-10-16', '15:00:00', '16:00:00', 'pendiente', 50.00, 'pagado', NULL, '2024-09-27 04:59:54', '2024-09-27 04:59:54'),
	(18, 8, 6, '2024-10-07', '10:00:00', '11:00:00', 'confirmada', 50.00, 'fallido', NULL, '2024-09-27 04:59:54', '2024-09-27 04:59:54'),
	(19, 14, 7, '2024-10-20', '15:00:00', '16:00:00', 'pendiente', 50.00, 'pendiente', NULL, '2024-09-27 04:59:54', '2024-09-27 04:59:54'),
	(20, 11, 6, '2024-10-15', '12:00:00', '13:00:00', 'confirmada', 50.00, 'fallido', NULL, '2024-09-27 04:59:54', '2024-09-27 04:59:54'),
	(21, 17, 7, '2024-09-27', '12:00:00', '13:00:00', 'pendiente', 50.00, 'pendiente', NULL, '2024-09-27 08:36:21', '2024-09-27 08:36:21'),
	(22, 17, 7, '2024-09-27', '12:00:00', '13:00:00', 'pendiente', 50.00, 'pendiente', NULL, '2024-09-27 08:36:40', '2024-09-27 08:36:40'),
	(23, 17, 6, '2024-10-01', '15:00:00', '16:00:00', 'cancelada', 50.00, 'pendiente', 'por motivo personal', '2024-09-27 08:37:27', '2024-09-29 07:55:36'),
	(24, 11, 7, '2024-09-27', '13:00:00', '14:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-09-27 08:55:35', '2024-09-27 08:55:35'),
	(25, 12, 7, '2024-09-28', '12:00:00', '13:00:00', 'confirmada', 50.00, 'fallido', NULL, '2024-09-27 08:55:54', '2024-09-27 08:55:54'),
	(26, 11, 6, '2024-12-19', '11:00:00', '12:00:00', 'pendiente', 50.00, 'pendiente', NULL, '2024-09-30 23:37:36', '2024-09-30 23:37:36'),
	(27, 10, 7, '2024-10-24', '10:00:00', '11:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-02 05:27:19', '2024-10-02 05:27:19'),
	(28, 10, 5, '2024-10-23', '10:00:00', '11:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-02 05:29:59', '2024-10-02 05:29:59'),
	(29, 10, 6, '2024-11-02', '11:00:00', '12:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-02 05:33:45', '2024-10-02 05:33:45'),
	(30, 10, 6, '2024-10-17', '11:00:00', '12:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-02 05:35:26', '2024-10-02 05:35:26'),
	(31, 10, 5, '2024-10-10', '10:00:00', '11:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-02 05:36:11', '2024-10-02 05:36:11'),
	(32, 10, 6, '2024-10-31', '10:00:00', '11:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-02 05:41:16', '2024-10-02 05:41:16'),
	(33, 10, 5, '2024-10-12', '10:00:00', '11:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-02 05:43:05', '2024-10-02 05:43:05'),
	(34, 10, 5, '2024-11-03', '09:00:00', '10:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-02 05:50:51', '2024-10-02 05:50:51'),
	(35, 10, 5, '2024-11-06', '10:00:00', '11:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-02 05:53:07', '2024-10-02 05:53:07'),
	(36, 10, 6, '2024-10-04', '10:00:00', '11:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-02 05:53:49', '2024-10-02 05:53:49'),
	(37, 10, 6, '2024-10-30', '10:00:00', '11:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-02 05:54:32', '2024-10-02 05:54:32'),
	(38, 10, 5, '2024-10-04', '10:00:00', '11:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-02 06:22:47', '2024-10-02 06:22:47'),
	(39, 10, 5, '2024-10-24', '09:00:00', '10:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-02 06:23:54', '2024-10-02 06:23:54'),
	(40, 10, 6, '2024-10-10', '11:00:00', '12:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-02 06:24:40', '2024-10-02 06:24:40'),
	(41, 10, 5, '2024-10-11', '10:00:00', '11:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-02 06:26:04', '2024-10-02 06:26:04'),
	(42, 10, 6, '2024-10-16', '12:00:00', '13:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-02 07:59:49', '2024-10-02 07:59:49'),
	(43, 10, 5, '2024-10-09', '11:00:00', '12:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-02 08:00:32', '2024-10-02 08:00:32'),
	(44, 8, 5, '2024-10-11', '10:00:00', '11:00:00', 'pendiente', 50.00, 'pendiente', NULL, '2024-10-02 08:06:51', '2024-10-02 08:06:51'),
	(45, 10, 6, '2024-10-17', '10:00:00', '11:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-02 08:07:18', '2024-10-02 08:07:18'),
	(46, 10, 6, '2024-10-17', '10:00:00', '11:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-02 08:08:48', '2024-10-02 08:08:48'),
	(47, 10, 5, '2024-10-06', '10:00:00', '11:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-02 08:12:34', '2024-10-02 08:12:34'),
	(48, 10, 6, '2024-10-16', '10:00:00', '11:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-02 08:13:51', '2024-10-02 08:13:51'),
	(49, 10, 6, '2024-10-22', '10:00:00', '11:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-02 08:15:23', '2024-10-02 08:15:23'),
	(50, 10, 5, '2024-10-16', '10:00:00', '11:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-02 08:16:44', '2024-10-02 08:16:44'),
	(51, 10, 5, '2024-10-17', '10:00:00', '11:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-02 08:18:26', '2024-10-02 08:18:26'),
	(52, 10, 5, '2024-12-20', '13:00:00', '14:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-03 06:31:52', '2024-10-03 06:31:52'),
	(53, 10, 6, '2024-10-31', '12:00:00', '13:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-03 06:33:05', '2024-10-03 06:33:05'),
	(54, 10, 6, '2024-10-14', '10:00:00', '11:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-03 06:33:43', '2024-10-03 06:33:43'),
	(55, 10, 6, '2024-11-08', '12:00:00', '13:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-03 06:37:31', '2024-10-03 06:37:31'),
	(56, 10, 6, '2024-10-17', '10:00:00', '11:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-03 06:38:29', '2024-10-03 06:38:29'),
	(57, 10, 5, '2024-10-17', '09:00:00', '10:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-03 06:41:37', '2024-10-03 06:41:37'),
	(58, 10, 5, '2024-10-24', '11:00:00', '12:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-03 06:42:40', '2024-10-03 06:42:40'),
	(59, 10, 5, '2024-10-16', '11:00:00', '12:00:00', 'pendiente', 50.00, 'pendiente', NULL, '2024-10-03 06:45:25', '2024-10-03 06:45:25'),
	(60, 10, 5, '2024-10-24', '10:00:00', '11:00:00', 'pendiente', 50.00, 'pendiente', NULL, '2024-10-03 06:47:00', '2024-10-03 06:47:00'),
	(61, 10, 6, '2024-10-24', '11:00:00', '12:00:00', 'confirmada', 50.00, 'pagado', NULL, '2024-10-08 21:08:26', '2024-10-08 21:08:26');

-- Volcando estructura para tabla curso_reserva.reservations_details
CREATE TABLE IF NOT EXISTS `reservations_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint unsigned NOT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payer_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payer_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `response_json` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reservations_details_reservation_id_foreign` (`reservation_id`),
  CONSTRAINT `reservations_details_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla curso_reserva.reservations_details: ~19 rows (aproximadamente)
DELETE FROM `reservations_details`;
INSERT INTO `reservations_details` (`id`, `reservation_id`, `transaction_id`, `payer_id`, `payer_email`, `payment_status`, `amount`, `response_json`, `created_at`, `updated_at`) VALUES
	(1, 1, '123456789', 'PAYER123', 'payer@example.com', 'COMPLETED', 50.00, '{"status":"success"}', '2024-10-02 06:20:49', '2024-10-02 06:20:49'),
	(2, 1, 'TX123', 'PAYER123', 'payer@example.com', 'COMPLETED', 50.00, '{"status":"success"}', '2024-10-02 06:23:54', '2024-10-02 06:23:54'),
	(3, 40, '1C264358B1668554X', '26J7GUD34S5KW', 'sb-456oi22303733@personal.example.com', 'COMPLETED', 50.00, '{"id":"1C264358B1668554X","intent":"CAPTURE","status":"COMPLETED","purchase_units":[{"reference_id":"default","amount":{"currency_code":"USD","value":"50.00"},"payee":{"email_address":"sb-yax3d22410815@business.example.com","merchant_id":"MWFTCD48KWR2U"},"shipping":{"name":{"full_name":"AnderCode Paypal"},"address":{"address_line_1":"Free Trade Zone","admin_area_2":"Lima","admin_area_1":"Lima","postal_code":"07001","country_code":"PE"}},"payments":{"captures":[{"id":"17M6745138696920B","status":"COMPLETED","amount":{"currency_code":"USD","value":"50.00"},"final_capture":true,"seller_protection":{"status":"ELIGIBLE","dispute_categories":["ITEM_NOT_RECEIVED","UNAUTHORIZED_TRANSACTION"]},"create_time":"2024-10-02T01:24:39Z","update_time":"2024-10-02T01:24:39Z"}]}}],"payer":{"name":{"given_name":"AnderCode","surname":"Paypal"},"email_address":"sb-456oi22303733@personal.example.com","payer_id":"26J7GUD34S5KW","address":{"country_code":"PE"}},"create_time":"2024-10-02T01:24:33Z","update_time":"2024-10-02T01:24:39Z","links":[{"href":"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/1C264358B1668554X","rel":"self","method":"GET"}]}', '2024-10-02 06:24:40', '2024-10-02 06:24:40'),
	(4, 41, '3LB48071W4137093B', '26J7GUD34S5KW', 'sb-456oi22303733@personal.example.com', 'COMPLETED', 50.00, '{"id":"3LB48071W4137093B","intent":"CAPTURE","status":"COMPLETED","purchase_units":[{"reference_id":"default","amount":{"currency_code":"USD","value":"50.00"},"payee":{"email_address":"sb-yax3d22410815@business.example.com","merchant_id":"MWFTCD48KWR2U"},"shipping":{"name":{"full_name":"AnderCode Paypal"},"address":{"address_line_1":"Free Trade Zone","admin_area_2":"Lima","admin_area_1":"Lima","postal_code":"07001","country_code":"PE"}},"payments":{"captures":[{"id":"7H092066JV283093N","status":"COMPLETED","amount":{"currency_code":"USD","value":"50.00"},"final_capture":true,"seller_protection":{"status":"ELIGIBLE","dispute_categories":["ITEM_NOT_RECEIVED","UNAUTHORIZED_TRANSACTION"]},"create_time":"2024-10-02T01:26:03Z","update_time":"2024-10-02T01:26:03Z"}]}}],"payer":{"name":{"given_name":"AnderCode","surname":"Paypal"},"email_address":"sb-456oi22303733@personal.example.com","payer_id":"26J7GUD34S5KW","address":{"country_code":"PE"}},"create_time":"2024-10-02T01:25:53Z","update_time":"2024-10-02T01:26:03Z","links":[{"href":"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/3LB48071W4137093B","rel":"self","method":"GET"}]}', '2024-10-02 06:26:04', '2024-10-02 06:26:04'),
	(5, 42, '3HB06457DP745161Y', '26J7GUD34S5KW', 'sb-456oi22303733@personal.example.com', 'COMPLETED', 50.00, '{"id":"3HB06457DP745161Y","intent":"CAPTURE","status":"COMPLETED","purchase_units":[{"reference_id":"default","amount":{"currency_code":"USD","value":"50.00"},"payee":{"email_address":"sb-yax3d22410815@business.example.com","merchant_id":"MWFTCD48KWR2U"},"shipping":{"name":{"full_name":"AnderCode Paypal"},"address":{"address_line_1":"Free Trade Zone","admin_area_2":"Lima","admin_area_1":"Lima","postal_code":"07001","country_code":"PE"}},"payments":{"captures":[{"id":"3X098282EY091405F","status":"COMPLETED","amount":{"currency_code":"USD","value":"50.00"},"final_capture":true,"seller_protection":{"status":"ELIGIBLE","dispute_categories":["ITEM_NOT_RECEIVED","UNAUTHORIZED_TRANSACTION"]},"create_time":"2024-10-02T02:59:48Z","update_time":"2024-10-02T02:59:48Z"}]}}],"payer":{"name":{"given_name":"AnderCode","surname":"Paypal"},"email_address":"sb-456oi22303733@personal.example.com","payer_id":"26J7GUD34S5KW","address":{"country_code":"PE"}},"create_time":"2024-10-02T02:59:34Z","update_time":"2024-10-02T02:59:48Z","links":[{"href":"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/3HB06457DP745161Y","rel":"self","method":"GET"}]}', '2024-10-02 07:59:49', '2024-10-02 07:59:49'),
	(6, 43, '4L524020VC341072A', '26J7GUD34S5KW', 'sb-456oi22303733@personal.example.com', 'COMPLETED', 50.00, '{"id":"4L524020VC341072A","intent":"CAPTURE","status":"COMPLETED","purchase_units":[{"reference_id":"default","amount":{"currency_code":"USD","value":"50.00"},"payee":{"email_address":"sb-yax3d22410815@business.example.com","merchant_id":"MWFTCD48KWR2U"},"shipping":{"name":{"full_name":"AnderCode Paypal"},"address":{"address_line_1":"Free Trade Zone","admin_area_2":"Lima","admin_area_1":"Lima","postal_code":"07001","country_code":"PE"}},"payments":{"captures":[{"id":"9E838600DS6612749","status":"COMPLETED","amount":{"currency_code":"USD","value":"50.00"},"final_capture":true,"seller_protection":{"status":"ELIGIBLE","dispute_categories":["ITEM_NOT_RECEIVED","UNAUTHORIZED_TRANSACTION"]},"create_time":"2024-10-02T03:00:31Z","update_time":"2024-10-02T03:00:31Z"}]}}],"payer":{"name":{"given_name":"AnderCode","surname":"Paypal"},"email_address":"sb-456oi22303733@personal.example.com","payer_id":"26J7GUD34S5KW","address":{"country_code":"PE"}},"create_time":"2024-10-02T03:00:24Z","update_time":"2024-10-02T03:00:31Z","links":[{"href":"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/4L524020VC341072A","rel":"self","method":"GET"}]}', '2024-10-02 08:00:32', '2024-10-02 08:00:32'),
	(7, 45, '1X798956PY829790D', '26J7GUD34S5KW', 'sb-456oi22303733@personal.example.com', 'COMPLETED', 50.00, '{"id":"1X798956PY829790D","intent":"CAPTURE","status":"COMPLETED","purchase_units":[{"reference_id":"default","amount":{"currency_code":"USD","value":"50.00"},"payee":{"email_address":"sb-yax3d22410815@business.example.com","merchant_id":"MWFTCD48KWR2U"},"shipping":{"name":{"full_name":"AnderCode Paypal"},"address":{"address_line_1":"Free Trade Zone","admin_area_2":"Lima","admin_area_1":"Lima","postal_code":"07001","country_code":"PE"}},"payments":{"captures":[{"id":"2VU8110964746405E","status":"COMPLETED","amount":{"currency_code":"USD","value":"50.00"},"final_capture":true,"seller_protection":{"status":"ELIGIBLE","dispute_categories":["ITEM_NOT_RECEIVED","UNAUTHORIZED_TRANSACTION"]},"create_time":"2024-10-02T03:07:18Z","update_time":"2024-10-02T03:07:18Z"}]}}],"payer":{"name":{"given_name":"AnderCode","surname":"Paypal"},"email_address":"sb-456oi22303733@personal.example.com","payer_id":"26J7GUD34S5KW","address":{"country_code":"PE"}},"create_time":"2024-10-02T03:07:11Z","update_time":"2024-10-02T03:07:18Z","links":[{"href":"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/1X798956PY829790D","rel":"self","method":"GET"}]}', '2024-10-02 08:07:18', '2024-10-02 08:07:18'),
	(8, 46, '21K49030JS923425W', '26J7GUD34S5KW', 'sb-456oi22303733@personal.example.com', 'COMPLETED', 50.00, '{"id":"21K49030JS923425W","intent":"CAPTURE","status":"COMPLETED","purchase_units":[{"reference_id":"default","amount":{"currency_code":"USD","value":"50.00"},"payee":{"email_address":"sb-yax3d22410815@business.example.com","merchant_id":"MWFTCD48KWR2U"},"shipping":{"name":{"full_name":"AnderCode Paypal"},"address":{"address_line_1":"Free Trade Zone","admin_area_2":"Lima","admin_area_1":"Lima","postal_code":"07001","country_code":"PE"}},"payments":{"captures":[{"id":"46E35349A53747918","status":"COMPLETED","amount":{"currency_code":"USD","value":"50.00"},"final_capture":true,"seller_protection":{"status":"ELIGIBLE","dispute_categories":["ITEM_NOT_RECEIVED","UNAUTHORIZED_TRANSACTION"]},"create_time":"2024-10-02T03:08:48Z","update_time":"2024-10-02T03:08:48Z"}]}}],"payer":{"name":{"given_name":"AnderCode","surname":"Paypal"},"email_address":"sb-456oi22303733@personal.example.com","payer_id":"26J7GUD34S5KW","address":{"country_code":"PE"}},"create_time":"2024-10-02T03:08:41Z","update_time":"2024-10-02T03:08:48Z","links":[{"href":"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/21K49030JS923425W","rel":"self","method":"GET"}]}', '2024-10-02 08:08:48', '2024-10-02 08:08:48'),
	(9, 47, '1KL38429XS871302L', '26J7GUD34S5KW', 'sb-456oi22303733@personal.example.com', 'COMPLETED', 50.00, '{"id":"1KL38429XS871302L","intent":"CAPTURE","status":"COMPLETED","purchase_units":[{"reference_id":"default","amount":{"currency_code":"USD","value":"50.00"},"payee":{"email_address":"sb-yax3d22410815@business.example.com","merchant_id":"MWFTCD48KWR2U"},"shipping":{"name":{"full_name":"AnderCode Paypal"},"address":{"address_line_1":"Free Trade Zone","admin_area_2":"Lima","admin_area_1":"Lima","postal_code":"07001","country_code":"PE"}},"payments":{"captures":[{"id":"04P925139W919421V","status":"COMPLETED","amount":{"currency_code":"USD","value":"50.00"},"final_capture":true,"seller_protection":{"status":"ELIGIBLE","dispute_categories":["ITEM_NOT_RECEIVED","UNAUTHORIZED_TRANSACTION"]},"create_time":"2024-10-02T03:12:34Z","update_time":"2024-10-02T03:12:34Z"}]}}],"payer":{"name":{"given_name":"AnderCode","surname":"Paypal"},"email_address":"sb-456oi22303733@personal.example.com","payer_id":"26J7GUD34S5KW","address":{"country_code":"PE"}},"create_time":"2024-10-02T03:12:27Z","update_time":"2024-10-02T03:12:34Z","links":[{"href":"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/1KL38429XS871302L","rel":"self","method":"GET"}]}', '2024-10-02 08:12:34', '2024-10-02 08:12:34'),
	(10, 48, '3X2365463Y314054N', '26J7GUD34S5KW', 'sb-456oi22303733@personal.example.com', 'COMPLETED', 50.00, '{"id":"3X2365463Y314054N","intent":"CAPTURE","status":"COMPLETED","purchase_units":[{"reference_id":"default","amount":{"currency_code":"USD","value":"50.00"},"payee":{"email_address":"sb-yax3d22410815@business.example.com","merchant_id":"MWFTCD48KWR2U"},"shipping":{"name":{"full_name":"AnderCode Paypal"},"address":{"address_line_1":"Free Trade Zone","admin_area_2":"Lima","admin_area_1":"Lima","postal_code":"07001","country_code":"PE"}},"payments":{"captures":[{"id":"60D38105CW3710540","status":"COMPLETED","amount":{"currency_code":"USD","value":"50.00"},"final_capture":true,"seller_protection":{"status":"ELIGIBLE","dispute_categories":["ITEM_NOT_RECEIVED","UNAUTHORIZED_TRANSACTION"]},"create_time":"2024-10-02T03:13:50Z","update_time":"2024-10-02T03:13:50Z"}]}}],"payer":{"name":{"given_name":"AnderCode","surname":"Paypal"},"email_address":"sb-456oi22303733@personal.example.com","payer_id":"26J7GUD34S5KW","address":{"country_code":"PE"}},"create_time":"2024-10-02T03:13:44Z","update_time":"2024-10-02T03:13:50Z","links":[{"href":"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/3X2365463Y314054N","rel":"self","method":"GET"}]}', '2024-10-02 08:13:51', '2024-10-02 08:13:51'),
	(11, 49, '3VR251610D945724G', '26J7GUD34S5KW', 'sb-456oi22303733@personal.example.com', 'COMPLETED', 50.00, '{"id":"3VR251610D945724G","intent":"CAPTURE","status":"COMPLETED","purchase_units":[{"reference_id":"default","amount":{"currency_code":"USD","value":"50.00"},"payee":{"email_address":"sb-yax3d22410815@business.example.com","merchant_id":"MWFTCD48KWR2U"},"shipping":{"name":{"full_name":"AnderCode Paypal"},"address":{"address_line_1":"Free Trade Zone","admin_area_2":"Lima","admin_area_1":"Lima","postal_code":"07001","country_code":"PE"}},"payments":{"captures":[{"id":"73391632EY5139224","status":"COMPLETED","amount":{"currency_code":"USD","value":"50.00"},"final_capture":true,"seller_protection":{"status":"ELIGIBLE","dispute_categories":["ITEM_NOT_RECEIVED","UNAUTHORIZED_TRANSACTION"]},"create_time":"2024-10-02T03:15:22Z","update_time":"2024-10-02T03:15:22Z"}]}}],"payer":{"name":{"given_name":"AnderCode","surname":"Paypal"},"email_address":"sb-456oi22303733@personal.example.com","payer_id":"26J7GUD34S5KW","address":{"country_code":"PE"}},"create_time":"2024-10-02T03:15:16Z","update_time":"2024-10-02T03:15:22Z","links":[{"href":"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/3VR251610D945724G","rel":"self","method":"GET"}]}', '2024-10-02 08:15:23', '2024-10-02 08:15:23'),
	(12, 50, '8H900239RH466203X', '26J7GUD34S5KW', 'sb-456oi22303733@personal.example.com', 'COMPLETED', 50.00, '{"id":"8H900239RH466203X","intent":"CAPTURE","status":"COMPLETED","purchase_units":[{"reference_id":"default","amount":{"currency_code":"USD","value":"50.00"},"payee":{"email_address":"sb-yax3d22410815@business.example.com","merchant_id":"MWFTCD48KWR2U"},"shipping":{"name":{"full_name":"AnderCode Paypal"},"address":{"address_line_1":"Free Trade Zone","admin_area_2":"Lima","admin_area_1":"Lima","postal_code":"07001","country_code":"PE"}},"payments":{"captures":[{"id":"6U8274008D354291X","status":"COMPLETED","amount":{"currency_code":"USD","value":"50.00"},"final_capture":true,"seller_protection":{"status":"ELIGIBLE","dispute_categories":["ITEM_NOT_RECEIVED","UNAUTHORIZED_TRANSACTION"]},"create_time":"2024-10-02T03:16:43Z","update_time":"2024-10-02T03:16:43Z"}]}}],"payer":{"name":{"given_name":"AnderCode","surname":"Paypal"},"email_address":"sb-456oi22303733@personal.example.com","payer_id":"26J7GUD34S5KW","address":{"country_code":"PE"}},"create_time":"2024-10-02T03:16:37Z","update_time":"2024-10-02T03:16:43Z","links":[{"href":"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/8H900239RH466203X","rel":"self","method":"GET"}]}', '2024-10-02 08:16:44', '2024-10-02 08:16:44'),
	(13, 51, '6UP52884L7455023L', '26J7GUD34S5KW', 'sb-456oi22303733@personal.example.com', 'COMPLETED', 50.00, '{"id":"6UP52884L7455023L","intent":"CAPTURE","status":"COMPLETED","purchase_units":[{"reference_id":"default","amount":{"currency_code":"USD","value":"50.00"},"payee":{"email_address":"sb-yax3d22410815@business.example.com","merchant_id":"MWFTCD48KWR2U"},"shipping":{"name":{"full_name":"AnderCode Paypal"},"address":{"address_line_1":"Free Trade Zone","admin_area_2":"Lima","admin_area_1":"Lima","postal_code":"07001","country_code":"PE"}},"payments":{"captures":[{"id":"661662625E853160U","status":"COMPLETED","amount":{"currency_code":"USD","value":"50.00"},"final_capture":true,"seller_protection":{"status":"ELIGIBLE","dispute_categories":["ITEM_NOT_RECEIVED","UNAUTHORIZED_TRANSACTION"]},"create_time":"2024-10-02T03:18:26Z","update_time":"2024-10-02T03:18:26Z"}]}}],"payer":{"name":{"given_name":"AnderCode","surname":"Paypal"},"email_address":"sb-456oi22303733@personal.example.com","payer_id":"26J7GUD34S5KW","address":{"country_code":"PE"}},"create_time":"2024-10-02T03:18:19Z","update_time":"2024-10-02T03:18:26Z","links":[{"href":"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/6UP52884L7455023L","rel":"self","method":"GET"}]}', '2024-10-02 08:18:26', '2024-10-02 08:18:26'),
	(14, 52, '26E47090BU938140A', '26J7GUD34S5KW', 'sb-456oi22303733@personal.example.com', 'COMPLETED', 50.00, '{"id":"26E47090BU938140A","intent":"CAPTURE","status":"COMPLETED","purchase_units":[{"reference_id":"default","amount":{"currency_code":"USD","value":"50.00"},"payee":{"email_address":"sb-yax3d22410815@business.example.com","merchant_id":"MWFTCD48KWR2U"},"shipping":{"name":{"full_name":"AnderCode Paypal"},"address":{"address_line_1":"Free Trade Zone","admin_area_2":"Lima","admin_area_1":"Lima","postal_code":"07001","country_code":"PE"}},"payments":{"captures":[{"id":"4XK93350V1290104A","status":"COMPLETED","amount":{"currency_code":"USD","value":"50.00"},"final_capture":true,"seller_protection":{"status":"ELIGIBLE","dispute_categories":["ITEM_NOT_RECEIVED","UNAUTHORIZED_TRANSACTION"]},"create_time":"2024-10-03T01:31:51Z","update_time":"2024-10-03T01:31:51Z"}]}}],"payer":{"name":{"given_name":"AnderCode","surname":"Paypal"},"email_address":"sb-456oi22303733@personal.example.com","payer_id":"26J7GUD34S5KW","address":{"country_code":"PE"}},"create_time":"2024-10-03T01:31:34Z","update_time":"2024-10-03T01:31:51Z","links":[{"href":"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/26E47090BU938140A","rel":"self","method":"GET"}]}', '2024-10-03 06:31:52', '2024-10-03 06:31:52'),
	(15, 53, '3VW87410LX052382F', '26J7GUD34S5KW', 'sb-456oi22303733@personal.example.com', 'COMPLETED', 50.00, '{"id":"3VW87410LX052382F","intent":"CAPTURE","status":"COMPLETED","purchase_units":[{"reference_id":"default","amount":{"currency_code":"USD","value":"50.00"},"payee":{"email_address":"sb-yax3d22410815@business.example.com","merchant_id":"MWFTCD48KWR2U"},"shipping":{"name":{"full_name":"AnderCode Paypal"},"address":{"address_line_1":"Free Trade Zone","admin_area_2":"Lima","admin_area_1":"Lima","postal_code":"07001","country_code":"PE"}},"payments":{"captures":[{"id":"81Y09496YR306935X","status":"COMPLETED","amount":{"currency_code":"USD","value":"50.00"},"final_capture":true,"seller_protection":{"status":"ELIGIBLE","dispute_categories":["ITEM_NOT_RECEIVED","UNAUTHORIZED_TRANSACTION"]},"create_time":"2024-10-03T01:33:04Z","update_time":"2024-10-03T01:33:04Z"}]}}],"payer":{"name":{"given_name":"AnderCode","surname":"Paypal"},"email_address":"sb-456oi22303733@personal.example.com","payer_id":"26J7GUD34S5KW","address":{"country_code":"PE"}},"create_time":"2024-10-03T01:32:55Z","update_time":"2024-10-03T01:33:04Z","links":[{"href":"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/3VW87410LX052382F","rel":"self","method":"GET"}]}', '2024-10-03 06:33:05', '2024-10-03 06:33:05'),
	(16, 54, '4MB48144HE8564717', '26J7GUD34S5KW', 'sb-456oi22303733@personal.example.com', 'COMPLETED', 50.00, '{"id":"4MB48144HE8564717","intent":"CAPTURE","status":"COMPLETED","purchase_units":[{"reference_id":"default","amount":{"currency_code":"USD","value":"50.00"},"payee":{"email_address":"sb-yax3d22410815@business.example.com","merchant_id":"MWFTCD48KWR2U"},"shipping":{"name":{"full_name":"AnderCode Paypal"},"address":{"address_line_1":"Free Trade Zone","admin_area_2":"Lima","admin_area_1":"Lima","postal_code":"07001","country_code":"PE"}},"payments":{"captures":[{"id":"7RF10907AW729544E","status":"COMPLETED","amount":{"currency_code":"USD","value":"50.00"},"final_capture":true,"seller_protection":{"status":"ELIGIBLE","dispute_categories":["ITEM_NOT_RECEIVED","UNAUTHORIZED_TRANSACTION"]},"create_time":"2024-10-03T01:33:42Z","update_time":"2024-10-03T01:33:42Z"}]}}],"payer":{"name":{"given_name":"AnderCode","surname":"Paypal"},"email_address":"sb-456oi22303733@personal.example.com","payer_id":"26J7GUD34S5KW","address":{"country_code":"PE"}},"create_time":"2024-10-03T01:33:35Z","update_time":"2024-10-03T01:33:42Z","links":[{"href":"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/4MB48144HE8564717","rel":"self","method":"GET"}]}', '2024-10-03 06:33:43', '2024-10-03 06:33:43'),
	(17, 55, '1NY49912GJ530361A', '26J7GUD34S5KW', 'sb-456oi22303733@personal.example.com', 'COMPLETED', 50.00, '{"id":"1NY49912GJ530361A","intent":"CAPTURE","status":"COMPLETED","purchase_units":[{"reference_id":"default","amount":{"currency_code":"USD","value":"50.00"},"payee":{"email_address":"sb-yax3d22410815@business.example.com","merchant_id":"MWFTCD48KWR2U"},"shipping":{"name":{"full_name":"AnderCode Paypal"},"address":{"address_line_1":"Free Trade Zone","admin_area_2":"Lima","admin_area_1":"Lima","postal_code":"07001","country_code":"PE"}},"payments":{"captures":[{"id":"8DX59445CV890772E","status":"COMPLETED","amount":{"currency_code":"USD","value":"50.00"},"final_capture":true,"seller_protection":{"status":"ELIGIBLE","dispute_categories":["ITEM_NOT_RECEIVED","UNAUTHORIZED_TRANSACTION"]},"create_time":"2024-10-03T01:37:30Z","update_time":"2024-10-03T01:37:30Z"}]}}],"payer":{"name":{"given_name":"AnderCode","surname":"Paypal"},"email_address":"sb-456oi22303733@personal.example.com","payer_id":"26J7GUD34S5KW","address":{"country_code":"PE"}},"create_time":"2024-10-03T01:37:24Z","update_time":"2024-10-03T01:37:30Z","links":[{"href":"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/1NY49912GJ530361A","rel":"self","method":"GET"}]}', '2024-10-03 06:37:31', '2024-10-03 06:37:31'),
	(18, 56, '79T99415WX865915E', '26J7GUD34S5KW', 'sb-456oi22303733@personal.example.com', 'COMPLETED', 50.00, '{"id":"79T99415WX865915E","intent":"CAPTURE","status":"COMPLETED","purchase_units":[{"reference_id":"default","amount":{"currency_code":"USD","value":"50.00"},"payee":{"email_address":"sb-yax3d22410815@business.example.com","merchant_id":"MWFTCD48KWR2U"},"shipping":{"name":{"full_name":"AnderCode Paypal"},"address":{"address_line_1":"Free Trade Zone","admin_area_2":"Lima","admin_area_1":"Lima","postal_code":"07001","country_code":"PE"}},"payments":{"captures":[{"id":"247104076W565762P","status":"COMPLETED","amount":{"currency_code":"USD","value":"50.00"},"final_capture":true,"seller_protection":{"status":"ELIGIBLE","dispute_categories":["ITEM_NOT_RECEIVED","UNAUTHORIZED_TRANSACTION"]},"create_time":"2024-10-03T01:38:28Z","update_time":"2024-10-03T01:38:28Z"}]}}],"payer":{"name":{"given_name":"AnderCode","surname":"Paypal"},"email_address":"sb-456oi22303733@personal.example.com","payer_id":"26J7GUD34S5KW","address":{"country_code":"PE"}},"create_time":"2024-10-03T01:38:22Z","update_time":"2024-10-03T01:38:28Z","links":[{"href":"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/79T99415WX865915E","rel":"self","method":"GET"}]}', '2024-10-03 06:38:29', '2024-10-03 06:38:29'),
	(19, 57, '9GE75410Y6557373U', '26J7GUD34S5KW', 'sb-456oi22303733@personal.example.com', 'COMPLETED', 50.00, '{"id":"9GE75410Y6557373U","intent":"CAPTURE","status":"COMPLETED","purchase_units":[{"reference_id":"default","amount":{"currency_code":"USD","value":"50.00"},"payee":{"email_address":"sb-yax3d22410815@business.example.com","merchant_id":"MWFTCD48KWR2U"},"shipping":{"name":{"full_name":"AnderCode Paypal"},"address":{"address_line_1":"Free Trade Zone","admin_area_2":"Lima","admin_area_1":"Lima","postal_code":"07001","country_code":"PE"}},"payments":{"captures":[{"id":"22K62616JK262261B","status":"COMPLETED","amount":{"currency_code":"USD","value":"50.00"},"final_capture":true,"seller_protection":{"status":"ELIGIBLE","dispute_categories":["ITEM_NOT_RECEIVED","UNAUTHORIZED_TRANSACTION"]},"create_time":"2024-10-03T01:41:36Z","update_time":"2024-10-03T01:41:36Z"}]}}],"payer":{"name":{"given_name":"AnderCode","surname":"Paypal"},"email_address":"sb-456oi22303733@personal.example.com","payer_id":"26J7GUD34S5KW","address":{"country_code":"PE"}},"create_time":"2024-10-03T01:41:30Z","update_time":"2024-10-03T01:41:36Z","links":[{"href":"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/9GE75410Y6557373U","rel":"self","method":"GET"}]}', '2024-10-03 06:41:37', '2024-10-03 06:41:37'),
	(20, 58, '88B1435065910270B', '26J7GUD34S5KW', 'sb-456oi22303733@personal.example.com', 'COMPLETED', 50.00, '{"id":"88B1435065910270B","intent":"CAPTURE","status":"COMPLETED","purchase_units":[{"reference_id":"default","amount":{"currency_code":"USD","value":"50.00"},"payee":{"email_address":"sb-yax3d22410815@business.example.com","merchant_id":"MWFTCD48KWR2U"},"shipping":{"name":{"full_name":"AnderCode Paypal"},"address":{"address_line_1":"Free Trade Zone","admin_area_2":"Lima","admin_area_1":"Lima","postal_code":"07001","country_code":"PE"}},"payments":{"captures":[{"id":"23P911730C134134K","status":"COMPLETED","amount":{"currency_code":"USD","value":"50.00"},"final_capture":true,"seller_protection":{"status":"ELIGIBLE","dispute_categories":["ITEM_NOT_RECEIVED","UNAUTHORIZED_TRANSACTION"]},"create_time":"2024-10-03T01:42:39Z","update_time":"2024-10-03T01:42:39Z"}]}}],"payer":{"name":{"given_name":"AnderCode","surname":"Paypal"},"email_address":"sb-456oi22303733@personal.example.com","payer_id":"26J7GUD34S5KW","address":{"country_code":"PE"}},"create_time":"2024-10-03T01:42:32Z","update_time":"2024-10-03T01:42:39Z","links":[{"href":"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/88B1435065910270B","rel":"self","method":"GET"}]}', '2024-10-03 06:42:40', '2024-10-03 06:42:40'),
	(21, 61, '2FH796038H6820932', '26J7GUD34S5KW', 'sb-456oi22303733@personal.example.com', 'COMPLETED', 50.00, '{"id":"2FH796038H6820932","intent":"CAPTURE","status":"COMPLETED","purchase_units":[{"reference_id":"default","amount":{"currency_code":"USD","value":"50.00"},"payee":{"email_address":"sb-yax3d22410815@business.example.com","merchant_id":"MWFTCD48KWR2U"},"shipping":{"name":{"full_name":"AnderCode Paypal"},"address":{"address_line_1":"Free Trade Zone","admin_area_2":"Lima","admin_area_1":"Lima","postal_code":"07001","country_code":"PE"}},"payments":{"captures":[{"id":"9P1772900Y646994A","status":"COMPLETED","amount":{"currency_code":"USD","value":"50.00"},"final_capture":true,"seller_protection":{"status":"ELIGIBLE","dispute_categories":["ITEM_NOT_RECEIVED","UNAUTHORIZED_TRANSACTION"]},"create_time":"2024-10-08T16:08:25Z","update_time":"2024-10-08T16:08:25Z"}]}}],"payer":{"name":{"given_name":"AnderCode","surname":"Paypal"},"email_address":"sb-456oi22303733@personal.example.com","payer_id":"26J7GUD34S5KW","address":{"country_code":"PE"}},"create_time":"2024-10-08T16:08:11Z","update_time":"2024-10-08T16:08:25Z","links":[{"href":"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/2FH796038H6820932","rel":"self","method":"GET"}]}', '2024-10-08 21:08:26', '2024-10-08 21:08:26');

-- Volcando estructura para tabla curso_reserva.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla curso_reserva.roles: ~2 rows (aproximadamente)
DELETE FROM `roles`;
INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES
	(1, 'Administrador', NULL, NULL),
	(2, 'Asesor', NULL, NULL),
	(3, 'Usuario', NULL, NULL);

-- Volcando estructura para tabla curso_reserva.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombres` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `teléfono` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol_id` bigint unsigned NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_rol_id_foreign` (`rol_id`),
  CONSTRAINT `users_rol_id_foreign` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla curso_reserva.users: ~16 rows (aproximadamente)
DELETE FROM `users`;
INSERT INTO `users` (`id`, `nombres`, `apellidos`, `teléfono`, `rol_id`, `email`, `foto`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(4, 'Admin', 'Principal', '999999999', 1, 'admin@example.com', NULL, NULL, '$2y$12$LrMUMxR84Z989VB9fopkA.GPBKm77d6d5AwQrgZz4lJmf4Ayd5yQC', NULL, '2024-09-19 04:34:04', '2024-09-19 04:34:04', NULL),
	(5, 'Jarvis', 'Hane', '+1 (828) 732', 2, 'asesor@example.net', NULL, '2024-09-19 04:34:05', '$2y$12$jJ2YLRhRDQ.cNaUeW8qJMuZT2EoBABy2yutVMXzeytf2K3/tRhcgq', 'EgY8Brclxcp00KMqbScFs7dSQb7XFbKlEwotGI9rBpkyNBC1SaDYLeruYFHX', '2024-09-19 04:34:05', '2024-10-01 04:28:12', NULL),
	(6, 'Elroy', 'Price', '+1 (458) 598-3915', 2, 'schneider.madeline@example.org', NULL, '2024-09-19 04:34:05', '$2y$12$Y3GzCEHT6RDgbUdXvUXD4OpLNP4AxzkXio.cPvNd3oxE7QeODQySi', 'KpTqcRQe7R', '2024-09-19 04:34:05', '2024-09-19 04:34:05', NULL),
	(7, 'Fleta', 'Heidenreich', '959.421.9729', 2, 'kerluke.ludie@example.com', NULL, '2024-09-19 04:34:05', '$2y$12$.BGj7IN7GaPtQqIXCGIBneF7mFgsYV4PQjeya0UC3T1a/P9DpGiG6', 'jM1WCXH8tn', '2024-09-19 04:34:05', '2024-09-19 04:34:05', NULL),
	(8, 'Lexi', 'Littel', '832-386-1984', 3, 'tparisian@example.org', NULL, '2024-09-19 04:34:05', '$2y$12$7KdfCH4j82TPPWTzMRj/4eQO8DsS9o3k3ss.UxhC9b2DqkELzc9hS', 'hf6CP1MRhz', '2024-09-19 04:34:07', '2024-09-19 04:34:07', NULL),
	(9, 'Doris', 'Abernathy', '1-458-751-1978', 3, 'dimitri.schmeler@example.org', NULL, '2024-09-19 04:34:05', '$2y$12$V8VVSsspSLaO72qKYEmbqupbvIK/jAxe5IfeuyDelXBESDc7.cmau', 'OdVezs0B3E', '2024-09-19 04:34:07', '2024-09-19 04:34:07', NULL),
	(10, 'Cliente', 'Usuario', '51981233834', 3, 'davis_anderson_87@hotmail.com', NULL, '2024-09-19 04:34:06', '$2y$12$w/O/I3eMbGZ7X7bTIZY0Pu3xpyxhb8vHqJRwQGJ7IGVvOVyuD2Wsy', 'JlHIb7INmzYx5MbpoxPGpSrNCtcsDQbTb71QuZHbGoqJinudpDqfMH9QBQ7I', '2024-09-19 04:34:07', '2024-10-01 04:44:34', NULL),
	(11, 'Reva', 'Hermiston', '801.260.2002', 3, 'april.lubowitz@example.com', NULL, '2024-09-19 04:34:06', '$2y$12$CEnUeUSW0o3IwAsdLnNvougpdse1too.j6aOOUW.PV6EfXYzmSlKO', 'bym3qxnaqC', '2024-09-19 04:34:07', '2024-09-19 04:34:07', NULL),
	(12, 'Elise', 'Murray', '+16077600095', 3, 'okey48@example.org', NULL, '2024-09-19 04:34:06', '$2y$12$6ePZcxVXzrwlLOo4upBp9e/1umvWCtX91r26JdXJGkNgUFjxMA/eK', '5KuAKVXaa4', '2024-09-19 04:34:07', '2024-09-19 04:34:07', NULL),
	(13, 'Delphine', 'Bins', '(701) 461-7148', 3, 'qgoodwin@example.com', NULL, '2024-09-19 04:34:06', '$2y$12$KzeBHurP8WJmJ1L0FTUQjelz9xcV.754Sl7fzfGnxGxsKxNhVgKXK', 'Fyq0sgGJIL', '2024-09-19 04:34:07', '2024-09-19 04:34:07', NULL),
	(14, 'Mossie', 'Mayer', '+1-508-323-9055', 3, 'delbert.dare@example.com', NULL, '2024-09-19 04:34:06', '$2y$12$RzJ8iWskO7vYwYtqs93.HuVDX0Ln9mM8jZvwIr4pHw8m93vOdaW.a', '5ioWyV0Dwh', '2024-09-19 04:34:07', '2024-09-19 04:34:07', NULL),
	(15, 'Britney', 'Hudson', '+1.660.845.2795', 3, 'brandyn.hammes@example.org', NULL, '2024-09-19 04:34:07', '$2y$12$rxBu0U74Au.vhtqB.fpBGOGaNnor.NDULF5gfDVC9VzYWwI7RvFHO', 'Fs856tX15t', '2024-09-19 04:34:07', '2024-09-19 04:34:07', NULL),
	(16, 'Claudine', 'Bernhard', '240-282-8033', 3, 'leilani11@example.com', NULL, '2024-09-19 04:34:07', '$2y$12$.C3T7Nnp.MRZqy0Jtr7Mgu4Delv.ueTJRk9GrWPNueQD2tmiXrKAS', 'pWaOz1G9Q8', '2024-09-19 04:34:07', '2024-09-19 04:34:07', NULL),
	(17, 'Vergie', 'Lehner', '843-318-5651', 3, 'trath@example.org', NULL, '2024-09-19 04:34:07', '$2y$12$dYcTP.7SE2.9rrX2CddexemMvOUngFnGZ8xHNcU2DLRKlkxpedQ72', 'SUj40cfMmL', '2024-09-19 04:34:07', '2024-09-19 04:34:07', NULL),
	(18, 'test2', 'test2', '12222', 3, 'test2@test.com', NULL, NULL, '$2y$12$Xw4VWAKxdCPwfhDnfUbLZOZp.tdP5mAIs6kfKJmnZ0AGOCORNnYX.', NULL, '2024-09-20 06:34:41', '2024-09-26 05:55:00', '2024-09-26 05:55:00');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
