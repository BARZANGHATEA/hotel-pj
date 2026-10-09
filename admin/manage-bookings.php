<?php
// تمام پردازش‌ها قبل از هرگونه خروجی HTML انجام می‌شوند
require_once 'auth-check.php';

$statuses = ['pending', 'confirmed', 'cancelled'];
$per_page = 20;

// پیام بازخورد
$flash_message = $_SESSION['flash_message'] ?? '';
$flash_type = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

function bookings_flash($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

// پردازش اکشن‌ها (تغییر وضعیت، یادداشت، حذف)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $booking_id = intval($_POST['booking_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $return_to = 'manage-bookings.php' . (!empty($_POST['return_query']) ? '?' . preg_replace('/[^a-zA-Z0-9_=&%+\-]/', '', $_POST['return_query']) : '');

    $stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$booking) {
        bookings_flash('رزرو مورد نظر یافت نشد.', 'error');
    } elseif ($action === 'set_status' && in_array($_POST['status'] ?? '', $statuses, true)) {
        $new_status = $_POST['status'];
        // قبل از تایید، تداخل با رزروهای تایید شده دیگر بررسی می‌شود
        if ($new_status === 'confirmed' && !room_is_available($conn, $booking['room_id'], $booking['check_in'], $booking['check_out'], $booking['id'])) {
            bookings_flash('این اتاق در بازه تاریخ این رزرو، رزرو تایید شده دیگری دارد. ابتدا تداخل را برطرف کنید.', 'error');
        } else {
            $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $new_status, $booking_id);
            $stmt->execute();
            $stmt->close();
            bookings_flash('وضعیت رزرو به «' . booking_status_label($new_status) . '» تغییر کرد.');
        }
    } elseif ($action === 'save_note') {
        $note = trim($_POST['admin_note'] ?? '');
        $stmt = $conn->prepare("UPDATE bookings SET admin_note = ? WHERE id = ?");
        $stmt->bind_param("si", $note, $booking_id);
        $stmt->execute();
        $stmt->close();
        bookings_flash('یادداشت ذخیره شد.');
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $stmt->close();
        bookings_flash('رزرو حذف شد.');
    }

    header('Location: ' . $return_to);
    exit();
}

// فیلترها
$status_filter = in_array($_GET['status'] ?? '', $statuses, true) ? $_GET['status'] : '';
$search = trim($_GET['q'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));

