<!-- Footer -->
<footer class="bg-hotel-dark text-hotel-cream">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Hotel Info -->
            <div class="space-y-4">
                <div class="flex items-center space-x-3 <?php echo $page_dir === 'rtl' ? 'space-x-reverse' : ''; ?>">
                    <img src="assets/images/logo-gold.png" alt="Hotel Logo" class="h-10 w-auto">
                    <h3 class="font-playfair text-xl font-bold text-hotel-gold">هتل پالاس</h3>
                </div>
                <p class="text-hotel-cream/80 leading-relaxed">
                    تجربه‌ای لوکس و بی‌نظیر در قلب شهر با خدمات درجه یک و امکانات مدرن.
                </p>
            </div>

            <!-- Contact Info -->
            <div class="space-y-4">
                <h4 class="font-playfair text-lg font-bold text-hotel-gold mb-4">
                    <?php echo $lang['footer_contact_info']; ?>
                </h4>
                <div class="space-y-3">
                    <div class="flex items-start space-x-3 <?php echo $page_dir === 'rtl' ? 'space-x-reverse' : ''; ?>">
                        <svg class="w-5 h-5 text-hotel-gold mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-4.198 0-8 3.402-8 7.602 0 4.198 3.469 9.21 8 16.398 4.531-7.188 8-12.2 8-16.398 0-4.198-3.801-7.602-8-7.602zm0 11c-1.657 0-3-1.343-3-3s1.343-3 3-3 3 1.343 3 3-1.343 3-3 3z"/>
                        </svg>
                        <span class="text-hotel-cream/80">ایران، تهران، خیابان فرشته، پلاک ۱۰</span>
                    </div>
                    <div class="flex items-center space-x-3 <?php echo $page_dir === 'rtl' ? 'space-x-reverse' : ''; ?>">
                        <svg class="w-5 h-5 text-hotel-gold flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20 22.621l-3.521-6.795c-.008.004-1.974.97-2.064 1.011-2.24 1.086-6.799-7.82-4.609-8.994l2.083-1.028-3.493-6.817-2.105 1.039c-7.202 3.755 4.233 25.982 11.6 22.615.121-.055 2.102-1.029 2.114-1.036.022-.012.008-.005-.009.004z"/>
                        </svg>
                        <a href="tel:+982112345678" class="text-hotel-cream/80 hover:text-hotel-gold transition-colors duration-300" dir="ltr">
                            +۹۸ (۲۱) ۱۲۳۴ ۵۶۷۸
                        </a>
                    </div>
                    <div class="flex items-center space-x-3 <?php echo $page_dir === 'rtl' ? 'space-x-reverse' : ''; ?>">
                        <svg class="w-5 h-5 text-hotel-gold flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M0 3v18h24v-18h-24zm21.518 2l-9.518 7.713-9.518-7.713h19.036zm-19.518 14v-11.817l10 8.104 10-8.104v11.817h-20z"/>
                        </svg>
                        <a href="mailto:reserve@palacehotel.com" class="text-hotel-cream/80 hover:text-hotel-gold transition-colors duration-300">
                            reserve@palacehotel.com
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="space-y-4">
                <h4 class="font-playfair text-lg font-bold text-hotel-gold mb-4">لینک‌های سریع</h4>
                <ul class="space-y-2">
                    <li>
                        <a href="index.php?lang=<?php echo $lang_code; ?>" 
                           class="text-hotel-cream/80 hover:text-hotel-gold transition-colors duration-300 flex items-center space-x-2 <?php echo $page_dir === 'rtl' ? 'space-x-reverse' : ''; ?>">
                            <span>→</span>
                            <span><?php echo $lang['nav_home']; ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="rooms.php?lang=<?php echo $lang_code; ?>" 
                           class="text-hotel-cream/80 hover:text-hotel-gold transition-colors duration-300 flex items-center space-x-2 <?php echo $page_dir === 'rtl' ? 'space-x-reverse' : ''; ?>">
                            <span>→</span>
                            <span><?php echo $lang['nav_rooms']; ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="blog.php?lang=<?php echo $lang_code; ?>" 
                           class="text-hotel-cream/80 hover:text-hotel-gold transition-colors duration-300 flex items-center space-x-2 <?php echo $page_dir === 'rtl' ? 'space-x-reverse' : ''; ?>">
                            <span>→</span>
                            <span><?php echo $lang['nav_blog']; ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="contact.php?lang=<?php echo $lang_code; ?>" 
                           class="text-hotel-cream/80 hover:text-hotel-gold transition-colors duration-300 flex items-center space-x-2 <?php echo $page_dir === 'rtl' ? 'space-x-reverse' : ''; ?>">
                            <span>→</span>
                            <span><?php echo $lang['nav_contact']; ?></span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Social Media & Newsletter -->
            <div class="space-y-4">
                <h4 class="font-playfair text-lg font-bold text-hotel-gold mb-4">ارتباط با ما</h4>
                
                <!-- Social Media Links -->
                <div class="flex space-x-4 <?php echo $page_dir === 'rtl' ? 'space-x-reverse' : ''; ?>">
                    <a href="#" class="text-hotel-cream/80 hover:text-hotel-gold transition-colors duration-300 transform hover:scale-110">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                        </svg>
                    </a>
                    <a href="#" class="text-hotel-cream/80 hover:text-hotel-gold transition-colors duration-300 transform hover:scale-110">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/>
                        </svg>
                    </a>
                    <a href="#" class="text-hotel-cream/80 hover:text-hotel-gold transition-colors duration-300 transform hover:scale-110">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.097.118.112.221.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24.009c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641.001.012.001z.017 0z"/>
                        </svg>
                    </a>
                    <a href="#" class="text-hotel-cream/80 hover:text-hotel-gold transition-colors duration-300 transform hover:scale-110">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                    </a>
                </div>

                <!-- Newsletter Signup -->
                <div class="mt-6">
                    <p class="text-hotel-cream/80 text-sm mb-3">عضویت در خبرنامه برای دریافت پیشنهادات ویژه</p>
                    <form class="flex space-x-2 <?php echo $page_dir === 'rtl' ? 'space-x-reverse' : ''; ?>">
                        <input type="email" 
                               placeholder="ایمیل شما" 
                               class="flex-1 px-3 py-2 bg-hotel-dark/50 border border-hotel-cream/20 rounded-lg text-hotel-cream placeholder-hotel-cream/60 focus:outline-none focus:border-hotel-gold transition-colors duration-300">
                        <button type="submit" 
                                class="bg-hotel-gold text-hotel-dark px-4 py-2 rounded-lg hover:bg-hotel-gold/90 transition-colors duration-300 font-bold">
                            عضویت
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="border-t border-hotel-cream/20 mt-12 pt-8 text-center">
            <p class="text-hotel-cream/60">
                &copy; 2025 هتل پالاس. تمام حقوق محفوظ است. | طراحی و توسعه با ❤️
            </p>
        </div>
    </div>
</footer>

<!-- Enhanced JavaScript with Alpine.js -->
<script>
    // Custom animations and interactions
    document.addEventListener('DOMContentLoaded', function() {
        // Fade in up animation for elements
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .animate-fade-in-up {
                animation: fadeInUp 0.8s ease-out forwards;
            }
            
            .fade-in-up {
                animation: fadeInUp 0.8s ease-out forwards;
            }
        `;
        document.head.appendChild(style);

        // Trigger fade-in animations
        setTimeout(() => {
            document.querySelectorAll('.fade-in-up').forEach(el => {
                el.style.opacity = '1';
            });
        }, 100);
    });
</script>

</body>
</html>
