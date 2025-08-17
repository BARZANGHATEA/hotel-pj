<?php
require_once 'config/db.php'; // اتصال به دیتابیس و شروع سشن

// --- منطق انتخاب زبان ---
$allowed_langs = ['fa', 'en', 'az'];
$lang_code = 'fa'; // زبان پیش‌فرض

if (isset($_GET['lang']) && in_array($_GET['lang'], $allowed_langs)) {
    $lang_code = $_GET['lang'];
    $_SESSION['lang'] = $lang_code;
} elseif (isset($_SESSION['lang'])) {
    $lang_code = $_SESSION['lang'];
}

// --- کد جدید: پردازش فرم ثبت نظر ---
$review_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $customer_name = htmlspecialchars($_POST['customer_name']);
    $rating = intval($_POST['rating']);
    $comment = htmlspecialchars($_POST['comment']);
    // room_id از قبل در متغیر $room_id موجود است

    if (!empty($customer_name) && $rating >= 1 && $rating <= 5 && !empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO room_reviews (room_id, customer_name, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isis", $room_id, $customer_name, $rating, $comment);
        if ($stmt->execute()) {
            $review_message = "<div class='alert success'>نظر شما با موفقیت ثبت شد و پس از تایید نمایش داده خواهد شد.</div>";
        } else {
            $review_message = "<div class='alert error'>خطایی در ثبت نظر رخ داد.</div>";
        }
        $stmt->close();
    } else {
        $review_message = "<div class='alert error'>لطفاً تمام فیلدها را به درستی پر کنید.</div>";
    }
}

// --- واکشی نظرات تایید شده برای این اتاق ---
$reviews_stmt = $conn->prepare("SELECT * FROM room_reviews WHERE room_id = ? AND status = 'approved' ORDER BY created_at DESC");
$reviews_stmt->bind_param("i", $room_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();

// بارگذاری فایل زبان مربوطه
require_once "lang/{$lang_code}.php";

// تعیین جهت صفحه بر اساس زبان
$page_dir = ($lang_code === 'fa') ? 'rtl' : 'ltr';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang_code; ?>" dir="<?php echo $page_dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>هتل مجلل پالاس</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <a href="index.php?lang=<?php echo $lang_code; ?>" class="logo">
                <img src="assets/images/logo-gold.png" alt="Hotel Logo"> </a>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php?lang=<?php echo $lang_code; ?>"><?php echo $lang['nav_home']; ?></a></li>
                    <li><a href="rooms.php?lang=<?php echo $lang_code; ?>"><?php echo $lang['nav_rooms']; ?></a></li>
                    <li><a href="blog.php?lang=<?php echo $lang_code; ?>"><?php echo $lang['nav_blog']; ?></a></li>
                    <li><a href="contact.php?lang=<?php echo $lang_code; ?>"><?php echo $lang['nav_contact']; ?></a></li>
                </ul>
            </nav>
            <div class="header-right">
                <div class="lang-switcher">
                    <a href="?lang=fa" class="<?php echo $lang_code === 'fa' ? 'active' : ''; ?>">FA</a>
                    <a href="?lang=en" class="<?php echo $lang_code === 'en' ? 'active' : ''; ?>">EN</a>
                    <a href="?lang=az" class="<?php echo $lang_code === 'az' ? 'active' : ''; ?>">AZ</a>
                </div>
            </div>
        </div>
    </header>