/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET FOREIGN_KEY_CHECKS=0 */;

-- Dumping structure for table bazalt_cms.com_shop_brands
DROP TABLE IF EXISTS `com_shop_brands`;
CREATE TABLE IF NOT EXISTS `com_shop_brands` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `shop_id` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_com_shop_brands_ref_cms_sites` (`shop_id`),
  CONSTRAINT `FK_com_shop_brands_com_shop_shops` FOREIGN KEY (`shop_id`) REFERENCES `com_shop_shops` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




-- Dumping structure for table bazalt_cms.com_shop_cart
DROP TABLE IF EXISTS `com_shop_cart`;
CREATE TABLE IF NOT EXISTS `com_shop_cart` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `shop_id` int(10) unsigned NOT NULL DEFAULT '1',
  `user_id` int(10) unsigned DEFAULT NULL,
  `price` double(10,4) unsigned NOT NULL DEFAULT '0.0000',
  `session_id` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `FK_com_shop_cart_cms_users` (`user_id`),
  KEY `FK_com_shop_cart_cms_sites` (`shop_id`),
  CONSTRAINT `FK_com_shop_cart_cms_sites` FOREIGN KEY (`shop_id`) REFERENCES `com_shop_shops` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_shop_cart_cms_users` FOREIGN KEY (`user_id`) REFERENCES `cms_users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




-- Dumping structure for table bazalt_cms.com_shop_cart_ref_product
DROP TABLE IF EXISTS `com_shop_cart_ref_product`;
CREATE TABLE IF NOT EXISTS `com_shop_cart_ref_product` (
  `cart_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `count` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`cart_id`,`product_id`),
  KEY `FK_com_shop_cart_ref_product_com_shop_products` (`product_id`),
  CONSTRAINT `FK_com_shop_cart_ref_product_com_shop_cart` FOREIGN KEY (`cart_id`) REFERENCES `com_shop_cart` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_shop_cart_ref_product_com_shop_products` FOREIGN KEY (`product_id`) REFERENCES `com_shop_products` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




-- Dumping structure for table bazalt_cms.com_shop_categories
DROP TABLE IF EXISTS `com_shop_categories`;
CREATE TABLE IF NOT EXISTS `com_shop_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `shop_id` int(10) unsigned DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `css` varchar(255) DEFAULT NULL,
  `lft` int(10) NOT NULL DEFAULT '1',
  `rgt` int(10) NOT NULL DEFAULT '2',
  `depth` int(10) unsigned NOT NULL DEFAULT '0',
  `is_published` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `FK_com_shop_categories_cms_sites` (`shop_id`),
  CONSTRAINT `FK_com_shop_categories_cms_sites` FOREIGN KEY (`shop_id`) REFERENCES `com_shop_shops` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




-- Dumping structure for table bazalt_cms.com_shop_categories_locale
DROP TABLE IF EXISTS `com_shop_categories_locale`;
CREATE TABLE IF NOT EXISTS `com_shop_categories_locale` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` mediumtext COLLATE utf8_unicode_ci,
  `completed` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`lang_id`),
  KEY `FK_com_shop_categories_locale_cms_languages` (`lang_id`),
  CONSTRAINT `FK_com_shop_categories_locale_cms_languages` FOREIGN KEY (`lang_id`) REFERENCES `cms_languages` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_shop_categories_locale_ref_elments` FOREIGN KEY (`id`) REFERENCES `com_shop_categories` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




-- Dumping structure for table bazalt_cms.com_shop_compare
DROP TABLE IF EXISTS `com_shop_compare`;
CREATE TABLE IF NOT EXISTS `com_shop_compare` (
  `cart_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `type_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`cart_id`,`product_id`),
  KEY `FK_com_shop_compare_com_shop_product_types` (`type_id`),
  KEY `FK_com_shop_compare_com_shop_products` (`product_id`),
  CONSTRAINT `FK_com_shop_compare_com_shop_cart` FOREIGN KEY (`cart_id`) REFERENCES `com_shop_cart` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_shop_compare_com_shop_products` FOREIGN KEY (`product_id`) REFERENCES `com_shop_products` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_shop_compare_com_shop_product_types` FOREIGN KEY (`type_id`) REFERENCES `com_shop_product_types` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




