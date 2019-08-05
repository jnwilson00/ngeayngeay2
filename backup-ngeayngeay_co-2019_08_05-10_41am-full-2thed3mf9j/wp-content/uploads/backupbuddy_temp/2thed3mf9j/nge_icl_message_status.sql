CREATE TABLE `nge_icl_message_status` (  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,  `rid` bigint(20) unsigned NOT NULL,  `object_id` bigint(20) unsigned NOT NULL,  `from_language` varchar(10) COLLATE utf8mb4_unicode_520_ci NOT NULL,  `to_language` varchar(10) COLLATE utf8mb4_unicode_520_ci NOT NULL,  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,  `md5` varchar(32) COLLATE utf8mb4_unicode_520_ci NOT NULL,  `object_type` varchar(64) COLLATE utf8mb4_unicode_520_ci NOT NULL,  `status` smallint(6) NOT NULL,  PRIMARY KEY (`id`),  UNIQUE KEY `rid` (`rid`),  KEY `object_id` (`object_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40000 ALTER TABLE `nge_icl_message_status` DISABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;
/*!40000 ALTER TABLE `nge_icl_message_status` ENABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
