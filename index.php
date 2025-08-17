<?php include_once 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="relative min-h-screen flex items-center justify-center overflow-hidden">
    <!-- Video Background -->
    <div class="absolute inset-0 w-full h-full">
        <video autoplay muted loop poster="assets/images/hero-poster1.jpg" class="w-full h-full object-cover">
            <source src="assets/videos/hotel-intro.mp4" type="video/mp4">
        </video>
    </div>
    
    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/50"></div>
    
    <!-- Hero Content -->
    <div class="relative z-10 text-center text-white px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
        <h1 class="font-playfair text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold mb-6 fade-in-up opacity-0">
            <?php echo $lang['hero_title']; ?>
        </h1>
        <p class="text-lg sm:text-xl md:text-2xl mb-8 max-w-2xl mx-auto leading-relaxed fade-in-up opacity-0 delay-1">
            <?php echo $lang['hero_subtitle']; ?>
        </p>
        <a href="rooms.php?lang=<?php echo $lang_code; ?>" 
           class="inline-block bg-hotel-gold text-hotel-dark px-8 py-4 rounded-lg font-bold text-lg hover:bg-hotel-gold/90 transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl fade-in-up opacity-0 delay-2">
            <?php echo $lang['hero_button']; ?>
        </a>
    </div>
    
    <!-- Scroll Indicator -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
        </svg>
    </div>
</section>

<!-- Rooms Preview Section -->
<section class="py-20 bg-hotel-cream">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Title -->
        <div class="text-center mb-16" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
            <h2 class="font-playfair text-4xl md:text-5xl font-bold text-hotel-dark mb-4">
                <?php echo $lang['rooms_section_title']; ?>
            </h2>
            <div class="w-20 h-1 bg-hotel-gold mx-auto"></div>
        </div>

        <!-- Rooms Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
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
            <div class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:scale-105 transition-all duration-300 hover:shadow-2xl"
                 x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                <!-- Room Image -->
                <div class="relative overflow-hidden h-64">
                    <img src="uploads/rooms/<?php echo htmlspecialchars($room['image']); ?>" 
                         alt="<?php echo htmlspecialchars($room['name']); ?>"
                         class="w-full h-full object-cover transform hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                </div>
                
                <!-- Room Content -->
                <div class="p-6">
                    <h3 class="font-playfair text-2xl font-bold text-hotel-dark mb-3">
                        <?php echo htmlspecialchars($room['name']); ?>
                    </h3>
                    <p class="text-gray-600 mb-4 leading-relaxed">
                        <?php echo htmlspecialchars($room['short_description']); ?>
                    </p>
                    <div class="flex justify-between items-center">
                        <span class="text-hotel-gold font-bold text-xl">
                            <?php echo number_format($room['price_per_night']); ?> تومان
                        </span>
                        <a href="room-details.php?id=<?php echo $room['id']; ?>&lang=<?php echo $lang_code; ?>" 
                           class="bg-hotel-dark text-white px-6 py-2 rounded-lg hover:bg-hotel-dark/90 transition-colors duration-300">
                            <?php echo $lang['view_details']; ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; $stmt->close(); ?>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-20 bg-hotel-sand">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Title -->
        <div class="text-center mb-16" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
            <h2 class="font-playfair text-4xl md:text-5xl font-bold text-hotel-dark mb-4">
                <?php echo $lang['testimonials_section_title']; ?>
            </h2>
            <div class="w-20 h-1 bg-hotel-gold mx-auto"></div>
        </div>

        <!-- Testimonials Slider -->
        <div x-data="{
            currentSlide: 0,
            testimonials: [],
            init() {
                this.testimonials = Array.from(this.$refs.slider.children);
                this.autoPlay();
            },
            nextSlide() {
                this.currentSlide = (this.currentSlide + 1) % this.testimonials.length;
            },
            prevSlide() {
                this.currentSlide = this.currentSlide === 0 ? this.testimonials.length - 1 : this.currentSlide - 1;
            },
            autoPlay() {
                setInterval(() => {
                    this.nextSlide();
                }, 5000);
            }
        }" class="relative">
            
            <div class="overflow-hidden rounded-xl">
                <div x-ref="slider" 
                     class="flex transition-transform duration-500 ease-in-out"
                     :style="`transform: translateX(-${currentSlide * 100}%)`">
                    
                    <?php
                    // کد جدید برای خواندن نظرات از جدول room_reviews
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
                    <div class="w-full flex-shrink-0 px-4">
                        <div class="bg-white rounded-xl p-8 shadow-lg text-center max-w-2xl mx-auto">
                            <div class="text-hotel-gold text-4xl mb-4">"</div>
                            <p class="text-gray-700 text-lg italic mb-6 leading-relaxed">
                                <?php echo htmlspecialchars($review['comment']); ?>
                            </p>
                            <cite class="text-hotel-dark font-bold text-xl not-italic">
                                - <?php echo htmlspecialchars($review['customer_name']); ?>
                            </cite>
                        </div>
                    </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <!-- Default testimonial if no reviews -->
                    <div class="w-full flex-shrink-0 px-4">
                        <div class="bg-white rounded-xl p-8 shadow-lg text-center max-w-2xl mx-auto">
                            <div class="text-hotel-gold text-4xl mb-4">"</div>
                            <p class="text-gray-700 text-lg italic mb-6 leading-relaxed">
                                تجربه فوق‌العاده‌ای در این هتل داشتم. خدمات عالی و محیط بسیار آرام و زیبا.
                            </p>
                            <cite class="text-hotel-dark font-bold text-xl not-italic">
                                - مهمان محترم
                            </cite>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Navigation Dots -->
            <div class="flex justify-center mt-8 space-x-2">
                <?php 
                $review_count = $result ? $result->num_rows : 1;
                for($i = 0; $i < max($review_count, 1); $i++): 
                ?>
                <button @click="currentSlide = <?php echo $i; ?>"
                        :class="currentSlide === <?php echo $i; ?> ? 'bg-hotel-gold' : 'bg-gray-300'"
                        class="w-3 h-3 rounded-full transition-colors duration-300"></button>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</section>

<?php include_once 'includes/footer.php'; ?>
