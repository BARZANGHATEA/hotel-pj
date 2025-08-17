<?php include_once 'includes/header.php'; ?>

<!-- Rooms Hero Section -->
<section class="relative min-h-[60vh] flex items-center justify-center overflow-hidden bg-gradient-to-br from-hotel-dark via-hotel-blue to-hotel-dark">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0 bg-repeat" style="background-image: url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23FFD700" fill-opacity="0.1"><circle cx="30" cy="30" r="2"/></g></g></svg>');"></div>
    </div>
    
    <!-- Hero Content -->
    <div class="relative z-10 text-center text-white px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
        <h1 class="font-playfair text-4xl sm:text-5xl md:text-6xl font-bold mb-6 fade-in-up opacity-0">
            اتاق‌ها و سوئیت‌ها
        </h1>
        <p class="text-lg sm:text-xl md:text-2xl mb-8 max-w-2xl mx-auto leading-relaxed fade-in-up opacity-0 delay-1">
            فضایی برای هر سلیقه، طراحی شده برای آرامش شما
        </p>
        <div class="w-20 h-1 bg-hotel-gold mx-auto fade-in-up opacity-0 delay-2"></div>
    </div>
</section>

<!-- Filter Section -->
<section class="py-8 bg-hotel-cream border-b border-hotel-gold/20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <!-- Filter Options -->
            <div class="flex flex-wrap items-center gap-4">
                <span class="text-hotel-dark font-semibold">فیلتر بر اساس:</span>
                <select class="bg-white border border-hotel-gold/30 rounded-lg px-4 py-2 text-hotel-dark focus:outline-none focus:border-hotel-gold transition-colors duration-300">
                    <option>همه اتاق‌ها</option>
                    <option>اتاق استاندارد</option>
                    <option>اتاق دلوکس</option>
                    <option>سوئیت</option>
                </select>
                <select class="bg-white border border-hotel-gold/30 rounded-lg px-4 py-2 text-hotel-dark focus:outline-none focus:border-hotel-gold transition-colors duration-300">
                    <option>قیمت: همه</option>
                    <option>کمتر از ۵۰۰,۰۰۰ تومان</option>
                    <option>۵۰۰,۰۰۰ - ۱,۰۰۰,۰۰۰ تومان</option>
                    <option>بیشتر از ۱,۰۰۰,۰۰۰ تومان</option>
                </select>
            </div>
            
            <!-- View Toggle -->
            <div class="flex items-center gap-2">
                <span class="text-hotel-dark text-sm">نمایش:</span>
                <button class="p-2 bg-hotel-gold text-hotel-dark rounded-lg">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z"/>
                    </svg>
                </button>
                <button class="p-2 bg-white border border-hotel-gold/30 text-hotel-dark rounded-lg hover:bg-hotel-gold/10 transition-colors duration-300">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M4 4h4v4H4V4zm6 0h4v4h-4V4zm6 0h4v4h-4V4zM4 10h4v4H4v-4zm6 0h4v4h-4v-4zm6 0h4v4h-4v-4zM4 16h4v4H4v-4zm6 0h4v4h-4v-4zm6 0h4v4h-4v-4z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Rooms Grid Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Rooms Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
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
            <div class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:scale-105 transition-all duration-300 hover:shadow-2xl group"
                 x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                
                <!-- Room Image -->
                <div class="relative overflow-hidden h-64">
                    <img src="uploads/rooms/<?php echo htmlspecialchars($room['image']); ?>" 
                         alt="<?php echo htmlspecialchars($room['name']); ?>"
                         class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500">
                    
                    <!-- Overlay with Quick Actions -->
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                        <div class="flex space-x-4 space-x-reverse">
                            <button class="bg-white/20 backdrop-blur-sm text-white p-3 rounded-full hover:bg-white/30 transition-colors duration-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                            <button class="bg-white/20 backdrop-blur-sm text-white p-3 rounded-full hover:bg-white/30 transition-colors duration-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Price Badge -->
                    <div class="absolute top-4 right-4 bg-hotel-gold text-hotel-dark px-3 py-1 rounded-full text-sm font-bold">
                        <?php echo number_format($room['price_per_night']); ?> تومان
                    </div>
                </div>
                
                <!-- Room Content -->
                <div class="p-6">
                    <h3 class="font-playfair text-2xl font-bold text-hotel-dark mb-3 group-hover:text-hotel-gold transition-colors duration-300">
                        <?php echo htmlspecialchars($room['name']); ?>
                    </h3>
                    <p class="text-gray-600 mb-4 leading-relaxed">
                        <?php echo htmlspecialchars($room['short_description']); ?>
                    </p>
                    
                    <!-- Room Features -->
                    <div class="flex items-center space-x-4 space-x-reverse mb-4 text-sm text-gray-500">
                        <div class="flex items-center space-x-1 space-x-reverse">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            <span>وای‌فای رایگان</span>
                        </div>
                        <div class="flex items-center space-x-1 space-x-reverse">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            <span>صبحانه</span>
                        </div>
                        <div class="flex items-center space-x-1 space-x-reverse">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            <span>تلویزیون</span>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex space-x-3 space-x-reverse">
                        <a href="room-details.php?id=<?php echo $room['id']; ?>&lang=<?php echo $lang_code; ?>" 
                           class="flex-1 bg-hotel-dark text-white text-center px-4 py-3 rounded-lg hover:bg-hotel-dark/90 transition-colors duration-300 font-semibold">
                            مشاهده جزئیات
                        </a>
                        <button class="bg-hotel-gold text-hotel-dark px-4 py-3 rounded-lg hover:bg-hotel-gold/90 transition-colors duration-300 font-semibold">
                            رزرو سریع
                        </button>
                    </div>
                </div>
            </div>
            <?php endwhile; $stmt->close(); ?>
        </div>

        <!-- Load More Button -->
        <div class="text-center mt-12">
            <button class="bg-hotel-gold text-hotel-dark px-8 py-3 rounded-lg hover:bg-hotel-gold/90 transition-colors duration-300 font-bold">
                نمایش اتاق‌های بیشتر
            </button>
        </div>
    </div>
