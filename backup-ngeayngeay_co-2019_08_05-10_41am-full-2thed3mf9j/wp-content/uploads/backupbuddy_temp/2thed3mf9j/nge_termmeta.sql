CREATE TABLE `nge_termmeta` (  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,  `term_id` bigint(20) unsigned NOT NULL DEFAULT '0',  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,  PRIMARY KEY (`meta_id`),  KEY `term_id` (`term_id`),  KEY `meta_key` (`meta_key`(191))) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40000 ALTER TABLE `nge_termmeta` DISABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;
/*!40000 ALTER TABLE `nge_termmeta` ENABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
