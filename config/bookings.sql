-- جدول درخواست‌های رزرو اتاق
-- برای دیتابیس‌های موجود: این فایل را یک بار در phpMyAdmin (دیتابیس hotel_db) اجرا کنید.
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `guest_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `guests` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `nights` int(11) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_room_dates` (`room_id`, `check_in`, `check_out`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_booking_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
