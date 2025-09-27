-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Июн 22 2025 г., 21:11
-- Версия сервера: 8.0.30
-- Версия PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `optika`
--

-- --------------------------------------------------------

--
-- Структура таблицы `favorites`
--

CREATE TABLE `favorites` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `product_price` decimal(10,2) DEFAULT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `product_category` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `product_id`, `product_name`, `product_price`, `product_image`, `product_category`, `created_at`) VALUES
(19, 1, 9, NULL, NULL, NULL, 'sun', '2025-06-10 00:01:37'),
(21, 1, 13, NULL, NULL, NULL, 'sun', '2025-06-10 09:19:46'),
(22, 1, 7, NULL, NULL, NULL, 'vision', '2025-06-10 22:50:12'),
(44, 1, 15, NULL, NULL, NULL, 'sun', '2025-06-10 23:16:21'),
(48, 1, 16, NULL, NULL, NULL, 'sun', '2025-06-11 00:29:14'),
(49, 1, 8, NULL, NULL, NULL, 'sun', '2025-06-11 00:57:11'),
(50, 1, 10, NULL, NULL, NULL, 'sun', '2025-06-11 03:16:56'),
(96, 3, 14, NULL, NULL, NULL, 'sun', '2025-06-11 10:00:45'),
(157, 1, 8, NULL, NULL, NULL, 'vision', '2025-06-14 23:58:27'),
(178, 1, 18, NULL, NULL, NULL, 'lenses', '2025-06-16 21:46:52'),
(179, 1, 7, NULL, NULL, NULL, 'lenses', '2025-06-16 21:54:03'),
(180, 1, 9, NULL, NULL, NULL, 'lenses', '2025-06-16 21:56:53'),
(181, 1, 4, NULL, NULL, NULL, 'computer', '2025-06-17 04:09:13'),
(182, 1, 8, NULL, NULL, NULL, 'computer', '2025-06-17 04:09:23');

-- --------------------------------------------------------

--
-- Структура таблицы `items_computer`
--

CREATE TABLE `items_computer` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `shape` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` decimal(2,1) NOT NULL DEFAULT '4.5',
  `popularity` int NOT NULL DEFAULT '0',
  `category` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'computer',
  `quantity` int NOT NULL DEFAULT '10',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `structure` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Не указано'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `items_computer`
--

INSERT INTO `items_computer` (`id`, `name`, `image`, `price`, `type`, `color`, `shape`, `rating`, `popularity`, `category`, `quantity`, `description`, `structure`) VALUES
(4, 'Женские кошачий глаз компьютерные очки Heliodor Gold', 'img/cat-35.jpg', '12490.00', 'Женский', 'Золотой', 'Кошачий глаз', '4.5', 0, 'computer', 10, NULL, 'Не указано'),
(5, 'Женские компьютерные очки Kyanite Black', 'img/cat-36.jpg', '11390.00', 'Женский', 'Черный', 'Прямоугольная', '4.5', 0, 'computer', 10, NULL, 'Не указано'),
(6, 'Женские кошачий глаз компьютерные очки Melanite Black', 'img/cat-37.jpg', '11390.00', 'Женский', 'Черный', 'Кошачий глаз', '4.5', 0, 'computer', 10, NULL, 'Не указано'),
(7, 'Женские компьютерные очки Olivine Gold', 'img/cat-38.jpg', '12890.00', 'Женский', 'Золотой', 'Прямоугольная', '4.5', 0, 'computer', 10, NULL, 'Не указано'),
(8, 'Компьютерные очки Sardier Gold', 'img/cat-39.jpg', '15090.00', 'Женский', 'Золотой', 'Прямоугольная', '4.5', 0, 'computer', 10, NULL, 'Не указано'),
(9, 'Компьютерные очки Dolomite Silver', 'img/cat-40.png', '18290.00', 'Унисекс', 'Серебряный', 'Круглая', '4.5', 0, 'computer', 9, NULL, 'Не указано'),
(10, 'Компьютерные очки Karfolit Gold', 'img/cat-41.jpg', '9590.00', 'Мужской', 'Золотой', 'Прямоугольная', '4.5', 0, 'computer', 10, NULL, 'Не указано'),
(11, 'Компьютерные очки Ahoit Turtle', 'img/cat-42.jpg', '14290.00', 'Мужской', 'Черепаховый', 'Прямоугольная', '4.5', 0, 'computer', 10, NULL, 'Не указано'),
(12, 'Компьютерные очки Cerite Grey', 'img/cat-43.png', '18290.00', 'Мужской', 'Серый', 'Прямоугольная', '4.5', 0, 'computer', 10, NULL, 'Не указано'),
(13, 'Компьютерные очки Kassit Black', 'img/cat-44.jpg', '6290.00', 'Мужской', 'Черный', 'Прямоугольная', '4.5', 0, 'computer', 10, NULL, 'Не указано');

-- --------------------------------------------------------

--
-- Структура таблицы `items_image`
--

CREATE TABLE `items_image` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `structure` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `shape` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` decimal(2,1) NOT NULL DEFAULT '4.5',
  `popularity` int NOT NULL DEFAULT '0',
  `category` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'image',
  `quantity` int NOT NULL DEFAULT '10',
  `description` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `items_image`
