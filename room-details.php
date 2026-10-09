<?php
require_once 'includes/bootstrap.php'; // دیتابیس، سشن و زبان (بدون خروجی HTML)

// ۱. گرفتن ID اتاق از URL و اعتبارسنجی آن
if (!isset($_GET['id']) || !ctype_digit((string) $_GET['id'])) {
    // اگر ID وجود نداشت یا معتبر نبود، کاربر را به صفحه اتاق‌ها منتقل کن
    header('Location: rooms.php');
    exit();
}
$room_id = intval($_GET['id']);
$self_url = 'room-details.php?id=' . $room_id . '&lang=' . $lang_code;

// ۲. واکشی اطلاعات اصلی و ترجمه شده اتاق از دیتابیس
$stmt = $conn->prepare("
    SELECT r.image, r.price_per_night, r.video_url, rt.name, rt.description
    FROM rooms r
    JOIN room_translations rt ON r.id = rt.room_id
    WHERE r.id = ? AND rt.lang_code = ?
");
$stmt->bind_param("is", $room_id, $lang_code);
$stmt->execute();
$room = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$room) {
    // اگر اتاقی با این ID پیدا نشد
    http_response_code(404);
    include_once 'includes/header.php';
    echo "<div class='max-w-4xl mx-auto px-4 py-40 text-center'><p class='text-xl text-gray-600'>اتاق مورد نظر یافت نشد.</p>"
       . "<a href='rooms.php?lang=" . e($lang_code) . "' class='inline-block mt-6 text-hotel-dark underline'>بازگشت به لیست اتاق‌ها</a></div>";
    include_once 'includes/footer.php';
    exit();
}

