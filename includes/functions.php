
<?php
// فایل: includes/config.php
// تنظیمات اتصال به دیتابیس و تنظیمات عمومی سایت

// تنظیمات دیتابیس
define('DB_HOST', 'localhost');
define('DB_NAME', 'hotel_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// تنظیمات عمومی سایت
define('SITE_NAME', 'هتل پارادایس');
define('SITE_URL', 'http://localhost/hotel-website');
define('ADMIN_EMAIL', 'admin@hotel.com');
define('PHONE_NUMBER', '+98 21 1234 5678');

// شروع session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// اتصال به دیتابیس
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("اتصال به دیتابیس برقرار نشد: " . $e->getMessage());
}

// تابع امنیت برای پاکسازی ورودی‌ها
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// تابع بررسی وضعیت لاگین ادمین
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// تابع ریدایرکت
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// تابع فرمت قیمت
function format_price($price) {
    return number_format($price) . ' تومان';
}

// تابع فرمت تاریخ فارسی
function persian_date($timestamp) {
    $months = array(
        1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر',
        5 => 'مرداد', 6 => 'شهریور', 7 => 'مهر', 8 => 'آبان',
        9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند'
    );
    
    $j_y = jdate('Y', $timestamp);
    $j_m = jdate('n', $timestamp);
    $j_d = jdate('j', $timestamp);
    
    return $j_d . ' ' . $months[$j_m] . ' ' . $j_y;
}
?>