--

INSERT INTO `items_image` (`id`, `name`, `image`, `price`, `structure`, `type`, `color`, `shape`, `rating`, `popularity`, `category`, `quantity`, `description`) VALUES
(2, 'Женская круглая оправа Natron Black', 'img/cat-26.png', '10890.00', NULL, 'Женский', 'Черный', 'Круглая', '4.5', 0, 'image', 10, NULL),
(3, '  Женская кошачий глаз оправа Ikait Gold', 'img/cat-27.png', '9090.00', 'Не указано', 'Женский', 'Золотой', 'Кошачий глаз', '4.5', 0, 'image', 10, NULL),
(4, 'Женская круглая оправа Natron Black', 'img/cat-28.jpg', '10890.00', 'Не указано', 'Женский', 'Черный', 'Круглая', '4.5', 0, 'image', 10, NULL),
(5, 'Унисекс прямоугольная оправа Canopus Brown', 'img/cat-29.png', '10590.00', 'Не указано', 'Унисекс', 'Коричневый', 'Прямоугольная', '4.5', 0, 'image', 10, NULL),
(6, '  Унисекс круглая оправа Rigel Silver', 'img/cat-30.png', '9290.00', 'Не указано', 'Унисекс', 'Серебряный', 'Круглая', '4.5', 0, 'image', 10, NULL),
(7, 'Женская кошачий глаз оправа Antares Pink', 'img/cat-31.png', '7190.00', 'Не указано', 'Женский', 'Розовый', 'Кошачий глаз', '4.5', 0, 'image', 10, NULL),
(8, 'Мужская прямоугольная оправа Muirite Silver', 'img/cat-32.jpg', '10890.00', 'Не указано', 'Мужской', 'Серебряный', 'Прямоугольная', '4.5', 0, 'image', 10, NULL),
(9, 'Унисекс прямоугольная оправа Arcturus Green', 'img/cat-33.png', '10590.00', 'Не указано', 'Унисекс', 'Зеленый', 'Прямоугольная', '4.5', 0, 'image', 10, NULL),
(10, 'Мужская прямоугольная оправа Muirite Gold', 'img/cat-34.jpg', '6490.00', 'Не указано', 'Мужской', 'Золотой', 'Прямоугольная', '4.5', 0, 'image', 10, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `items_lenses`
--

CREATE TABLE `items_lenses` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `structure` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Не указано',
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` decimal(2,1) NOT NULL DEFAULT '4.5',
  `popularity` int NOT NULL DEFAULT '0',
  `category` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lenses',
  `shape` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '10',
  `description` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `items_lenses`
--

INSERT INTO `items_lenses` (`id`, `name`, `image`, `price`, `structure`, `type`, `color`, `rating`, `popularity`, `category`, `shape`, `quantity`, `description`) VALUES
(7, 'Контактные линзы Acuvue Oasys with Hydraclear plus (6 линз)', 'img/cat-45.webp', '1990.00', 'Срок замены 2 недели', 'Дневные', 'Прозрачный', '4.5', 0, 'lenses', '', 10, NULL),
(8, 'Контактные линзы Aculife 1-Day (30 линз)', 'img/cat-46.png', '2500.00', 'Срок замены 1 день', 'Дневные', 'Прозрачный', '4.5', 0, 'lenses', ' ', 10, NULL),
(9, 'Контактные линзы 1-Day Acuvue Oasys with Hydraluxe (30 линз)', 'img/cat-47.png', '2690.00', 'Срок замены 1 день', 'Дневные', 'Прозрачный', '4.5', 0, 'lenses', '', 10, NULL),
(10, 'Контактные линзы Alcon DAILIES TOTAL 1 (30 линз)', 'img/cat-48.jpg', '2890.00', 'Срок замены 1 день', 'Дневные', 'Прозрачный', '4.5', 0, 'lenses', '', 10, NULL),
(11, 'Контактные линзы Adria GO (5)', 'img/cat-49.png', '390.00', 'Срок замены 1 день', 'Дневные', 'Прозрачный', '4.5', 0, 'lenses', '', 10, NULL),
(12, 'Контактные линзы CLARITI ELITE (6 линз)', 'img/cat-50.jpg', '1499.00', 'Срок замены 1 месяц', 'Дневные', 'Прозрачный', '4.5', 0, 'lenses', '', 10, NULL),
(13, 'Контактные линзы Adria Sport (6)', 'img/cat-51.png', '1290.00', 'Срок замены 1 месяц', 'Дневные', 'Прозрачный', '4.5', 0, 'lenses', '', 9, NULL),
(14, 'Контактные линзы ADRIA O2O2 (2 линзы)', 'img/cat-52.png', '720.00', 'Срок замены 1 месяц', ' Дневные/Пролонгированные (день и ночь)', 'Прозрачный', '4.5', 0, 'lenses', '', 10, NULL),
(17, 'Контактные линзы BIOTRUE ONEDay (30 линз)', 'img/cat-53.png', '1570.00', 'Срок замены 1 день', 'Дневные', 'Прозрачный', '4.5', 0, 'lenses', '', 10, NULL),
(18, 'Контактные линзы 1-Day Acuvue Moist (30 линз)', 'img/cat-54.png', '1680.00', 'Срок замены 1 день', 'Дневные', 'Прозрачный', '4.5', 0, 'lenses', '', 8, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `items_sun`
--

CREATE TABLE `items_sun` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `structure` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Не указано',
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `shape` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` decimal(2,1) NOT NULL DEFAULT '4.5',
  `popularity` int NOT NULL DEFAULT '0',
  `category` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sun',
  `quantity` int NOT NULL DEFAULT '10',
  `description` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `items_sun`
--

INSERT INTO `items_sun` (`id`, `name`, `image`, `price`, `structure`, `type`, `color`, `shape`, `rating`, `popularity`, `category`, `quantity`, `description`) VALUES
(8, '  Унисекс оправа Alhena Black', 'img/cat-14.png', '23970.00', NULL, 'Унисекс', 'Черный', 'Круглая', '4.5', 0, 'sun', 48, NULL),
(9, 'Женская квадратная оправа Grenada Pink', 'img/cat-15.png', '8290.00', NULL, 'Женский', 'Розовый', 'Квадратная', '4.5', 0, 'sun', 10, NULL),
(10, 'Женская кошачий глаз оправа Capri Gold', 'img/cat-16.png', '8390.00', NULL, 'Женский', 'Золотой', 'Кошачий глаз', '4.5', 0, 'sun', 10, NULL),
(11, 'Женская кошачий глаз оправа Saint Lucia Black', 'img/cat-17.png', '8290.00', NULL, 'Женский', 'Черный', 'Кошачий глаз', '4.5', 0, 'sun', 10, NULL),
(12, 'Унисекс многоугольная оправа Saint Vincent Clear', 'img/cat-18.png', '8290.00', NULL, 'Унисекс', 'Прозрачный', 'Многоугольная', '4.5', 0, 'sun', 100, NULL),
(13, 'Женская кошачий глаз оправа Loosha Black', 'img/cat-19.png', '8490.00', NULL, 'Женский', 'Черный', 'Кошачий глаз', '4.5', 0, 'sun', 10, NULL),
(14, '  Женская квадратная оправа Amur Tigris Black', 'img/cat-20.png', '9790.00', NULL, 'Женский', 'Черный', 'Квадратная', '4.5', 0, 'sun', 50, NULL),
(15, 'Procyon Black', 'img/cat-21.png', '8290.00', NULL, 'Унисекс', 'Черный', 'Круглая', '4.5', 0, 'sun', 20, NULL),
(16, 'Мужская оправа Peiades Black', 'img/cat-22.png', '23970.00', NULL, 'Мужской', 'Черный', 'Многоугольная', '4.5', 0, 'sun', 50, NULL),
(17, 'Мужская квадратная оправа Belial Black', 'img/cat-23.png', '7890.00', NULL, 'Мужской', 'Серый', 'Квадратная', '4.5', 0, 'sun', 7, NULL),
(18, 'Мужская авиатор оправа Irbys Black', 'img/cat-23.png', '9690.00', NULL, 'Мужской', 'Черный', 'Авиатор', '4.5', 0, 'sun', 10, NULL),
(19, '  Мужская авиатор оправа Irbys Black', 'img/cat-24.png', '9890.00', NULL, 'Мужской', 'Черный', 'Авиатор', '4.5', 0, 'sun', 50, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `items_vision`
--

CREATE TABLE `items_vision` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `shape` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` decimal(2,1) NOT NULL DEFAULT '4.5',
  `popularity` int NOT NULL DEFAULT '0',
  `category` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'vision',
  `quantity` int NOT NULL DEFAULT '10',
  `description` text COLLATE utf8mb4_unicode_ci,
  `structure` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Не указано'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `items_vision`
--

INSERT INTO `items_vision` (`id`, `name`, `image`, `price`, `type`, `color`, `shape`, `rating`, `popularity`, `category`, `quantity`, `description`, `structure`) VALUES
(7, 'Женская кошачий глаз оправа Ruby Gold', 'img/cat-1.png', '6490.00', 'Женский', 'Золотой', 'Кошачий глаз', '4.5', 0, 'vision', 8, NULL, 'Не указано'),
(8, 'Унисекс круглая оправа Moonstone Silver', 'img/cat-2.jpg', '6490.00', 'Унисекс', 'Серебряный', 'Круглая', '4.5', 0, 'vision', 9, NULL, 'Не указано'),
(10, 'Женская прямоугольная оправа Feline Gold', 'img/cat-4.png', '6490.00', 'Женский', 'Золотой', 'Прямоугольная', '4.5', 0, 'vision', 0, NULL, 'Не указано'),
(11, 'Женские очки Sulfur Gold', 'img/catalog-glasses1.png', '27120.00', 'Женский', 'Золотой', 'Прямоугольная', '4.5', 0, 'vision', 10, NULL, 'Не указано'),
(12, 'Женская кошачий глаз оправа Vesuvian Clear', 'img/cat-5.png', '6490.00', 'Женский', 'Прозрачный', 'Кошачий глаз', '4.5', 0, 'vision', 10, NULL, 'Не указано'),
(13, '  Мужская прямоугольная оправа Garnet Black', 'img/cat-6.png', '6190.00', 'Мужской', 'Черный', 'Прямоугольная', '4.5', 0, 'vision', 5, NULL, 'Не указано'),
(14, 'Унисекс круглая оправа Holtit Grey', 'img/cat-9.png', '10190.00', 'Унисекс', 'Серый', 'Круглая', '4.5', 0, 'vision', 10, NULL, 'Не указано'),
(16, 'Женская кошачий глаз оправа Fuksit Black', 'img/cat-10.png', '23870.00', 'Женский', 'Черный', 'Кошачий глаз', '4.5', 0, 'vision', 10, NULL, 'Не указано'),
(17, '  Унисекс прямоугольная оправа Nitratin Black', 'img/cat-11.png', '9790.00', 'Унисекс', 'Черный', 'Прямоугольная', '4.5', 0, 'vision', 0, NULL, 'Не указано'),
(18, 'Женская круглая оправа Natron Black', 'img/cat-12.jpg', '10890.00', 'Женский', 'Черный', 'Круглая', '4.5', 0, 'vision', 10, NULL, 'Не указано'),
(19, 'Женская кошачий глаз оправа Uramfit Gold', 'img/cat-12.png', '13190.00', 'Женский', 'Серебряный', 'Кошачий глаз', '4.5', 0, 'vision', 10, NULL, 'Не указано'),
(20, 'Мужская прямоугольная оправа Mozzarite Black', 'img/cat-13.jpg', '3190.00', 'Мужской', 'Серый', 'Прямоугольная', '4.5', 0, 'vision', 10, NULL, 'Не указано'),
(23, 'Мужская оправа Peiades Black', 'img/cat-22.png', '23970.00', 'Мужской', 'Черный', 'Многоугольная', '4.5', 0, 'vision', 10, NULL, 'Не указано'),
(25, '  Унисекс прямоугольная оправа Hadar Silver', 'img/cat-55.png', '7090.00', 'Унисекс', 'Серебряный', 'Прямоугольная', '4.5', 0, 'vision', 10, NULL, 'Не указано');

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `street` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `house` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apartment` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','approved','rejected','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `order_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_price`, `phone`, `city`, `street`, `house`, `apartment`, `comment`, `status`, `order_date`) VALUES
(6, 1, '6490.00', '+79199709507', 'москва', 'усиевича', '1', '39', '', 'completed', '2025-05-28 23:53:38'),
(7, 1, '27120.00', '+79199709507', 'москва', 'усиевича', '1', '39', '', 'completed', '2025-06-01 15:11:38'),
(14, 1, '6490.00', '+79199709507', 'москва', 'усиевича', '1', '39', '', 'completed', '2025-06-04 01:09:07'),
(19, 1, '7890.00', '+79199709507', 'москва', 'усиевича', '1', '39', '', 'completed', '2025-06-11 00:17:44'),
(20, 1, '11111.00', '+79199709507', 'москва', 'усиевича', '1', '39', '', 'pending', '2025-06-11 04:25:47'),
(21, 1, '18290.00', '+79199709507', 'москва', 'усиевича', '1', '39', '', 'completed', '2025-06-11 04:37:56'),
(22, 3, '31860.00', '+79199709507', 'москва', 'усиевича', '1', '39', '', 'completed', '2025-06-11 05:18:06'),
(23, 3, '6490.00', '+79199709507', 'москва', 'усиевича', '1', '39', '', 'completed', '2025-06-11 10:03:28'),
(24, 1, '3360.00', '+79199709507', 'москва', 'усиевича', '1', '39', '', 'completed', '2025-06-16 22:19:28'),
(25, 1, '1290.00', '+79199709507', 'москва', 'усиевича', '1', '39', 'оставить у двери', 'completed', '2025-06-17 00:22:24');

-- --------------------------------------------------------

--
-- Структура таблицы `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `item_id` int NOT NULL,
  `item_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_price` decimal(10,2) NOT NULL,
  `quantity` int NOT NULL,
  `product_category` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `item_id`, `item_name`, `item_price`, `quantity`, `product_category`) VALUES
(17, 6, 7, 'Женская кошачий глаз оправа Ruby Gold', '6490.00', 1, 'vision'),
(18, 7, 0, 'Женские очки Sulfur Gold', '27120.00', 1, 'vision'),
(26, 14, 0, 'Женская прямоугольная оправа Feline Gold', '6490.00', 1, 'vision'),
(31, 19, 17, 'Мужская квадратная оправа Belial Black', '7890.00', 1, 'sun'),
(32, 20, 3, 'аааааааааааа', '11111.00', 1, 'computer'),
(33, 21, 9, 'Компьютерные очки Dolomite Silver', '18290.00', 1, 'computer'),
(34, 22, 8, '  Унисекс оправа Alhena Black', '23970.00', 1, 'sun'),
(35, 22, 17, 'Мужская квадратная оправа Belial Black', '7890.00', 1, 'sun'),
(36, 23, 7, 'Женская кошачий глаз оправа Ruby Gold', '6490.00', 1, 'vision'),
(37, 24, 18, 'Контактные линзы 1-Day Acuvue Moist (30 линз)', '1680.00', 2, 'lenses'),
(38, 25, 13, 'Контактные линзы Adria Sport (6)', '1290.00', 1, 'lenses');

-- --------------------------------------------------------

--
-- Структура таблицы `popular_searches`
--

CREATE TABLE `popular_searches` (
  `id` int NOT NULL,
  `text` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `count` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `popular_searches`
--

INSERT INTO `popular_searches` (`id`, `text`, `category`, `count`) VALUES
(1, 'Солнцезащитные очки', 'sun', 10),
(2, 'Оправы для зрения', 'vision', 8),
(3, 'Контактные линзы', 'lenses', 15),
(4, 'iWear Activ', 'lenses', 5),
(5, 'Seen очки', 'sun', 7);

-- --------------------------------------------------------

--
-- Структура таблицы `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `product_category` varchar(50) NOT NULL,
  `user_id` int NOT NULL,
  `rating` tinyint NOT NULL,
  `review_text` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` datetime NOT NULL,
  `moderated_at` datetime DEFAULT NULL,
  `moderator_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `product_reviews`
--

INSERT INTO `product_reviews` (`id`, `product_id`, `product_category`, `user_id`, `rating`, `review_text`, `status`, `created_at`, `moderated_at`, `moderator_id`) VALUES
(12, 17, 'sun', 1, 5, 'СУПЕЕР', 'approved', '2025-06-11 07:34:25', '2025-06-11 07:39:01', 1),
(13, 9, 'computer', 1, 5, 'очень красиво', 'approved', '2025-06-11 07:38:45', '2025-06-11 07:38:59', 1),
(14, 8, 'sun', 3, 5, 'мне понравились', 'approved', '2025-06-11 08:19:07', '2025-06-11 12:56:39', 1),
(17, 7, 'vision', 3, 5, 'круто хорошо сидят', 'approved', '2025-06-11 13:05:01', '2025-06-11 13:05:20', 1),
(18, 17, 'sun', 3, 3, 'не мое', 'approved', '2025-06-11 13:06:33', '2025-06-11 13:06:57', 1),
(19, 13, 'lenses', 1, 4, 'неплохие линзы', 'approved', '2025-06-17 03:23:35', '2025-06-17 03:24:04', 1),
(20, 18, 'lenses', 1, 5, 'понравились', 'approved', '2025-06-17 03:26:02', '2025-06-17 03:26:11', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_admin` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `is_admin`) VALUES
(1, 'admin', 'admin@admin.com', 'admin', 1),
(3, 'anna', 'anna@anna', 'anna', 0),
(5, 'anna', 'anna@123', '12345', 0),
(7, '11', '11@1', '1', 0),
(8, 'a$', 'aa@aa', '1111', 0),
(9, '1', '11@322', '222222', 0),
(11, 'anna', 'anna@134', '12345', 0),
(12, '11@', '111@1111', '12345', 0),
(13, '%%%%%%', '123@anna', '12345', 0);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favorite` (`user_id`,`product_id`,`product_category`);

--
-- Индексы таблицы `items_computer`
--
ALTER TABLE `items_computer`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `items_image`
--
ALTER TABLE `items_image`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `items_lenses`
--
ALTER TABLE `items_lenses`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `items_sun`
--
ALTER TABLE `items_sun`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `items_vision`
--
ALTER TABLE `items_vision`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Индексы таблицы `popular_searches`
--
ALTER TABLE `popular_searches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `text_category` (`text`,`category`);

--
-- Индексы таблицы `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=184;

--
-- AUTO_INCREMENT для таблицы `items_computer`
--
ALTER TABLE `items_computer`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT для таблицы `items_image`
--
ALTER TABLE `items_image`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `items_lenses`
--
ALTER TABLE `items_lenses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT для таблицы `items_sun`
--
ALTER TABLE `items_sun`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT для таблицы `items_vision`
--
ALTER TABLE `items_vision`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT для таблицы `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT для таблицы `popular_searches`
--
ALTER TABLE `popular_searches`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Ограничения внешнего ключа таблицы `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