// ۳. پردازش فرم‌ها (ثبت نظر / درخواست رزرو) — الگوی Post/Redirect/Get
$review_errors = [];
$booking_errors = [];
$review_old = ['customer_name' => '', 'rating' => 0, 'comment' => ''];
$booking_old = ['guest_name' => '', 'phone' => '', 'email' => '', 'check_in' => '', 'check_out' => '', 'guests' => 1, 'notes' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    // فیلد مخفی ضد اسپم: ربات‌ها معمولاً آن را پر می‌کنند
    $is_spam = !empty($_POST['website']);

    if (isset($_POST['submit_review'])) {
        $review_old = [
            'customer_name' => trim($_POST['customer_name'] ?? ''),
            'rating'        => intval($_POST['rating'] ?? 0),
            'comment'       => trim($_POST['comment'] ?? ''),
        ];
        if ($review_old['customer_name'] === '' || mb_strlen($review_old['customer_name']) > 100) {
            $review_errors[] = 'نام خود را (حداکثر ۱۰۰ کاراکتر) وارد کنید.';
        }
        if ($review_old['rating'] < 1 || $review_old['rating'] > 5) {
            $review_errors[] = 'لطفاً امتیاز بین ۱ تا ۵ ستاره انتخاب کنید.';
        }
        if ($review_old['comment'] === '' || mb_strlen($review_old['comment']) > 2000) {
            $review_errors[] = 'متن نظر را (حداکثر ۲۰۰۰ کاراکتر) وارد کنید.';
        }

        if (empty($review_errors)) {
            if (!$is_spam) {
                $stmt = $conn->prepare("INSERT INTO room_reviews (room_id, customer_name, rating, comment, status) VALUES (?, ?, ?, ?, 'pending')");
                $stmt->bind_param("isis", $room_id, $review_old['customer_name'], $review_old['rating'], $review_old['comment']);
                $stmt->execute();
                $stmt->close();
            }
            $_SESSION['room_flash'] = ['form' => 'review', 'msg' => 'نظر شما با موفقیت ثبت شد و پس از تایید ادمین نمایش داده خواهد شد.'];
            header('Location: ' . $self_url . '#reviews');
            exit();
        }
    } elseif (isset($_POST['submit_booking'])) {
        $booking_old = [
            'guest_name' => trim($_POST['guest_name'] ?? ''),
            'phone'      => trim($_POST['phone'] ?? ''),
            'email'      => trim($_POST['email'] ?? ''),
            'check_in'   => trim($_POST['check_in'] ?? ''),
            'check_out'  => trim($_POST['check_out'] ?? ''),
            'guests'     => intval($_POST['guests'] ?? 1),
            'notes'      => trim($_POST['notes'] ?? ''),
        ];

        if ($booking_old['guest_name'] === '' || mb_strlen($booking_old['guest_name']) > 100) {
            $booking_errors[] = 'نام و نام خانوادگی را وارد کنید.';
        }
        if (!preg_match('/^\+?[0-9\s\-]{7,20}$/', $booking_old['phone'])) {
            $booking_errors[] = 'شماره تماس معتبر نیست.';
        }
        if ($booking_old['email'] !== '' && !filter_var($booking_old['email'], FILTER_VALIDATE_EMAIL)) {
            $booking_errors[] = 'آدرس ایمیل معتبر نیست.';
        }
        if ($booking_old['guests'] < 1 || $booking_old['guests'] > 10) {
            $booking_errors[] = 'تعداد مهمانان باید بین ۱ تا ۱۰ نفر باشد.';
        }
        if (mb_strlen($booking_old['notes']) > 1000) {
            $booking_errors[] = 'توضیحات حداکثر می‌تواند ۱۰۰۰ کاراکتر باشد.';
        }

        $in  = DateTime::createFromFormat('!Y-m-d', $booking_old['check_in']);
        $out = DateTime::createFromFormat('!Y-m-d', $booking_old['check_out']);
        $today = new DateTime('today');
        $nights = 0;
        if (!$in || !$out || $in->format('Y-m-d') !== $booking_old['check_in'] || $out->format('Y-m-d') !== $booking_old['check_out']) {
            $booking_errors[] = 'تاریخ ورود و خروج را به درستی انتخاب کنید.';
        } elseif ($in < $today) {
            $booking_errors[] = 'تاریخ ورود نمی‌تواند در گذشته باشد.';
        } elseif ($out <= $in) {
            $booking_errors[] = 'تاریخ خروج باید بعد از تاریخ ورود باشد.';
        } else {
            $nights = (int) $in->diff($out)->days;
            if ($nights > 30) {
                $booking_errors[] = 'حداکثر مدت اقامت در یک درخواست ۳۰ شب است.';
            } elseif (!room_is_available($conn, $room_id, $booking_old['check_in'], $booking_old['check_out'])) {
                $booking_errors[] = 'متأسفانه این اتاق در تاریخ‌های انتخابی رزرو شده است. لطفاً تاریخ دیگری انتخاب کنید.';
            }
        }

        if (empty($booking_errors)) {
            $reference = '';
            if (!$is_spam) {
                $total_price = $nights * (float) $room['price_per_night'];
                $email = $booking_old['email'] !== '' ? $booking_old['email'] : null;
                $stmt = $conn->prepare("
                    INSERT INTO bookings (room_id, guest_name, phone, email, check_in, check_out, guests, nights, total_price, notes, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
                ");
                $stmt->bind_param("isssssiids", $room_id, $booking_old['guest_name'], $booking_old['phone'], $email,
                    $booking_old['check_in'], $booking_old['check_out'], $booking_old['guests'], $nights, $total_price, $booking_old['notes']);
                $stmt->execute();
                $reference = 'BK-' . str_pad((string) $stmt->insert_id, 5, '0', STR_PAD_LEFT);
                $stmt->close();
            }
            $_SESSION['room_flash'] = [
                'form' => 'booking',
                'msg'  => 'درخواست رزرو شما ثبت شد. همکاران ما برای تایید نهایی با شما تماس خواهند گرفت.',
                'ref'  => $reference,
            ];
            header('Location: ' . $self_url . '#booking');
            exit();
        }
    }
}

$flash = $_SESSION['room_flash'] ?? null;
unset($_SESSION['room_flash']);

// ۴. تصاویر: تصویر اصلی + گالری جدید (uploads/rooms/gallery) یا جدول قدیمی room_images (uploads/rooms)
$gallery_images = [];
if (!empty($room['image'])) {
    $gallery_images[] = 'uploads/rooms/' . $room['image'];
}
$gstmt = $conn->prepare("SELECT image_path FROM room_gallery_images WHERE room_id = ? ORDER BY sort_order ASC, id ASC");
$gstmt->bind_param("i", $room_id);
$gstmt->execute();
$gresult = $gstmt->get_result();
$has_new_gallery = false;
while ($r = $gresult->fetch_assoc()) {
    $gallery_images[] = 'uploads/rooms/gallery/' . $r['image_path'];
    $has_new_gallery = true;
}
$gstmt->close();

if (!$has_new_gallery) {
    $legacy_stmt = $conn->prepare("SELECT image_url FROM room_images WHERE room_id = ? AND image_url != ''");
    $legacy_stmt->bind_param("i", $room_id);
    $legacy_stmt->execute();
    $legacy_result = $legacy_stmt->get_result();
    while ($row = $legacy_result->fetch_assoc()) {
        $gallery_images[] = 'uploads/rooms/' . $row['image_url'];
    }
    $legacy_stmt->close();
}

// اگر تصویری وجود نداشت، تصویر پیش‌فرض اضافه کن
if (empty($gallery_images)) {
    $gallery_images[] = 'uploads/rooms/default-image.jpg';
}
$gallery_images = array_values(array_unique($gallery_images));

$video_embed = video_embed_url($room['video_url'] ?? '');

// ۵. واکشی نظرات تایید شده برای این اتاق + میانگین امتیاز
$reviews_stmt = $conn->prepare("SELECT customer_name, rating, comment, created_at FROM room_reviews WHERE room_id = ? AND status = 'approved' ORDER BY created_at DESC");
$reviews_stmt->bind_param("i", $room_id);
$reviews_stmt->execute();
$reviews = $reviews_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$reviews_stmt->close();
$avg_rating = $reviews ? round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1) : 0;

$page_title = $room['name'];
include_once 'includes/header.php';
?>

<!-- Room Hero Section -->
<section class="relative min-h-[60vh] flex items-center justify-center overflow-hidden">
    <!-- Background Image -->
    <div class="absolute inset-0">
        <img src="<?php echo e($gallery_images[0]); ?>" 
             alt="<?php echo e($room['name']); ?>"
             class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-black/50"></div>
    </div>
    
    <!-- Hero Content -->
    <div class="relative z-10 text-center text-white px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
        <div class="mb-4">
            <span class="inline-block bg-hotel-gold text-hotel-dark px-4 py-2 rounded-full text-sm font-bold">
                جزئیات اتاق
            </span>
        </div>
        <h1 class="font-playfair text-3xl sm:text-4xl md:text-5xl font-bold mb-6 fade-in-up opacity-0">
            <?php echo htmlspecialchars($room['name']); ?>
        </h1>
        <div class="text-2xl font-bold text-hotel-gold fade-in-up opacity-0 delay-1">
            شروع از <?php echo number_format($room['price_per_night']); ?> تومان در شب
        </div>
        <?php if ($avg_rating > 0): ?>
        <div class="mt-4 text-white/90 fade-in-up opacity-0 delay-2">
            ★ <?php echo $avg_rating; ?> از ۵ (<?php echo count($reviews); ?> نظر)
        </div>
        <?php endif; ?>
        <a href="#booking" class="inline-block mt-8 bg-hotel-gold text-hotel-dark px-8 py-3 rounded-lg font-bold hover:bg-hotel-gold/90 transition-colors duration-300 fade-in-up opacity-0 delay-2">
            درخواست رزرو
        </a>
    </div>
</section>

<!-- Room Gallery & Video Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            <!-- Main Image + Lightbox -->
            <div class="<?php echo $video_embed ? 'lg:col-span-2' : 'lg:col-span-3'; ?>">
                <div class="mb-6 relative rounded-xl overflow-hidden shadow-2xl">
                    <img id="mainGalleryImage" src="<?php echo e($gallery_images[0]); ?>" alt="<?php echo e($room['name']); ?>" class="w-full h-96 lg:h-[520px] object-cover cursor-zoom-in">
                    <button id="openLightboxBtn" type="button" class="absolute top-4 left-4 bg-white/80 text-gray-800 px-3 py-2 rounded-lg text-sm font-semibold hover:bg-white transition-colors">
                        نمایش بزرگ (<span id="galleryCounter">1</span>/<?php echo count($gallery_images); ?>)
                    </button>
                </div>

                <!-- Thumbnails -->
                <div class="flex space-x-4 space-x-reverse mt-4 overflow-x-auto pb-2">
                    <?php foreach ($gallery_images as $index => $image): ?>
                        <button type="button" class="thumb-btn flex-shrink-0 rounded-lg ring-2 ring-transparent transition <?php echo $index === 0 ? 'ring-hotel-gold' : ''; ?>" data-index="<?php echo $index; ?>">
                            <img src="<?php echo e($image); ?>" alt="" loading="lazy" class="w-24 h-24 object-cover rounded-lg">
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Video Section -->
            <?php if ($video_embed): ?>
            <div>
                <div class="bg-hotel-sand rounded-xl p-6 sticky top-24">
                    <h3 class="font-playfair text-xl font-bold mb-4">ویدیو معرفی</h3>
                    <div class="relative w-full rounded overflow-hidden bg-black" style="padding-top: 56.25%;">
                        <iframe src="<?php echo e($video_embed); ?>" title="ویدیو معرفی اتاق" loading="lazy"
                                allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen" allowfullscreen
                                class="absolute inset-0 w-full h-full border-0"></iframe>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Lightbox Modal -->
    <div id="lightbox" class="hidden fixed inset-0 z-[60] bg-black/90 flex items-center justify-center p-4" role="dialog" aria-modal="true">
        <button id="closeLightbox" type="button" aria-label="بستن" class="absolute top-6 right-6 text-white text-4xl leading-none">×</button>
        <button id="prevImg" type="button" aria-label="قبلی" class="absolute left-4 md:left-8 text-white text-5xl px-3">‹</button>
        <img id="lightboxImg" src="" alt="" class="max-w-full max-h-[90vh] rounded-lg">
        <button id="nextImg" type="button" aria-label="بعدی" class="absolute right-4 md:right-8 text-white text-5xl px-3">›</button>
    </div>
</section>

<!-- Room Details Section -->
<section class="py-20 bg-hotel-sand">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            <!-- Room Description -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Description -->
                <div class="bg-white rounded-xl shadow-lg p-8" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                    <h2 class="font-playfair text-3xl font-bold text-hotel-dark mb-6">درباره این اتاق</h2>
                    <div class="text-gray-700 leading-relaxed text-lg space-y-4">
                        <?php echo safe_html($room['description']); ?>
                    </div>
                </div>

                <!-- Amenities -->
                <div class="bg-white rounded-xl shadow-lg p-8" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                    <h3 class="font-playfair text-2xl font-bold text-hotel-dark mb-6">امکانات اتاق</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center space-x-3 space-x-reverse">
                            <div class="w-8 h-8 bg-hotel-gold/20 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-hotel-gold" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                                </svg>
                            </div>
                            <span class="text-gray-700">وای‌فای پرسرعت رایگان</span>
                        </div>
                        <div class="flex items-center space-x-3 space-x-reverse">
                            <div class="w-8 h-8 bg-hotel-gold/20 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-hotel-gold" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M21 6H3a1 1 0 00-1 1v10a1 1 0 001 1h18a1 1 0 001-1V7a1 1 0 00-1-1zM4 8h16v8H4V8z"/>
                                </svg>
                            </div>
                            <span class="text-gray-700">تلویزیون هوشمند ۴K</span>
                        </div>
                        <div class="flex items-center space-x-3 space-x-reverse">
                            <div class="w-8 h-8 bg-hotel-gold/20 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-hotel-gold" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                                </svg>
                            </div>
                            <span class="text-gray-700">سیستم تهویه مطبوع</span>
                        </div>
                        <div class="flex items-center space-x-3 space-x-reverse">
                            <div class="w-8 h-8 bg-hotel-gold/20 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-hotel-gold" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                                </svg>
                            </div>
                            <span class="text-gray-700">مینی‌بار</span>
                        </div>
                        <div class="flex items-center space-x-3 space-x-reverse">
                            <div class="w-8 h-8 bg-hotel-gold/20 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-hotel-gold" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/>
                                </svg>
                            </div>
                            <span class="text-gray-700">صندوق امانات</span>
                        </div>
                        <div class="flex items-center space-x-3 space-x-reverse">
                            <div class="w-8 h-8 bg-hotel-gold/20 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-hotel-gold" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                                </svg>
                            </div>
                            <span class="text-gray-700">سرویس اتاق ۲۴ ساعته</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Sidebar -->
            <div class="space-y-6">
                <!-- Booking Card -->
                <div id="booking" class="bg-white rounded-xl shadow-lg p-8 scroll-mt-28"
                     x-data="{
                         price: <?php echo (float) $room['price_per_night']; ?>,
                         checkIn: '<?php echo e($booking_old['check_in']); ?>',
                         checkOut: '<?php echo e($booking_old['check_out']); ?>',
                         get nights() {
                             if (!this.checkIn || !this.checkOut) return 0;
                             const d = (new Date(this.checkOut) - new Date(this.checkIn)) / 86400000;
                             return d > 0 ? Math.round(d) : 0;
                         },
                         fmt(n) { return Number(n).toLocaleString('fa-IR'); }
                     }">
                    <div class="text-center mb-6">
                        <div class="text-3xl font-bold text-hotel-dark mb-2">
                            <?php echo number_format($room['price_per_night']); ?> تومان
                        </div>
                        <div class="text-gray-600">در شب</div>
                    </div>

                    <h4 class="font-playfair text-xl font-bold text-hotel-dark mb-4">درخواست رزرو آنلاین</h4>

                    <?php if ($flash && $flash['form'] === 'booking'): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 text-sm">
                            <?php echo e($flash['msg']); ?>
                            <?php if (!empty($flash['ref'])): ?>
                                <div class="mt-2 font-semibold">کد پیگیری: <bdi dir="ltr" class="font-mono whitespace-nowrap"><?php echo e($flash['ref']); ?></bdi></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($booking_errors): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 text-sm">
                            <?php foreach ($booking_errors as $err): ?><div><?php echo e($err); ?></div><?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo e($self_url); ?>#booking" method="POST" class="space-y-4">
                        <?php echo csrf_field(); ?>
                        <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="check_in" class="block text-sm font-semibold text-hotel-dark mb-1">تاریخ ورود *</label>
                                <input type="date" id="check_in" name="check_in" required min="<?php echo date('Y-m-d'); ?>" x-model="checkIn"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent">
                            </div>
                            <div>
                                <label for="check_out" class="block text-sm font-semibold text-hotel-dark mb-1">تاریخ خروج *</label>
                                <input type="date" id="check_out" name="check_out" required :min="checkIn || '<?php echo date('Y-m-d'); ?>'" x-model="checkOut"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent">
                            </div>
                        </div>
                        <div>
                            <label for="guest_name" class="block text-sm font-semibold text-hotel-dark mb-1">نام و نام خانوادگی *</label>
                            <input type="text" id="guest_name" name="guest_name" required maxlength="100" value="<?php echo e($booking_old['guest_name']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="phone" class="block text-sm font-semibold text-hotel-dark mb-1">شماره تماس *</label>
                                <input type="tel" id="phone" name="phone" required dir="ltr" value="<?php echo e($booking_old['phone']); ?>" placeholder="0912 123 4567"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent">
                            </div>
                            <div>
                                <label for="guests" class="block text-sm font-semibold text-hotel-dark mb-1">تعداد نفرات *</label>
                                <select id="guests" name="guests" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent">
                                    <?php for ($g = 1; $g <= 10; $g++): ?>
                                        <option value="<?php echo $g; ?>" <?php echo $booking_old['guests'] === $g ? 'selected' : ''; ?>><?php echo $g; ?> نفر</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-semibold text-hotel-dark mb-1">ایمیل (اختیاری)</label>
                            <input type="email" id="email" name="email" dir="ltr" value="<?php echo e($booking_old['email']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent">
                        </div>
                        <div>
                            <label for="notes" class="block text-sm font-semibold text-hotel-dark mb-1">توضیحات (اختیاری)</label>
                            <textarea id="notes" name="notes" rows="2" maxlength="1000"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent resize-none"><?php echo e($booking_old['notes']); ?></textarea>
                        </div>

                        <div x-show="nights > 0" class="bg-hotel-cream rounded-lg p-3 text-sm text-hotel-dark flex justify-between">
                            <span><span x-text="fmt(nights)"></span> شب</span>
                            <span class="font-bold">مبلغ تقریبی: <span x-text="fmt(nights * price)"></span> تومان</span>
                        </div>

                        <button type="submit" name="submit_booking" value="1"
                                class="w-full bg-hotel-gold text-hotel-dark px-6 py-3 rounded-lg hover:bg-hotel-gold/90 transition-colors duration-300 font-bold text-lg">
                            ثبت درخواست رزرو
                        </button>
                    </form>

                    <div class="mt-6 pt-6 border-t border-gray-200 space-y-3">
                        <p class="text-sm text-gray-600 text-center">یا برای رزرو تلفنی تماس بگیرید</p>
                        <a href="tel:+982112345678"
                           class="flex items-center justify-center space-x-3 space-x-reverse bg-hotel-dark text-white px-6 py-3 rounded-lg hover:bg-hotel-dark/90 transition-colors duration-300 font-semibold">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20 22.621l-3.521-6.795c-.008.004-1.974.97-2.064 1.011-2.24 1.086-6.799-7.82-4.609-8.994l2.083-1.028-3.493-6.817-2.105 1.039c-7.202 3.755 4.233 25.982 11.6 22.615.121-.055 2.102-1.029 2.114-1.036.022-.012.008-.005-.009.004z"/>
                            </svg>
                            <span dir="ltr">+۹۸ (۲۱) ۱۲۳۴ ۵۶۷۸</span>
                        </a>
                        <a href="rooms.php?lang=<?php echo e($lang_code); ?>"
                           class="block w-full text-center border-2 border-hotel-gold text-hotel-dark px-6 py-3 rounded-lg hover:bg-hotel-gold/10 transition-colors duration-300 font-semibold">
                            مشاهده سایر اتاق‌ها
                        </a>
                    </div>
                </div>

                <!-- Hotel Features -->
                <div class="bg-white rounded-xl shadow-lg p-8" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                    <h4 class="font-playfair text-xl font-bold text-hotel-dark mb-4">امکانات هتل</h4>
                    <div class="space-y-3 text-sm text-gray-600">
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <svg class="w-4 h-4 text-hotel-gold" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            <span>پارکینگ رایگان</span>
                        </div>
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <svg class="w-4 h-4 text-hotel-gold" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            <span>صبحانه رایگان</span>
                        </div>
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <svg class="w-4 h-4 text-hotel-gold" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            <span>استخر و سالن ورزش</span>
                        </div>
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <svg class="w-4 h-4 text-hotel-gold" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            <span>رستوران و کافه</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Reviews Section -->
<section id="reviews" class="py-20 bg-white scroll-mt-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Title -->
        <div class="text-center mb-16" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
            <h2 class="font-playfair text-4xl md:text-5xl font-bold text-hotel-dark mb-4">
                نظرات مهمانان
            </h2>
            <?php if ($avg_rating > 0): ?>
            <p class="text-gray-600">میانگین امتیاز <span class="font-bold text-hotel-dark"><?php echo $avg_rating; ?></span> از ۵ بر اساس <?php echo count($reviews); ?> نظر</p>
            <?php endif; ?>
            <div class="w-20 h-1 bg-hotel-gold mx-auto mb-6"></div>
        </div>

        <!-- Reviews Display -->
        <?php if ($reviews): ?>
        <div class="space-y-6 mb-12">
            <?php foreach ($reviews as $review): ?>
            <div class="bg-hotel-cream rounded-xl p-6 shadow-lg" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="font-bold text-hotel-dark"><?php echo e(html_entity_decode($review['customer_name'], ENT_QUOTES, 'UTF-8')); ?></h4>
                        <div class="flex items-center space-x-1 space-x-reverse mt-1">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <svg class="w-4 h-4 <?php echo $i <= $review['rating'] ? 'text-hotel-gold' : 'text-gray-300'; ?>" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                <p class="text-gray-700 leading-relaxed"><?php echo nl2br(e(html_entity_decode($review['comment'], ENT_QUOTES, 'UTF-8'))); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center mb-12">
            <p class="text-gray-600 text-lg">هنوز نظری برای این اتاق ثبت نشده است. شما اولین نفر باشید!</p>
        </div>
        <?php endif; ?>

        <!-- Review Form -->
        <div class="bg-hotel-sand rounded-xl p-8" x-data="{ rating: <?php echo (int) $review_old['rating']; ?> }" x-intersect="$el.classList.add('animate-fade-in-up')">
            <h3 class="font-playfair text-2xl font-bold text-hotel-dark mb-6">نظر خود را ثبت کنید</h3>
            
            <!-- Display Message -->
            <?php if ($flash && $flash['form'] === 'review'): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6"><?php echo e($flash['msg']); ?></div>
            <?php endif; ?>
            <?php if ($review_errors): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?php foreach ($review_errors as $err): ?><div><?php echo e($err); ?></div><?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo e($self_url); ?>#reviews" method="POST" class="space-y-6">
                <?php echo csrf_field(); ?>
                <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
                <!-- Customer Name -->
                <div>
                    <label for="customer_name" class="block text-sm font-semibold text-hotel-dark mb-2">نام شما *</label>
                    <input type="text" 
                           id="customer_name" 
                           name="customer_name" 
                           required
                           maxlength="100"
                           value="<?php echo e($review_old['customer_name']); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300"
                           placeholder="نام کامل خود را وارد کنید">
                </div>

                <!-- Rating -->
                <div>
                    <label class="block text-sm font-semibold text-hotel-dark mb-2">امتیاز شما *</label>
                    <div class="flex items-center space-x-2 space-x-reverse">
                        <template x-for="star in [1,2,3,4,5]" :key="star">
                            <button type="button"
                                    @click="rating = star"
                                    :class="star <= rating ? 'text-hotel-gold' : 'text-gray-300'"
                                    class="text-2xl hover:text-hotel-gold transition-colors duration-200 focus:outline-none">
                                ★
                            </button>
                        </template>
                        <span x-show="rating > 0" class="text-sm text-gray-600 mr-3">
                            (<span x-text="rating"></span> از ۵ ستاره)
                        </span>
                    </div>
                    <input type="hidden" name="rating" :value="rating" required>
                </div>

                <!-- Comment -->
                <div>
                    <label for="comment" class="block text-sm font-semibold text-hotel-dark mb-2">نظر شما *</label>
                    <textarea id="comment" 
                              name="comment" 
                              rows="4" 
                              required
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300 resize-none"
                              maxlength="2000"
                              placeholder="تجربه خود از این اتاق را با ما به اشتراک بگذارید..."><?php echo e($review_old['comment']); ?></textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-between">
                    <button type="submit" 
                            name="submit_review"
                            value="1"
                            :disabled="rating === 0"
                            :class="rating === 0 ? 'bg-gray-400 cursor-not-allowed' : 'bg-hotel-gold hover:bg-hotel-gold/90'"
                            class="px-8 py-3 text-hotel-dark font-bold rounded-lg transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-hotel-gold focus:ring-offset-2">
                        ارسال نظر
                    </button>
                    <p class="text-sm text-gray-600">
                        نظر شما پس از تایید ادمین نمایش داده خواهد شد
                    </p>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