$where = [];
$types = '';
$params = [];
if ($status_filter !== '') {
    $where[] = 'b.status = ?';
    $types .= 's';
    $params[] = $status_filter;
}
if ($search !== '') {
    // جستجو با نام، تلفن، ایمیل یا کد پیگیری (مثل BK-00012)
    $ref_id = preg_match('/^(?:BK-)?0*(\d+)$/i', $search, $m) ? intval($m[1]) : 0;
    $where[] = '(b.guest_name LIKE ? OR b.phone LIKE ? OR b.email LIKE ? OR b.id = ?)';
    $like = '%' . $search . '%';
    $types .= 'sssi';
    array_push($params, $like, $like, $like, $ref_id);
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM bookings b $where_sql");
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total = (int) $stmt->get_result()->fetch_assoc()['c'];
$stmt->close();

$total_pages = max(1, (int) ceil($total / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

$stmt = $conn->prepare("
    SELECT b.*, rt.name AS room_name
    FROM bookings b
    LEFT JOIN room_translations rt ON rt.room_id = b.room_id AND rt.lang_code = 'fa'
    $where_sql
    ORDER BY (b.status = 'pending') DESC, b.check_in ASC, b.id DESC
    LIMIT ? OFFSET ?
");
$list_types = $types . 'ii';
$list_params = array_merge($params, [$per_page, $offset]);
$stmt->bind_param($list_types, ...$list_params);
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// شمارش هر وضعیت برای تب‌ها
$counts = ['' => 0, 'pending' => 0, 'confirmed' => 0, 'cancelled' => 0];
$res = $conn->query("SELECT status, COUNT(*) AS c FROM bookings GROUP BY status");
while ($row = $res->fetch_assoc()) {
    $counts[$row['status']] = (int) $row['c'];
    $counts[''] += (int) $row['c'];
}

$status_styles = [
    'pending'   => 'bg-yellow-100 text-yellow-800',
    'confirmed' => 'bg-green-100 text-green-800',
    'cancelled' => 'bg-gray-200 text-gray-700',
];

function bookings_url(array $overrides = []) {
    global $status_filter, $search, $page;
    $query = array_filter(array_merge(['status' => $status_filter, 'q' => $search, 'page' => $page > 1 ? $page : ''], $overrides), 'strlen');
    return 'manage-bookings.php' . ($query ? '?' . http_build_query($query) : '');
}
$return_query = http_build_query(array_filter(['status' => $status_filter, 'q' => $search, 'page' => $page > 1 ? $page : ''], 'strlen'));
$today = date('Y-m-d');

include_once 'partials/header.php';
?>

<!-- Page Header -->
<div class="flex flex-wrap items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">مدیریت رزروها</h1>
        <p class="text-gray-600 mt-2">بررسی، تایید یا لغو درخواست‌های رزرو مهمانان</p>
    </div>
    <form method="GET" action="manage-bookings.php" class="flex items-center gap-2">
        <?php if ($status_filter): ?><input type="hidden" name="status" value="<?php echo e($status_filter); ?>"><?php endif; ?>
        <input type="search" name="q" value="<?php echo e($search); ?>" placeholder="نام، تلفن یا کد پیگیری..."
               class="w-64 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent">
        <button type="submit" class="bg-hotel-dark text-white px-4 py-2 rounded-lg hover:bg-hotel-dark/90">جستجو</button>
    </form>
</div>

<!-- Flash Message -->
<?php if ($flash_message): ?>
<div class="<?php echo $flash_type === 'error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-green-50 border-green-200 text-green-700'; ?> border px-4 py-3 rounded-lg mb-6">
    <?php echo e($flash_message); ?>
</div>
<?php endif; ?>

<!-- Status Tabs -->
<div class="flex flex-wrap gap-2 mb-6">
    <?php foreach (['' => 'همه', 'pending' => 'در انتظار بررسی', 'confirmed' => 'تایید شده', 'cancelled' => 'لغو شده'] as $key => $label): ?>
        <a href="<?php echo e(bookings_url(['status' => $key, 'page' => ''])); ?>"
           class="px-4 py-2 rounded-full text-sm font-medium border <?php echo $status_filter === $key ? 'bg-hotel-gold border-hotel-gold text-hotel-dark' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'; ?>">
            <?php echo $label; ?> <span class="opacity-70">(<?php echo $counts[$key]; ?>)</span>
        </a>
    <?php endforeach; ?>
</div>

<!-- Bookings Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-900">کد</th>
                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-900">مهمان</th>
                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-900">اتاق</th>
                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-900">تاریخ اقامت</th>
                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-900">مبلغ</th>
                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-900">وضعیت</th>
                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-900">عملیات</th>
                </tr>
            </thead>
            <?php if ($bookings): ?>
                <?php foreach ($bookings as $b): $ref = 'BK-' . str_pad((string) $b['id'], 5, '0', STR_PAD_LEFT); ?>
                <tbody x-data="{ open: false }" class="border-b border-gray-200">
                    <tr class="hover:bg-gray-50 transition-colors duration-200 <?php echo $b['check_out'] < $today ? 'opacity-60' : ''; ?>">
                        <td class="px-4 py-3 text-sm font-mono text-gray-700 whitespace-nowrap"><?php echo $ref; ?></td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900"><?php echo e($b['guest_name']); ?></div>
                            <a href="tel:<?php echo e($b['phone']); ?>" dir="ltr" class="text-sm text-blue-600 hover:underline"><?php echo e($b['phone']); ?></a>
                            <?php if ($b['email']): ?><div class="text-xs text-gray-500" dir="ltr"><?php echo e($b['email']); ?></div><?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <?php echo e($b['room_name'] ?? ('اتاق #' . $b['room_id'])); ?>
                            <div class="text-xs text-gray-500"><?php echo (int) $b['guests']; ?> نفر</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">
                            <div dir="ltr" class="text-right"><?php echo e($b['check_in']); ?> → <?php echo e($b['check_out']); ?></div>
                            <div class="text-xs text-gray-500"><?php echo (int) $b['nights']; ?> شب</div>
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 whitespace-nowrap"><?php echo number_format($b['total_price']); ?> تومان</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $status_styles[$b['status']] ?? ''; ?>">
                                <?php echo e(booking_status_label($b['status'])); ?>
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <?php foreach (['confirmed' => ['تایید', 'bg-green-600 hover:bg-green-700'], 'cancelled' => ['لغو', 'bg-yellow-600 hover:bg-yellow-700'], 'pending' => ['بازگشت به انتظار', 'bg-gray-500 hover:bg-gray-600']] as $st => [$btn_label, $btn_class]): ?>
                                    <?php if ($b['status'] !== $st): ?>
                                    <form method="POST" action="manage-bookings.php" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="booking_id" value="<?php echo (int) $b['id']; ?>">
                                        <input type="hidden" name="action" value="set_status">
                                        <input type="hidden" name="status" value="<?php echo $st; ?>">
                                        <input type="hidden" name="return_query" value="<?php echo e($return_query); ?>">
                                        <button type="submit" class="px-3 py-1.5 text-white text-xs font-medium rounded-lg transition-colors <?php echo $btn_class; ?>"><?php echo $btn_label; ?></button>
                                    </form>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <button type="button" @click="open = !open" class="px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-200">
                                    <span x-text="open ? 'بستن' : 'جزئیات'">جزئیات</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr x-show="open" x-cloak class="bg-gray-50">
                        <td colspan="7" class="px-6 py-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                                <div>
                                    <p class="font-semibold text-gray-700 mb-1">توضیحات مهمان:</p>
                                    <p class="text-gray-600 whitespace-pre-line"><?php echo $b['notes'] !== null && $b['notes'] !== '' ? e($b['notes']) : '—'; ?></p>
                                    <p class="text-xs text-gray-500 mt-3">ثبت شده در: <span dir="ltr"><?php echo e($b['created_at']); ?></span></p>
                                </div>
                                <div>
                                    <form method="POST" action="manage-bookings.php" class="space-y-2">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="booking_id" value="<?php echo (int) $b['id']; ?>">
                                        <input type="hidden" name="action" value="save_note">
                                        <input type="hidden" name="return_query" value="<?php echo e($return_query); ?>">
                                        <label class="font-semibold text-gray-700">یادداشت داخلی:</label>
                                        <textarea name="admin_note" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent"><?php echo e($b['admin_note'] ?? ''); ?></textarea>
                                        <div class="flex justify-between">
                                            <button type="submit" class="px-3 py-1.5 bg-hotel-gold text-hotel-dark text-xs font-semibold rounded-lg">ذخیره یادداشت</button>
                                        </div>
                                    </form>
                                    <form method="POST" action="manage-bookings.php" class="mt-3" onsubmit="return confirm('این رزرو برای همیشه حذف شود؟')">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="booking_id" value="<?php echo (int) $b['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="return_query" value="<?php echo e($return_query); ?>">
                                        <button type="submit" class="text-xs text-red-600 hover:underline">حذف رزرو</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
                <?php endforeach; ?>
            <?php else: ?>
                <tbody>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <?php echo $search !== '' ? 'نتیجه‌ای برای این جستجو یافت نشد.' : 'هنوز رزروی ثبت نشده است.'; ?>
                        </td>
                    </tr>
                </tbody>
            <?php endif; ?>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<div class="flex justify-center gap-2 mt-6">
    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
        <a href="<?php echo e(bookings_url(['page' => $p > 1 ? $p : ''])); ?>"
           class="px-3 py-1.5 rounded-lg text-sm <?php echo $p === $page ? 'bg-hotel-gold text-hotel-dark font-bold' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'; ?>"><?php echo $p; ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<style>[x-cloak] { display: none !important; }</style>

<?php include_once 'partials/footer.php'; ?>
