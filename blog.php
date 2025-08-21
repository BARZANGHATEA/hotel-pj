<?php include_once 'includes/header.php'; ?>

<!-- Blog Hero Section -->
<section class="relative min-h-[60vh] flex items-center justify-center overflow-hidden bg-gradient-to-br from-hotel-dark via-hotel-blue to-hotel-dark">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0 bg-repeat" style="background-image: url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23FFD700" fill-opacity="0.1"><circle cx="30" cy="30" r="2"/></g></g></svg>');"></div>
    </div>
    
    <!-- Hero Content -->
    <div class="relative z-10 text-center text-white px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
        <h1 class="font-playfair text-4xl sm:text-5xl md:text-6xl font-bold mb-6 fade-in-up opacity-0">
            وبلاگ هتل سیروان
        </h1>
        <p class="text-lg sm:text-xl md:text-2xl mb-8 max-w-2xl mx-auto leading-relaxed fade-in-up opacity-0 delay-1">
            داستان‌ها، راهنماها و آخرین اخبار از دنیای ما
        </p>
        <div class="w-20 h-1 bg-hotel-gold mx-auto fade-in-up opacity-0 delay-2"></div>
    </div>
</section>

<!-- Blog Categories & Search -->
<section class="py-8 bg-hotel-cream border-b border-hotel-gold/20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-6">
            <!-- Categories -->
            <div class="flex flex-wrap items-center gap-4">
                <span class="text-hotel-dark font-semibold">دسته‌بندی:</span>
                <button class="bg-hotel-gold text-hotel-dark px-4 py-2 rounded-full text-sm font-semibold hover:bg-hotel-gold/90 transition-colors duration-300">
                    همه مقالات
                </button>
                <button class="bg-white border border-hotel-gold/30 text-hotel-dark px-4 py-2 rounded-full text-sm font-semibold hover:bg-hotel-gold/10 transition-colors duration-300">
                    راهنمای سفر
                </button>
                <button class="bg-white border border-hotel-gold/30 text-hotel-dark px-4 py-2 rounded-full text-sm font-semibold hover:bg-hotel-gold/10 transition-colors duration-300">
                    اخبار هتل
                </button>
                <button class="bg-white border border-hotel-gold/30 text-hotel-dark px-4 py-2 rounded-full text-sm font-semibold hover:bg-hotel-gold/10 transition-colors duration-300">
                    نکات مفید
                </button>
            </div>
            
            <!-- Search Box -->
            <div class="relative">
                <input type="text" 
                       placeholder="جستجو در مقالات..." 
                       class="bg-white border border-hotel-gold/30 rounded-lg px-4 py-2 pr-10 text-hotel-dark focus:outline-none focus:border-hotel-gold transition-colors duration-300 w-64">
                <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>
    </div>
</section>

<!-- Featured Post Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Featured Post -->
        <div class="mb-16" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
            <div class="bg-gradient-to-r from-hotel-gold/10 to-hotel-blue/10 rounded-2xl overflow-hidden shadow-xl">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
                    <!-- Featured Image -->
                    <div class="relative h-64 lg:h-auto">
                        <img src="assets/images/tourism2.jpg" 
                             alt="Featured Post" 
                             class="w-full h-full object-cover">
                        <div class="absolute top-4 right-4 bg-hotel-gold text-hotel-dark px-3 py-1 rounded-full text-sm font-bold">
                            مقاله ویژه
                        </div>
                    </div>
                    
                    <!-- Featured Content -->
                    <div class="p-8 lg:p-12 flex flex-col justify-center">
                        <div class="flex items-center space-x-4 space-x-reverse mb-4 text-sm text-gray-500">
                            <span>۱۵ دی ۱۴۰۲</span>
                            <span>•</span>
                            <span>راهنمای سفر</span>
                            <span>•</span>
                            <span>۵ دقیقه مطالعه</span>
                        </div>
                        <h2 class="font-playfair text-3xl lg:text-4xl font-bold text-hotel-dark mb-4">
                            راهنمای کامل اقامت در تهران: بهترین مکان‌ها و تجربه‌ها
                        </h2>
                        <p class="text-gray-600 text-lg leading-relaxed mb-6">
                            تهران، پایتخت پرجنب‌وجوش ایران، شهری است که تاریخ کهن و مدرنیت را در خود جای داده. در این راهنمای جامع، با بهترین جاذبه‌های گردشگری، رستوران‌ها و تجربه‌های فرهنگی این شهر آشنا شوید.
                        </p>
                        <a href="#" class="inline-flex items-center space-x-2 space-x-reverse bg-hotel-dark text-white px-6 py-3 rounded-lg hover:bg-hotel-dark/90 transition-colors duration-300 font-semibold">
                            <span>ادامه مطالعه</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Blog Posts Grid -->
