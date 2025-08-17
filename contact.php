
<?php
// این متغیرها برای نمایش پیام به کاربر هستند
$message_sent = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // اطلاعات را از فرم دریافت و پاکسازی کن
    $name = htmlspecialchars(strip_tags($_POST['name']));
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $subject = htmlspecialchars(strip_tags($_POST['subject']));
    $message_body = htmlspecialchars(strip_tags($_POST['message']));

    // اعتبارسنجی ایمیل
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // اطلاعات ایمیلی که می‌خواهید دریافت کنید
        $to = "your-email@example.com"; // <-- ایمیل خود را اینجا وارد کنید
        $email_subject = "پیام جدید از سایت هتل: " . $subject;

        $email_content = "نام فرستنده: $name\n";
        $email_content .= "ایمیل فرستنده: $email\n\n";
        $email_content .= "متن پیام:\n$message_body\n";

        // هدرهای ایمیل برای پشتیبانی از کاراکترهای فارسی
        $headers = "From: no-reply@yourhotel.com\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "MIME-Version: 1.0\r\n";

        // تابع ارسال ایمیل
        if (mail($to, $email_subject, $email_content, $headers)) {
            $message_sent = true;
        } else {
            $error_message = "خطایی در ارسال ایمیل رخ داد. لطفاً بعداً تلاش کنید.";
        }
    } else {
        $error_message = "آدرس ایمیل وارد شده معتبر نیست.";
    }
}
include_once 'includes/header.php'; ?>

<!-- Contact Hero Section -->
<section class="relative min-h-[60vh] flex items-center justify-center overflow-hidden bg-gradient-to-br from-hotel-dark via-hotel-blue to-hotel-dark">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0 bg-repeat" style="background-image: url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23FFD700" fill-opacity="0.1"><circle cx="30" cy="30" r="2"/></g></g></svg>');"></div>
    </div>
    
    <!-- Hero Content -->
    <div class="relative z-10 text-center text-white px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
        <h1 class="font-playfair text-4xl sm:text-5xl md:text-6xl font-bold mb-6 fade-in-up opacity-0">
            تماس با ما
        </h1>
        <p class="text-lg sm:text-xl md:text-2xl mb-8 max-w-2xl mx-auto leading-relaxed fade-in-up opacity-0 delay-1">
            ما همیشه برای شنیدن صدای شما آماده‌ایم
        </p>
        <div class="w-20 h-1 bg-hotel-gold mx-auto fade-in-up opacity-0 delay-2"></div>
    </div>
</section>

