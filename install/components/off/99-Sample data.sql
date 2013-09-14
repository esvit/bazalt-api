INSERT INTO `com_pages_pages` (`id`, `site_id`, `user_id`, `category_id`, `url`, `updated_at`, `created_at`, `is_published`) VALUES (1, 1, NULL, NULL, 'second-page', '2013-05-01 18:25:41', '2013-05-01 18:25:41', 1);

INSERT INTO `com_pages_pages` (`id`, `site_id`, `user_id`, `category_id`, `url`, `updated_at`, `created_at`, `is_published`) VALUES (2, 1, NULL, NULL, 'part-of-main-page', '2013-05-01 18:27:22', '2013-05-01 18:27:22', 1);


INSERT INTO `cms_widgets_instances` (`id`, `site_id`, `widget_id`, `theme_id`, `template`, `widget_template`, `config`, `publish`, `position`, `order`) VALUES (1, 1, 1, NULL, 'index.twg', NULL, 'a:1:{s:6:"pageId";s:1:"2";}', 1, 'home', 0);

INSERT INTO `cms_widgets_instances` (`id`, `site_id`, `widget_id`, `theme_id`, `template`, `widget_template`, `config`, `publish`, `position`, `order`) VALUES (2, 1, 2, NULL, 'layout.twg', NULL, 'a:2:{s:7:"menu_id";s:1:"1";s:3:"css";s:3:"nav";}', 1, 'main-menu', 0);

INSERT INTO `com_pages_pages_locale` (`id`, `lang_id`, `title`, `body`, `completed`) VALUES (1, 'en', 'Second page', '<p>Test page</p>\n', 1);

INSERT INTO `com_pages_pages_locale` (`id`, `lang_id`, `title`, `body`, `completed`) VALUES (2, 'en', 'Part of main page', '<p>Welcome, this is a widget, click &quot;Widget&quot; button and you see a magic :)</p>\n', 1);

INSERT INTO `com_menu_elements` (`id`, `root_id`, `site_id`, `component_id`, `menuType`, `config`, `lft`, `rgt`, `depth`, `is_publish`) VALUES (1, 1, 1, NULL, NULL, NULL, 1, 6, 0, 0);
INSERT INTO `com_menu_elements` (`id`, `root_id`, `site_id`, `component_id`, `menuType`, `config`, `lft`, `rgt`, `depth`, `is_publish`) VALUES (2, 1, 1, 2, 'Components\\Pages\\Menu\\Page', 'a:1:{s:7:"page_id";s:1:"1";}', 4, 5, 1, 1);
INSERT INTO `com_menu_elements` (`id`, `root_id`, `site_id`, `component_id`, `menuType`, `config`, `lft`, `rgt`, `depth`, `is_publish`) VALUES (3, 1, 1, 3, 'Components\\Menu\\Menu\\MainPage', 'a:0:{}', 2, 3, 1, 1);

INSERT INTO `com_menu_elements_locale` (`id`, `lang_id`, `title`, `description`, `completed`) VALUES (1, 'en', 'Main menu', NULL, 1);
INSERT INTO `com_menu_elements_locale` (`id`, `lang_id`, `title`, `description`, `completed`) VALUES (2, 'en', 'Second page', NULL, 1);
INSERT INTO `com_menu_elements_locale` (`id`, `lang_id`, `title`, `description`, `completed`) VALUES (3, 'en', 'Home', NULL, 1);

INSERT INTO `cms_widgets_instances` (`id`, `site_id`, `widget_id`, `theme_id`, `template`, `widget_template`, `config`, `publish`, `position`, `order`) VALUES (3, 1, 3, NULL, 'index.twg', 'widgets/googlemap', 'a:3:{s:5:"width";i:400;s:6:"height";i:400;s:7:"address";s:53:"Вінниця, ул. Академика Ющенко";}', 1, 'home', 1);