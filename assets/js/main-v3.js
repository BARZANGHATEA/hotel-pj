// تابع جدید برای کنترل گالری در صفحه جزئیات اتاق
function changeMainImage(thumbnailElement) {
    const mainImage = document.getElementById('mainGalleryImage');
    if (mainImage) {
        mainImage.src = thumbnailElement.src;
    }
}

// کدهای قبلی (کرسر، پارالاکس و ...) را دست نزنید و این تابع را به فایل اضافه کنید
document.addEventListener('DOMContentLoaded', function() {
    // ... کدهای قبلی شما اینجا قرار دارند ...
});
document.addEventListener('DOMContentLoaded', function() {
    
    // افزودن کلاس برای شروع انیمیشن‌های ورودی
    document.documentElement.classList.add('is-ready');

    // --- منطق کرسر سفارشی ---
    const cursor = document.querySelector('.cursor');
    const follower = document.querySelector('.cursor-follower');
    
    document.addEventListener('mousemove', e => {
        cursor.style.left = e.clientX + 'px';
        cursor.style.top = e.clientY + 'px';
        follower.style.left = e.clientX + 'px';
        follower.style.top = e.clientY + 'px';
    });

    // --- افکت پارالاکس برای تصاویر هنگام اسکرول ---
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        document.querySelectorAll('[data-parallax-speed]').forEach(el => {
            const speed = parseFloat(el.getAttribute('data-parallax-speed'));
            const yPos = -(scrolled * speed);
            el.style.transform = `translate3d(0, ${yPos}px, 0)`;
        });
    });

    // --- انیمیشن نمایش المان‌ها هنگام اسکرول ---
    const revealElements = document.querySelectorAll('.reveal-on-scroll');
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    revealElements.forEach(el => {
        revealObserver.observe(el);
    });
});