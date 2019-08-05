CREATE TABLE `nge_icl_core_status` (  `id` bigint(20) NOT NULL AUTO_INCREMENT,  `rid` bigint(20) NOT NULL,  `module` varchar(16) COLLATE utf8mb4_unicode_520_ci NOT NULL,  `origin` varchar(64) COLLATE utf8mb4_unicode_520_ci NOT NULL,  `target` varchar(64) COLLATE utf8mb4_unicode_520_ci NOT NULL,  `status` smallint(6) NOT NULL,  `tp_revision` int(11) NOT NULL DEFAULT '1',  `ts_status` text COLLATE utf8mb4_unicode_520_ci,  PRIMARY KEY (`id`),  KEY `rid` (`rid`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40000 ALTER TABLE `nge_icl_core_status` DISABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;
/*!40000 ALTER TABLE `nge_icl_core_status` ENABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
