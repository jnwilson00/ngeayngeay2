CREATE TABLE `nge_icl_content_status` (  `rid` bigint(20) NOT NULL,  `nid` bigint(20) NOT NULL,  `timestamp` datetime NOT NULL,  `md5` varchar(32) COLLATE utf8mb4_unicode_520_ci NOT NULL,  PRIMARY KEY (`rid`),  KEY `nid` (`nid`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40000 ALTER TABLE `nge_icl_content_status` DISABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;
/*!40000 ALTER TABLE `nge_icl_content_status` ENABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
