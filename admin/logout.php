<?php
// session را شروع می‌کنیم تا به آن دسترسی داشته باشیم
session_start();

// تمام متغیرهای session را پاک می‌کنیم
$_SESSION = array();

// کوکی سشن را هم حذف می‌کنیم
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

// session را از بین می‌بریم
session_destroy();

// کاربر را به صفحه لاگین هدایت می‌کنیم
header("Location: login.php");
exit;
