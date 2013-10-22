DROP TABLE IF EXISTS `com_payments_accounts`;
CREATE TABLE IF NOT EXISTS `com_payments_accounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `state` decimal(10,4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_com_payments_accounts_com_payments_account_types` (`type_id`),
  CONSTRAINT `FK_com_payments_accounts_com_payments_account_types` FOREIGN KEY (`type_id`) REFERENCES `com_payments_account_types` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `com_payments_account_types`;
CREATE TABLE IF NOT EXISTS `com_payments_account_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ratio` float unsigned NOT NULL,
  `currency` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_com_payments_account_types_cms_sites` (`site_id`),
  CONSTRAINT `FK_com_payments_account_types_cms_sites` FOREIGN KEY (`site_id`) REFERENCES `cms_sites` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `com_payments_transactions`;
CREATE TABLE IF NOT EXISTS `com_payments_transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int(10) unsigned NOT NULL,
  `type` enum('up','down') COLLATE utf8_unicode_ci NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `data` blob,
  `state` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sum` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000',
  `period_state` decimal(10,2) NOT NULL DEFAULT '0.00',
  `comment` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `FK_com_payments_transactions_com_payments_accounts` (`account_id`),
  CONSTRAINT `FK_com_payments_transactions_com_payments_accounts` FOREIGN KEY (`account_id`) REFERENCES `com_payments_accounts` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
