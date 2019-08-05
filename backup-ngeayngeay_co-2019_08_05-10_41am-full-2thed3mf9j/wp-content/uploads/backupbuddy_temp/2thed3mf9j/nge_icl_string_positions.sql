CREATE TABLE `nge_icl_string_positions` (  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,  `string_id` bigint(20) NOT NULL,  `kind` tinyint(4) DEFAULT NULL,  `position_in_page` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,  PRIMARY KEY (`id`),  KEY `string_id` (`string_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40000 ALTER TABLE `nge_icl_string_positions` DISABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;
/*!40000 ALTER TABLE `nge_icl_string_positions` ENABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
