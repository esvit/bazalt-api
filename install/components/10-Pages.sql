/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET FOREIGN_KEY_CHECKS=0 */;

-- Дамп структуры для таблица bazalt_cms.com_pages_categories
DROP TABLE IF EXISTS `com_pages_categories`;
CREATE TABLE IF NOT EXISTS `com_pages_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(10) unsigned DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
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

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица bazalt_cms.com_pages_categories_locale
DROP TABLE IF EXISTS `com_pages_categories_locale`;
CREATE TABLE IF NOT EXISTS `com_pages_categories_locale` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` mediumtext COLLATE utf8_unicode_ci,
  `completed` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`lang_id`),
  KEY `FK_com_pages_categories_locale_cms_languages_2` (`lang_id`),
  CONSTRAINT `FK_com_pages_categories_locale_cms_languages_2` FOREIGN KEY (`lang_id`) REFERENCES `cms_languages` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_pages_categories_locale_com_pages_categories` FOREIGN KEY (`id`) REFERENCES `com_pages_categories` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица bazalt_cms.com_pages_comments
DROP TABLE IF EXISTS `com_pages_comments`;
CREATE TABLE IF NOT EXISTS `com_pages_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `nickname` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `body` text,
  `ip` int(10) unsigned DEFAULT NULL,
  `browser_agent` varchar(500) DEFAULT NULL,
  `rating` int(10) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_moderated` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `lft` int(10) NOT NULL DEFAULT '1',
  `rgt` int(10) NOT NULL DEFAULT '2',
  `depth` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `created_at` (`created_at`),
  KEY `FK_com_pages_comments_cms_users` (`user_id`),
  KEY `FK_com_pages_comments_ref_pages_com_pages_pages` (`page_id`),
  KEY `is_deleted` (`is_deleted`),
  KEY `is_moderated` (`is_moderated`),
  KEY `lft` (`lft`),
  KEY `rgt` (`rgt`),
  KEY `depth` (`depth`),
  CONSTRAINT `FK_com_pages_comments_cms_users` FOREIGN KEY (`user_id`) REFERENCES `cms_users` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_pages_comments_ref_pages_com_pages_pages` FOREIGN KEY (`page_id`) REFERENCES `com_pages_pages` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица bazalt_cms.com_pages_images
DROP TABLE IF EXISTS `com_pages_images`;
CREATE TABLE IF NOT EXISTS `com_pages_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `size` int(10) unsigned NOT NULL DEFAULT '0',
  `sort_order` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_com_pages_images_com_pages_pages` (`page_id`),
  CONSTRAINT `FK_com_pages_images_com_pages_pages` FOREIGN KEY (`page_id`) REFERENCES `com_pages_pages` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица bazalt_cms.com_pages_pages
DROP TABLE IF EXISTS `com_pages_pages`;
CREATE TABLE IF NOT EXISTS `com_pages_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `category_id` int(10) unsigned DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `template` varchar(255) DEFAULT 'default.html',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `is_published` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_allow_comments` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `comments_count` int(10) unsigned NOT NULL DEFAULT '0',
  `rating` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_com_pages_pages_cms_users` (`user_id`),
  KEY `FK_com_pages_pages_cms_sites` (`site_id`),
  KEY `FK_com_pages_pages_com_pages_categories` (`category_id`),
  CONSTRAINT `FK_com_pages_pages_cms_sites` FOREIGN KEY (`site_id`) REFERENCES `cms_sites` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_pages_pages_cms_users` FOREIGN KEY (`user_id`) REFERENCES `cms_users` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_pages_pages_com_pages_categories` FOREIGN KEY (`category_id`) REFERENCES `com_pages_categories` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица bazalt_cms.com_pages_pages_locale
DROP TABLE IF EXISTS `com_pages_pages_locale`;
CREATE TABLE IF NOT EXISTS `com_pages_pages_locale` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_id` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `body` mediumtext COLLATE utf8_unicode_ci,
  `completed` tinyint(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`lang_id`),
  KEY `FK_com_pages_pages_cms_languages` (`lang_id`),
  CONSTRAINT `FK_com_pages_pages_locale_cms_languages` FOREIGN KEY (`lang_id`) REFERENCES `cms_languages` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_pages_pages_locale_com_pages_pages` FOREIGN KEY (`id`) REFERENCES `com_pages_pages` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица bazalt_cms.com_pages_pages_ref_tags
DROP TABLE IF EXISTS `com_pages_pages_ref_tags`;
CREATE TABLE IF NOT EXISTS `com_pages_pages_ref_tags` (
  `tag_id` int(10) unsigned NOT NULL,
  `page_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`tag_id`,`page_id`),
  KEY `FK__com_pages_pages` (`page_id`),
  CONSTRAINT `FK__com_pages_pages` FOREIGN KEY (`page_id`) REFERENCES `com_pages_pages` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK__com_pages_tags` FOREIGN KEY (`tag_id`) REFERENCES `com_pages_tags` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица bazalt_cms.com_pages_tags
DROP TABLE IF EXISTS `com_pages_tags`;
CREATE TABLE IF NOT EXISTS `com_pages_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(50) DEFAULT NULL,
  `url` varchar(50) DEFAULT NULL,
  `is_published` tinyint(4) DEFAULT '1',
  `quantity` int(11) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_com_pages_tags_cms_sites` (`site_id`),
  CONSTRAINT `FK_com_pages_tags_cms_sites` FOREIGN KEY (`site_id`) REFERENCES `cms_sites` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `cms_components` (`name`, `dependencies`, `is_active`) VALUES ('Pages', NULL, 1);

SET @component_id = LAST_INSERT_ID();

INSERT INTO `cms_components_locale` (`id`, `lang_id`, `title`, `description`, `completed`) VALUES (@component_id, 'en', 'Pages', '', 1);

INSERT INTO `cms_widgets` (`site_id`, `component_id`, `className`, `default_template`, `is_active`) VALUES (NULL, @component_id, 'Components\\Pages\\Widget\\Page', 'widgets/page', 1);

SET @widget_id = LAST_INSERT_ID();

INSERT INTO `cms_widgets_locale` (`id`, `lang_id`, `title`, `description`, `completed`) VALUES (@widget_id, 'en', 'Page', NULL, 1);

/*!40014 SET FOREIGN_KEY_CHECKS=1 */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;