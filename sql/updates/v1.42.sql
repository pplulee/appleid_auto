/*Table structure for table `proxy` */

CREATE TABLE `proxy` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `protocol` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `content` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `owner` int(10) unsigned NOT NULL,
  `last_use` datetime NOT NULL DEFAULT '2000-01-01 00:00:00',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;