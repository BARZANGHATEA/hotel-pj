<?php
require_once __DIR__ . '/../auth-check.php';

// لینک‌های منوی کناری؛ لینک صفحه فعلی هایلایت می‌شود
$current_admin_page = basename($_SERVER['SCRIPT_NAME']);
$pending_bookings_badge = 0;
try {
    $badge_result = $conn->query("SELECT COUNT(*) AS c FROM bookings WHERE status = 'pending'");
    $pending_bookings_badge = $badge_result ? (int) $badge_result->fetch_assoc()['c'] : 0;
} catch (mysqli_sql_exception $ex) {
    // جدول bookings هنوز ساخته نشده (فایل config/bookings.sql اجرا نشده است)
}
$admin_nav = [
    'index.php'           => ['داشبورد', 'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z', 0],
    'manage-bookings.php' => ['مدیریت رزروها', 'M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11zM7 11h5v5H7z', $pending_bookings_badge],
    'manage-rooms.php'    => ['مدیریت اتاق‌ها', 'M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z', 0],
    'manage-blog.php'     => ['مدیریت وبلاگ', 'M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 2 2h8c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z', 0],
    'manage-reviews.php'  => ['مدیریت نظرات', 'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z', 0],
];
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت هتل</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
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
    <script src="https://cdn.tiny.cloud/1/3fhpj4fbwaga5z3i2uk4yyi9bbfzl62i3nnykuzxyesrio3v/tinymce/8/tinymce.min.js" referrerpolicy="origin"></script>
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: false }">
    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden"
         @click="sidebarOpen = false"></div>

    <!-- Sidebar -->
    <div :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full'"
         class="fixed inset-y-0 right-0 z-50 w-64 bg-hotel-dark transform transition-transform duration-300 ease-in-out lg:translate-x-0">
        
        <!-- Sidebar Header -->
        <div class="flex items-center justify-between h-16 px-6 bg-hotel-dark border-b border-gray-700">
            <div class="flex items-center space-x-3 space-x-reverse">
                <div class="w-8 h-8 bg-hotel-gold rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-hotel-dark" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-white">پنل مدیریت</h2>
            </div>
            <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="mt-6 px-3">
            <div class="space-y-1">
                <?php foreach ($admin_nav as $href => [$label, $icon, $badge]): $active = $href === $current_admin_page; ?>
                <a href="<?php echo $href; ?>" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg group <?php echo $active ? 'text-white bg-hotel-gold/20' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                    <svg class="w-5 h-5 ml-3 <?php echo $active ? 'text-hotel-gold' : 'text-gray-400 group-hover:text-white'; ?>" fill="currentColor" viewBox="0 0 24 24">
                        <path d="<?php echo $icon; ?>"/>
                    </svg>
                    <span class="flex-1"><?php echo $label; ?></span>
                    <?php if ($badge > 0): ?>
                    <span class="bg-hotel-gold text-hotel-dark text-xs font-bold rounded-full px-2 py-0.5"><?php echo $badge; ?></span>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
                <a href="../index.php" target="_blank" class="flex items-center px-3 py-2 text-sm font-medium text-gray-300 rounded-lg hover:bg-gray-700 hover:text-white group">
                    <svg class="w-5 h-5 ml-3 text-gray-400 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 19H5V5h7V3H5a2 2 0 00-2 2v14a2 2 0 002 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/>
                    </svg>
                    مشاهده سایت
                </a>
            </div>

            <!-- Logout Button -->
            <div class="mt-8 pt-6 border-t border-gray-700">
                <a href="logout.php" class="flex items-center px-3 py-2 text-sm font-medium text-red-400 rounded-lg hover:bg-red-600 hover:text-white group">
                    <svg class="w-5 h-5 ml-3 text-red-400 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.59L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                    </svg>
                    خروج
                </a>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="lg:mr-64">
        <!-- Top Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="flex items-center justify-between h-16 px-6">
                <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                
                <div class="flex items-center space-x-4 space-x-reverse">
                    <span class="text-sm text-gray-600">خوش آمدید، <?php echo e($_SESSION['admin_username'] ?? 'مدیر'); ?></span>
                    <div class="w-8 h-8 bg-hotel-gold rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-hotel-dark" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="p-6">
