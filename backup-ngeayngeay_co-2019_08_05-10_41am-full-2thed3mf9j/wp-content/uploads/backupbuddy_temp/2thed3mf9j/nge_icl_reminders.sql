CREATE TABLE `nge_icl_reminders` (  `id` bigint(20) NOT NULL,  `message` text COLLATE utf8mb4_unicode_520_ci NOT NULL,  `url` text COLLATE utf8mb4_unicode_520_ci NOT NULL,  `can_delete` tinyint(4) NOT NULL,  `show` tinyint(4) NOT NULL,  PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40000 ALTER TABLE `nge_icl_reminders` DISABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;
/*!40000 ALTER TABLE `nge_icl_reminders` ENABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
