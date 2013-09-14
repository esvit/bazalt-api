/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

DROP TABLE IF EXISTS `com_gallery_albums`;
CREATE TABLE IF NOT EXISTS `com_gallery_albums` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(10) unsigned NOT NULL,
  `alias` varchar(50) NOT NULL,
  `lft` int(10) NOT NULL DEFAULT '1',
  `rgt` int(10) NOT NULL DEFAULT '2',
  `depth` int(10) unsigned NOT NULL DEFAULT '0',
  `images_count` int(10) unsigned NOT NULL DEFAULT '0',
  `is_hidden` int(10) NOT NULL DEFAULT '0',
  `is_published` int(10) NOT NULL DEFAULT '0',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_com_gallery_albums_cms_sites` (`site_id`),
  CONSTRAINT `FK_com_gallery_albums_cms_sites` FOREIGN KEY (`site_id`) REFERENCES `cms_sites` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

DROP TABLE IF EXISTS `com_gallery_albums_locale`;
CREATE TABLE IF NOT EXISTS `com_gallery_albums_locale` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` varchar(2) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` mediumtext,
  `completed` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`lang_id`),
  KEY `FK_com_categories_elements_locale_cms_languages_2` (`lang_id`),
  CONSTRAINT `FK_com_gallery_albums_locale_cms_languages` FOREIGN KEY (`lang_id`) REFERENCES `cms_languages` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_gallery_albums_locale_com_gallery_albums` FOREIGN KEY (`id`) REFERENCES `com_gallery_albums` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COLLATE='utf8_unicode_ci';

DROP TABLE IF EXISTS `com_gallery_photo`;
CREATE TABLE IF NOT EXISTS `com_gallery_photo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(10) unsigned DEFAULT NULL,
  `album_id` int(10) unsigned DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `thumbs` mediumtext,
  `is_published` int(10) NOT NULL DEFAULT '0',
  `order` int(10) unsigned NOT NULL,
  `width` INT(10) UNSIGNED NOT NULL,
  `height` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_com_gallery_photo_cms_sites` (`site_id`),
  KEY `FK_com_gallery_photo_com_gallery_albums` (`album_id`),
  CONSTRAINT `FK_com_gallery_photo_cms_sites` FOREIGN KEY (`site_id`) REFERENCES `cms_sites` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_gallery_photo_com_gallery_albums` FOREIGN KEY (`album_id`) REFERENCES `com_gallery_albums` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `com_gallery_photo_locale`;
CREATE TABLE IF NOT EXISTS `com_gallery_photo_locale` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` varchar(2) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` mediumtext,
  `completed` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`lang_id`),
  KEY `FK_com_gallery_photo_locale_cms_languages` (`lang_id`),
  CONSTRAINT `FK_com_gallery_photo_locale_cms_languages` FOREIGN KEY (`lang_id`) REFERENCES `cms_languages` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_gallery_photo_locale_com_gallery_photo` FOREIGN KEY (`id`) REFERENCES `com_gallery_photo` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE='utf8_unicode_ci';

INSERT INTO `cms_components` (`name`, `dependencies`, `is_active`) VALUES ('Gallery', NULL, 1);

SET @component_id = LAST_INSERT_ID();

INSERT INTO `cms_components_locale` (`id`, `lang_id`, `title`, `description`, `completed`) VALUES (@component_id, 'en', 'Gallery', '', 1);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;