CREATE TABLE `nge_revslider_layer_animations` (  `id` int(9) NOT NULL AUTO_INCREMENT,  `handle` text COLLATE utf8_unicode_ci NOT NULL,  `params` text COLLATE utf8_unicode_ci NOT NULL,  `settings` text COLLATE utf8_unicode_ci,  UNIQUE KEY `id` (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40000 ALTER TABLE `nge_revslider_layer_animations` DISABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;
/*!40000 ALTER TABLE `nge_revslider_layer_animations` ENABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
