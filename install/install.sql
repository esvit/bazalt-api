
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET FOREIGN_KEY_CHECKS=0 */;

DROP TABLE IF EXISTS `cms_changelog`;
DROP TABLE IF EXISTS `cms_languages`;
DROP TABLE IF EXISTS `cms_components`;
DROP TABLE IF EXISTS `cms_components_locale`;
DROP TABLE IF EXISTS `cms_components_ref_sites`;
DROP TABLE IF EXISTS `cms_languages_ref_sites`;
DROP TABLE IF EXISTS `cms_options`;
DROP TABLE IF EXISTS `cms_roles`;
DROP TABLE IF EXISTS `cms_roles_locale`;
DROP TABLE IF EXISTS `cms_roles_ref_components`;
DROP TABLE IF EXISTS `cms_roles_ref_users`;
DROP TABLE IF EXISTS `cms_sites`;
DROP TABLE IF EXISTS `cms_sites_ref_users`;
DROP TABLE IF EXISTS `cms_themes`;
DROP TABLE IF EXISTS `cms_users`;
DROP TABLE IF EXISTS `cms_users_settings`;
DROP TABLE IF EXISTS `cms_widgets`;
DROP TABLE IF EXISTS `cms_widgets_instances`;
DROP TABLE IF EXISTS `cms_widgets_locale`;

CREATE TABLE `cms_languages` (
    `id` VARCHAR(2) NOT NULL COLLATE 'utf8_unicode_ci',
    `title` VARCHAR(50) NOT NULL COLLATE 'utf8_unicode_ci',
    `ico` VARCHAR(5) NOT NULL COLLATE 'utf8_unicode_ci',
    PRIMARY KEY (`id`)
) COLLATE='utf8_unicode_ci' ENGINE=InnoDB ROW_FORMAT=DEFAULT;

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


CREATE TABLE IF NOT EXISTS `cms_components` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `dependencies` text,
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='System components';

