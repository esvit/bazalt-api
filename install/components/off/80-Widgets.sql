INSERT INTO `cms_widgets` (`site_id`, `component_id`, `className`, `default_template`, `is_active`) VALUES (NULL, NULL, 'Widgets\\GoogleMap\\Widget', 'widgets/googlemap', 1);

SET @widget_id = LAST_INSERT_ID();

INSERT INTO `cms_widgets_locale` (`id`, `lang_id`, `title`, `description`, `completed`) VALUES (@widget_id, 1, 'Google map', NULL, 1);

