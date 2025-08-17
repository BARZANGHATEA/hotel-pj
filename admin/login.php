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
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'hotel-gold': '#FFD700',
                        'hotel-dark': '#1F2937',
                        'hotel-sand': '#F4E1C1',
                        'hotel-cream': '#F5F5F0'
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Vazirmatn', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-hotel-sand to-hotel-cream min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-hotel-gold to-yellow-400 p-8 text-center">
                <div class="w-16 h-16 bg-hotel-dark rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-hotel-gold" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-hotel-dark">پنل مدیریت هتل</h1>
                <p class="text-hotel-dark/80 mt-2">برای ورود اطلاعات خود را وارد کنید</p>
            </div>

            <!-- Form -->
            <div class="p-8">
                <?php if (!empty($error_message)): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 ml-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            <?php echo $error_message; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST" class="space-y-6">
                    <!-- Username Field -->
                    <div>
                        <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">نام کاربری</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                </svg>
                            </div>
                            <input type="text" 
                                   id="username" 
                                   name="username" 
                                   required
                                   class="w-full pr-10 pl-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300"
                                   placeholder="نام کاربری خود را وارد کنید">
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">رمز عبور</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                                </svg>
                            </div>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   required
                                   class="w-full pr-10 pl-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300"
                                   placeholder="رمز عبور خود را وارد کنید">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-hotel-gold to-yellow-400 text-hotel-dark font-bold py-3 px-4 rounded-lg hover:from-yellow-400 hover:to-hotel-gold transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl">
                        ورود به پنل مدیریت
                    </button>
                </form>

                <!-- Footer -->
                <div class="mt-8 text-center">
                    <p class="text-sm text-gray-500">
                        © 2025 هتل پالاس - پنل مدیریت
                    </p>
                </div>
            </div>
        </div>

        <!-- Additional Info -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                در صورت فراموشی رمز عبور با مدیر سیستم تماس بگیرید
            </p>
        </div>
    </div>
</body>
</html>
