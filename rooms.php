<?php include_once 'includes/header.php'; ?>

<main class="main-content">
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">اتاق‌ها و سوئیت‌ها</h1>
            <p class="page-subtitle">فضایی برای هر سلیقه، طراحی شده برای آرامش شما.</p>
        </div>

        <div class="rooms-page-grid">
            <?php
            // واکشی تمام اتاق‌ها به همراه ترجمه آن‌ها
            $stmt = $conn->prepare("
                SELECT r.id, r.image, r.price_per_night, rt.name, rt.short_description
                FROM rooms r
                JOIN room_translations rt ON r.id = rt.room_id
                WHERE rt.lang_code = ?
                ORDER BY r.id ASC
            ");
            $stmt->bind_param("s", $lang_code);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($room = $result->fetch_assoc()):
            ?>
            <a href="room-details.php?id=<?php echo $room['id']; ?>&lang=<?php echo $lang_code; ?>" class="room-card-v3">
                <div class="room-card-image-wrapper">
                    <img src="uploads/rooms/<?php echo htmlspecialchars($room['image']); ?>" alt="<?php echo htmlspecialchars($room['name']); ?>">
                </div>
                <div class="room-card-content">
                    <h3 class="room-card-title"><?php echo htmlspecialchars($room['name']); ?></h3>
                    <p class="room-card-desc"><?php echo htmlspecialchars($room['short_description']); ?></p>
                    <div class="room-card-footer">
                        <span class="room-card-price">شروع از شبی <?php echo number_format($room['price_per_night']); ?> تومان</span>
                        <span class="link-with-arrow">مشاهده جزئیات</span>
                    </div>
                </div>
            </a>
            <?php endwhile; $stmt->close(); ?>
        </div>
    </div>
</main>

<?php include_once 'includes/footer.php'; ?>