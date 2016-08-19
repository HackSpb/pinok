-- phpMyAdmin SQL Dump
-- version 4.6.3
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Авг 19 2016 г., 16:46
-- Версия сервера: 5.5.50-0ubuntu0.14.04.1
-- Версия PHP: 5.5.9-1ubuntu4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `MOTIVATOR`
--
CREATE DATABASE IF NOT EXISTS `MOTIVATOR` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `MOTIVATOR`;

-- --------------------------------------------------------

--
-- Структура таблицы `tasks`
--

CREATE TABLE `tasks` (
  `t_id` int(11) UNSIGNED NOT NULL,
  `t_name` char(30) COLLATE utf8_bin NOT NULL,
  `t_description` char(150) COLLATE utf8_bin DEFAULT NULL,
  `t_type` int(1) DEFAULT NULL,
  `t_date_create` datetime NOT NULL,
  `t_status` int(1) NOT NULL DEFAULT '1',
  `t_hour_reminder` time NOT NULL,
  `t_frequency_reminder` int(11) NOT NULL,
  `t_date_last_reminder` datetime DEFAULT NULL,
  `t_date_finish` datetime DEFAULT NULL,
  `t_level` int(1) NOT NULL DEFAULT '1',
  `t_id_parents` int(60) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Дамп данных таблицы `tasks`
--

INSERT INTO `tasks` (`t_id`, `t_name`, `t_description`, `t_type`, `t_date_create`, `t_status`, `t_hour_reminder`, `t_frequency_reminder`, `t_date_last_reminder`, `t_date_finish`, `t_level`, `t_id_parents`) VALUES
(1, 'магаз (#1)', 'купи молока', 1, '2016-06-21 00:00:00', 1, '00:00:00', 0, '0000-00-00 00:00:00', '2016-06-30 00:00:00', 1, 0),
(2, 'помыть собаку (#2)', 'с шампунем', 1, '2016-06-21 07:17:12', 1, '00:00:00', 0, '0000-00-00 00:00:00', '2016-06-29 06:27:34', 2, 1),
(3, 'сделать сайт (#1)', 'крутой сайт', 1, '2016-06-21 00:00:00', 0, '00:00:00', 0, '0000-00-00 00:00:00', '2016-06-29 06:27:34', 1, 0),
(4, 'помыть кота (#1)', 'с мылом', 1, '2016-06-21 00:00:00', 1, '00:00:00', 0, '0000-00-00 00:00:00', '2016-06-29 06:27:34', 1, 0),
(5, 'поиграть (#1)', 'в игры', 1, '2016-08-09 15:57:00', 0, '00:00:00', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 0),
(6, 'лето (#2)', 'наконец то', 1, '2016-06-21 00:00:00', 1, '00:00:00', 0, '0000-00-00 00:00:00', '2016-06-29 06:27:34', 2, 1),
(7, 'сайтики (#2)', 'клевые', 1, '2016-06-21 00:00:00', 1, '00:00:00', 0, '0000-00-00 00:00:00', '2016-06-29 06:27:34', 2, 4),
(8, 'булочки (#2)', 'сладкие', 1, '2016-06-21 00:00:00', 1, '00:00:00', 0, '0000-00-00 00:00:00', '2016-06-29 06:27:34', 2, 4),
(9, 'мышка (#2)', 'купить', 1, '2016-06-21 00:00:00', 1, '00:00:00', 0, '0000-00-00 00:00:00', '2016-06-29 06:27:34', 2, 5),
(10, 'съесть рыбку (#2)', 'жирную', 1, '2016-06-21 00:00:00', 1, '00:00:00', 0, '0000-00-00 00:00:00', '2016-06-29 06:27:34', 2, 3),
(11, 'покормить золотую рыбку (#2)', 'кормом', 1, '2016-06-23 13:17:33', 1, '00:00:00', 0, '0000-00-00 00:00:00', '2016-06-29 06:27:34', 2, 4),
(12, 'котики', 'покормить всех котиков', 2, '2016-08-09 01:02:23', 1, '00:00:00', 0, '0000-00-00 00:00:00', '2016-07-19 01:02:23', 1, 0),
(13, 'собачки', 'покормить всех собачек', 2, '2016-07-19 01:03:51', 1, '00:00:00', 0, '0000-00-00 00:00:00', '2021-06-17 00:00:00', 1, 0),
(14, 'рыбки', 'покормить всех рыбок', 2, '2016-07-19 01:04:54', 1, '00:00:00', 0, '0000-00-00 00:00:00', '2027-12-01 00:00:00', 1, 0),
(15, 'мишка', 'покормить всех мишек', 2, '2016-07-19 01:05:46', 1, '00:00:00', 0, '0000-00-00 00:00:00', '2021-06-16 00:00:00', 1, 0),
(16, 'птички', 'покормить всех птичек', 2, '2016-07-19 02:54:07', 1, '00:00:00', 0, '0000-00-00 00:00:00', '2016-07-21 00:00:00', 1, 0),
(17, 'булками', 'закорми их всех булками', 2, '2016-07-08 00:00:00', 1, '00:00:00', 0, '0000-00-00 00:00:00', '2016-07-22 00:00:00', 2, 12),
(18, 'лошадки', 'покормить лошадок', 2, '2016-07-19 14:20:50', 1, '00:00:00', 0, '0000-00-00 00:00:00', '2021-06-09 00:00:00', 1, 0),
(19, 'съесть сосиску', 'полив ее соусом', 1, '2016-07-20 20:11:30', 1, '00:00:00', 0, '0000-00-00 00:00:00', '2016-07-20 00:00:00', 1, 0),
(20, 'скушать салатик', 'смазиком', 2, '2016-07-20 20:12:16', 1, '00:00:00', 0, '0000-00-00 00:00:00', '2016-07-20 00:00:00', 1, 0),
(21, 'верни деньги', '', NULL, '2016-08-09 16:49:39', 1, '00:00:00', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 0),
(22, 'купи мне пиццу', '', NULL, '2016-08-09 16:52:43', 1, '00:00:00', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 0),
(23, 'помой мне спинку', '', NULL, '2016-08-09 16:54:10', 1, '00:00:00', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 0),
(24, 'скоро экзамен', '', NULL, '2016-08-09 16:57:29', 1, '00:00:00', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 0),
(25, 'скоро экзамен2', '', NULL, '2016-08-09 16:59:08', 1, '00:00:00', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 0),
(26, 'скоро экзамен3', '', NULL, '2016-08-09 17:02:16', 1, '00:00:00', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 0),
(27, 'скоро экзамен4', '', NULL, '2016-08-09 17:03:17', 1, '00:00:00', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 0),
(28, 'пол', 'помой пол', NULL, '2016-08-18 16:23:01', 1, '00:00:00', 0, '0000-00-00 00:00:00', '2023-05-20 16:00:00', 1, 0),
(29, 'котики', 'поогладь котика', NULL, '2016-08-18 16:26:13', 1, '00:00:00', 0, '0000-00-00 00:00:00', '2023-05-20 16:00:00', 1, 0),
(30, 'cat', 'catcat', NULL, '2016-08-18 16:27:05', 1, '00:00:00', 0, '0000-00-00 00:00:00', '2023-05-20 16:00:00', 1, 0),
(31, 'котики', 'купить котика', NULL, '2016-08-18 16:28:28', 1, '00:00:00', 0, '0000-00-00 00:00:00', '2023-05-20 16:00:00', 1, 0),
(32, 'мышка', 'купить мышек', NULL, '2016-08-18 16:29:27', 0, '00:00:00', 0, '0000-00-00 00:00:00', '2023-05-20 16:00:00', 1, 0),
(33, 'машина', 'купить машину', NULL, '2016-08-18 16:30:42', 0, '00:00:00', 0, '0000-00-00 00:00:00', '2023-05-20 16:00:00', 1, 0),
(34, 'поезд', 'купить себе поезщд', NULL, '2016-08-18 17:04:04', 0, '00:00:00', 0, '0000-00-00 00:00:00', '2023-05-20 16:00:00', 1, 0),
(35, 'пообедать', 'съесть супчик', NULL, '2016-08-19 13:56:24', 0, '00:00:00', 0, '0000-00-00 00:00:00', '2023-05-20 16:00:00', 1, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `u_id` int(11) UNSIGNED NOT NULL,
  `u_name` char(30) COLLATE utf8_bin DEFAULT NULL,
  `u_surname` char(30) COLLATE utf8_bin DEFAULT NULL,
  `u_city` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `u_phone_number` bigint(12) UNSIGNED DEFAULT NULL,
  `u_login` char(30) COLLATE utf8_bin DEFAULT NULL,
  `u_password` char(200) COLLATE utf8_bin DEFAULT NULL,
  `u_email` char(60) COLLATE utf8_bin NOT NULL,
  `u_activation` varchar(40) COLLATE utf8_bin DEFAULT NULL,
  `u_status` int(1) NOT NULL DEFAULT '0',
  `u_avatar` char(30) COLLATE utf8_bin NOT NULL DEFAULT 'standart.jpg',
  `u_role` tinyint(1) NOT NULL DEFAULT '1',
  `u_date_registration` datetime DEFAULT NULL,
  `u_date_active` datetime DEFAULT NULL,
  `u_online` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`u_id`, `u_name`, `u_surname`, `u_city`, `u_phone_number`, `u_login`, `u_password`, `u_email`, `u_activation`, `u_status`, `u_avatar`, `u_role`, `u_date_registration`, `u_date_active`, `u_online`) VALUES
(76, 'Павел', '', NULL, 0, NULL, '$2y$10$zXemYiVd6ng4OQQhF8xV9uWvrxsS0TZYgXjn2AE7Nseswrt2BaBL.', 'pav1887@yandex.ru', '2e1ef83fecba202aba507926ff72d6b9', 1, 'standart.jpg', 1, '2016-07-24 21:36:30', '2016-08-19 16:20:41', 0),
(77, NULL, NULL, NULL, NULL, NULL, '$2y$10$UVYgm8Rq15Aw34291TCvyOrnKJklPIxdole7kJu.PL6s2uMjHlNOC', 'pav2089@gmail.com', '71a25a7a1b6b8044c884bc30f25cfe4a', 1, 'standart.jpg', 1, '2016-07-26 20:36:31', '2016-08-09 09:58:05', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `users_tasks`
--

CREATE TABLE `users_tasks` (
  `u_id` int(11) UNSIGNED NOT NULL,
  `t_id` int(11) UNSIGNED NOT NULL,
  `ut_role` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Дамп данных таблицы `users_tasks`
--

INSERT INTO `users_tasks` (`u_id`, `t_id`, `ut_role`) VALUES
(76, 1, 1),
(77, 1, 2),
(77, 2, 1),
(77, 2, 2),
(76, 3, 1),
(76, 3, 2),
(76, 5, 1),
(77, 5, 2),
(76, 12, 1),
(77, 12, 2),
(76, 19, 1),
(77, 19, 2),
(76, 27, 1),
(77, 27, 2),
(76, 31, 1),
(76, 31, 2),
(76, 32, 1),
(76, 32, 2),
(76, 33, 1),
(76, 33, 2),
(76, 34, 1),
(76, 34, 2),
(76, 35, 1),
(76, 35, 2);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`t_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`u_id`),
  ADD UNIQUE KEY `u_email_2` (`u_email`),
  ADD KEY `u_email` (`u_email`);

--
-- Индексы таблицы `users_tasks`
--
ALTER TABLE `users_tasks`
  ADD KEY `u_email` (`u_id`),
  ADD KEY `t_id` (`t_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `tasks`
--
ALTER TABLE `tasks`
  MODIFY `t_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;
--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `u_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;
--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `users_tasks`
--
ALTER TABLE `users_tasks`
  ADD CONSTRAINT `users_tasks_ibfk_1` FOREIGN KEY (`u_id`) REFERENCES `users` (`u_id`),
  ADD CONSTRAINT `users_tasks_ibfk_2` FOREIGN KEY (`t_id`) REFERENCES `tasks` (`t_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
