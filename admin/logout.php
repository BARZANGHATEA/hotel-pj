<?php
// session را شروع می‌کنیم تا به آن دسترسی داشته باشیم
session_start();

// تمام متغیرهای session را پاک می‌کنیم
$_SESSION = array();

// session را از بین می‌بریم
session_destroy();

// کاربر را به صفحه لاگین هدایت می‌کنیم
header("location: login.php");
exit;
?>