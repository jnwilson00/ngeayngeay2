CREATE TABLE `nge_icl_node` (  `nid` bigint(20) NOT NULL,  `md5` varchar(32) COLLATE utf8mb4_unicode_520_ci NOT NULL,  `links_fixed` tinyint(4) NOT NULL DEFAULT '0',  PRIMARY KEY (`nid`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40000 ALTER TABLE `nge_icl_node` DISABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;
/*!40000 ALTER TABLE `nge_icl_node` ENABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
