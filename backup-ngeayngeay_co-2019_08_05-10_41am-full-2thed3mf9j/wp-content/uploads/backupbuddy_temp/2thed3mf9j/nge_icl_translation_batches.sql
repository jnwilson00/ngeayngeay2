CREATE TABLE `nge_icl_translation_batches` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `batch_name` text COLLATE utf8mb4_unicode_520_ci NOT NULL,  `tp_id` int(11) DEFAULT NULL,  `ts_url` text COLLATE utf8mb4_unicode_520_ci,  `last_update` datetime DEFAULT NULL,  PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40000 ALTER TABLE `nge_icl_translation_batches` DISABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;
INSERT INTO `nge_icl_translation_batches` VALUES('1', 'Manual Translations from May the 25th, 2019', NULL, NULL, '2019-05-25 15:50:45');
INSERT INTO `nge_icl_translation_batches` VALUES('2', 'Manual Translations from May the 28th, 2019', NULL, NULL, '2019-05-28 02:43:57');
INSERT INTO `nge_icl_translation_batches` VALUES('3', 'Manual Translations from June the 24th, 2019', NULL, NULL, '2019-06-24 06:30:54');
INSERT INTO `nge_icl_translation_batches` VALUES('4', 'Manual Translations from June the 25th, 2019', NULL, NULL, '2019-06-25 03:48:13');
INSERT INTO `nge_icl_translation_batches` VALUES('5', 'Manual Translations from June the 26th, 2019', NULL, NULL, '2019-06-26 02:38:20');
INSERT INTO `nge_icl_translation_batches` VALUES('6', 'Ngeay Ngeay|WPML|en', NULL, NULL, '2019-06-26 08:08:05');
INSERT INTO `nge_icl_translation_batches` VALUES('7', 'Manual Translations from July the 19th, 2019', NULL, NULL, '2019-07-19 08:47:19');
INSERT INTO `nge_icl_translation_batches` VALUES('8', 'Manual Translations from July the 20th, 2019', NULL, NULL, '2019-07-20 02:15:05');
INSERT INTO `nge_icl_translation_batches` VALUES('9', 'Manual Translations from July the 23rd, 2019', NULL, NULL, '2019-07-23 04:10:45');
INSERT INTO `nge_icl_translation_batches` VALUES('10', 'Manual Translations from July the 26th, 2019', NULL, NULL, '2019-07-26 01:51:26');
INSERT INTO `nge_icl_translation_batches` VALUES('11', 'Ngeay Ngeay|WPML|en|2', NULL, NULL, '2019-07-26 03:23:26');
INSERT INTO `nge_icl_translation_batches` VALUES('12', 'Manual Translations from July the 30th, 2019', NULL, NULL, '2019-07-30 09:17:00');
INSERT INTO `nge_icl_translation_batches` VALUES('13', 'Manual Translations from August the 1st, 2019', NULL, NULL, '2019-08-01 17:40:03');
INSERT INTO `nge_icl_translation_batches` VALUES('14', 'Ngeay Ngeay|WPML|en|3', NULL, NULL, '2019-08-01 17:42:31');
INSERT INTO `nge_icl_translation_batches` VALUES('15', 'Ngeay Ngeay|WPML|en|4', NULL, NULL, '2019-08-02 00:47:28');
/*!40000 ALTER TABLE `nge_icl_translation_batches` ENABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
