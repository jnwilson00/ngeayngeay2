CREATE TABLE `nge_icl_string_status` (  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,  `rid` bigint(20) NOT NULL,  `string_translation_id` bigint(20) NOT NULL,  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,  `md5` varchar(32) COLLATE utf8mb4_unicode_520_ci NOT NULL,  PRIMARY KEY (`id`),  KEY `string_translation_id` (`string_translation_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40000 ALTER TABLE `nge_icl_string_status` DISABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;
/*!40000 ALTER TABLE `nge_icl_string_status` ENABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
