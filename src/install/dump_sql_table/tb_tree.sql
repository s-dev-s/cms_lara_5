-- phpMyAdmin SQL Dump
-- version 4.4.15.5
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Апр 11 2016 г., 10:55
-- Версия сервера: 10.1.0-MariaDB
-- Версия PHP: 5.5.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `c1_lara5`
--

-- --------------------------------------------------------

--
-- Структура таблицы `tb_tree`
--

CREATE TABLE IF NOT EXISTS `tb_tree` (
  `id` int(10) unsigned NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `lft` int(11) DEFAULT NULL,
  `rgt` int(11) DEFAULT NULL,
  `depth` int(11) DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `template` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  `is_active` tinyint(4) NOT NULL,
  `seo_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `seo_description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `seo_keywords` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `map` text COLLATE utf8_unicode_ci NOT NULL,
  `show_in_menu` tinyint(4) NOT NULL,
  `picture` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `additional_pictures` text COLLATE utf8_unicode_ci NOT NULL,
  `show_in_footer_menu` tinyint(4) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=119 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `tb_tree`
--

INSERT INTO `tb_tree` (`id`, `parent_id`, `lft`, `rgt`, `depth`, `title`, `description`, `slug`, `template`, `is_active`, `seo_title`, `seo_description`, `seo_keywords`, `created_at`, `updated_at`, `map`, `show_in_menu`, `picture`, `additional_pictures`, `show_in_footer_menu`) VALUES
(1, NULL, 1, 62, 0, 'Главная', '', '/', 'main', 1, '', '', '', '2015-06-02 09:01:32', '2016-04-11 07:51:44', '', 0, '', '[]', 0),
(115, 1, 54, 55, 1, 'Контакты', '', 'kontakty', 'contacts', 1, '', '', '', '2016-04-11 07:51:07', '2016-04-11 07:51:15', '', 0, '', '', 0),
(116, 1, 56, 57, 1, 'Новости', '', 'novosti', 'news', 1, '', '', '', '2016-04-11 07:51:25', '2016-04-11 07:53:55', '', 0, '', '', 0),
(117, 1, 58, 59, 1, 'Статьи', '', 'stati', 'article', 1, '', '', '', '2016-04-11 07:51:33', '2016-04-11 07:51:33', '', 0, '', '', 0),
(118, 1, 60, 61, 1, 'О нас', '', 'o-nas', 'about', 1, '', '', '', '2016-04-11 07:51:44', '2016-04-11 07:51:44', '', 0, '', '', 0);

--
-- Индексы сохранённых таблиц
--


--
-- Индексы таблицы `tb_tree`
--
ALTER TABLE `tb_tree`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tb_tree_parent_id_index` (`parent_id`),
  ADD KEY `tb_tree_lft_index` (`lft`),
  ADD KEY `tb_tree_rgt_index` (`rgt`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `tb_tree`
--
ALTER TABLE `tb_tree`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=119;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
