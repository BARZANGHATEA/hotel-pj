<?php
// فایل: includes/bootstrap.php
// راه‌اندازی مشترک صفحات عمومی: اتصال به دیتابیس، سشن و انتخاب زبان.
// صفحاتی که قبل از خروجی HTML نیاز به redirect یا پردازش فرم دارند، این فایل را
// قبل از header.php فراخوانی می‌کنند.
require_once __DIR__ . '/../config/db.php'; // اتصال به دیتابیس و شروع سشن

// --- منطق انتخاب زبان ---
$allowed_langs = ['fa', 'en', 'az'];
$lang_code = 'fa'; // زبان پیش‌فرض

if (isset($_GET['lang']) && in_array($_GET['lang'], $allowed_langs, true)) {
    $lang_code = $_GET['lang'];
    $_SESSION['lang'] = $lang_code;
} elseif (isset($_SESSION['lang']) && in_array($_SESSION['lang'], $allowed_langs, true)) {
    $lang_code = $_SESSION['lang'];
}

// بارگذاری فایل زبان مربوطه
require_once __DIR__ . "/../lang/{$lang_code}.php";

// تعیین جهت صفحه بر اساس زبان
$page_dir = ($lang_code === 'fa') ? 'rtl' : 'ltr';
