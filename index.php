<?php include_once 'includes/header.php'; ?>

<section class="hero-section">
    <div class="hero-video-bg">
        <video autoplay muted loop poster="assets/images/hero-poster1.jpg">
            <source src="assets/videos/hotel-intro.mp4" type="video/mp4">
        </video>
    </div>
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <h1 class="fade-in-up"><?php echo $lang['hero_title']; ?></h1>
        <p class="fade-in-up delay-1"><?php echo $lang['hero_subtitle']; ?></p>
        <a href="rooms.php?lang=<?php echo $lang_code; ?>" class="btn btn-primary fade-in-up delay-2"><?php echo $lang['hero_button']; ?></a>
    </div>
</section>

<section class="rooms-preview-section content-section">
    <div class="container">
        <h2 class="section-title"><?php echo $lang['rooms_section_title']; ?></h2>
        <div class="rooms-grid">
            <?php
            // کوئری برای گرفتن ۳ اتاق برای نمایش در صفحه اصلی
            $stmt = $conn->prepare("
                SELECT r.id, r.image, r.price_per_night, rt.name, rt.short_description
                FROM rooms r
                JOIN room_translations rt ON r.id = rt.room_id
                WHERE rt.lang_code = ?
                ORDER BY r.id DESC
                LIMIT 3
            ");
            $stmt->bind_param("s", $lang_code);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($room = $result->fetch_assoc()):
            ?>
            <div class="room-card reveal-on-scroll">
                <div class="room-card-image">
                    <img src="uploads/rooms/<?php echo htmlspecialchars($room['image']); ?>" alt="<?php echo htmlspecialchars($room['name']); ?>">
                </div>
                <div class="room-card-content">
                    <h3><?php echo htmlspecialchars($room['name']); ?></h3>
                    <p><?php echo htmlspecialchars($room['short_description']); ?></p>
                    <a href="room-details.php?id=<?php echo $room['id']; ?>&lang=<?php echo $lang_code; ?>" class="btn btn-secondary"><?php echo $lang['view_details']; ?></a>
                </div>
            </div>
            <?php endwhile; $stmt->close(); ?>
        </div>
    </div>
</section>


<section class="testimonials-section content-section">
    <div class="container">
        <h2 class="section-title"><?php echo $lang['testimonials_section_title']; ?></h2>
        <div class="testimonial-slider">
            <?php
            // کد جدید برای خواندن نظرات از جدول room_reviews
            // این کد ۳ نظر از بهترین نظرات تایید شده را نمایش می‌دهد
            $reviews_query = "
                SELECT customer_name, comment 
                FROM room_reviews 
                WHERE status = 'approved' 
                ORDER BY rating DESC, created_at DESC 
                LIMIT 3
            ";
            $result = $conn->query($reviews_query);

            if ($result && $result->num_rows > 0):
                while($review = $result->fetch_assoc()):
            ?>
            <div class="testimonial-item">
                <p>"<?php echo htmlspecialchars($review['comment']); ?>"</p>
                <cite>- <?php echo htmlspecialchars($review['customer_name']); ?></cite>
            </div>
            <?php 
                endwhile;
            endif; 
            ?>
        </div>
    </div>
</section>
<?php include_once 'includes/footer.php'; ?>