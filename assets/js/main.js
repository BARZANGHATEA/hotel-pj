document.addEventListener('DOMContentLoaded', function() {

    // --- انیمیشن نظرات مشتریان (Testimonial Slider) ---
    const slider = document.querySelector('.testimonial-slider');
    if (slider) {
        const testimonials = slider.querySelectorAll('.testimonial-item');
        let currentIndex = 0;

        if (testimonials.length > 0) {
            testimonials[currentIndex].classList.add('active');

            setInterval(() => {
                const nextIndex = (currentIndex + 1) % testimonials.length;

                testimonials[currentIndex].classList.add('leaving');
                
                setTimeout(() => {
                    testimonials[currentIndex].classList.remove('active', 'leaving');
                    testimonials[nextIndex].classList.add('active');
                    currentIndex = nextIndex;
                }, 1000); // باید با زمان transition در CSS هماهنگ باشد

            }, 5000); // هر ۵ ثانیه نظر عوض می‌شود
        }
    }


    // --- انیمیشن نمایش المان‌ها هنگام اسکرول ---
    const revealElements = document.querySelectorAll('.reveal-on-scroll');
    
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                // وقتی یک بار نمایش داده شد، دیگر نیاز به مشاهده آن نیست
                revealObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1 // وقتی ۱۰٪ از المان دیده شد، انیمیشن اجرا شود
    });

    revealElements.forEach(el => {
        revealObserver.observe(el);
    });

});