CREATE TABLE `cms_components_locale` (
`id` INT(10) UNSIGNED NOT NULL,
`lang_id` VARCHAR(2) NOT NULL,
`title` VARCHAR(255) NULL DEFAULT NULL,
`description` LONGTEXT NULL,
`completed` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
PRIMARY KEY (`id`, `lang_id`),
INDEX `FK_cms_components_locale_cms_languages` (`lang_id`),
CONSTRAINT `FK_cms_components_locale_cms_components` FOREIGN KEY (`id`) REFERENCES `cms_components` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
CONSTRAINT `FK_cms_components_locale_cms_languages` FOREIGN KEY (`lang_id`) REFERENCES `cms_languages` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
) COLLATE='utf8_unicode_ci' ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `cms_components_ref_sites` (
  `component_id` int(10) unsigned NOT NULL,
  `site_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`component_id`,`site_id`),
  KEY `FK_cms_components_ref_sites_cms_sites` (`site_id`),
  CONSTRAINT `FK_cms_components_ref_sites_cms_components` FOREIGN KEY (`component_id`) REFERENCES `cms_components` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_cms_components_ref_sites_cms_sites` FOREIGN KEY (`site_id`) REFERENCES `cms_sites` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cms_languages_ref_sites` (
`language_id` VARCHAR(2) NOT NULL,
`site_id` INT(10) UNSIGNED NOT NULL,
`is_active` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
PRIMARY KEY (`language_id`, `site_id`),
INDEX `FK__cms_sites` (`site_id`),
INDEX `language_id_site_id_is_active` (`language_id`, `site_id`, `is_active`),
CONSTRAINT `FK__cms_languages` FOREIGN KEY (`language_id`) REFERENCES `cms_languages` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
CONSTRAINT `FK__cms_sites` FOREIGN KEY (`site_id`) REFERENCES `cms_sites` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS `cms_options` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `component_id` int(10) unsigned DEFAULT NULL,
  `site_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `site_id_name` (`site_id`,`name`),
  KEY `component_id` (`component_id`),
  CONSTRAINT `FK_cms_options_cms_components` FOREIGN KEY (`component_id`) REFERENCES `cms_components` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_cms_options_cms_sites` FOREIGN KEY (`site_id`) REFERENCES `cms_sites` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Options of site and components';

CREATE TABLE IF NOT EXISTS `cms_roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(10) unsigned DEFAULT NULL COMMENT 'Якщо в цьому полі NULL, то цю роль не можна видаляти',
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `is_guest` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `is_hidden` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `system_acl` int(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `FK_cms_roles_cms_sites` (`site_id`),
  CONSTRAINT `FK_cms_roles_cms_sites` FOREIGN KEY (`site_id`) REFERENCES `cms_sites` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Ролі користувачів';

CREATE TABLE IF NOT EXISTS `cms_roles_locale` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` varchar(2) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` longtext,
  `completed` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`lang_id`),
  KEY `FK_cms_roles_locale_cms_languages` (`lang_id`),
  CONSTRAINT `FK_cms_roles_locale_cms_languages` FOREIGN KEY (`lang_id`) REFERENCES `cms_languages` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_cms_roles_locale_cms_roles` FOREIGN KEY (`id`) REFERENCES `cms_roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE='utf8_unicode_ci';

CREATE TABLE IF NOT EXISTS `cms_roles_ref_components` (
  `role_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Роль',
  `component_id` int(10) unsigned NOT NULL COMMENT 'Компонент, null - система',
  `value` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Значення',
  PRIMARY KEY (`role_id`,`component_id`),
  KEY `FK_cms_acl_cms_components` (`component_id`),
  CONSTRAINT `FK_cms_acl_cms_components` FOREIGN KEY (`component_id`) REFERENCES `cms_components` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_cms_acl_cms_roles` FOREIGN KEY (`role_id`) REFERENCES `cms_roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Таблиця прав доступу';

CREATE TABLE IF NOT EXISTS `cms_roles_ref_users` (
  `user_id` int(10) unsigned NOT NULL,
  `site_id` int(10) unsigned NOT NULL DEFAULT '1',
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`,`site_id`),
  KEY `FK_cms_roles_ref_users_cms_roles` (`role_id`),
  KEY `FK_cms_roles_ref_users_cms_sites` (`site_id`),
  CONSTRAINT `FK_cms_roles_ref_users_cms_roles` FOREIGN KEY (`role_id`) REFERENCES `cms_roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_cms_roles_ref_users_cms_sites` FOREIGN KEY (`site_id`) REFERENCES `cms_sites` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_cms_roles_ref_users_cms_users` FOREIGN KEY (`user_id`) REFERENCES `cms_users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cms_sites` (
`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
`domain` VARCHAR(255) NULL DEFAULT NULL,
`path` VARCHAR(255) NOT NULL DEFAULT '/',
`title` VARCHAR(255) NULL DEFAULT NULL,
`secret_key` VARCHAR(255) NULL DEFAULT NULL,
`theme_id` VARCHAR(50) NULL,
`language_id` VARCHAR(2) NULL DEFAULT NULL,
`languages` VARCHAR(255) NOT NULL DEFAULT 'en',
`is_subdomain` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
`is_active` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
`is_multilingual` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
`is_allow_indexing` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
`user_id` INT(10) UNSIGNED NULL DEFAULT NULL,
`created_at` DATETIME NOT NULL,
`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
`site_id` INT(10) UNSIGNED NULL DEFAULT NULL,
`is_redirect` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
UNIQUE INDEX `domain` (`domain`),
INDEX `FK_cms_sites_cms_sites` (`site_id`),
INDEX `FK_cms_sites_cms_users` (`user_id`),
INDEX `FK_cms_sites_cms_languages` (`language_id`),
INDEX `FK_cms_sites_cms_themes` (`theme_id`),
CONSTRAINT `FK_cms_sites_cms_themes` FOREIGN KEY (`theme_id`) REFERENCES `cms_themes` (`id`) ON UPDATE NO ACTION ON DELETE SET NULL,
CONSTRAINT `FK_cms_sites_cms_languages` FOREIGN KEY (`language_id`) REFERENCES `cms_languages` (`id`) ON UPDATE NO ACTION ON DELETE SET NULL,
CONSTRAINT `FK_cms_sites_cms_sites` FOREIGN KEY (`site_id`) REFERENCES `cms_sites` (`id`) ON UPDATE NO ACTION ON DELETE SET NULL,
CONSTRAINT `FK_cms_sites_cms_users` FOREIGN KEY (`user_id`) REFERENCES `cms_users` (`id`) ON UPDATE NO ACTION ON DELETE SET NULL
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `cms_sites_ref_users` (
  `site_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `last_activity` datetime DEFAULT NULL,
  `session_id` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`site_id`,`user_id`),
  KEY `FK_cms_sites_ref_users_cms_users` (`user_id`),
  CONSTRAINT `FK_cms_sites_ref_users_cms_sites` FOREIGN KEY (`site_id`) REFERENCES `cms_sites` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_cms_sites_ref_users_cms_users` FOREIGN KEY (`user_id`) REFERENCES `cms_users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cms_themes` (
`id` VARCHAR(50) NOT NULL DEFAULT 'default' COLLATE 'utf8_unicode_ci',
`settings` TEXT NULL DEFAULT NULL,
`is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
`is_hidden` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
PRIMARY KEY (`id`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `cms_users` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `login` VARCHAR(60) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `firstname` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Ім\'я',
  `secondname` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Прізвище',
  `patronymic` VARCHAR(255) NULL DEFAULT NULL COMMENT 'По-батькові',
  `gender` ENUM('unknown','male','female') NOT NULL DEFAULT 'unknown' COMMENT 'Стать',
  `birth_date` DATE NULL DEFAULT NULL COMMENT 'Дата народження',
  `email` VARCHAR(60) NULL DEFAULT NULL,
  `created_at` DATETIME NULL DEFAULT NULL,
  `is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `last_activity` DATETIME NULL DEFAULT NULL,
  `session_id` VARCHAR(50) NULL DEFAULT NULL,
  `is_god` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cms_users_settings` (
	`user_id` INT(10) UNSIGNED NOT NULL,
	`setting` VARCHAR(255) NULL DEFAULT NULL,
	`value` TEXT NULL,
	PRIMARY KEY (`user_id`, `setting`),
	CONSTRAINT `FK_cms_users_settings_cms_users` FOREIGN KEY (`user_id`) REFERENCES `cms_users` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
)
ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE='utf8_general_ci';

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


INSERT INTO `cms_languages` (`id`, `title`, `ico`) VALUES ('en', 'English', 'gb');
INSERT INTO `cms_languages` (`id`, `title`, `ico`) VALUES ('uk', 'Українська (Ukrainian)', 'uk');
INSERT INTO `cms_languages` (`id`, `title`, `ico`) VALUES ('ru', 'Русский (Russian)', 'ru');

INSERT INTO `cms_themes` (`id`, `is_active`) VALUES ('default', 1);

INSERT INTO `cms_users` (`id`, `login`, `password`, `firstname`, `secondname`, `patronymic`, `gender`, `birth_date`, `email`, `created_at`, `is_active`, `last_activity`, `session_id`, `is_god`) VALUES (1, 'admin', 'b109f3bbbc244eb82441917ed06d618b9008dd09b3befd1b5e07394c706a8bb980b1d7785e5976ec049b46df5f1326af5a2ea6d103fd07c95385ffab0cacbc86', 'Administrator', NULL, NULL, 'unknown', NULL, NULL, '2013-04-23 11:13:01', 1, NULL, NULL, 1);

INSERT INTO `cms_sites` (`id`, `domain`, `path`, `title`, `theme_id`, `language_id`, `is_subdomain`, `is_active`, `is_multilingual`, `user_id`, `created_at`, `updated_at`, `site_id`, `is_redirect`) VALUES (1, 'localhost', '/', NULL, 'default', 'en', 0, 1, 0, 1, NOW(), NOW(), NULL, 0);

/*!40014 SET FOREIGN_KEY_CHECKS=1 */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;