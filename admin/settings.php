<?php
// فایل اتصال به دیتابیس را فراخوانی می‌کنیم تا session_start() اجرا شود
require_once '../config/db.php';

// بررسی می‌کنیم آیا session مربوط به ادمین ست شده است یا نه
if (!isset($_SESSION['admin_id'])) {
    // اگر ست نشده بود، کاربر را به صفحه لاگین منتقل کن
    header('Location: login.php');
    exit(); // اجرای اسکریپت را متوقف کن
}
?>