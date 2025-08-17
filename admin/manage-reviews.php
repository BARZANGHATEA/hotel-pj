<?php
include_once 'partials/header.php';

// --- شروع بخش بهبودیافته پردازش ---

// برای نمایش پیام بازخورد از سشن استفاده می‌کنیم
if (isset($_SESSION['flash_message'])) {
    echo "<div class='alert success'>" . $_SESSION['flash_message'] . "</div>";
    unset($_SESSION['flash_message']); // پیام را بعد از نمایش پاک می‌کنیم
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
        // بررسی می‌کنیم که آماده‌سازی کوئری موفقیت‌آمیز بوده یا نه
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
// --- پایان بخش بهبودیافته پردازش ---
?>

<div class="content-header">
    <h1>مدیریت نظرات اتاق‌ها</h1>
</div>

<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>اتاق</th>
                <th>نام مشتری</th>
                <th>امتیاز</th>
                <th>نظر</th>
                <th>تاریخ ثبت</th> <th>وضعیت</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // واکشی تمام نظرات به همراه نام اتاق
            $reviews_list = $conn->query("
                SELECT rr.*, rt.name as room_name 
                FROM room_reviews rr
                JOIN room_translations rt ON rr.room_id = rt.room_id AND rt.lang_code = 'fa'
                ORDER BY rr.created_at DESC
            ");
            while ($review = $reviews_list->fetch_assoc()):
            ?>
            <tr>
                <td><?php echo htmlspecialchars($review['room_name']); ?></td>
                <td><?php echo htmlspecialchars($review['customer_name']); ?></td>
                <td><?php echo str_repeat('★', $review['rating']); ?></td>
                <td><?php echo mb_substr(htmlspecialchars($review['comment']), 0, 50) . '...'; ?></td>
                <td><?php echo date("Y-m-d", strtotime($review['created_at'])); ?></td> <td>
                    <?php if ($review['status'] == 'approved'): ?>
                        <span class="status approved">تایید شده</span>
                    <?php else: ?>
                        <span class="status pending">در انتظار تایید</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($review['status'] == 'pending'): ?>
                        <a href="?action=approve&id=<?php echo $review['id']; ?>" class="btn btn-sm btn-success">تایید</a>
                    <?php else: ?>
                        <a href="?action=reject&id=<?php echo $review['id']; ?>" class="btn btn-sm btn-warning">رد کردن</a>
                    <?php endif; ?>
                    <a href="?action=delete&id=<?php echo $review['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('آیا مطمئن هستید؟')">حذف</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<style>
    .status { padding: 3px 8px; color: white; border-radius: 4px; font-size: 12px; }
    .status.approved { background-color: #28a745; }
    .status.pending { background-color: #ffc107; color: #333; }
    .btn-success { background-color: #28a745; }
    .btn-warning { background-color: #ffc107; color: #333; }
    /* استایل برای پیام بازخورد */
    .alert.success {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
        background-color: #d4edda;
        color: #155724;
    }
</style>

<?php include_once 'partials/footer.php'; ?>