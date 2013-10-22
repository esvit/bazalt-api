
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET FOREIGN_KEY_CHECKS=0 */;

DROP TABLE IF EXISTS `cms_changelog`;
DROP TABLE IF EXISTS `cms_widgets`;
DROP TABLE IF EXISTS `cms_widgets_instances`;
DROP TABLE IF EXISTS `cms_widgets_locale`;

CREATE TABLE IF NOT EXISTS `cms_changelog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `component_id` int(10) unsigned DEFAULT NULL,
  `site_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `params` text,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `FK_cms_changelog_cms_components` (`component_id`),
  KEY `FK_cms_changelog_cms_users` (`user_id`),
  KEY `FK_cms_changelog_cms_sites` (`site_id`),
  CONSTRAINT `FK_cms_changelog_cms_components` FOREIGN KEY (`component_id`) REFERENCES `cms_components` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_cms_changelog_cms_sites` FOREIGN KEY (`site_id`) REFERENCES `cms_sites` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_cms_changelog_cms_users` FOREIGN KEY (`user_id`) REFERENCES `cms_users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Last changes';

CREATE TABLE IF NOT EXISTS `cms_widgets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(10) unsigned DEFAULT NULL,
  `component_id` int(10) unsigned DEFAULT NULL,
  `className` varchar(50) NOT NULL,
  `default_template` varchar(255) NOT NULL,
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `component_id` (`component_id`),
  KEY `FK_cms_widgets_cms_sites` (`site_id`),
  CONSTRAINT `FK_cms_widgets_cms_sites` FOREIGN KEY (`site_id`) REFERENCES `cms_sites` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `widget_ref_comp` FOREIGN KEY (`component_id`) REFERENCES `cms_components` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cms_widgets_instances` (
`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
`site_id` INT(10) UNSIGNED NOT NULL,
`widget_id` INT(10) UNSIGNED NOT NULL,
`theme_id` VARCHAR(50) NULL DEFAULT NULL,
`template` VARCHAR(255) NULL DEFAULT NULL,
`widget_template` VARCHAR(255) NULL DEFAULT NULL,
`config` MEDIUMTEXT NULL,
`publish` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
`position` VARCHAR(60) NULL DEFAULT NULL,
`order` INT(10) UNSIGNED NULL DEFAULT NULL,
PRIMARY KEY (`id`),
INDEX `widget_id` (`widget_id`),
INDEX `FK_cms_widgets_config_cms_themes` (`theme_id`),
INDEX `FK_cms_widgets_config_cms_sites` (`site_id`),
CONSTRAINT `FK_cms_widgets_instances_cms_themes` FOREIGN KEY (`theme_id`) REFERENCES `cms_themes` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
CONSTRAINT `config_ref_widget` FOREIGN KEY (`widget_id`) REFERENCES `cms_widgets` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
CONSTRAINT `FK_cms_widgets_config_cms_sites` FOREIGN KEY (`site_id`) REFERENCES `cms_sites` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `cms_widgets_locale` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` varchar(2) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` longtext,
  `completed` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`lang_id`),
  KEY `FK_cms_widgets_locale_cms_languages` (`lang_id`),
  CONSTRAINT `FK_cms_widgets_locale_cms_languages` FOREIGN KEY (`lang_id`) REFERENCES `cms_languages` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_cms_widgets_locale_cms_widgets` FOREIGN KEY (`id`) REFERENCES `cms_widgets` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COLLATE='utf8_unicode_ci';

INSERT INTO `cms_themes` (`id`, `is_active`) VALUES ('default', 1);

INSERT INTO `cms_users` (`id`, `login`, `password`, `firstname`, `secondname`, `patronymic`, `gender`, `birth_date`, `email`, `created_at`, `is_active`, `last_activity`, `session_id`, `is_god`) VALUES (1, 'admin', 'b109f3bbbc244eb82441917ed06d618b9008dd09b3befd1b5e07394c706a8bb980b1d7785e5976ec049b46df5f1326af5a2ea6d103fd07c95385ffab0cacbc86', 'Administrator', NULL, NULL, 'unknown', NULL, NULL, '2013-04-23 11:13:01', 1, NULL, NULL, 1);

INSERT INTO `cms_sites` (`id`, `domain`, `path`, `title`, `theme_id`, `language_id`, `is_subdomain`, `is_active`, `is_multilingual`, `user_id`, `created_at`, `updated_at`, `site_id`, `is_redirect`) VALUES (1, 'localhost', '/', NULL, 'default', 'en', 0, 1, 0, 1, NOW(), NOW(), NULL, 0);

/*!40014 SET FOREIGN_KEY_CHECKS=1 */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;