// گالری تصاویر + لایت‌باکس (کلیدهای جهت‌دار و Esc هم پشتیبانی می‌شوند)
(function(){
    const images = <?php echo json_encode($gallery_images, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    const thumbs = document.querySelectorAll('.thumb-btn');
    const main = document.getElementById('mainGalleryImage');
    const counter = document.getElementById('galleryCounter');
    const lightbox = document.getElementById('lightbox');
    const lbImg = document.getElementById('lightboxImg');
    let current = 0;

    function show(idx) {
        current = (idx + images.length) % images.length;
        main.src = images[current];
        lbImg.src = images[current];
        counter.textContent = current + 1;
        thumbs.forEach((t, i) => t.classList.toggle('ring-hotel-gold', i === current));
    }
    function open() { lbImg.src = images[current]; lightbox.classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
    function close() { lightbox.classList.add('hidden'); document.body.style.overflow = ''; }

    thumbs.forEach(b => b.addEventListener('click', () => show(parseInt(b.dataset.index, 10))));
    document.getElementById('openLightboxBtn').addEventListener('click', open);
    main.addEventListener('click', open);
    document.getElementById('closeLightbox').addEventListener('click', close);
    document.getElementById('prevImg').addEventListener('click', () => show(current - 1));
    document.getElementById('nextImg').addEventListener('click', () => show(current + 1));
    lightbox.addEventListener('click', e => { if (e.target === lightbox) close(); });
    document.addEventListener('keydown', e => {
        if (lightbox.classList.contains('hidden')) return;
        if (e.key === 'Escape') close();
        if (e.key === 'ArrowLeft') show(current - 1);
        if (e.key === 'ArrowRight') show(current + 1);
    });
})();
</script>

<?php include_once 'includes/footer.php'; ?>
