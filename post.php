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
    echo "<div class='max-w-4xl mx-auto px-4 py-20 text-center'><p class='text-xl text-gray-600'>مقاله مورد نظر یافت نشد.</p></div>";
    include_once 'includes/footer.php';
    exit();
}

$post = $result->fetch_assoc();
$stmt->close();

$post_date = date("d F Y", strtotime($post['created_at']));
?>

<!-- Post Hero Section -->
<section class="relative min-h-[50vh] flex items-center justify-center overflow-hidden">
    <!-- Background Image -->
    <div class="absolute inset-0">
        <img src="uploads/blog/<?php echo htmlspecialchars($post['image']); ?>" 
             alt="<?php echo htmlspecialchars($post['title']); ?>"
             class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-black/60"></div>
    </div>
    
    <!-- Hero Content -->
    <div class="relative z-10 text-center text-white px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
        <div class="mb-4">
            <span class="inline-block bg-hotel-gold text-hotel-dark px-4 py-2 rounded-full text-sm font-bold">
                مقاله وبلاگ
            </span>
        </div>
        <h1 class="font-playfair text-3xl sm:text-4xl md:text-5xl font-bold mb-6 fade-in-up opacity-0">
            <?php echo htmlspecialchars($post['title']); ?>
        </h1>
        <div class="flex items-center justify-center space-x-4 space-x-reverse text-hotel-cream fade-in-up opacity-0 delay-1">
            <div class="flex items-center space-x-2 space-x-reverse">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/>
                </svg>
                <span><?php echo $post_date; ?></span>
            </div>
            <span>•</span>
            <span>۵ دقیقه مطالعه</span>
        </div>
    </div>
</section>

<!-- Post Content -->
<article class="py-20 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Post Content -->
        <div class="prose prose-lg max-w-none" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
            <div class="text-gray-700 leading-relaxed text-lg space-y-6">
                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
            </div>
        </div>

        <!-- Post Actions -->
        <div class="mt-12 pt-8 border-t border-gray-200" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
                <!-- Share Buttons -->
                <div class="flex items-center space-x-4 space-x-reverse">
                    <span class="text-gray-600 font-semibold">اشتراک‌گذاری:</span>
                    <button class="bg-blue-600 text-white p-3 rounded-full hover:bg-blue-700 transition-colors duration-300">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                        </svg>
                    </button>
                    <button class="bg-blue-800 text-white p-3 rounded-full hover:bg-blue-900 transition-colors duration-300">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </button>
                    <button class="bg-green-600 text-white p-3 rounded-full hover:bg-green-700 transition-colors duration-300">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.386"/>
                        </svg>
                    </button>
                </div>

                <!-- Back to Blog -->
                <a href="blog.php?lang=<?php echo $lang_code; ?>" 
                   class="inline-flex items-center space-x-2 space-x-reverse bg-hotel-dark text-white px-6 py-3 rounded-lg hover:bg-hotel-dark/90 transition-colors duration-300 font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>بازگشت به وبلاگ</span>
                </a>
            </div>
        </div>
    </div>
</article>

<!-- Related Posts Section -->
<section class="py-20 bg-hotel-sand">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Title -->
        <div class="text-center mb-16" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
            <h2 class="font-playfair text-4xl md:text-5xl font-bold text-hotel-dark mb-4">
                مقالات مرتبط
            </h2>
            <div class="w-20 h-1 bg-hotel-gold mx-auto mb-6"></div>
            <p class="text-gray-600 text-lg">
                مطالب دیگری که ممکن است برایتان جالب باشد
            </p>
        </div>

        <!-- Related Posts Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php
            // واکشی مقالات مرتبط (3 مقاله آخر به جز مقاله فعلی)
            $related_stmt = $conn->prepare("
                SELECT p.id, p.image, p.created_at, pt.title, pt.summary
                FROM blog_posts p
                JOIN blog_post_translations pt ON p.id = pt.post_id
                WHERE pt.lang_code = ? AND p.id != ?
                ORDER BY p.created_at DESC
                LIMIT 3
            ");
            $related_stmt->bind_param("si", $lang_code, $post_id);
            $related_stmt->execute();
            $related_result = $related_stmt->get_result();

            while ($related_post = $related_result->fetch_assoc()):
                $related_date = date("d F Y", strtotime($related_post['created_at']));
            ?>
            <article class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:scale-105 transition-all duration-300 hover:shadow-2xl group"
                     x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                
                <!-- Post Image -->
                <div class="relative overflow-hidden h-48">
                    <img src="uploads/blog/<?php echo htmlspecialchars($related_post['image']); ?>" 
                         alt="<?php echo htmlspecialchars($related_post['title']); ?>"
                         class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500">
                    
                    <!-- Reading Time Badge -->
                    <div class="absolute top-4 right-4 bg-black/50 backdrop-blur-sm text-white px-3 py-1 rounded-full text-sm">
                        ۳ دقیقه مطالعه
                    </div>
                </div>
                
                <!-- Post Content -->
                <div class="p-6">
                    <!-- Post Meta -->
                    <div class="flex items-center space-x-2 space-x-reverse mb-3 text-sm text-gray-500">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/>
                        </svg>
                        <span><?php echo $related_date; ?></span>
                    </div>
                    
                    <!-- Post Title -->
                    <h3 class="font-playfair text-xl font-bold text-hotel-dark mb-3 group-hover:text-hotel-gold transition-colors duration-300 line-clamp-2">
                        <?php echo htmlspecialchars($related_post['title']); ?>
                    </h3>
                    
                    <!-- Post Summary -->
                    <p class="text-gray-600 mb-4 leading-relaxed line-clamp-3">
                        <?php echo htmlspecialchars($related_post['summary']); ?>
                    </p>
                    
                    <!-- Read More Button -->
                    <a href="post.php?id=<?php echo $related_post['id']; ?>&lang=<?php echo $lang_code; ?>" 
                       class="inline-flex items-center space-x-2 space-x-reverse text-hotel-dark hover:text-hotel-gold transition-colors duration-300 font-semibold">
                        <span>ادامه مطالعه</span>
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                </div>
            </article>
            <?php endwhile; $related_stmt->close(); ?>
        </div>
    </div>
</section>

<?php include_once 'includes/footer.php'; ?>