-- Dumping structure for table bazalt_cms.com_shop_fields
DROP TABLE IF EXISTS `com_shop_fields`;
CREATE TABLE IF NOT EXISTS `com_shop_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `data` longtext,
  `type` int(10) unsigned NOT NULL,
  `require` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_published` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_filter` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `order` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




-- Dumping structure for table bazalt_cms.com_shop_fields_locale
DROP TABLE IF EXISTS `com_shop_fields_locale`;
CREATE TABLE IF NOT EXISTS `com_shop_fields_locale` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_id` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `completed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`lang_id`),
  KEY `FK_com_shop_fields_locale_ref_cms_langs` (`lang_id`),
  CONSTRAINT `FK_com_shop_fields_locale_cms_languages` FOREIGN KEY (`lang_id`) REFERENCES `cms_languages` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_shop_fields_locale_ref_com_shop_fields` FOREIGN KEY (`id`) REFERENCES `com_shop_fields` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




-- Dumping structure for table bazalt_cms.com_shop_orders
DROP TABLE IF EXISTS `com_shop_orders`;
CREATE TABLE IF NOT EXISTS `com_shop_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `shop_id` int(10) unsigned NOT NULL,
  `cart_id` int(10) unsigned DEFAULT '0',
  `price` double(20,4) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text,
  `comment` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_com_shop_orders_cms_sites` (`shop_id`),
  KEY `cart_id` (`cart_id`),
  CONSTRAINT `com_shop_orders_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `com_shop_cart` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_shop_orders_cms_sites` FOREIGN KEY (`shop_id`) REFERENCES `com_shop_shops` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




-- Dumping structure for table bazalt_cms.com_shop_order_ref_product
DROP TABLE IF EXISTS `com_shop_order_ref_product`;
CREATE TABLE IF NOT EXISTS `com_shop_order_ref_product` (
  `order_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `count` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`order_id`,`product_id`),
  KEY `FK_com_shop_order_ref_product_com_shop_products` (`product_id`),
  CONSTRAINT `FK_com_shop_order_ref_product_com_shop_orders` FOREIGN KEY (`order_id`) REFERENCES `com_shop_orders` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_shop_order_ref_product_com_shop_products` FOREIGN KEY (`product_id`) REFERENCES `com_shop_products` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




-- Dumping structure for table bazalt_cms.com_shop_products
DROP TABLE IF EXISTS `com_shop_products`;
CREATE TABLE IF NOT EXISTS `com_shop_products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `shop_id` int(10) unsigned NOT NULL,
  `type_id` int(10) unsigned DEFAULT NULL,
  `brand_id` int(10) unsigned DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `price` decimal(20,4) NOT NULL,
  `count` int(10) unsigned NOT NULL DEFAULT '0',
  `hit` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_latest` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_discount` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `in_stock` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_order` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `count_img` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_published` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `FK_com_shop_products_ref_products_types` (`type_id`),
  KEY `FK_com_shop_products_ref_brands` (`brand_id`),
  KEY `FK_com_shop_products_com_enterprise_company` (`shop_id`),
  CONSTRAINT `FK_com_shop_products_com_shop_shops` FOREIGN KEY (`shop_id`) REFERENCES `com_shop_shops` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_shop_products_ref_brands` FOREIGN KEY (`brand_id`) REFERENCES `com_shop_brands` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_shop_products_ref_products_types` FOREIGN KEY (`type_id`) REFERENCES `com_shop_product_types` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




-- Dumping structure for table bazalt_cms.com_shop_products_categories
DROP TABLE IF EXISTS `com_shop_products_categories`;
CREATE TABLE IF NOT EXISTS `com_shop_products_categories` (
  `product_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`product_id`,`category_id`),
  KEY `FK_products_ref_categories` (`category_id`),
  CONSTRAINT `FK_cats_ref_products` FOREIGN KEY (`product_id`) REFERENCES `com_shop_products` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_products_ref_categories` FOREIGN KEY (`category_id`) REFERENCES `com_shop_categories` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




-- Dumping structure for table bazalt_cms.com_shop_products_fields
DROP TABLE IF EXISTS `com_shop_products_fields`;
CREATE TABLE IF NOT EXISTS `com_shop_products_fields` (
  `product_id` int(10) unsigned NOT NULL,
  `field_id` int(10) unsigned NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`product_id`,`field_id`,`value`),
  KEY `FK_ref_fields` (`field_id`),
  CONSTRAINT `FK_ref_fields` FOREIGN KEY (`field_id`) REFERENCES `com_shop_fields` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_ref_products` FOREIGN KEY (`product_id`) REFERENCES `com_shop_products` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




