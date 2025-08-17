<?php
include_once 'partials/header.php';

// برای نمایش پیام بازخورد از سشن استفاده می‌کنیم
$flash_message = '';
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $review_id = intval($_GET['id']);
    $sql = '';
    $message = '';

    // تعیین کوئری و پیام بر اساس اکشن
    switch ($action) {
        case 'approve':
            $sql = "UPDATE room_reviews SET status = 'approved' WHERE id = ?";
            $message = "نظر با موفقیت تایید شد.";
            break;
        case 'reject':
            $sql = "UPDATE room_reviews SET status = 'pending' WHERE id = ?";
            $message = "نظر با موفقیت به حالت 'در انتظار تایید' بازگشت.";
            break;
        case 'delete':
            $sql = "DELETE FROM room_reviews WHERE id = ?";
            $message = "نظر با موفقیت حذف شد.";
            break;
    }

    // اجرای کوئری فقط اگر sql خالی نباشد
    if (!empty($sql)) {
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $review_id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['flash_message'] = $message;
        } else {
            $_SESSION['flash_message'] = "خطا در اجرای عملیات.";
        }
    }
    
    header("Location: manage-reviews.php");
    exit();
}
?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">مدیریت نظرات</h1>
        <p class="text-gray-600 mt-2">تایید، رد یا حذف نظرات مهمانان</p>
    </div>
    <div class="flex items-center space-x-3 space-x-reverse">
        <div class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">
            <?php
            $pending_count = $conn->query("SELECT COUNT(*) as count FROM room_reviews WHERE status = 'pending'")->fetch_assoc()['count'];
            echo $pending_count . ' نظر در انتظار';
            ?>
        </div>
    </div>
</div>

<!-- Flash Message -->
<?php if (!empty($flash_message)): ?>
<div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
    <div class="flex items-center">
        <svg class="w-5 h-5 ml-2" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
        </svg>
        <?php echo $flash_message; ?>
    </div>
</div>
<?php endif; ?>

<!-- Reviews Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">اتاق</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">نام مشتری</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">امتیاز</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">نظر</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">تاریخ ثبت</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">وضعیت</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">عملیات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php
                // واکشی تمام نظرات به همراه نام اتاق
                $reviews_list = $conn->query("
                    SELECT rr.*, rt.name as room_name 
                    FROM room_reviews rr
                    JOIN room_translations rt ON rr.room_id = rt.room_id AND rt.lang_code = 'fa'
                    ORDER BY rr.created_at DESC
                ");
                
                if ($reviews_list->num_rows > 0):
                    while ($review = $reviews_list->fetch_assoc()):
                ?>
                <tr class="hover:bg-gray-50 transition-colors duration-200">
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($review['room_name']); ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-hotel-gold rounded-full flex items-center justify-center ml-3">
                                <svg class="w-4 h-4 text-hotel-dark" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                </svg>
                            </div>
                            <span class="font-medium text-gray-900"><?php echo htmlspecialchars($review['customer_name']); ?></span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <svg class="w-4 h-4 <?php echo $i <= $review['rating'] ? 'text-hotel-gold' : 'text-gray-300'; ?>" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            <?php endfor; ?>
                            <span class="mr-2 text-sm text-gray-600">(<?php echo $review['rating']; ?>/5)</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-gray-900 max-w-xs">
                            <p class="truncate" title="<?php echo htmlspecialchars($review['comment']); ?>">
                                <?php echo mb_substr(htmlspecialchars($review['comment']), 0, 50) . (mb_strlen($review['comment']) > 50 ? '...' : ''); ?>
                            </p>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        <?php echo date("Y/m/d", strtotime($review['created_at'])); ?>
                    </td>
                    <td class="px-6 py-4">
                        <?php if ($review['status'] == 'approved'): ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                                تایید شده
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 15h2v-6h-2v6zm0-8h2V7h-2v2z"/>
                                </svg>
                                در انتظار تایید
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <?php if ($review['status'] == 'pending'): ?>
                                <a href="?action=approve&id=<?php echo $review['id']; ?>" 
                                   class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700 transition-colors duration-200">
                                    <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                    </svg>
                                    تایید
                                </a>
                            <?php else: ?>
                                <a href="?action=reject&id=<?php echo $review['id']; ?>" 
                                   class="inline-flex items-center px-3 py-1.5 bg-yellow-600 text-white text-xs font-medium rounded-lg hover:bg-yellow-700 transition-colors duration-200">
                                    <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 15h2v-6h-2v6zm0-8h2V7h-2v2z"/>
                                    </svg>
                                    رد کردن
                                </a>
                            <?php endif; ?>
                            <a href="?action=delete&id=<?php echo $review['id']; ?>" 
                               onclick="return confirm('آیا مطمئن هستید که می‌خواهید این نظر را حذف کنید؟')"
                               class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700 transition-colors duration-200">
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                </svg>
                                حذف
                            </a>
                        </div>
                    </td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10m0 0V6a2 2 0 00-2-2H9a2 2 0 00-2 2v2m10 0v10a2 2 0 01-2 2H9a2 2 0 01-2-2V8m10 0H7m5 5v4"/>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">هیچ نظری یافت نشد</h3>
                            <p class="text-gray-500">هنوز هیچ نظری برای اتاق‌ها ثبت نشده است.</p>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once 'partials/footer.php'; ?>
