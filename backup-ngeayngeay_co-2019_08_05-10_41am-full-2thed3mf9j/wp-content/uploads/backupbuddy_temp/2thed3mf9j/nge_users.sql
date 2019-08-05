CREATE TABLE `nge_users` (  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,  `user_login` varchar(60) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',  `user_pass` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',  `user_nicename` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',  `user_email` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',  `user_url` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',  `user_registered` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',  `user_activation_key` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',  `user_status` int(11) NOT NULL DEFAULT '0',  `display_name` varchar(250) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',  PRIMARY KEY (`ID`),  KEY `user_login_key` (`user_login`),  KEY `user_nicename` (`user_nicename`),  KEY `user_email` (`user_email`)) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40000 ALTER TABLE `nge_users` DISABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;
INSERT INTO `nge_users` VALUES('1', 'bchanpanha@gmail.com', '$P$BLhSKKqEaJ1jRS3n.P4rL7XGdDqzxb0', 'bchanpanhagmail-com', 'jnwilson00@gmail.com', '', '2019-03-07 03:16:58', '', '0', 'James');
INSERT INTO `nge_users` VALUES('2', 'panha', '$P$BVu0ONWnvbCq9xcmeS4Eh8DpdKngN.0', 'panha', 'bchanpanha@gmail.com', '', '2019-06-18 07:09:43', '', '0', 'panha');
/*!40000 ALTER TABLE `nge_users` ENABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
