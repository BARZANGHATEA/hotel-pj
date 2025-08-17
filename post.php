<?php
include_once 'includes/header.php';

// ۱. گرفتن ID مقاله از URL و اعتبارسنجی آن
$post_id = 0;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $post_id = intval($_GET['id']);
} else {
    // اگر ID نامعتبر بود، به صفحه وبلاگ برگرد
    header('Location: blog.php');
    exit();
}

// ۲. واکشی اطلاعات مقاله از دیتابیس
$stmt = $conn->prepare("
    SELECT p.image, p.created_at, pt.title, pt.content
    FROM blog_posts p
    JOIN blog_post_translations pt ON p.id = pt.post_id
    WHERE p.id = ? AND pt.lang_code = ?
");
$stmt->bind_param("is", $post_id, $lang_code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // اگر مقاله‌ای با این ID پیدا نشد
    echo "<div class='container page-header'><p>مقاله مورد نظر یافت نشد.</p></div>";
    include_once 'includes/footer.php';
    exit();
}

$post = $result->fetch_assoc();
$stmt->close();

$post_date = date("d F Y", strtotime($post['created_at']));
?>

<main class="main-content">
    <div class="container">
        <article class="post-layout">
            <header class="post-header">
                <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                <p class="post-meta">منتشر شده در تاریخ <?php echo $post_date; ?></p>
            </header>

            <div class="post-featured-image">
                <img src="uploads/blog/<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
            </div>

            <section class="post-content">
                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
            </section>

            <footer class="post-footer">
                <a href="blog.php?lang=<?php echo $lang_code; ?>" class="link-with-arrow">بازگشت به وبلاگ</a>
            </footer>
        </article>
    </div>
</main>

<?php include_once 'includes/footer.php'; ?>