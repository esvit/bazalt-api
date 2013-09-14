/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

DROP TABLE IF EXISTS `com_menu_elements`;
CREATE TABLE IF NOT EXISTS `com_menu_elements` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `root_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `site_id` int(10) unsigned NOT NULL,
  `component_id` int(10) unsigned DEFAULT NULL,
  `menuType` varchar(30) DEFAULT NULL,
  `config` text,
  `lft` int(10) NOT NULL DEFAULT '0',
  `rgt` int(10) NOT NULL DEFAULT '0',
  `depth` int(10) unsigned NOT NULL DEFAULT '0',
  `is_publish` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `FK_com_menu_elements_cms_components` (`component_id`),
  CONSTRAINT `FK_com_menu_elements_cms_components` FOREIGN KEY (`component_id`) REFERENCES `cms_components` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `com_menu_elements_locale`;
CREATE TABLE IF NOT EXISTS `com_menu_elements_locale` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` varchar(2) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `completed` tinyint(3) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`,`lang_id`),
  KEY `FK_com_menu_elements_locale_cms_languages` (`lang_id`),
  CONSTRAINT `FK_com_menu_elements_locale_cms_languages` FOREIGN KEY (`lang_id`) REFERENCES `cms_languages` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_menu_elements_locale_com_menu_elements` FOREIGN KEY (`id`) REFERENCES `com_menu_elements` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE='utf8_unicode_ci';

INSERT INTO `cms_components` (`name`, `dependencies`, `is_active`) VALUES ('Menu', NULL, 1);

SET @component_id = LAST_INSERT_ID();

INSERT INTO `cms_components_locale` (`id`, `lang_id`, `title`, `description`, `completed`) VALUES (@component_id, 'en', 'Menu', '', 1);

INSERT INTO `cms_widgets` (`site_id`, `component_id`, `className`, `default_template`, `is_active`) VALUES (NULL, @component_id, 'Components\\Menu\\Widget\\Menu', 'widgets/menu', 1);

SET @widget_id = LAST_INSERT_ID();

INSERT INTO `cms_widgets_locale` (`id`, `lang_id`, `title`, `description`, `completed`) VALUES (@widget_id, 'en', 'Menu', NULL, 1);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;