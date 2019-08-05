CREATE TABLE `nge_revslider_navigations` (  `id` int(9) NOT NULL AUTO_INCREMENT,  `name` varchar(191) COLLATE utf8_unicode_ci NOT NULL,  `handle` varchar(191) COLLATE utf8_unicode_ci NOT NULL,  `css` longtext COLLATE utf8_unicode_ci NOT NULL,  `markup` longtext COLLATE utf8_unicode_ci NOT NULL,  `settings` longtext COLLATE utf8_unicode_ci,  UNIQUE KEY `id` (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40000 ALTER TABLE `nge_revslider_navigations` DISABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;
/*!40000 ALTER TABLE `nge_revslider_navigations` ENABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
