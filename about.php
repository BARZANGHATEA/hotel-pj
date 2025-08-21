<?php include_once 'includes/header.php'; ?>

<!-- About Hero Section -->
<section class="relative min-h-[70vh] flex items-center justify-center overflow-hidden bg-gradient-to-br from-hotel-dark to-hotel-blue">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0 bg-repeat" style="background-image: url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23FFD700" fill-opacity="0.1"><circle cx="30" cy="30" r="2"/></g></g></svg>');"></div>
    </div>
    
    <!-- Hero Content -->
    <div class="relative z-10 text-center text-white px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
        <h1 class="font-playfair text-4xl sm:text-5xl md:text-6xl font-bold mb-6 fade-in-up opacity-0">
            درباره هتل سیروان
        </h1>
        <p class="text-lg sm:text-xl md:text-2xl mb-8 max-w-2xl mx-auto leading-relaxed fade-in-up opacity-0 delay-1">
            تجربه‌ای لوکس و بی‌نظیر در قلب شهر
        </p>
        <div class="w-20 h-1 bg-hotel-gold mx-auto fade-in-up opacity-0 delay-2"></div>
    </div>
</section>

<!-- Hotel Story Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <!-- Text Content -->
            <div class="space-y-6" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                <h2 class="font-playfair text-3xl md:text-4xl font-bold text-hotel-dark mb-6">
                    داستان ما
                </h2>
                <p class="text-gray-700 leading-relaxed text-lg">
                    هتل سیروان از سال ۱۳۸۰ با هدف ارائه بهترین خدمات مهمان‌نوازی در تهران تأسیس شد. ما با ترکیب معماری کلاسیک و امکانات مدرن، فضایی منحصر به فرد برای اقامت شما فراهم کرده‌ایم.
                </p>
                <p class="text-gray-700 leading-relaxed text-lg">
                    تیم مجرب ما با بیش از ۲۰ سال تجربه در صنعت هتل‌داری، همواره در تلاش است تا لحظات فراموش‌نشدنی برای مهمانان عزیز خود خلق کند.
                </p>
                <div class="flex items-center space-x-4 space-x-reverse">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-hotel-gold">20+</div>
                        <div class="text-gray-600">سال تجربه</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-hotel-gold">50+</div>
                        <div class="text-gray-600">اتاق لوکس</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-hotel-gold">1000+</div>
                        <div class="text-gray-600">مهمان راضی</div>
                    </div>
                </div>
            </div>
            
            <!-- Image -->
            <div class="relative" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                <img src="assets/images/tourism2.jpg" 
                     alt="Hotel Interior" 
                     class="w-full h-96 object-cover rounded-xl shadow-2xl">
                <div class="absolute -bottom-6 -right-6 w-32 h-32 bg-hotel-gold rounded-xl flex items-center justify-center">
                    <div class="text-center text-hotel-dark">
                        <div class="text-2xl font-bold">4.8</div>
                        <div class="text-sm">امتیاز مهمانان</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-20 bg-hotel-cream">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Title -->
        <div class="text-center mb-16" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
            <h2 class="font-playfair text-4xl md:text-5xl font-bold text-hotel-dark mb-4">
                امکانات و خدمات
            </h2>
            <div class="w-20 h-1 bg-hotel-gold mx-auto"></div>
        </div>

        <!-- Features Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="bg-white p-8 rounded-xl shadow-lg text-center transform hover:scale-105 transition-all duration-300"
                 x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                <div class="w-16 h-16 bg-hotel-gold/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-hotel-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                    </svg>
                </div>
                <h3 class="font-playfair text-xl font-bold text-hotel-dark mb-3">اتاق‌های لوکس</h3>
                <p class="text-gray-600 leading-relaxed">اتاق‌های مجهز با امکانات مدرن و دکوراسیون زیبا</p>
            </div>

            <!-- Feature 2 -->
            <div class="bg-white p-8 rounded-xl shadow-lg text-center transform hover:scale-105 transition-all duration-300"
                 x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                <div class="w-16 h-16 bg-hotel-gold/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-hotel-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                    </svg>
                </div>
                <h3 class="font-playfair text-xl font-bold text-hotel-dark mb-3">رستوران درجه یک</h3>
                <p class="text-gray-600 leading-relaxed">غذاهای محلی و بین‌المللی با کیفیت بالا</p>
            </div>

            <!-- Feature 3 -->
            <div class="bg-white p-8 rounded-xl shadow-lg text-center transform hover:scale-105 transition-all duration-300"
                 x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                <div class="w-16 h-16 bg-hotel-gold/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-hotel-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <h3 class="font-playfair text-xl font-bold text-hotel-dark mb-3">امنیت ۲۴ ساعته</h3>
                <p class="text-gray-600 leading-relaxed">نگهبانی و امنیت کامل در تمام ساعات شبانه‌روز</p>
            </div>

            <!-- Feature 4 -->
            <div class="bg-white p-8 rounded-xl shadow-lg text-center transform hover:scale-105 transition-all duration-300"
                 x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                <div class="w-16 h-16 bg-hotel-gold/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-hotel-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                    </svg>
                </div>
                <h3 class="font-playfair text-xl font-bold text-hotel-dark mb-3">وای‌فای رایگان</h3>
                <p class="text-gray-600 leading-relaxed">اینترنت پرسرعت و رایگان در تمام قسمت‌های هتل</p>
            </div>

            <!-- Feature 5 -->
            <div class="bg-white p-8 rounded-xl shadow-lg text-center transform hover:scale-105 transition-all duration-300"
                 x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                <div class="w-16 h-16 bg-hotel-gold/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-hotel-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <h3 class="font-playfair text-xl font-bold text-hotel-dark mb-3">پارکینگ اختصاصی</h3>
                <p class="text-gray-600 leading-relaxed">پارکینگ امن و رایگان برای خودروی شما</p>
            </div>

            <!-- Feature 6 -->
            <div class="bg-white p-8 rounded-xl shadow-lg text-center transform hover:scale-105 transition-all duration-300"
                 x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                <div class="w-16 h-16 bg-hotel-gold/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-hotel-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
                <h3 class="font-playfair text-xl font-bold text-hotel-dark mb-3">خدمات اتاق</h3>
                <p class="text-gray-600 leading-relaxed">سرویس اتاق ۲۴ ساعته برای راحتی بیشتر شما</p>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Title -->
        <div class="text-center mb-16" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
            <h2 class="font-playfair text-4xl md:text-5xl font-bold text-hotel-dark mb-4">
                تیم مدیریت
            </h2>
            <div class="w-20 h-1 bg-hotel-gold mx-auto mb-6"></div>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                تیم مجرب و حرفه‌ای ما آماده ارائه بهترین خدمات به شما عزیزان است
            </p>
        </div>

        <!-- Team Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Team Member 1 -->
            <div class="text-center group" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                <div class="relative mb-6 mx-auto w-48 h-48">
                    <div class="w-full h-full bg-gradient-to-br from-hotel-gold to-hotel-blue rounded-full flex items-center justify-center">
                        <svg class="w-24 h-24 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="font-playfair text-xl font-bold text-hotel-dark mb-2">احمد محمدی</h3>
                <p class="text-hotel-gold font-semibold mb-2">مدیر عامل</p>
                <p class="text-gray-600 text-sm">بیش از ۱۵ سال تجربه در مدیریت هتل‌های لوکس</p>
            </div>

            <!-- Team Member 2 -->
            <div class="text-center group" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                <div class="relative mb-6 mx-auto w-48 h-48">
                    <div class="w-full h-full bg-gradient-to-br from-hotel-blue to-hotel-gold rounded-full flex items-center justify-center">
                        <svg class="w-24 h-24 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="font-playfair text-xl font-bold text-hotel-dark mb-2">فاطمه احمدی</h3>
                <p class="text-hotel-gold font-semibold mb-2">مدیر خدمات مهمانان</p>
                <p class="text-gray-600 text-sm">متخصص در ارائه خدمات مهمان‌نوازی درجه یک</p>
            </div>

            <!-- Team Member 3 -->
            <div class="text-center group" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                <div class="relative mb-6 mx-auto w-48 h-48">
                    <div class="w-full h-full bg-gradient-to-br from-hotel-gold to-hotel-blue rounded-full flex items-center justify-center">
                        <svg class="w-24 h-24 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="font-playfair text-xl font-bold text-hotel-dark mb-2">علی رضایی</h3>
                <p class="text-hotel-gold font-semibold mb-2">سرآشپز اجرایی</p>
                <p class="text-gray-600 text-sm">استاد آشپزی با تجربه در رستوران‌های معتبر جهان</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-gradient-to-r from-hotel-dark to-hotel-blue">
    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <div x-data x-intersect="$el.classList.add('animate-fade-in-up')">
            <h2 class="font-playfair text-3xl md:text-4xl font-bold text-white mb-6">
                آماده تجربه اقامتی فراموش‌نشدنی هستید؟
            </h2>
            <p class="text-white/90 text-lg mb-8 leading-relaxed">
                همین امروز اتاق خود را رزرو کنید و از خدمات بی‌نظیر هتل سیروان لذت ببرید
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="rooms.php?lang=<?php echo $lang_code; ?>" 
                   class="bg-hotel-gold text-hotel-dark px-8 py-4 rounded-lg font-bold text-lg hover:bg-hotel-gold/90 transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl">
                    مشاهده اتاق‌ها
                </a>
                <a href="contact.php?lang=<?php echo $lang_code; ?>" 
                   class="border-2 border-white text-white px-8 py-4 rounded-lg font-bold text-lg hover:bg-white hover:text-hotel-dark transition-all duration-300">
                    تماس با ما
                </a>
            </div>
        </div>
    </div>
</section>

<?php include_once 'includes/footer.php'; ?>
