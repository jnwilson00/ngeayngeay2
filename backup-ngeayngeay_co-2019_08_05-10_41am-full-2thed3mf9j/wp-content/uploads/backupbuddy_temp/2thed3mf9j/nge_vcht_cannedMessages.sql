CREATE TABLE `nge_vcht_cannedMessages` (  `id` mediumint(9) NOT NULL AUTO_INCREMENT,  `keyB` varchar(16) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'shift',  `title` varchar(64) COLLATE utf8mb4_unicode_520_ci NOT NULL,  `content` text COLLATE utf8mb4_unicode_520_ci NOT NULL,  `shortcut` varchar(32) COLLATE utf8mb4_unicode_520_ci NOT NULL,  `createdByAdmin` tinyint(1) NOT NULL DEFAULT '0',  UNIQUE KEY `id` (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40000 ALTER TABLE `nge_vcht_cannedMessages` DISABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;
/*!40000 ALTER TABLE `nge_vcht_cannedMessages` ENABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