-- Dumping structure for table bazalt_cms.com_shop_products_images
DROP TABLE IF EXISTS `com_shop_products_images`;
CREATE TABLE IF NOT EXISTS `com_shop_products_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned DEFAULT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `order` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_images_ref_products` (`product_id`),
  CONSTRAINT `FK_images_ref_products` FOREIGN KEY (`product_id`) REFERENCES `com_shop_products` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




-- Dumping structure for table bazalt_cms.com_shop_products_locale
DROP TABLE IF EXISTS `com_shop_products_locale`;
CREATE TABLE IF NOT EXISTS `com_shop_products_locale` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `completed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`lang_id`),
  KEY `FK_com_shop_products_locale_ref_cms_langs` (`lang_id`),
  CONSTRAINT `FK_com_shop_products_locale_cms_languages` FOREIGN KEY (`lang_id`) REFERENCES `cms_languages` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_shop_products_locale_com_shop_products` FOREIGN KEY (`id`) REFERENCES `com_shop_products` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




-- Dumping structure for table bazalt_cms.com_shop_product_types
DROP TABLE IF EXISTS `com_shop_product_types`;
CREATE TABLE IF NOT EXISTS `com_shop_product_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned DEFAULT NULL,
  `lft` int(10) NOT NULL DEFAULT '1',
  `rgt` int(10) NOT NULL DEFAULT '2',
  `depth` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




-- Dumping structure for table bazalt_cms.com_shop_product_types_fields
DROP TABLE IF EXISTS `com_shop_product_types_fields`;
CREATE TABLE IF NOT EXISTS `com_shop_product_types_fields` (
  `product_type_id` int(10) unsigned NOT NULL,
  `field_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`product_type_id`,`field_id`),
  KEY `FK_ref_fields` (`field_id`),
  CONSTRAINT `FK_type_ref_fields` FOREIGN KEY (`field_id`) REFERENCES `com_shop_fields` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_type_ref_products` FOREIGN KEY (`product_type_id`) REFERENCES `com_shop_product_types` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




-- Dumping structure for table bazalt_cms.com_shop_product_types_locale
DROP TABLE IF EXISTS `com_shop_product_types_locale`;
CREATE TABLE IF NOT EXISTS `com_shop_product_types_locale` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_id` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `completed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`lang_id`),
  KEY `FK_com_shop_product_types_locale_ref_cms_langs` (`lang_id`),
  CONSTRAINT `FK_com_shop_product_types_locale_cms_languages` FOREIGN KEY (`lang_id`) REFERENCES `cms_languages` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_com_shop_product_types_locale_com_types` FOREIGN KEY (`id`) REFERENCES `com_shop_product_types` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




