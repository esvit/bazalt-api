DROP TABLE IF EXISTS `com_users_gifts`;
CREATE TABLE IF NOT EXISTS `com_users_gifts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `price` double unsigned DEFAULT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `com_users_gifts_locale`;
CREATE TABLE IF NOT EXISTS `com_users_gifts_locale` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `body` mediumtext COLLATE utf8_unicode_ci,
  KEY `FK_com_users_presents_locale_com_users_presents` (`id`),
  KEY `FK_com_users_presents_locale_cms_languages` (`lang_id`),
  CONSTRAINT `FK_com_users_presents_locale_cms_languages` FOREIGN KEY (`lang_id`) REFERENCES `cms_languages` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_users_presents_locale_com_users_presents` FOREIGN KEY (`id`) REFERENCES `com_users_gifts` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `com_users_gifts_ref_users`;
CREATE TABLE IF NOT EXISTS `com_users_gifts_ref_users` (
  `gift_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`gift_id`,`user_id`),
  KEY `FK_com_users_gifts_ref_users_cms_users` (`user_id`),
  CONSTRAINT `FK_com_users_gifts_ref_users_com_users_gifts` FOREIGN KEY (`gift_id`) REFERENCES `com_users_gifts` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_users_gifts_ref_users_cms_users` FOREIGN KEY (`user_id`) REFERENCES `cms_users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;