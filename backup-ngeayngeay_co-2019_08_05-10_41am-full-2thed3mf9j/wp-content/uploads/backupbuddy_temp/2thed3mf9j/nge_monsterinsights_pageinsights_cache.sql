CREATE TABLE `nge_monsterinsights_pageinsights_cache` (  `request_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,  `path` varchar(2048) COLLATE utf8mb4_unicode_520_ci NOT NULL,  `value` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,  `expiry` datetime NOT NULL,  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,  PRIMARY KEY (`request_id`),  UNIQUE KEY `request_id` (`request_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40000 ALTER TABLE `nge_monsterinsights_pageinsights_cache` DISABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;
/*!40000 ALTER TABLE `nge_monsterinsights_pageinsights_cache` ENABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
