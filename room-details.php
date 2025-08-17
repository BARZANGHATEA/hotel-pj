<?php
include_once 'includes/header.php';

// ۱. گرفتن ID اتاق از URL و اعتبارسنجی آن
$room_id = 0;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $room_id = intval($_GET['id']);
} else {
    // اگر ID وجود نداشت یا معتبر نبود، کاربر را به صفحه اتاق‌ها منتقل کن
    header('Location: rooms.php');
    exit();
}

// ۲. پردازش فرم ثبت نظر
$review_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $customer_name = htmlspecialchars($_POST['customer_name']);
    $rating = intval($_POST['rating']);
    $comment = htmlspecialchars($_POST['comment']);

    if (!empty($customer_name) && $rating >= 1 && $rating <= 5 && !empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO room_reviews (room_id, customer_name, rating, comment, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param("isis", $room_id, $customer_name, $rating, $comment);
        if ($stmt->execute()) {
            $review_message = "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6'>نظر شما با موفقیت ثبت شد و پس از تایید ادمین نمایش داده خواهد شد.</div>";
        } else {
            $review_message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6'>خطایی در ثبت نظر رخ داد.</div>";
        }
        $stmt->close();
    } else {
        $review_message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6'>لطفاً تمام فیلدها را به درستی پر کنید.</div>";
    }
}

