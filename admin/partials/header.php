<?php require_once 'auth-check.php'; ?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت هتل</title>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* Reset CSS */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: #f8f9fa;
            direction: rtl;
            display: flex;
        }

        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: #fff;
            height: 100vh;
            position: fixed;
            top: 0;
            right: 0;
            padding-top: 20px;
        }
        .sidebar-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .sidebar-header h3 {
            font-size: 24px;
        }
        .sidebar-menu a {
            display: block;
            padding: 15px 20px;
            color: #adb5bd;
            text-decoration: none;
            transition: background-color 0.3s, color 0.3s;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: #495057;
            color: #fff;
        }
        .sidebar-menu a.logout {
            color: #dc3545;
            position: absolute;
            bottom: 20px;
            width: 100%;
        }
        .sidebar-menu a.logout:hover {
            background-color: #dc3545;
            color: #fff;
        }

        .main-content {
            margin-right: 250px; /* به اندازه عرض سایدبار */
            width: calc(100% - 250px);
            padding: 30px;
        }
        
        .content-header h1 {
            margin-bottom: 20px;
            color: #333;
        }
        
        .card {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>پنل مدیریت</h3>
        </div>
        <nav class="sidebar-menu">
            <a href="index.php" class="active">داشبورد</a>
            <a href="manage-rooms.php">مدیریت اتاق‌ها</a>
            <a href="manage-blog.php">مدیریت وبلاگ</a>
            <a href="manage-reviews.php">مدیریت نظرات</a>
            <a href="logout.php" class="logout">خروج</a>
        </nav>
    </div>
    <div class="main-content">