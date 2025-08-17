<?php

// فایل اتصال به دیتابیس را فراخوانی می‌کنیم
require_once '../config/db.php';

$error_message = ''; // متغیری برای ذخیره پیام‌های خطا

// بررسی می‌کنیم که آیا فرم ارسال شده است (متد POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // برای جلوگیری از SQL Injection، از prepared statements استفاده می‌کنیم
    $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username); // "s" یعنی متغیر از نوع String است
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // اگر کاربری با این نام کاربری پیدا شد
        $admin = $result->fetch_assoc();
        
        // رمز عبور وارد شده را با رمز عبور هش شده در دیتابیس مقایسه می‌کنیم
        if (password_verify($password, $admin['password'])) {
            // اگر رمز عبور صحیح بود
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            
            // کاربر را به داشبورد ادمین هدایت کن
            header("Location: index.php");
            exit(); // خروج از اسکریپت برای اطمینان از اجرای redirect
        } else {
            // اگر رمز عبور اشتباه بود
            $error_message = "نام کاربری یا رمز عبور اشتباه است.";
        }
    } else {
        // اگر کاربری پیدا نشد
        $error_message = "نام کاربری یا رمز عبور اشتباه است.";
    }
    
    $stmt->close();
}

// اگر ادمین از قبل لاگین کرده بود، مستقیم به داشبورد برود
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به پنل مدیریت</title>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .login-container h1 {
            margin-bottom: 25px;
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: right;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box; /* برای اینکه padding به عرض اضافه نشود */
        }
        .btn {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 700;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>ورود به پنل مدیریت</h1>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">نام کاربری</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">رمز عبور</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">ورود</button>
        </form>
    </div>
</body>
</html>