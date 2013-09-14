INSERT INTO `cms_components` (`name`, `dependencies`, `is_active`) VALUES ('Themes', NULL, 1);

SET @component_id = LAST_INSERT_ID();

INSERT INTO `cms_components_locale` (`id`, `lang_id`, `title`, `description`, `completed`) VALUES (@component_id, 'en', 'Themes', '', 1);