// ۳. واکشی اطلاعات اصلی و ترجمه شده اتاق از دیتابیس
$stmt = $conn->prepare("
    SELECT r.price_per_night, rt.name, rt.description
    FROM rooms r
    JOIN room_translations rt ON r.id = rt.room_id
    WHERE r.id = ? AND rt.lang_code = ?
");
$stmt->bind_param("is", $room_id, $lang_code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // اگر اتاقی با این ID پیدا نشد
    echo "<div class='max-w-4xl mx-auto px-4 py-20 text-center'><p class='text-xl text-gray-600'>اتاق مورد نظر یافت نشد.</p></div>";
    include_once 'includes/footer.php';
    exit();
}
$room = $result->fetch_assoc();
$stmt->close();

// ۴. واکشی تصاویر گالری مربوط به این اتاق
$gallery_stmt = $conn->prepare("SELECT image_url FROM room_images WHERE room_id = ?");
$gallery_stmt->bind_param("i", $room_id);
$gallery_stmt->execute();
$gallery_result = $gallery_stmt->get_result();
$gallery_images = [];
while ($row = $gallery_result->fetch_assoc()) {
    $gallery_images[] = $row['image_url'];
}
$gallery_stmt->close();

// اگر تصویری وجود نداشت، تصویر پیش‌فرض اضافه کن
if (empty($gallery_images)) {
    $gallery_images[] = 'default-image.jpg';
}

// ۵. واکشی نظرات تایید شده برای این اتاق
$reviews_stmt = $conn->prepare("SELECT * FROM room_reviews WHERE room_id = ? AND status = 'approved' ORDER BY created_at DESC");
$reviews_stmt->bind_param("i", $room_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();
?>

<!-- Room Hero Section -->
<section class="relative min-h-[60vh] flex items-center justify-center overflow-hidden">
    <!-- Background Image -->
    <div class="absolute inset-0">
        <img src="uploads/rooms/<?php echo htmlspecialchars($gallery_images[0]); ?>" 
             alt="<?php echo htmlspecialchars($room['name']); ?>"
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
    </div>
</section>

<!-- Room Gallery Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Main Image -->
        <div class="mb-8" x-data="{ currentImage: '<?php echo htmlspecialchars($gallery_images[0]); ?>' }">
            <div class="relative h-96 lg:h-[500px] rounded-xl overflow-hidden shadow-2xl">
                <img :src="'uploads/rooms/' + currentImage" 
                     alt="<?php echo htmlspecialchars($room['name']); ?>"
                     class="w-full h-full object-cover transition-all duration-500">
                
                <!-- Image Navigation -->
                <?php if (count($gallery_images) > 1): ?>
                <div class="absolute inset-y-0 left-4 flex items-center">
                    <button @click="currentImage = '<?php echo htmlspecialchars($gallery_images[array_search($currentImage ?? $gallery_images[0], $gallery_images) - 1] ?? end($gallery_images)); ?>'"
                            class="bg-black/50 text-white p-3 rounded-full hover:bg-black/70 transition-colors duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                </div>
                <div class="absolute inset-y-0 right-4 flex items-center">
                    <button @click="currentImage = '<?php echo htmlspecialchars($gallery_images[array_search($currentImage ?? $gallery_images[0], $gallery_images) + 1] ?? $gallery_images[0]); ?>'"
                            class="bg-black/50 text-white p-3 rounded-full hover:bg-black/70 transition-colors duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Thumbnail Gallery -->
            <?php if (count($gallery_images) > 1): ?>
            <div class="flex space-x-4 space-x-reverse mt-6 overflow-x-auto pb-2">
                <?php foreach ($gallery_images as $index => $image): ?>
                <button @click="currentImage = '<?php echo htmlspecialchars($image); ?>'"
                        :class="currentImage === '<?php echo htmlspecialchars($image); ?>' ? 'ring-4 ring-hotel-gold' : 'ring-2 ring-gray-200'"
                        class="flex-shrink-0 w-24 h-24 rounded-lg overflow-hidden transition-all duration-300 hover:ring-hotel-gold">
                    <img src="uploads/rooms/<?php echo htmlspecialchars($image); ?>" 
                         alt="تصویر <?php echo $index + 1; ?>"
                         class="w-full h-full object-cover">
                </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
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
                        <?php echo nl2br(htmlspecialchars($room['description'])); ?>
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
                <div class="bg-white rounded-xl shadow-lg p-8 sticky top-24" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                    <div class="text-center mb-6">
                        <div class="text-3xl font-bold text-hotel-dark mb-2">
                            <?php echo number_format($room['price_per_night']); ?> تومان
                        </div>
                        <div class="text-gray-600">در شب</div>
                    </div>
                    
                    <!-- Contact Info -->
                    <div class="space-y-4 mb-6">
                        <h4 class="font-playfair text-xl font-bold text-hotel-dark">برای رزرو تماس بگیرید</h4>
                        <a href="tel:+982112345678" 
                           class="flex items-center justify-center space-x-3 space-x-reverse bg-hotel-gold text-hotel-dark px-6 py-4 rounded-lg hover:bg-hotel-gold/90 transition-colors duration-300 font-bold text-lg">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20 22.621l-3.521-6.795c-.008.004-1.974.97-2.064 1.011-2.24 1.086-6.799-7.82-4.609-8.994l2.083-1.028-3.493-6.817-2.105 1.039c-7.202 3.755 4.233 25.982 11.6 22.615.121-.055 2.102-1.029 2.114-1.036.022-.012.008-.005-.009.004z"/>
                            </svg>
                            <span dir="ltr">+۹۸ (۲۱) ۱۲۳۴ ۵۶۷۸</span>
                        </a>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="space-y-3">
                        <button class="w-full bg-hotel-dark text-white px-6 py-3 rounded-lg hover:bg-hotel-dark/90 transition-colors duration-300 font-semibold">
                            درخواست اطلاعات بیشتر
                        </button>
                        <a href="rooms.php?lang=<?php echo $lang_code; ?>" 
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
<section class="py-20 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Title -->
        <div class="text-center mb-16" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
            <h2 class="font-playfair text-4xl md:text-5xl font-bold text-hotel-dark mb-4">
                نظرات مهمانان
            </h2>
            <div class="w-20 h-1 bg-hotel-gold mx-auto mb-6"></div>
        </div>

        <!-- Reviews Display -->
        <?php if ($reviews_result->num_rows > 0): ?>
        <div class="space-y-6 mb-12">
            <?php while($review = $reviews_result->fetch_assoc()): ?>
            <div class="bg-hotel-cream rounded-xl p-6 shadow-lg" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="font-bold text-hotel-dark"><?php echo htmlspecialchars($review['customer_name']); ?></h4>
                        <div class="flex items-center space-x-1 space-x-reverse mt-1">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <svg class="w-4 h-4 <?php echo $i <= $review['rating'] ? 'text-hotel-gold' : 'text-gray-300'; ?>" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                <p class="text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="text-center mb-12">
            <p class="text-gray-600 text-lg">هنوز نظری برای این اتاق ثبت نشده است. شما اولین نفر باشید!</p>
        </div>
        <?php endif; ?>

        <!-- Review Form -->
        <div class="bg-hotel-sand rounded-xl p-8" x-data="{ rating: 0 }" x-intersect="$el.classList.add('animate-fade-in-up')">
            <h3 class="font-playfair text-2xl font-bold text-hotel-dark mb-6">نظر خود را ثبت کنید</h3>
            
            <!-- Display Message -->
            <?php echo $review_message; ?>
            
            <form action="room-details.php?id=<?php echo $room_id; ?>&lang=<?php echo $lang_code; ?>" method="POST" class="space-y-6">
                <!-- Customer Name -->
                <div>
                    <label for="customer_name" class="block text-sm font-semibold text-hotel-dark mb-2">نام شما *</label>
                    <input type="text" 
                           id="customer_name" 
                           name="customer_name" 
                           required
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
                              placeholder="تجربه خود از این اتاق را با ما به اشتراک بگذارید..."></textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-between">
                    <button type="submit" 
                            name="submit_review"
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

<?php 
$reviews_stmt->close();
include_once 'includes/footer.php'; 
?>
