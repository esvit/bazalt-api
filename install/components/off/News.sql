/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

DROP TABLE IF EXISTS `com_news_categories`;
CREATE TABLE IF NOT EXISTS `com_news_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(10) unsigned DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `lft` int(10) NOT NULL,
  `rgt` int(10) NOT NULL,
  `depth` int(10) unsigned NOT NULL,
  `is_hidden` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_publish` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `FK_com_news_categories_cms_sites` (`site_id`),
  CONSTRAINT `FK_com_news_categories_cms_sites` FOREIGN KEY (`site_id`) REFERENCES `cms_sites` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `com_news_categories_locale`;
CREATE TABLE IF NOT EXISTS `com_news_categories_locale` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` int(10) unsigned NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` mediumtext,
  `completed` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`lang_id`),
  KEY `FK_com_news_categories_locale_cms_languages_2` (`lang_id`),
  CONSTRAINT `FK_com_news_categories_locale_cms_languages_2` FOREIGN KEY (`lang_id`) REFERENCES `cms_languages` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_news_categories_locale_com_news_categories` FOREIGN KEY (`id`) REFERENCES `com_news_categories` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `com_news_comments`;
CREATE TABLE IF NOT EXISTS `com_news_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `news_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `user_name` varchar(100) NOT NULL,
  `body` text NOT NULL,
  `ip` int(10) unsigned NOT NULL,
  `browser_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_deleted` tinyint(1) unsigned NOT NULL,
  `is_moderated` tinyint(1) unsigned NOT NULL,
  `lft` int(10) NOT NULL,
  `rgt` int(10) NOT NULL,
  `depth` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `created_at` (`created_at`),
  KEY `FK_com_news_comments_com_tracking_browsers` (`browser_id`),
  KEY `FK_com_news_comments_cms_users` (`user_id`),
  KEY `FK_com_news_comments_ref_news_com_news_news` (`news_id`),
  KEY `is_deleted` (`is_deleted`),
  KEY `is_moderated` (`is_moderated`),
  KEY `lft` (`lft`),
  KEY `rgt` (`rgt`),
  KEY `depth` (`depth`),
  CONSTRAINT `FK_com_news_comments_cms_users` FOREIGN KEY (`user_id`) REFERENCES `cms_users` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_news_comments_com_tracking_browsers` FOREIGN KEY (`browser_id`) REFERENCES `com_tracking_browsers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_news_comments_ref_news_com_news_news` FOREIGN KEY (`news_id`) REFERENCES `com_news_news` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `com_news_images`;
CREATE TABLE IF NOT EXISTS `com_news_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `news_id` int(10) unsigned NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `order` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_com_news_images_com_news_news` (`news_id`),
  CONSTRAINT `FK_com_news_images_com_news_news` FOREIGN KEY (`news_id`) REFERENCES `com_news_news` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `com_news_news`;
CREATE TABLE IF NOT EXISTS `com_news_news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(10) unsigned NOT NULL,
  `company_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `region_id` int(10) unsigned DEFAULT NULL,
  `region` varchar(50) DEFAULT NULL,
  `category_id` int(10) unsigned DEFAULT NULL,
  `categories_count` int(10) unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `publish` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_top` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `url` varchar(255) DEFAULT NULL,
  `source` varchar(255) DEFAULT NULL,
  `item_type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `comments_number` int(10) unsigned NOT NULL DEFAULT '0',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_com_news_news_cms_sites` (`site_id`),
  KEY `FK_com_news_news_cms_components` (`company_id`),
  KEY `FK_com_news_news_cms_users` (`user_id`),
  KEY `FK_com_news_news_com_news_categories` (`category_id`),
  CONSTRAINT `FK_com_news_news_cms_sites` FOREIGN KEY (`site_id`) REFERENCES `cms_sites` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_news_news_cms_users` FOREIGN KEY (`user_id`) REFERENCES `cms_users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_news_news_com_enterprise_company` FOREIGN KEY (`company_id`) REFERENCES `com_enterprise_company` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_news_news_com_news_categories` FOREIGN KEY (`category_id`) REFERENCES `com_news_categories` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `com_news_news_locale`;
CREATE TABLE IF NOT EXISTS `com_news_news_locale` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(10) unsigned NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `body` text,
  `completed` tinyint(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`lang_id`),
  KEY `FK_com_news_news_locale_com_i18n_languages` (`lang_id`),
  CONSTRAINT `FK_com_news_news_locale_com_i18n_languages` FOREIGN KEY (`lang_id`) REFERENCES `cms_languages` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_news_news_locale_com_news_news` FOREIGN KEY (`id`) REFERENCES `com_news_news` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `com_news_ref_categories`;
CREATE TABLE IF NOT EXISTS `com_news_ref_categories` (
  `news_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`news_id`,`category_id`),
  KEY `FK_com_news_ref_categories_com_categories_elements` (`category_id`),
  CONSTRAINT `FK_com_news_ref_categories_com_articles_articles` FOREIGN KEY (`news_id`) REFERENCES `com_news_news` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_news_ref_categories_com_categories_elements` FOREIGN KEY (`category_id`) REFERENCES `cms_categories` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `com_news_ref_tags`;
CREATE TABLE IF NOT EXISTS `com_news_ref_tags` (
  `news_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`news_id`,`tag_id`),
  KEY `FK_com_news_ref_categories_com_tags_tags` (`tag_id`),
  CONSTRAINT `FK_com_news_ref_categories_com_news` FOREIGN KEY (`news_id`) REFERENCES `com_news_news` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_news_ref_tag_com_tags_tags` FOREIGN KEY (`tag_id`) REFERENCES `com_tags_tags` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `com_news_regions`;
CREATE TABLE IF NOT EXISTS `com_news_regions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title_in_case` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `alias` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `keywords` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alias` (`alias`),
  UNIQUE KEY `title` (`title`),
  KEY `alias2` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;