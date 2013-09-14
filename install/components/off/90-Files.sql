/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

DROP TABLE IF EXISTS `com_filestorage_fs`;
CREATE TABLE IF NOT EXISTS `com_filestorage_fs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(10) unsigned NOT NULL DEFAULT '1',
  `component_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `mimetype` varchar(255) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `is_system` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `access` int(10) unsigned NOT NULL,
  `downloads` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `size` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `width` INT(10) NULL DEFAULT NULL,
  `height` INT(10) NULL DEFAULT NULL,
  `lft` int(10) NOT NULL DEFAULT '1',
  `rgt` int(10) NOT NULL DEFAULT '2',
  `depth` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FKcom_file_storage_file_system_cms_sites` (`site_id`),
  KEY `FKcom_file_storage_file_system_cms_users` (`user_id`),
  CONSTRAINT `FKcom_file_storage_file_system_cms_sites` FOREIGN KEY (`site_id`) REFERENCES `cms_sites` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FKcom_file_storage_file_system_cms_users` FOREIGN KEY (`user_id`) REFERENCES `cms_users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `com_filestorage_fs_locale`;
CREATE TABLE IF NOT EXISTS `com_filestorage_fs_locale` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_id` varchar(2) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `body` text,
  `completed` tinyint(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`lang_id`),
  KEY `FKcom_filestorage_fs_locale_locale_com_i18n_languages` (`lang_id`),
  CONSTRAINT `FKcom_filestorage_fs_locale_com_filestorage_fs` FOREIGN KEY (`id`) REFERENCES `com_filestorage_fs` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FKcom_filestorage_fs_locale_locale_com_i18n_languages` FOREIGN KEY (`lang_id`) REFERENCES `cms_languages` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE='utf8_unicode_ci';

INSERT INTO `cms_components` (`name`, `dependencies`, `is_active`) VALUES ('Files', NULL, 1);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;