CREATE TABLE `nge_vcht_texts` (  `id` mediumint(9) NOT NULL AUTO_INCREMENT,  `original` text COLLATE utf8mb4_unicode_520_ci NOT NULL,  `content` text COLLATE utf8mb4_unicode_520_ci NOT NULL,  `isTextarea` tinyint(1) NOT NULL,  UNIQUE KEY `id` (`id`)) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40000 ALTER TABLE `nge_vcht_texts` DISABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;
INSERT INTO `nge_vcht_texts` VALUES('1', 'Need Help ?', 'Need Help ?', '0');
INSERT INTO `nge_vcht_texts` VALUES('2', 'Start', 'Start', '0');
INSERT INTO `nge_vcht_texts` VALUES('3', 'Hello! How can we help you ?', 'Hello! How can we help you ?', '1');
INSERT INTO `nge_vcht_texts` VALUES('4', 'This discussion is finished.', 'This discussion is finished.', '0');
INSERT INTO `nge_vcht_texts` VALUES('5', 'Sorry, there is currently no operator online. Feel free to contact us by using the form below.', 'Sorry, there is currently no operator online. Feel free to contact us by using the form below.', '1');
INSERT INTO `nge_vcht_texts` VALUES('6', 'Send this message', 'Send this message', '0');
INSERT INTO `nge_vcht_texts` VALUES('7', 'Thank you.\\nYour message has been sent.\\nWe will contact you soon.', 'Thank you.\\nYour message has been sent.\\nWe will contact you soon.', '1');
INSERT INTO `nge_vcht_texts` VALUES('8', 'There was an error while transferring the file', 'There was an error while transferring the file', '0');
INSERT INTO `nge_vcht_texts` VALUES('9', 'The selected file exceeds the authorized size', 'The selected file exceeds the authorized size', '0');
INSERT INTO `nge_vcht_texts` VALUES('10', 'The selected type of file is not allowed', 'The selected type of file is not allowed', '0');
INSERT INTO `nge_vcht_texts` VALUES('11', 'Drop files to upload here', 'Drop files to upload here', '0');
INSERT INTO `nge_vcht_texts` VALUES('12', 'You can not upload any more files', 'You can not upload any more files', '0');
INSERT INTO `nge_vcht_texts` VALUES('13', 'New message from your website', 'New message from your website', '0');
INSERT INTO `nge_vcht_texts` VALUES('14', 'Yes', 'Yes', '0');
INSERT INTO `nge_vcht_texts` VALUES('15', 'No', 'No', '0');
INSERT INTO `nge_vcht_texts` VALUES('16', 'Shows an element of the website', 'Shows an element of the website', '0');
INSERT INTO `nge_vcht_texts` VALUES('17', '[username] stopped the chat', '[username] stopped the chat', '0');
INSERT INTO `nge_vcht_texts` VALUES('18', 'Confirm', 'Confirm', '0');
INSERT INTO `nge_vcht_texts` VALUES('19', 'Transfer some files', 'Transfer some files', '0');
INSERT INTO `nge_vcht_texts` VALUES('20', '[username1] tranfers the chat to [username2]', '[username1] tranfers the chat to [username2]', '0');
/*!40000 ALTER TABLE `nge_vcht_texts` ENABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