<!-- Contact Information Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Contact Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
            <!-- Address Card -->
            <div class="text-center p-8 bg-hotel-cream rounded-xl shadow-lg transform hover:scale-105 transition-all duration-300"
                 x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                <div class="w-16 h-16 bg-hotel-gold/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-hotel-gold" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-4.198 0-8 3.402-8 7.602 0 4.198 3.469 9.21 8 16.398 4.531-7.188 8-12.2 8-16.398 0-4.198-3.801-7.602-8-7.602zm0 11c-1.657 0-3-1.343-3-3s1.343-3 3-3 3 1.343 3 3-1.343 3-3 3z"/>
                    </svg>
                </div>
                <h3 class="font-playfair text-xl font-bold text-hotel-dark mb-3">آدرس هتل</h3>
                <p class="text-gray-600 leading-relaxed">ایران، تهران، خیابان فرشته، پلاک ۱۰</p>
            </div>

            <!-- Phone Card -->
            <div class="text-center p-8 bg-hotel-cream rounded-xl shadow-lg transform hover:scale-105 transition-all duration-300"
                 x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                <div class="w-16 h-16 bg-hotel-gold/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-hotel-gold" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20 22.621l-3.521-6.795c-.008.004-1.974.97-2.064 1.011-2.24 1.086-6.799-7.82-4.609-8.994l2.083-1.028-3.493-6.817-2.105 1.039c-7.202 3.755 4.233 25.982 11.6 22.615.121-.055 2.102-1.029 2.114-1.036.022-.012.008-.005-.009.004z"/>
                    </svg>
                </div>
                <h3 class="font-playfair text-xl font-bold text-hotel-dark mb-3">تلفن تماس</h3>
                <a href="tel:+982112345678" class="text-hotel-blue hover:text-hotel-gold transition-colors duration-300 font-semibold" dir="ltr">
                    +۹۸ (۲۱) ۱۲۳۴ ۵۶۷۸
                </a>
            </div>

            <!-- Email Card -->
            <div class="text-center p-8 bg-hotel-cream rounded-xl shadow-lg transform hover:scale-105 transition-all duration-300"
                 x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                <div class="w-16 h-16 bg-hotel-gold/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-hotel-gold" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M0 3v18h24v-18h-24zm21.518 2l-9.518 7.713-9.518-7.713h19.036zm-19.518 14v-11.817l10 8.104 10-8.104v11.817h-20z"/>
                    </svg>
                </div>
                <h3 class="font-playfair text-xl font-bold text-hotel-dark mb-3">ایمیل</h3>
                <a href="mailto:reserve@palacehotel.com" class="text-hotel-blue hover:text-hotel-gold transition-colors duration-300 font-semibold">
                    reserve@palacehotel.com
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form & Map Section -->
<section class="py-20 bg-hotel-sand">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Contact Form -->
            <div class="bg-white rounded-xl shadow-xl p-8" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                <h2 class="font-playfair text-3xl font-bold text-hotel-dark mb-6">برای ما پیام بگذارید</h2>
                
                <?php if ($message_sent): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                        پیام شما با موفقیت ارسال شد. به زودی با شما تماس خواهیم گرفت.
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form action="contact.php" method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-hotel-dark mb-2">نام شما</label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   required
                                   class="w-full px-4 py-3 border border-hotel-gold/30 rounded-lg focus:outline-none focus:border-hotel-gold transition-colors duration-300">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-semibold text-hotel-dark mb-2">ایمیل شما</label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   required
                                   class="w-full px-4 py-3 border border-hotel-gold/30 rounded-lg focus:outline-none focus:border-hotel-gold transition-colors duration-300">
                        </div>
                    </div>
                    
                    <div>
                        <label for="subject" class="block text-sm font-semibold text-hotel-dark mb-2">موضوع</label>
                        <input type="text" 
                               id="subject" 
                               name="subject" 
                               required
                               class="w-full px-4 py-3 border border-hotel-gold/30 rounded-lg focus:outline-none focus:border-hotel-gold transition-colors duration-300">
                    </div>
                    
                    <div>
                        <label for="message" class="block text-sm font-semibold text-hotel-dark mb-2">پیام شما</label>
                        <textarea id="message" 
                                  name="message" 
                                  rows="5" 
                                  required
                                  class="w-full px-4 py-3 border border-hotel-gold/30 rounded-lg focus:outline-none focus:border-hotel-gold transition-colors duration-300 resize-none"></textarea>
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-hotel-gold text-hotel-dark px-8 py-4 rounded-lg font-bold text-lg hover:bg-hotel-gold/90 transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl">
                        ارسال پیام
                    </button>
                </form>
            </div>

            <!-- Map -->
            <div class="bg-white rounded-xl shadow-xl overflow-hidden" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
                <div class="p-6 bg-hotel-dark text-white">
                    <h3 class="font-playfair text-2xl font-bold mb-2">موقعیت هتل</h3>
                    <p class="text-hotel-cream">ما را در نقشه پیدا کنید</p>
                </div>
                <div class="h-96">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3238.252611728108!2d51.41913411526118!3d35.74457008017949!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3f8e06cb3a701b31%3A0x7c73752c06173d1!2sTehran%2C%20Tehran%20Province%2C%20Iran!5e0!3m2!1sen!2s!4v1677682836541!5m2!1sen!2s" 
                            width="100%" 
                            height="100%" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade"
                            class="w-full h-full">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-20 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Title -->
        <div class="text-center mb-16" x-data x-intersect="$el.classList.add('animate-fade-in-up')">
            <h2 class="font-playfair text-4xl md:text-5xl font-bold text-hotel-dark mb-4">
                سوالات متداول
            </h2>
            <div class="w-20 h-1 bg-hotel-gold mx-auto mb-6"></div>
            <p class="text-gray-600 text-lg">
                پاسخ سوالات رایج مهمانان عزیز
            </p>
        </div>

        <!-- FAQ Items -->
        <div class="space-y-4" x-data="{ openFaq: null }">
            <!-- FAQ 1 -->
            <div class="border border-hotel-gold/20 rounded-lg overflow-hidden">
                <button @click="openFaq = openFaq === 1 ? null : 1" 
                        class="w-full px-6 py-4 text-right bg-hotel-cream hover:bg-hotel-gold/10 transition-colors duration-300 flex justify-between items-center">
                    <span class="font-semibold text-hotel-dark">ساعت چک‌این و چک‌اوت چیست؟</span>
                    <svg :class="openFaq === 1 ? 'rotate-180' : ''" class="w-5 h-5 text-hotel-gold transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="openFaq === 1" x-transition class="px-6 py-4 bg-white">
                    <p class="text-gray-600">چک‌این از ساعت ۱۴:۰۰ و چک‌اوت تا ساعت ۱۲:۰۰ امکان‌پذیر است. در صورت نیاز به چک‌این زودتر یا چک‌اوت دیرتر، لطفاً با ما تماس بگیرید.</p>
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="border border-hotel-gold/20 rounded-lg overflow-hidden">
                <button @click="openFaq = openFaq === 2 ? null : 2" 
                        class="w-full px-6 py-4 text-right bg-hotel-cream hover:bg-hotel-gold/10 transition-colors duration-300 flex justify-between items-center">
                    <span class="font-semibold text-hotel-dark">آیا پارکینگ رایگان دارید؟</span>
                    <svg :class="openFaq === 2 ? 'rotate-180' : ''" class="w-5 h-5 text-hotel-gold transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="openFaq === 2" x-transition class="px-6 py-4 bg-white">
                    <p class="text-gray-600">بله، پارکینگ امن و رایگان برای تمام مهمانان هتل در دسترس است. پارکینگ ما دارای سیستم امنیتی ۲۴ ساعته می‌باشد.</p>
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="border border-hotel-gold/20 rounded-lg overflow-hidden">
                <button @click="openFaq = openFaq === 3 ? null : 3" 
                        class="w-full px-6 py-4 text-right bg-hotel-cream hover:bg-hotel-gold/10 transition-colors duration-300 flex justify-between items-center">
                    <span class="font-semibold text-hotel-dark">آیا اینترنت وای‌فای رایگان دارید؟</span>
                    <svg :class="openFaq === 3 ? 'rotate-180' : ''" class="w-5 h-5 text-hotel-gold transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="openFaq === 3" x-transition class="px-6 py-4 bg-white">
                    <p class="text-gray-600">بله، اینترنت وای‌فای پرسرعت و رایگان در تمام قسمت‌های هتل شامل اتاق‌ها، لابی، رستوران و سایر مناطق عمومی در دسترس است.</p>
                </div>
            </div>

            <!-- FAQ 4 -->
            <div class="border border-hotel-gold/20 rounded-lg overflow-hidden">
                <button @click="openFaq = openFaq === 4 ? null : 4" 
                        class="w-full px-6 py-4 text-right bg-hotel-cream hover:bg-hotel-gold/10 transition-colors duration-300 flex justify-between items-center">
                    <span class="font-semibold text-hotel-dark">آیا امکان لغو رزرو وجود دارد؟</span>
                    <svg :class="openFaq === 4 ? 'rotate-180' : ''" class="w-5 h-5 text-hotel-gold transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="openFaq === 4" x-transition class="px-6 py-4 bg-white">
                    <p class="text-gray-600">لغو رزرو تا ۲۴ ساعت قبل از تاریخ ورود بدون هیچ هزینه‌ای امکان‌پذیر است. برای لغو رزرو، لطفاً با شماره تلفن هتل تماس بگیرید.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include_once 'includes/footer.php'; ?>
