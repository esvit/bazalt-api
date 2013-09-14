/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET FOREIGN_KEY_CHECKS=0 */;

DROP TABLE IF EXISTS `com_pages_categories`;
CREATE TABLE IF NOT EXISTS `com_pages_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(10) unsigned DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `lft` int(10) NOT NULL,
  `rgt` int(10) NOT NULL,
  `depth` int(10) unsigned NOT NULL,
  `is_hidden` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_published` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `FK_com_pages_categories_cms_sites` (`site_id`),
  CONSTRAINT `FK_com_pages_categories_cms_sites` FOREIGN KEY (`site_id`) REFERENCES `cms_sites` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `com_pages_categories_locale`;
CREATE TABLE IF NOT EXISTS `com_pages_categories_locale` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` varchar(2) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` mediumtext,
  `completed` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`lang_id`),
  KEY `FK_com_pages_categories_locale_cms_languages_2` (`lang_id`),
  CONSTRAINT `FK_com_pages_categories_locale_cms_languages_2` FOREIGN KEY (`lang_id`) REFERENCES `cms_languages` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_pages_categories_locale_com_pages_categories` FOREIGN KEY (`id`) REFERENCES `com_pages_categories` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE='utf8_unicode_ci';

DROP TABLE IF EXISTS `com_pages_images`;
CREATE TABLE IF NOT EXISTS `com_pages_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` INT(10) unsigned NULL DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_com_pages_images_com_pages_pages` (`page_id`),
  CONSTRAINT `FK_com_pages_images_com_pages_pages` FOREIGN KEY (`page_id`) REFERENCES `com_pages_pages` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `com_pages_pages`;
CREATE TABLE IF NOT EXISTS `com_pages_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `category_id` int(10) unsigned DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `is_published` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_com_pages_pages_cms_users` (`user_id`),
  KEY `FK_com_pages_pages_cms_sites` (`site_id`),
  KEY `FK_com_pages_pages_com_pages_categories` (`category_id`),
  CONSTRAINT `FK_com_pages_pages_cms_sites` FOREIGN KEY (`site_id`) REFERENCES `cms_sites` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_pages_pages_cms_users` FOREIGN KEY (`user_id`) REFERENCES `cms_users` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_pages_pages_com_pages_categories` FOREIGN KEY (`category_id`) REFERENCES `com_pages_categories` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `com_pages_pages_locale`;
CREATE TABLE IF NOT EXISTS `com_pages_pages_locale` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_id` varchar(2) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `body` mediumtext,
  `completed` tinyint(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`lang_id`),
  KEY `FK_com_pages_pages_cms_languages` (`lang_id`),
  CONSTRAINT `FK_com_pages_pages_locale_cms_languages` FOREIGN KEY (`lang_id`) REFERENCES `cms_languages` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_pages_pages_locale_com_pages_pages` FOREIGN KEY (`id`) REFERENCES `com_pages_pages` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE='utf8_unicode_ci';

INSERT INTO `cms_components` (`name`, `dependencies`, `is_active`) VALUES ('Pages', NULL, 1);

SET @component_id = LAST_INSERT_ID();

INSERT INTO `cms_components_locale` (`id`, `lang_id`, `title`, `description`, `completed`) VALUES (@component_id, 'en', 'Pages', '', 1);

INSERT INTO `cms_widgets` (`site_id`, `component_id`, `className`, `default_template`, `is_active`) VALUES (NULL, @component_id, 'Components\\Pages\\Widget\\Page', 'widgets/page', 1);

SET @widget_id = LAST_INSERT_ID();

INSERT INTO `cms_widgets_locale` (`id`, `lang_id`, `title`, `description`, `completed`) VALUES (@widget_id, 'en', 'Page', NULL, 1);

/*!40014 SET FOREIGN_KEY_CHECKS=1 */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;