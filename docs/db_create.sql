SET NAMES utf8;
SET time_zone = '+02:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

USE `ovh_invoice_scraper`;

SET NAMES utf8mb4;

CREATE TABLE `invoices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service` enum('OVH','SoYouStart','Kimsufi') NOT NULL,
  `original_id` varchar(32) NOT NULL,
  `filename` varchar(128) NOT NULL,
  `filepath` varchar(128) NOT NULL,
  `issued_at` datetime NOT NULL,
  `price_without_tax` float NOT NULL,
  `price_with_tax` float NOT NULL,
  `pdf_url` varchar(256) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `number` (`original_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;