<section class="py-20 bg-hotel-sand">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Title -->
        <div class="text-center mb-16" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
            <h2 class="font-playfair text-4xl md:text-5xl font-bold text-hotel-dark mb-4">
                آخرین مقالات
            </h2>
            <div class="w-20 h-1 bg-hotel-gold mx-auto mb-6"></div>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                مطالب جدید و مفید برای بهتر شدن تجربه سفر شما
            </p>
        </div>

        <!-- Blog Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
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
            ?>
            <article class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:scale-105 transition-all duration-300 hover:shadow-2xl group"
                     x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                
                <!-- Post Image -->
                <div class="relative overflow-hidden h-48">
                    <img src="uploads/blog/<?php echo htmlspecialchars($post['image']); ?>" 
                         alt="<?php echo htmlspecialchars($post['title']); ?>"
                         class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500">
                    
                    <!-- Reading Time Badge -->
                    <div class="absolute top-4 right-4 bg-black/50 backdrop-blur-sm text-white px-3 py-1 rounded-full text-sm">
                        ۳ دقیقه مطالعه
                    </div>
                    
                    <!-- Category Badge -->
                    <div class="absolute bottom-4 right-4 bg-hotel-gold text-hotel-dark px-3 py-1 rounded-full text-sm font-bold">
                        راهنمای سفر
                    </div>
                </div>
                
                <!-- Post Content -->
                <div class="p-6">
                    <!-- Post Meta -->
                    <div class="flex items-center space-x-4 space-x-reverse mb-3 text-sm text-gray-500">
                        <div class="flex items-center space-x-1 space-x-reverse">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/>
                            </svg>
                            <span><?php echo $post_date; ?></span>
                        </div>
                        <div class="flex items-center space-x-1 space-x-reverse">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            <span>۴.۸</span>
                        </div>
                    </div>
                    
                    <!-- Post Title -->
                    <h3 class="font-playfair text-xl font-bold text-hotel-dark mb-3 group-hover:text-hotel-gold transition-colors duration-300 line-clamp-2">
                        <?php echo htmlspecialchars($post['title']); ?>
                    </h3>
                    
                    <!-- Post Summary -->
                    <p class="text-gray-600 mb-4 leading-relaxed line-clamp-3">
                        <?php echo htmlspecialchars($post['summary']); ?>
                    </p>
                    
                    <!-- Read More Button -->
                    <a href="post.php?id=<?php echo $post['id']; ?>&lang=<?php echo $lang_code; ?>" 
                       class="inline-flex items-center space-x-2 space-x-reverse text-hotel-dark hover:text-hotel-gold transition-colors duration-300 font-semibold">
                        <span>ادامه مطالعه</span>
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                </div>
            </article>
            <?php endwhile; $stmt->close(); ?>
        </div>

        <!-- Load More Button -->
        <div class="text-center mt-12">
            <button class="bg-hotel-gold text-hotel-dark px-8 py-3 rounded-lg hover:bg-hotel-gold/90 transition-colors duration-300 font-bold">
                مقالات بیشتر
            </button>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="py-20 bg-gradient-to-r from-hotel-dark to-hotel-blue">
    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <div x-data x-intersect="$el.classList.add('animate-fade-in-up')">
            <h2 class="font-playfair text-3xl md:text-4xl font-bold text-white mb-6">
                عضویت در خبرنامه
            </h2>
            <p class="text-white/90 text-lg mb-8 leading-relaxed">
                آخرین مقالات، پیشنهادات سفر و تخفیف‌های ویژه را مستقیماً در ایمیل خود دریافت کنید
            </p>
            
            <!-- Newsletter Form -->
            <div class="max-w-md mx-auto">
                <div class="flex flex-col sm:flex-row gap-4">
                    <input type="email" 
                           placeholder="آدرس ایمیل شما" 
                           class="flex-1 px-4 py-3 rounded-lg border-0 focus:outline-none focus:ring-2 focus:ring-hotel-gold text-hotel-dark">
                    <button class="bg-hotel-gold text-hotel-dark px-6 py-3 rounded-lg font-bold hover:bg-hotel-gold/90 transition-colors duration-300 whitespace-nowrap">
                        عضویت
                    </button>
                </div>
                <p class="text-white/70 text-sm mt-3">
                    با عضویت، شرایط و قوانین ما را می‌پذیرید
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Popular Tags Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
            <h2 class="font-playfair text-3xl md:text-4xl font-bold text-hotel-dark mb-4">
                برچسب‌های محبوب
            </h2>
            <div class="w-20 h-1 bg-hotel-gold mx-auto mb-6"></div>
        </div>
        
        <!-- Tags Cloud -->
        <div class="flex flex-wrap justify-center gap-3" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
            <span class="bg-hotel-gold/20 text-hotel-dark px-4 py-2 rounded-full text-sm hover:bg-hotel-gold/30 transition-colors duration-300 cursor-pointer">
                #سفر_تهران
            </span>
            <span class="bg-hotel-gold/20 text-hotel-dark px-4 py-2 rounded-full text-sm hover:bg-hotel-gold/30 transition-colors duration-300 cursor-pointer">
                #هتل_لوکس
            </span>
            <span class="bg-hotel-gold/20 text-hotel-dark px-4 py-2 rounded-full text-sm hover:bg-hotel-gold/30 transition-colors duration-300 cursor-pointer">
                #راهنمای_سفر
            </span>
            <span class="bg-hotel-gold/20 text-hotel-dark px-4 py-2 rounded-full text-sm hover:bg-hotel-gold/30 transition-colors duration-300 cursor-pointer">
                #گردشگری
            </span>
            <span class="bg-hotel-gold/20 text-hotel-dark px-4 py-2 rounded-full text-sm hover:bg-hotel-gold/30 transition-colors duration-300 cursor-pointer">
                #اقامت
            </span>
            <span class="bg-hotel-gold/20 text-hotel-dark px-4 py-2 rounded-full text-sm hover:bg-hotel-gold/30 transition-colors duration-300 cursor-pointer">
                #تجربه_سفر
            </span>
            <span class="bg-hotel-gold/20 text-hotel-dark px-4 py-2 rounded-full text-sm hover:bg-hotel-gold/30 transition-colors duration-300 cursor-pointer">
                #رستوران
            </span>
            <span class="bg-hotel-gold/20 text-hotel-dark px-4 py-2 rounded-full text-sm hover:bg-hotel-gold/30 transition-colors duration-300 cursor-pointer">
                #خدمات_هتل
            </span>
        </div>
    </div>
</section>

<?php include_once 'includes/footer.php'; ?>
