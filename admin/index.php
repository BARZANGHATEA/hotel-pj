<?php 
// ابتدا احراز هویت و کوئری‌ها، سپس هدر
require_once 'auth-check.php';


// کوئری برای شمارش تعداد کل اتاق‌ها
$total_rooms_result = $conn->query("SELECT COUNT(id) as total FROM rooms");
$total_rooms = $total_rooms_result->fetch_assoc()['total'];

// کوئری برای شمارش تعداد کل مقالات
$total_posts_result = $conn->query("SELECT COUNT(id) as total FROM blog_posts");
$total_posts = $total_posts_result->fetch_assoc()['total'];

// کوئری برای شمارش نظرات در انتظار تایید
$pending_reviews_result = $conn->query("SELECT COUNT(id) as total FROM room_reviews WHERE status = 'pending'");
$pending_reviews = $pending_reviews_result->fetch_assoc()['total'];

// آمار رزروها (اگر جدول bookings هنوز ساخته نشده باشد، صفر نمایش داده می‌شود)
$pending_bookings = 0;
$latest_bookings = [];
$bookings_table_missing = false;
try {
    $pending_bookings = (int) $conn->query("SELECT COUNT(*) AS total FROM bookings WHERE status = 'pending'")->fetch_assoc()['total'];
    $latest_bookings = $conn->query("
        SELECT b.id, b.guest_name, b.check_in, b.check_out, b.status, rt.name AS room_name
        FROM bookings b
        LEFT JOIN room_translations rt ON rt.room_id = b.room_id AND rt.lang_code = 'fa'
        ORDER BY b.created_at DESC
        LIMIT 5
    ")->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $ex) {
    $bookings_table_missing = true;
}

include_once 'partials/header.php';
?>

<!-- Page Header -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">داشبورد</h1>
    <p class="text-gray-600 mt-2">خوش آمدید، <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</p>
</div>

<?php if ($bookings_table_missing): ?>
<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
    جدول رزروها در دیتابیس وجود ندارد. لطفاً فایل <code>config/bookings.sql</code> را یک بار در phpMyAdmin اجرا کنید.
</div>
<?php endif; ?>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Rooms -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100">
                <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                </svg>
            </div>
            <div class="mr-4">
                <p class="text-sm font-medium text-gray-600">تعداد اتاق‌ها</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo $total_rooms; ?></p>
            </div>
        </div>
    </div>

    <!-- Total Posts -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100">
                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 2 2h8c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                </svg>
            </div>
            <div class="mr-4">
                <p class="text-sm font-medium text-gray-600">تعداد مقالات</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo $total_posts; ?></p>
            </div>
        </div>
    </div>

    <!-- Pending Reviews -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100">
                <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
            </div>
            <div class="mr-4">
                <p class="text-sm font-medium text-gray-600">نظرات در انتظار</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo $pending_reviews; ?></p>
            </div>
        </div>
    </div>

    <!-- Pending Bookings -->
    <a href="manage-bookings.php?status=pending" class="block bg-hotel-gold rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-hotel-dark">
                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11zM7 11h5v5H7z"/>
                </svg>
            </div>
            <div class="mr-4">
                <p class="text-sm font-medium text-hotel-dark">رزروهای در انتظار</p>
                <p class="text-2xl font-bold text-hotel-dark"><?php echo $pending_bookings; ?></p>
            </div>
        </div>
    </a>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Quick Actions Card -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">عملیات سریع</h3>
        <div class="space-y-3">
            <a href="manage-bookings.php" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                <div class="p-2 bg-hotel-gold/30 rounded-lg ml-3">
                    <svg class="w-5 h-5 text-hotel-dark" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11zM7 11h5v5H7z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900">مدیریت رزروها</p>
                    <p class="text-sm text-gray-500">تایید و لغو درخواست‌های رزرو</p>
                </div>
            </a>

            <a href="manage-rooms.php" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                <div class="p-2 bg-blue-100 rounded-lg ml-3">
                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900">مدیریت اتاق‌ها</p>
                    <p class="text-sm text-gray-500">افزودن و ویرایش اتاق‌ها</p>
                </div>
            </a>
            
            <a href="manage-blog.php" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                <div class="p-2 bg-green-100 rounded-lg ml-3">
                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 2 2h8c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900">مدیریت وبلاگ</p>
                    <p class="text-sm text-gray-500">نوشتن و ویرایش مقالات</p>
                </div>
            </a>
            
            <a href="manage-reviews.php" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                <div class="p-2 bg-yellow-100 rounded-lg ml-3">
                    <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900">مدیریت نظرات</p>
                    <p class="text-sm text-gray-500">تایید و رد نظرات کاربران</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Latest Bookings -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">آخرین درخواست‌های رزرو</h3>
            <a href="manage-bookings.php" class="text-sm text-blue-600 hover:underline">مشاهده همه</a>
        </div>
        <?php if ($latest_bookings): ?>
        <div class="divide-y divide-gray-100">
            <?php foreach ($latest_bookings as $b): ?>
            <div class="flex items-center justify-between py-3">
                <div>
                    <p class="text-sm font-medium text-gray-900"><?php echo e($b['guest_name']); ?> — <?php echo e($b['room_name'] ?? ''); ?></p>
                    <p class="text-xs text-gray-500" dir="ltr"><?php echo e($b['check_in']); ?> → <?php echo e($b['check_out']); ?></p>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full <?php echo $b['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : ($b['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700'); ?>">
                    <?php echo e(booking_status_label($b['status'])); ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-sm text-gray-500">هنوز درخواست رزروی ثبت نشده است.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Welcome Message -->
<div class="bg-gradient-to-r from-hotel-gold to-yellow-400 rounded-xl p-6 text-hotel-dark">
    <h2 class="text-xl font-bold mb-2">به پنل مدیریت هتل خوش آمدید!</h2>
    <p class="opacity-90">از این پنل می‌توانید تمام بخش‌های سایت را مدیریت کنید. مهمانان اکنون می‌توانند از صفحه هر اتاق، درخواست رزرو آنلاین ثبت کنند.</p>
</div>

<?php 
// فوتر را فراخوانی می‌کنیم
include_once 'partials/footer.php'; 
?>
