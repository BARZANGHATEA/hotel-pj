-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 21, 2025 at 10:05 PM
-- Server version: 10.4.21-MariaDB
-- PHP Version: 7.4.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hotel_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$Arlr/50VUwejxjl41EJRMeZEiqPkWFKTWC7BPi3AwdZ9AMrF9Yame');

-- --------------------------------------------------------

--
-- Table structure for table `blog_categories`
--

CREATE TABLE `blog_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categories` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `image`, `categories`, `tags`, `created_at`, `status`) VALUES
(3, '1755353441_Screenshot 2025-05-13 025625.png', NULL, NULL, '2025-08-16 14:10:41', 'draft'),
(4, '1755354639_tourism2.jpg', 'نکات مفید,اخبار هتل', 'هتل,گردشگری', '2025-08-16 14:30:39', 'draft');

-- --------------------------------------------------------

--
-- Table structure for table `blog_post_categories`
--

CREATE TABLE `blog_post_categories` (
  `post_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `blog_post_tags`
--

CREATE TABLE `blog_post_tags` (
  `post_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `blog_post_translations`
--

CREATE TABLE `blog_post_translations` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `lang_code` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `summary` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_post_translations`
--

INSERT INTO `blog_post_translations` (`id`, `post_id`, `lang_code`, `title`, `content`, `summary`, `updated_at`) VALUES
(7, 3, 'fa', 'حالاچه بخشهایی باقی موندهحالاچه بخشهایی باقی موندهحالاچه بخشهایی باقی مونده', 'https://t.me/c/1142372981/190207https://t.me/c/1142372981/190207https://t.me/c/1142372981/190207https://t.me/c/1142372981/190207https://t.me/c/1142372981/190207https://t.me/c/1142372981/190207https://t.me/c/1142372981/190207https://t.me/c/1142372981/190207', 'https://t.me/c/1142372981/190207https://t.me/c/1142372981/190207https://t.me/c/1142372981/190207https://t.me/c/1142372981/190207https://t.me/c/1142372981/190207'),
(8, 3, 'en', '', '', ''),
(9, 3, 'az', '', '', ''),
(10, 4, 'fa', 'معرفی انواع مختلف گردشگری', '<p>انواع مختلف گردشگری صنعت گردشگری سهم عمده&zwnj;ای در اقتصادهای محلی در کشورهای مختلف دارد. قبل از همه گیری کووید-۱۹، سهم گردشگری معادل ۱۰.۳ درصد از تولید ناخالص داخلی جهانی را تشکیل می&zwnj;داد که البته، دوباره در مسیر رشد قرار گرفته است. اما گردشگری مدل&zwnj;های مختلفی دارد. انواع مختلف گردشگری را می&zwnj;شناسید؟ ما قصد دارید طبق تعاریف سازمان جهانی گردشگری سازمان ملل (UNWTO) هشت نوع گردشگری اصلی را معرفی کنیم. البته، ابتدا گردشگری را به سه دسته اصلی تقسیم می&zwnj;کنیم و به تعریف هر یک از آنها خواهیم پرداخت. سه شکل اصلی گردشگری ابتدا قصد داریم از نظر سفر و ورود و خروج انواع مختلف گردشگری را بررسی کنیم. طبق آنچه سازمان جهانی گردشگری تعریف کرده است ما سه دسته اصلی داریم که شامل: گردشگری داخلی، گردشگری ورودی (inbound tourism) و گردشگری برون&zwnj;مرزی (Outbound tourism) می&zwnj;شود. در ادامه هر کدام را با مثال&zwnj;هایی بررسی خواهیم کرد. انواع مختلف گردشگری مطالب مرتبط: چگونه اکوتوریسم وارد ادبیات گردشگری شد؟ با شعارهای گردشگری کشورهای آسیا آشنایید؟ گردشگری سیاه چیست ؟ گردشگری داخلی (Domestic tourism) گردشگری داخلی به عنوان مسافرت در کشور محل سکونت شما با اهداف تجاری یا تفریحی تعریف می&zwnj;شود. معمولاً سازماندهی این نوع سفرها آسان&zwnj;تر از سفرهای بین&zwnj;المللی است چون به مدارک اضافی، بررسی&zwnj;های بهداشتی و مسائلی از این دست نیاز ندارد. در نتیجه به راحتی با یک پرواز داخلی، اتوبوس یا قطار به مقصد خود خواهید رسید. برای مثال، شما بلیط هواپیما تهران به شیراز را به راحتی از مجموعه الی گشت تهیه می&zwnj;کنید، دیگر نیاز به ویزا و ارائه گواهی تمکن مالی و چنین مدارکی نخواهید داشت. علاوه بر آن موانع زبانی و شوک فرهنگی هم در این حالت وجود ندارد. در نتیجه، گردشگران استرس کمتری برای انجام این سفرها خواهند داشت. گردشگری درون یا ورودی (Inbound tourism) وقتی وارد کشور دیگری می&zwnj;شود، این گردشگری ورودی برای کشور مقصد به حساب می&zwnj;آید. یعنی اگر از ایران به اسپانیا سفر می&zwnj;کنید، این گردشگری ورودی برای اسپانیا به حساب می&zwnj;آید. گردشگری ورودی نوعی گردشگری بین&zwnj;المللی است. در واقع سازمان جهانی گردشگری می&zwnj;توانست تنها دودسته داخلی و بین&zwnj;المللی را تعریف کند اما به نظر می&zwnj;رسد با ارائه تعریف&zwnj;های گردشگری ورودی و خروجی سعی کرده این مسئله را پیچیده کند. همان&zwnj;طور که اشاره کردیم گردشگری ورودی یک نوع بین&zwnj;المللی است، پس باید همه مدارک لازم را از قبل آماده کنید و در صورت لزوم واکسیناسیون و بررسی سلامت را نیز انجام دهید. گردشگری خروجی یا برون&zwnj;مرزی (Outbound tourism) گردشگری برون&zwnj;مرزی، زمانی است که شما از کشور خود به کشور دیگری می&zwnj;روید. همان مثال قبلی را در نظر بگیرید، یعنی شما قصد دارید از ایران به اسپانیا سفر کنید، در این حالت این گردشگری خروجی برای ایران حساب می&zwnj;شود، زیرا شما در حال ترک این کشور هستید. گردشگری برون&zwnj;مرزی هم شکلی از سفرهای بین&zwnj;المللی است و مجموعه از الزامات رسمی خود را دارد که باید آنها را رعایت کنید. آژانس&zwnj;های مسافرتی باعث راحت&zwnj;تر شدن این فرایند می&zwnj;شوند. ۱۳ نوع گردشگری با توجه به انگیزه ابتدا اشاره کردیم که انواع مختلف گردشگری به ۱۳ نوع تقسیم می&zwnj;شود. این دسته بندی براساس انگیزه&zwnj;ها، اهداف و نیازهای مسافران تعریف می&zwnj;شود. در اینجا ما به انواع اصلی و مهم که سازمان جهانی گردشگری معرفی کرده است خواهیم پرداخت. انواع مختلف گردشگری گردشگری ماجراجویی گردشگری ماجراجویی که به سفر ماجراجویی نیز معروف است، نوعی از این صنعت است که در میان ماجراجویان و آن هایی که همیشه به دنبال کارهای هیجان انگیز بوده اند، بسیار محبوب می باشد. برای اینکه جزو گردشگران این نوع باشید، نیاز دارید که ریسک پذیر باشید و تمرین های ورزشی متفاوتی را انجام دهید. به طور کلی، گردشگران ماجراجویی در فعالیت های دشوار و ورزش هایی مانند کوهنوردی، پیاده روی در کویر، بانجی جامپینگ، غواصی، پرواز با پاراگلایدر، زیپ لاین، صخره نوردی و غیره به کار خود ادامه می دهند. مقصدهای محبوب این نوع گردشگری، نپال برای کوهنوردی، کرواسی برای صخره نوردی و دوچرخه سواری در کوه، نیوزیلند برای اسکی هستند. گردشگری تجاری گردشگری تجاری یا سفر تجاری زیر مجموعه&zwnj;ای از انواع مختلف گردشگری است که در آن مسافران به دلایل حرفه به مکان دیگری (داخلی یا بین&zwnj;المللی) می&zwnj;روند، برای مثال: ملاقات با شرکای تجاری یا بالقوه شرکت در یک رویداد، کنفرانس یا نمایشگاه تجاری مراجعه به شعبه&zwnj;های دیگر شرکت خرید بلیط آنلاین سفرهای کاری را متحول کرده است و در حالی که راه&zwnj;های زیادی برای مدیریت سفرهای کاری وجود دارد، یکی از کارآمدترین روش&zwnj;ها برای مدیران و کارکنان، استفاده&zwnj;کردن از این پلتفرم&zwnj;ها است. پلتفرمی که کمک می&zwnj;کند تا برنامه&zwnj;های سفر را برنامه&zwnj;ریزی کنید، رزروهای خود را انجام دهید و همچنین هزینه&zwnj;های سفر را پیگیری کنید. برای مثال الی گشت یکی از پلتفرم&zwnj;هایی است که به راحتی به صورت آنلاین می&zwnj;توانید بلیت سفر خود را تهیه کنید. گاهی اوقات، مسافران تجاری بخشی از سفر خود برای اوقات فراغت تمدید می&zwnj;کنند. در این حالت تجارت و اوقات فراغت ترکیب می&zwnj;شود و اصطلاح &laquo;Bleisure&raquo; شکل می&zwnj;گیرد. انواع مختلف گردشگری گردشگری فرهنگی یکی دیگر از انواع مختلف گردشگری، گردشگری فرهنگی است. اگر به فرهنگ کشورهای مختلف علاقه&zwnj;مند هستید، این نوع گردشگری مناسب شما است. در این نوع گردشگری شما می&zwnj;توانید میراث&zwnj;فرهنگی کشوری دیگر را ببینید. میراثی که شامل بناهای تاریخی، ادبیات، مذهب، جشنواره&zwnj;ها، تئاتر، موسیقی، غذا و موارد گوناگون دیگر می&zwnj;شود. اروپا به دلیل داشتن تاریخ غنی خود سالانه از تعداد زیادی گردشگر پذیرایی می&zwnj;کند. اگر دوست دارید مناطق فرهنگی و مهم را بشناسید، پیشنهاد می&zwnj;کنیم نگاهی به فهرست میراث جهانی یونسکو داشته باشید. گردشگری در اوقات فراغت گردشگری اوقات فراغت و تفریح مقوله وسیعی را در بر می&zwnj;گیرد. در این بخش گردشگری ماجراجویی، اکوتوریسم، گردشگری فرهنگی، گردشگری شهری و بسیاری موارد دیگر قرار می&zwnj;گیرند. گردشگری اوقات فراغت به&zwnj;سادگی به عنوان گردشگری تعریف می&zwnj;شود که شما در وقت خالی خود انجامش می&zwnj;دهید. جاذبه&zwnj;های توریستی محلی اغلب کانون اصلی گردشگری تفریحی یا اوقات فراغت هستند. غذا نیز یک انگیزه اصلی برای بسیاری از مسافرانی است که تفریحی سفر می&zwnj;کنند. بسیاری از گردشگران برای چشیدن غذاهای اصیل محلی از مقاصد جدید بازی می&zwnj;کنند. گردشگری غذایی را نیز می&zwnj;توان زیر مجموعه گردشگری تفریحی تعریف کرد. گردشگری خرید کسی هست که دوست نداشته باشد با یک چمدان پر از اقلام عجیب و غریب و لباس&zwnj;های نو به خانه بازگردد؟ گردشگری خرید یک نوع سفر محبوب در میان کسانی است که می&zwnj;خواهند برندها و اشیایی را خریداری کنند که به صورت محلی در دسترس نیستند. سفر به شهرهایی مانند استانبول، وان، میلان، پاریس از مقاصد گردشگری معروف برای خرید هستند.</p>', 'انواع مختلف گردشگری وجود دارد که هر یک تعریف خاص خود را دارند. گردشگری تنها جنبه تفریحی ندارد و افراد با انگیزه‌های مختلفی سفر خود را انجام می‌دهند. در این …'),
(11, 4, 'en', '', '', ''),
(12, 4, 'az', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `blog_tags`
--

CREATE TABLE `blog_tags` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price_per_night` decimal(10,2) NOT NULL,
  `video_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `image`, `price_per_night`, `video_url`, `created_at`) VALUES
(1, '1755299928_room1-0.jpg', '20.00', NULL, '2025-08-15 23:18:48'),
(2, '1755300451_room1-1.jpg', '21000.00', NULL, '2025-08-15 23:27:31'),
(3, '1755445640_93caf59b2138d17e0ac052d730a1bcda6cee0c1dfe079e303d968981804b8016-01c1c4cd48_v0_w1280xh0_rDEF.jpg', '20.00', '', '2025-08-17 15:47:20');

-- --------------------------------------------------------

--
-- Table structure for table `room_gallery_images`
--

CREATE TABLE `room_gallery_images` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `alt_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `room_gallery_images`
--

INSERT INTO `room_gallery_images` (`id`, `room_id`, `image_path`, `sort_order`, `alt_text`, `created_at`) VALUES
(1, 3, '1755445640_0bb87afd4c53181e3a755b4cc49ddfd99b72eb7e01fc5727b9f62c65a8b3c4b2-01c1c4cd48_v0_w1280xh0_rDEF.jpg', 1, NULL, '2025-08-17 15:47:20'),
(2, 3, '1755445640_8f06bff2acd88a3bad9fdc72208dbdc165609d5eead27e78e39d0a603d22aa82-01c1c4cd48_v0_w1280xh0_rDEF.jpg', 2, NULL, '2025-08-17 15:47:20'),
(3, 3, '1755445640_9c2bbecbb3f3bfb955ba387cf82b053647e81b3686e4f9c4b2e43bb2914c72f7-01c1c4cd48_v0_w1280xh0_rDEF.jpg', 3, NULL, '2025-08-17 15:47:20'),
(4, 3, '1755445640_23cecfe74c77ed35567e138c7b0389c97addccbb82602b20d253eefe5358a2d4-01c1c4cd48_v0_w1280xh0_rDEF.jpg', 4, NULL, '2025-08-17 15:47:20'),
(5, 3, '1755445640_810a18940228c7f52ccad9491888def8d7c490f1d1c375e05c8594babc9e2da4-01c1c4cd48_v0_w1280xh0_rDEF.jpg', 5, NULL, '2025-08-17 15:47:20');

-- --------------------------------------------------------

--
-- Table structure for table `room_images`
--

CREATE TABLE `room_images` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `room_images`
--

INSERT INTO `room_images` (`id`, `room_id`, `image_url`) VALUES
(1, 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `room_reviews`
--

CREATE TABLE `room_reviews` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `customer_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` tinyint(1) NOT NULL COMMENT 'امتیاز از ۱ تا ۵',
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending, approved',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `room_reviews`
--

INSERT INTO `room_reviews` (`id`, `room_id`, `customer_name`, `rating`, `comment`, `status`, `created_at`) VALUES
(6, 2, 'بارزان قاطع', 5, 'من توی این هتل اقامت داشتم تجربه عالی بود', 'approved', '2025-08-17 13:19:47');

-- --------------------------------------------------------

--
-- Table structure for table `room_translations`
--

CREATE TABLE `room_translations` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `lang_code` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `room_translations`
--

INSERT INTO `room_translations` (`id`, `room_id`, `lang_code`, `name`, `description`, `short_description`) VALUES
(1, 1, 'fa', 'اتاق دو نفره ', 'اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره ', 'اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره '),
(2, 1, 'en', '', '', ''),
(3, 1, 'az', '', '', ''),
(4, 2, 'fa', 'اتاق دو نفره ', 'اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره ', 'اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره '),
(5, 2, 'en', '', '', ''),
(6, 2, 'az', '', '', ''),
(7, 3, 'fa', 'اتاق دو نفره ', 'اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره ', 'اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره اتاق دو نفره '),
(8, 3, 'en', '', '', ''),
(9, 3, 'az', '', '', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `blog_categories`
--
ALTER TABLE `blog_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blog_post_categories`
--
ALTER TABLE `blog_post_categories`
  ADD PRIMARY KEY (`post_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `blog_post_tags`
--
ALTER TABLE `blog_post_tags`
  ADD PRIMARY KEY (`post_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `blog_post_translations`
--
ALTER TABLE `blog_post_translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `blog_tags`
--
ALTER TABLE `blog_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `room_gallery_images`
--
ALTER TABLE `room_gallery_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_room_gallery` (`room_id`,`sort_order`);

--
-- Indexes for table `room_images`
--
ALTER TABLE `room_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `room_reviews`
--
ALTER TABLE `room_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `room_translations`
--
ALTER TABLE `room_translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `blog_categories`
--
ALTER TABLE `blog_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `blog_post_translations`
--
ALTER TABLE `blog_post_translations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `blog_tags`
--
ALTER TABLE `blog_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `room_gallery_images`
--
ALTER TABLE `room_gallery_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `room_images`
--
ALTER TABLE `room_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `room_reviews`
--
ALTER TABLE `room_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `room_translations`
--
ALTER TABLE `room_translations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blog_post_categories`
--
ALTER TABLE `blog_post_categories`
  ADD CONSTRAINT `blog_post_categories_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `blog_post_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `blog_post_tags`
--
ALTER TABLE `blog_post_tags`
  ADD CONSTRAINT `blog_post_tags_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `blog_post_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `blog_tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `blog_post_translations`
--
ALTER TABLE `blog_post_translations`
  ADD CONSTRAINT `fk_blog_translation` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `room_gallery_images`
--
ALTER TABLE `room_gallery_images`
  ADD CONSTRAINT `room_gallery_images_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `room_images`
--
ALTER TABLE `room_images`
  ADD CONSTRAINT `fk_room_image` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `room_reviews`
--
ALTER TABLE `room_reviews`
  ADD CONSTRAINT `fk_room_review` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `room_translations`
--
ALTER TABLE `room_translations`
  ADD CONSTRAINT `fk_room_translation` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
