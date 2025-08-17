<?php
// تنظیمات اتصال به پایگاه داده
define('DB_HOST', 'localhost'); // آدرس هاست پایگاه داده (معمولا localhost)
define('DB_USER', 'root');      // نام کاربری پایگاه داده
define('DB_PASS', '');          // رمز عبور پایگاه داده (اگر ندارید خالی بگذارید)
define('DB_NAME', 'hotel_db'); // نام پایگاه داده‌ای که ساختید

// ایجاد اتصال با استفاده از MySQLi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// بررسی وضعیت اتصال
if ($conn->connect_error) {
    // اگر اتصال برقرار نشد، برنامه را متوقف کن و خطا را نمایش بده
    die("Connection failed: " . $conn->connect_error);
}

// تنظیم اینکدینگ کاراکترها روی UTF-8 برای پشتیبانی کامل از زبان فارسی
$conn->set_charset("utf8mb4");

// شروع session برای مدیریت ورود کاربر (لاگین)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>