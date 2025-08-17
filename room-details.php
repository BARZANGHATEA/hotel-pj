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

// ۲. واکشی اطلاعات اصلی و ترجمه شده اتاق از دیتابیس
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
    echo "<div class='container'><p>اتاق مورد نظر یافت نشد.</p></div>";
    include_once 'includes/footer.php';
    exit();
}
$room = $result->fetch_assoc();
$stmt->close();


// ۳. واکشی تصاویر گالری مربوط به این اتاق
$gallery_stmt = $conn->prepare("SELECT image_url FROM room_images WHERE room_id = ?");
$gallery_stmt->bind_param("i", $room_id);
$gallery_stmt->execute();
$gallery_result = $gallery_stmt->get_result();
$gallery_images = [];
while ($row = $gallery_result->fetch_assoc()) {
    $gallery_images[] = $row['image_url'];
}
$gallery_stmt->close();

?>

<main class="main-content">
    <div class="container">
        <div class="room-details-layout">
            
            <div class="room-header">
                <h1><?php echo htmlspecialchars($room['name']); ?></h1>
                <div class="price">
                    شروع قیمت از شبی <strong><?php echo number_format($room['price_per_night']); ?></strong> تومان
                </div>
            </div>

            <div class="room-gallery">
                <div class="main-image">
                    <img id="mainGalleryImage" src="uploads/rooms/<?php echo !empty($gallery_images) ? htmlspecialchars($gallery_images[0]) : 'default-image.jpg'; ?>" alt="تصویر اصلی اتاق">
                </div>
                <div class="thumbnail-images">
                    <?php foreach ($gallery_images as $image): ?>
                    <div class="thumb-item">
                        <img src="uploads/rooms/<?php echo htmlspecialchars($image); ?>" alt="تصویر کوچک اتاق" onclick="changeMainImage(this)">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="room-info-grid">
                <div class="room-description">
                    <h2>درباره این اتاق</h2>
                    <p><?php echo nl2br(htmlspecialchars($room['description'])); ?></p>
                </div>
                <div class="room-amenities">
                    <h3>امکانات اصلی</h3>
                    <ul>
                        <li><svg>...</svg> وای‌فای پرسرعت رایگان</li>
                        <li><svg>...</svg> تلویزیون هوشمند ۴K</li>
                        <li><svg>...</svg> سیستم تهویه مطبوع</li>
                        <li><svg>...</svg> مینی‌بار</li>
                        <li><svg>...</svg> صندوق امانات</li>
                        <li><svg>...</svg> سرویس اتاق ۲۴ ساعته</li>
                    </ul>
                    <div class="cta-box">
                        <h4>برای رزرو تماس بگیرید</h4>
                        <a href="tel:+982112345678" class="phone-number" dir="ltr">+۹۸ (۲۱) ۱۲۳۴ ۵۶۷۸</a>
                    </div>
                    <div class="reviews-section">
    <h2>نظرات و امتیازات</h2>
    
    <?php if ($reviews_result->num_rows > 0): ?>
        <div class="reviews-list">
            <?php while($review = $reviews_result->fetch_assoc()): ?>
            <div class="review-item">
                <div class="review-header">
                    <strong><?php echo htmlspecialchars($review['customer_name']); ?></strong>
                    <div class="star-rating-display" data-rating="<?php echo $review['rating']; ?>"></div>
                </div>
                <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
            </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>هنوز نظری برای این اتاق ثبت نشده است. شما اولین نفر باشید!</p>
    <?php endif; ?>

    <div class="review-form-container">
        <h3>نظر خود را ثبت کنید</h3>
        <?php echo $review_message; ?>
        <form action="room-details.php?id=<?php echo $room_id; ?>" method="POST">
            <div class="form-group">
                <label for="customer_name">نام شما</label>
                <input type="text" id="customer_name" name="customer_name" required>
            </div>
            <div class="form-group">
                <label>امتیاز شما</label>
                <div class="star-rating">
                    <input type="radio" id="5-stars" name="rating" value="5" /><label for="5-stars">★</label>
                    <input type="radio" id="4-stars" name="rating" value="4" /><label for="4-stars">★</label>
                    <input type="radio" id="3-stars" name="rating" value="3" /><label for="3-stars">★</label>
                    <input type="radio" id="2-stars" name="rating" value="2" /><label for="2-stars">★</label>
                    <input type="radio" id="1-star" name="rating" value="1" /><label for="1-star">★</label>
                </div>
            </div>
            <div class="form-group">
                <label for="comment">نظر شما</label>
                <textarea id="comment" name="comment" rows="4" required></textarea>
            </div>
            <button type="submit" name="submit_review" class="btn btn-primary-v3">ارسال نظر</button>
        </form>
    </div>
</div>
                </div>
            </div>

        </div>
    </div>
</main>


<?php include_once 'includes/footer.php'; ?>