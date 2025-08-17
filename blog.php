<?php include_once 'includes/header.php'; ?>

<main class="main-content">
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">وبلاگ هتل پالاس</h1>
            <p class="page-subtitle">داستان‌ها، راهنماها و آخرین اخبار از دنیای ما.</p>
        </div>

        <div class="blog-page-grid">
            <?php
            // واکشی تمام مقالات به همراه ترجمه آن‌ها
            $stmt = $conn->prepare("
                SELECT p.id, p.image, p.created_at, pt.title, pt.summary
                FROM blog_posts p
                JOIN blog_post_translations pt ON p.id = pt.post_id
                WHERE pt.lang_code = ?
                ORDER BY p.created_at DESC
            ");
            $stmt->bind_param("s", $lang_code);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($post = $result->fetch_assoc()):
                // قالب‌بندی تاریخ انتشار
                $post_date = date("d F Y", strtotime($post['created_at']));
                // برای تاریخ فارسی می‌توان از کتابخانه‌های جلالی استفاده کرد، اما فعلا به صورت میلادی نمایش می‌دهیم.
            ?>
            <a href="post.php?id=<?php echo $post['id']; ?>&lang=<?php echo $lang_code; ?>" class="blog-card">
                <div class="blog-card-image-wrapper">
                    <img src="uploads/blog/<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                </div>
                <div class="blog-card-content">
                    <div class="blog-card-meta">
                        <span class="post-date"><?php echo $post_date; ?></span>
                    </div>
                    <h3 class="blog-card-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                    <p class="blog-card-summary"><?php echo htmlspecialchars($post['summary']); ?></p>
                    <span class="link-with-arrow">ادامه مطلب</span>
                </div>
            </a>
            <?php endwhile; $stmt->close(); ?>
        </div>
    </div>
</main>

<?php include_once 'includes/footer.php'; ?>