-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 17, 2025 at 04:51 PM
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `image`, `created_at`) VALUES
(3, '1755353441_Screenshot 2025-05-13 025625.png', '2025-08-16 14:10:41'),
(4, '1755354639_tourism2.jpg', '2025-08-16 14:30:39');

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
  `summary` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_post_translations`
--

INSERT INTO `blog_post_translations` (`id`, `post_id`, `lang_code`, `title`, `content`, `summary`) VALUES
(7, 3, 'fa', 'حالاچه بخشهایی باقی موندهحالاچه بخشهایی باقی موندهحالاچه بخشهایی باقی مونده', 'https://t.me/c/1142372981/190207https://t.me/c/1142372981/190207https://t.me/c/1142372981/190207https://t.me/c/1142372981/190207https://t.me/c/1142372981/190207https://t.me/c/1142372981/190207https://t.me/c/1142372981/190207https://t.me/c/1142372981/190207', 'https://t.me/c/1142372981/190207https://t.me/c/1142372981/190207https://t.me/c/1142372981/190207https://t.me/c/1142372981/190207https://t.me/c/1142372981/190207'),
(8, 3, 'en', '', '', ''),
(9, 3, 'az', '', '', ''),
(10, 4, 'fa', 'معرفی انواع مختلف گردشگری', 'انواع مختلف گردشگری\r\nصنعت گردشگری سهم عمده‌ای در اقتصادهای محلی در کشورهای مختلف دارد. قبل از همه گیری کووید-۱۹، سهم گردشگری معادل ۱۰.۳ درصد از تولید ناخالص داخلی جهانی را تشکیل می‌داد که البته، دوباره در مسیر رشد قرار گرفته است. اما گردشگری مدل‌های مختلفی دارد. انواع مختلف گردشگری را می‌شناسید؟ ما قصد دارید طبق تعاریف سازمان جهانی گردشگری سازمان ملل (UNWTO) هشت نوع گردشگری اصلی را معرفی کنیم. البته، ابتدا گردشگری را به سه دسته اصلی تقسیم می‌کنیم و به تعریف هر یک از آنها خواهیم پرداخت.\r\n\r\nسه شکل اصلی گردشگری\r\nابتدا قصد داریم از نظر سفر و ورود و خروج انواع مختلف گردشگری را بررسی کنیم. طبق آنچه سازمان جهانی گردشگری تعریف کرده است ما سه دسته اصلی داریم که شامل: گردشگری داخلی، گردشگری ورودی (inbound tourism) و گردشگری برون‌مرزی (Outbound tourism) می‌شود. در ادامه هر کدام را با مثال‌هایی بررسی خواهیم کرد.\r\n\r\nانواع مختلف گردشگری\r\n\r\nمطالب مرتبط:\r\nچگونه اکوتوریسم وارد ادبیات گردشگری شد؟\r\nبا شعارهای گردشگری کشورهای آسیا آشنایید؟ گردشگری سیاه چیست ؟\r\nگردشگری داخلی (Domestic tourism)\r\nگردشگری داخلی به عنوان مسافرت در کشور محل سکونت شما با اهداف تجاری یا تفریحی تعریف می‌شود. معمولاً سازماندهی این نوع سفرها آسان‌تر از سفرهای بین‌المللی است چون به مدارک اضافی، بررسی‌های بهداشتی و مسائلی از این دست نیاز ندارد. در نتیجه به راحتی با یک پرواز داخلی، اتوبوس یا قطار به مقصد خود خواهید رسید. برای مثال، شما بلیط هواپیما تهران به شیراز را به راحتی از مجموعه الی گشت تهیه می‌کنید، دیگر نیاز به ویزا و ارائه گواهی تمکن مالی و چنین مدارکی نخواهید داشت.\r\n\r\nعلاوه بر آن موانع زبانی و شوک فرهنگی هم در این حالت وجود ندارد. در نتیجه، گردشگران استرس کمتری برای انجام این سفرها خواهند داشت.\r\n\r\nگردشگری درون یا ورودی (Inbound tourism)\r\nوقتی وارد کشور دیگری می‌شود، این گردشگری ورودی برای کشور مقصد به حساب می‌آید. یعنی اگر از ایران به اسپانیا سفر می‌کنید، این گردشگری ورودی برای اسپانیا به حساب می‌آید. گردشگری ورودی نوعی گردشگری بین‌المللی است. در واقع سازمان جهانی گردشگری می‌توانست تنها دودسته داخلی و بین‌المللی را تعریف کند اما به نظر می‌رسد با ارائه تعریف‌های گردشگری ورودی و خروجی سعی کرده این مسئله را پیچیده کند.\r\n\r\nهمان‌طور که اشاره کردیم گردشگری ورودی یک نوع بین‌المللی است، پس باید همه مدارک لازم را از قبل آماده کنید و در صورت لزوم واکسیناسیون و بررسی سلامت را نیز انجام دهید.\r\n\r\nگردشگری خروجی یا برون‌مرزی (Outbound tourism)\r\nگردشگری برون‌مرزی، زمانی است که شما از کشور خود به کشور دیگری می‌روید. همان مثال قبلی را در نظر بگیرید، یعنی شما قصد دارید از ایران به اسپانیا سفر کنید، در این حالت این گردشگری خروجی برای ایران حساب می‌شود، زیرا شما در حال ترک این کشور هستید.\r\n\r\nگردشگری برون‌مرزی هم شکلی از سفرهای بین‌المللی است و مجموعه از الزامات رسمی خود را دارد که باید آنها را رعایت کنید. آژانس‌های مسافرتی باعث راحت‌تر شدن این فرایند می‌شوند.\r\n\r\n۱۳ نوع گردشگری با توجه به انگیزه\r\nابتدا اشاره کردیم که انواع مختلف گردشگری به ۱۳ نوع تقسیم می‌شود. این دسته بندی براساس انگیزه‌ها، اهداف و نیازهای مسافران تعریف می‌شود. در اینجا ما به انواع اصلی و مهم که سازمان جهانی گردشگری معرفی کرده است خواهیم پرداخت.\r\n\r\nانواع مختلف گردشگری\r\n\r\nگردشگری ماجراجویی\r\nگردشگری ماجراجویی که به سفر ماجراجویی نیز معروف است، نوعی از این صنعت است که در میان ماجراجویان و آن هایی که همیشه به دنبال کارهای هیجان انگیز بوده اند، بسیار محبوب می باشد. برای اینکه جزو گردشگران این نوع باشید، نیاز دارید که ریسک پذیر باشید و تمرین های ورزشی متفاوتی را انجام دهید. به طور کلی، گردشگران ماجراجویی در فعالیت های دشوار و ورزش هایی مانند کوهنوردی، پیاده روی در کویر، بانجی جامپینگ، غواصی، پرواز با پاراگلایدر، زیپ لاین، صخره نوردی و غیره به کار خود ادامه می دهند. مقصدهای محبوب این نوع گردشگری، نپال برای کوهنوردی، کرواسی برای صخره نوردی و دوچرخه سواری در کوه، نیوزیلند برای اسکی هستند.\r\n\r\nگردشگری تجاری\r\nگردشگری تجاری یا سفر تجاری زیر مجموعه‌ای از انواع مختلف گردشگری است که در آن مسافران به دلایل حرفه به مکان دیگری (داخلی یا بین‌المللی) می‌روند، برای مثال:\r\n\r\nملاقات با شرکای تجاری یا بالقوه\r\nشرکت در یک رویداد، کنفرانس یا نمایشگاه تجاری\r\nمراجعه به شعبه‌های دیگر شرکت\r\nخرید بلیط آنلاین سفرهای کاری را متحول کرده است و در حالی که راه‌های زیادی برای مدیریت سفرهای کاری وجود دارد، یکی از کارآمدترین روش‌ها برای مدیران و کارکنان، استفاده‌کردن از این پلتفرم‌ها است. پلتفرمی که کمک می‌کند تا برنامه‌های سفر را برنامه‌ریزی کنید، رزروهای خود را انجام دهید و همچنین هزینه‌های سفر را پیگیری کنید. برای مثال الی گشت یکی از پلتفرم‌هایی است که به راحتی به صورت آنلاین می‌توانید بلیت سفر خود را تهیه کنید.\r\n\r\nگاهی اوقات، مسافران تجاری بخشی از سفر خود برای اوقات فراغت تمدید می‌کنند. در این حالت تجارت و اوقات فراغت ترکیب می‌شود و اصطلاح «Bleisure» شکل می‌گیرد.\r\n\r\nانواع مختلف گردشگری\r\n\r\nگردشگری فرهنگی\r\nیکی دیگر از انواع مختلف گردشگری، گردشگری فرهنگی است. اگر به فرهنگ کشورهای مختلف علاقه‌مند هستید، این نوع گردشگری مناسب شما است. در این نوع گردشگری شما می‌توانید میراث‌فرهنگی کشوری دیگر را ببینید. میراثی که شامل بناهای تاریخی، ادبیات، مذهب، جشنواره‌ها، تئاتر، موسیقی، غذا و موارد گوناگون دیگر می‌شود. اروپا به دلیل داشتن تاریخ غنی خود سالانه از تعداد زیادی گردشگر پذیرایی می‌کند. اگر دوست دارید مناطق فرهنگی و مهم را بشناسید، پیشنهاد می‌کنیم نگاهی به فهرست میراث جهانی یونسکو داشته باشید.\r\n\r\nگردشگری در اوقات فراغت\r\nگردشگری اوقات فراغت و تفریح مقوله وسیعی را در بر می‌گیرد. در این بخش گردشگری ماجراجویی، اکوتوریسم، گردشگری فرهنگی، گردشگری شهری و بسیاری موارد دیگر قرار می‌گیرند. گردشگری اوقات فراغت به‌سادگی به عنوان گردشگری تعریف می‌شود که شما در وقت خالی خود انجامش می‌دهید.\r\n\r\nجاذبه‌های توریستی محلی اغلب کانون اصلی گردشگری تفریحی یا اوقات فراغت هستند. غذا نیز یک انگیزه اصلی برای بسیاری از مسافرانی است که تفریحی سفر می‌کنند. بسیاری از گردشگران برای چشیدن غذاهای اصیل محلی از مقاصد جدید بازی می‌کنند. گردشگری غذایی را نیز می‌توان زیر مجموعه گردشگری تفریحی تعریف کرد.\r\n\r\nگردشگری خرید\r\nکسی هست که دوست نداشته باشد با یک چمدان پر از اقلام عجیب و غریب و لباس‌های نو به خانه بازگردد؟ گردشگری خرید یک نوع سفر محبوب در میان کسانی است که می‌خواهند برندها و اشیایی را خریداری کنند که به صورت محلی در دسترس نیستند. سفر به شهرهایی مانند استانبول، وان، میلان، پاریس از مقاصد گردشگری معروف برای خرید هستند.', 'انواع مختلف گردشگری وجود دارد که هر یک تعریف خاص خود را دارند. گردشگری تنها جنبه تفریحی ندارد و افراد با انگیزه‌های مختلفی سفر خود را انجام می‌دهند. در این …'),
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `image`, `price_per_night`, `created_at`) VALUES
(1, '1755299928_room1-0.jpg', '20.00', '2025-08-15 23:18:48'),
(2, '1755300451_room1-1.jpg', '21000.00', '2025-08-15 23:27:31');

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
(5, 2, 'سامان احمدی', 5, 'تجربه عالی بود ', 'approved', '2025-08-17 13:19:11'),
(6, 2, 'بارزان قاطع', 5, 'من توی این هتل اقامت داشتم تجربه عالی بود', 'pending', '2025-08-17 13:19:47');

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
(6, 2, 'az', '', '', '');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