</section>

<!-- Special Offers Section -->
<section class="py-20 bg-hotel-sand">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Title -->
        <div class="text-center mb-16" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
            <h2 class="font-playfair text-4xl md:text-5xl font-bold text-hotel-dark mb-4">
                پیشنهادات ویژه
            </h2>
            <div class="w-20 h-1 bg-hotel-gold mx-auto mb-6"></div>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                از تخفیف‌های ویژه و پکیج‌های اقامت ما بهره‌مند شوید
            </p>
        </div>

        <!-- Offers Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Offer 1 -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:scale-105 transition-all duration-300"
                 x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                <div class="bg-gradient-to-r from-hotel-gold to-hotel-blue p-6 text-white text-center">
                    <h3 class="font-playfair text-2xl font-bold mb-2">پکیج عاشقان</h3>
                    <p class="text-lg">۲۰% تخفیف برای اقامت دو شب</p>
                </div>
                <div class="p-6">
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-center space-x-2 space-x-reverse">
                            <svg class="w-4 h-4 text-hotel-gold" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            <span>شام رمانتیک رایگان</span>
                        </li>
                        <li class="flex items-center space-x-2 space-x-reverse">
                            <svg class="w-4 h-4 text-hotel-gold" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            <span>تزئین اتاق با گل</span>
                        </li>
                        <li class="flex items-center space-x-2 space-x-reverse">
                            <svg class="w-4 h-4 text-hotel-gold" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            <span>چک‌اوت تا ساعت ۱۴</span>
                        </li>
                    </ul>
                    <button class="w-full mt-4 bg-hotel-gold text-hotel-dark py-2 rounded-lg hover:bg-hotel-gold/90 transition-colors duration-300 font-bold">
                        رزرو کنید
                    </button>
                </div>
            </div>

            <!-- Offer 2 -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:scale-105 transition-all duration-300"
                 x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                <div class="bg-gradient-to-r from-hotel-blue to-hotel-dark p-6 text-white text-center">
                    <h3 class="font-playfair text-2xl font-bold mb-2">پکیج خانوادگی</h3>
                    <p class="text-lg">۱۵% تخفیف برای خانواده‌ها</p>
                </div>
                <div class="p-6">
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-center space-x-2 space-x-reverse">
                            <svg class="w-4 h-4 text-hotel-gold" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            <span>صبحانه رایگان کودکان</span>
                        </li>
                        <li class="flex items-center space-x-2 space-x-reverse">
                            <svg class="w-4 h-4 text-hotel-gold" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            <span>اتاق اضافی با ۵۰% تخفیف</span>
                        </li>
                        <li class="flex items-center space-x-2 space-x-reverse">
                            <svg class="w-4 h-4 text-hotel-gold" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            <span>بازی‌های کودکان</span>
                        </li>
                    </ul>
                    <button class="w-full mt-4 bg-hotel-gold text-hotel-dark py-2 rounded-lg hover:bg-hotel-gold/90 transition-colors duration-300 font-bold">
                        رزرو کنید
                    </button>
                </div>
            </div>

            <!-- Offer 3 -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:scale-105 transition-all duration-300"
                 x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                <div class="bg-gradient-to-r from-hotel-dark to-hotel-gold p-6 text-white text-center">
                    <h3 class="font-playfair text-2xl font-bold mb-2">پکیج تجاری</h3>
                    <p class="text-lg">۱۰% تخفیف برای مسافران کاری</p>
                </div>
                <div class="p-6">
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-center space-x-2 space-x-reverse">
                            <svg class="w-4 h-4 text-hotel-gold" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            <span>اینترنت پرسرعت</span>
                        </li>
                        <li class="flex items-center space-x-2 space-x-reverse">
                            <svg class="w-4 h-4 text-hotel-gold" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            <span>دسترسی به سالن کنفرانس</span>
                        </li>
                        <li class="flex items-center space-x-2 space-x-reverse">
                            <svg class="w-4 h-4 text-hotel-gold" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            <span>چاپ و فکس رایگان</span>
                        </li>
                    </ul>
                    <button class="w-full mt-4 bg-hotel-gold text-hotel-dark py-2 rounded-lg hover:bg-hotel-gold/90 transition-colors duration-300 font-bold">
                        رزرو کنید
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include_once 'includes/footer.php'; ?>
