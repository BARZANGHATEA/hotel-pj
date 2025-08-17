
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

<main class="main-content">
    <div class="contact-page-layout">
        
        <div class="contact-info-side">
            <div class="page-header-contact">
                <h1 class="page-title">تماس با ما</h1>
                <p class="page-subtitle">ما همیشه برای شنیدن صدای شما آماده‌ایم.</p>
            </div>
            <div class="contact-details-list">
                <div class="contact-item">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-4.198 0-8 3.402-8 7.602 0 4.198 3.469 9.21 8 16.398 4.531-7.188 8-12.2 8-16.398 0-4.198-3.801-7.602-8-7.602zm0 11c-1.657 0-3-1.343-3-3s1.343-3 3-3 3 1.343 3 3-1.343 3-3 3z"/></svg>
                    <span>ایران، تهران، خیابان فرشته، پلاک ۱۰</span>
                </div>
                <div class="contact-item">
                     <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M20 22.621l-3.521-6.795c-.008.004-1.974.97-2.064 1.011-2.24 1.086-6.799-7.82-4.609-8.994l2.083-1.028-3.493-6.817-2.105 1.039c-7.202 3.755 4.233 25.982 11.6 22.615.121-.055 2.102-1.029 2.114-1.036.022-.012.008-.005-.009.004z"/></svg>
                    <a href="tel:+982112345678" dir="ltr">+۹۸ (۲۱) ۱۲۳۴ ۵۶۷۸</a>
                </div>
                <div class="contact-item">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M0 3v18h24v-18h-24zm21.518 2l-9.518 7.713-9.518-7.713h19.036zm-19.518 14v-11.817l10 8.104 10-8.104v11.817h-20z"/></svg>
                    <a href="mailto:reserve@palacehotel.com">reserve@palacehotel.com</a>
                </div>
            </div>
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3238.252611728108!2d51.41913411526118!3d35.74457008017949!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3f8e06cb3a701b31%3A0x7c73752c06173d1!2sTehran%2C%20Tehran%20Province%2C%20Iran!5e0!3m2!1sen!2s!4v1677682836541!5m2!1sen!2s" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>

        <div class="contact-form-side">
            <h2>برای ما پیام بگذارید</h2>
            <form action="contact.php" method="POST">
                <div class="form-group">
                    <label for="name">نام شما</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">ایمیل شما</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="subject">موضوع</label>
                    <input type="text" id="subject" name="subject" required>
                </div>
                <div class="form-group">
                    <label for="message">پیام شما</label>
                    <textarea id="message" name="message" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary-v3">ارسال پیام</button>
            </form>
        </div>

    </div>
</main>

<?php include_once 'includes/footer.php'; ?>