-- Dumping structure for table bazalt_cms.com_shop_shops
DROP TABLE IF EXISTS `com_shop_shops`;
CREATE TABLE IF NOT EXISTS `com_shop_shops` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `cms_components` (`name`, `dependencies`, `is_active`) VALUES ('Shop', NULL, 1);

SET @component_id = LAST_INSERT_ID();

INSERT INTO `cms_components_locale` (`id`, `lang_id`, `title`, `description`, `completed`) VALUES (@component_id, 'en', 'Shop', '', 1);

INSERT INTO `cms_themes` (`id`, `is_active`, `is_hidden`) VALUES ('saleway', 1, 0);
INSERT INTO `cms_themes` (`id`, `is_active`, `is_hidden`) VALUES ('saleway-admin', 1, 0);


INSERT INTO `cms_sites` (`id`, `domain`, `path`, `title`, `secret_key`, `theme_id`, `language_id`, `is_subdomain`, `is_active`, `is_multilingual`, `is_allow_indexing`, `user_id`, `created_at`, `updated_at`, `site_id`, `is_redirect`) VALUES (2, 'saleway.biz', '/', 'My first site on BazaltCMS', '01bd4cbc-58ae-f814-3103-9d0d15944ecf', 'saleway-admin', 'uk', 0, 1, 1, 1, 1, '2013-05-10 11:35:59', '2013-05-11 07:15:34', NULL, 0);

INSERT INTO `cms_components_ref_sites` (`component_id`, `site_id`) VALUES (1, 1);
INSERT INTO `cms_components_ref_sites` (`component_id`, `site_id`) VALUES (2, 1);
INSERT INTO `cms_components_ref_sites` (`component_id`, `site_id`) VALUES (3, 1);
INSERT INTO `cms_components_ref_sites` (`component_id`, `site_id`) VALUES (4, 1);
INSERT INTO `cms_components_ref_sites` (`component_id`, `site_id`) VALUES (5, 1);
INSERT INTO `cms_components_ref_sites` (`component_id`, `site_id`) VALUES (6, 1);
INSERT INTO `cms_components_ref_sites` (`component_id`, `site_id`) VALUES (7, 1);
INSERT INTO `cms_components_ref_sites` (`component_id`, `site_id`) VALUES (1, 2);
INSERT INTO `cms_components_ref_sites` (`component_id`, `site_id`) VALUES (2, 2);
INSERT INTO `cms_components_ref_sites` (`component_id`, `site_id`) VALUES (3, 2);
INSERT INTO `cms_components_ref_sites` (`component_id`, `site_id`) VALUES (4, 2);
INSERT INTO `cms_components_ref_sites` (`component_id`, `site_id`) VALUES (5, 2);
INSERT INTO `cms_components_ref_sites` (`component_id`, `site_id`) VALUES (6, 2);
INSERT INTO `cms_components_ref_sites` (`component_id`, `site_id`) VALUES (7, 2);

INSERT INTO `cms_widgets` (`site_id`, `component_id`, `className`, `default_template`, `is_active`) VALUES (NULL, @component_id, 'Components\\Shop\\Widget\\Shops', 'widgets/shop/shops', 1);

SET @widget_id = LAST_INSERT_ID();

INSERT INTO `cms_widgets_locale` (`id`, `lang_id`, `title`, `description`, `completed`) VALUES (@widget_id, 'en', 'Shops', NULL, 1);

INSERT INTO `cms_widgets` (`site_id`, `component_id`, `className`, `default_template`, `is_active`) VALUES (NULL, @component_id, 'Components\\Shop\\Widget\\Categories', 'widgets/shop/categories', 1);

SET @widget_id = LAST_INSERT_ID();

INSERT INTO `cms_widgets_locale` (`id`, `lang_id`, `title`, `description`, `completed`) VALUES (@widget_id, 'en', 'Categories', NULL, 1);

/*!40014 SET FOREIGN_KEY_CHECKS=1 */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;