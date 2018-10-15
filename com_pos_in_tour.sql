-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Сен 24 2018 г., 22:50
-- Версия сервера: 5.7.23-0ubuntu0.16.04.1
-- Версия PHP: 7.2.9-1+ubuntu16.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `g4ubase`
--

-- --------------------------------------------------------

--
-- Структура таблицы `com_pos_in_tour`
--

CREATE TABLE `com_pos_in_tour` (
  `position_id` int(11) NOT NULL,
  `sub_tournament_id` int(11) NOT NULL,
  `command_id` int(11) NOT NULL,
  `scored` int(11) NOT NULL DEFAULT '0',
  `missed` int(11) NOT NULL DEFAULT '0',
  `status` enum('up','down','unchanged') COLLATE utf8_croatian_ci NOT NULL DEFAULT 'unchanged',
  `pts` int(11) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  `old_position` int(11) NOT NULL DEFAULT '0',
  `win` int(11) DEFAULT '0',
  `draw` int(11) NOT NULL DEFAULT '0',
  `lose` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_croatian_ci;

--
-- Дамп данных таблицы `com_pos_in_tour`
--

INSERT INTO `com_pos_in_tour` (`position_id`, `sub_tournament_id`, `command_id`, `scored`, `missed`, `status`, `pts`, `position`, `old_position`, `win`, `draw`, `lose`) VALUES
(1, 6, 30, 14, 11, 'unchanged', 6, 1, 1, 2, 0, 1),
(2, 6, 47, 5, 5, 'up', 3, 5, 5, 1, 0, 2),
(3, 6, 36, 17, 17, 'up', 3, 6, 6, 1, 0, 1),
(4, 6, 21, 7, 20, 'unchanged', 0, 9, 9, 0, 0, 2),
(5, 6, 53, 8, 7, 'down', 4, 2, 2, 1, 1, 1),
(6, 6, 37, 12, 7, 'up', 4, 3, 3, 1, 1, 1),
(7, 6, 27, 9, 2, 'unchanged', 7, 0, 0, 2, 1, 0),
(8, 6, 44, 9, 4, 'unchanged', 3, 7, 7, 1, 0, 1),
(9, 8, 38, 11, 11, 'unchanged', 6, 1, 1, 2, 0, 1),
(10, 8, 35, 12, 5, 'unchanged', 6, 2, 2, 2, 0, 1),
(11, 8, 29, 11, 11, 'unchanged', 3, 5, 5, 1, 0, 1),
(12, 8, 40, 5, 21, 'unchanged', 0, 10, 10, 0, 0, 3),
(13, 8, 31, 12, 12, 'unchanged', 6, 3, 3, 2, 0, 1),
(14, 8, 41, 7, 13, 'unchanged', 3, 6, 6, 1, 0, 1),
(15, 8, 34, 9, 8, 'unchanged', 3, 7, 7, 1, 0, 1),
(16, 8, 39, 2, 7, 'unchanged', 3, 8, 8, 1, 0, 1),
(17, 8, 24, 16, 16, 'unchanged', 4, 4, 4, 1, 1, 0),
(18, 8, 25, 10, 10, 'unchanged', 1, 9, 9, 0, 1, 1),
(19, 8, 26, 23, 23, 'unchanged', 9, 0, 0, 3, 0, 0),
(20, 8, 33, 2, 26, 'unchanged', 0, 11, 11, 0, 0, 3),
(21, 9, 58, 2, 17, 'unchanged', 0, 8, 8, 0, 0, 2),
(22, 9, 48, 5, 5, 'unchanged', 4, 3, 3, 1, 1, 0),
(23, 9, 43, 5, 5, 'unchanged', 1, 7, 7, 0, 1, 2),
(24, 9, 42, 7, 7, 'unchanged', 3, 6, 6, 1, 0, 1),
(25, 9, 49, 1, 1, 'unchanged', 0, 9, 9, 0, 0, 3),
(26, 9, 57, 20, 5, 'unchanged', 6, 2, 2, 2, 0, 1),
(27, 9, 56, 6, 6, 'unchanged', 7, 0, 0, 2, 1, 0),
(28, 9, 51, 19, 3, 'unchanged', 4, 4, 4, 1, 1, 0),
(29, 9, 52, 8, 8, 'unchanged', 4, 5, 5, 1, 1, 1),
(30, 9, 46, 12, 6, 'unchanged', 7, 1, 1, 2, 1, 0),
(31, 6, 32, 10, 13, 'unchanged', 2, 8, 8, 0, 2, 0),
(32, 6, 28, 8, 7, 'up', 4, 4, 4, 1, 1, 1);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `com_pos_in_tour`
--
ALTER TABLE `com_pos_in_tour`
  ADD PRIMARY KEY (`position_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `com_pos_in_tour`
--
ALTER TABLE `com_pos_in_tour`
  MODIFY `position_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
