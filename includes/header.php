<?php
require_once 'config/db.php'; // اتصال به دیتابیس و شروع سشن

// --- منطق انتخاب زبان ---
$allowed_langs = ['fa', 'en', 'az'];
$lang_code = 'fa'; // زبان پیش‌فرض

if (isset($_GET['lang']) && in_array($_GET['lang'], $allowed_langs)) {
    $lang_code = $_GET['lang'];
    $_SESSION['lang'] = $lang_code;
} elseif (isset($_SESSION['lang'])) {
    $lang_code = $_SESSION['lang'];
}

// --- کد جدید: پردازش فرم ثبت نظر ---
$review_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $customer_name = htmlspecialchars($_POST['customer_name']);
    $rating = intval($_POST['rating']);
    $comment = htmlspecialchars($_POST['comment']);
    // room_id از قبل در متغیر $room_id موجود است

    if (!empty($customer_name) && $rating >= 1 && $rating <= 5 && !empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO room_reviews (room_id, customer_name, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isis", $room_id, $customer_name, $rating, $comment);
        if ($stmt->execute()) {
            $review_message = "<div class='alert success'>نظر شما با موفقیت ثبت شد و پس از تایید نمایش داده خواهد شد.</div>";
        } else {
            $review_message = "<div class='alert error'>خطایی در ثبت نظر رخ داد.</div>";
        }
        $stmt->close();
    } else {
        $review_message = "<div class='alert error'>لطفاً تمام فیلدها را به درستی پر کنید.</div>";
    }
}

// --- واکشی نظرات تایید شده برای این اتاق ---
$reviews_stmt = $conn->prepare("SELECT * FROM room_reviews WHERE room_id = ? AND status = 'approved' ORDER BY created_at DESC");
$reviews_stmt->bind_param("i", $room_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();

// بارگذاری فایل زبان مربوطه
require_once "lang/{$lang_code}.php";

// تعیین جهت صفحه بر اساس زبان
$page_dir = ($lang_code === 'fa') ? 'rtl' : 'ltr';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang_code; ?>" dir="<?php echo $page_dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>هتل مجلل پالاس</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Custom Tailwind Configuration -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'hotel-gold': '#FFD700',
                        'hotel-blue': '#00B8D4',
                        'hotel-cream': '#F5F5F0',
                        'hotel-sand': '#F4E1C1',
                        'hotel-gray': '#ECEFF1',
                        'hotel-dark': '#0a192f'
                    },
                    fontFamily: {
                        'vazir': ['Vazirmatn', 'sans-serif'],
                        'playfair': ['Playfair Display', 'serif']
                    }
                }
            }
        }
    </script>
    
    <style>
        /* Custom animations */
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
        
        .fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }
        
        .delay-1 { animation-delay: 0.3s; }
        .delay-2 { animation-delay: 0.6s; }
        .delay-3 { animation-delay: 0.9s; }
    </style>
</head>
<body class="font-vazir bg-white text-gray-800 leading-relaxed">
    <!-- Header -->
    <header 
        x-data="{ 
            isOpen: false, 
            scrolled: false,
            init() {
                window.addEventListener('scroll', () => {
                    this.scrolled = window.scrollY > 50;
                });
            }
        }" 
        x-init="init()"
        :class="scrolled ? 'bg-hotel-dark/95 backdrop-blur-lg shadow-lg' : 'bg-hotel-dark/85 backdrop-blur-md'"
        class="fixed top-0 left-0 w-full z-50 transition-all duration-300"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="index.php?lang=<?php echo $lang_code; ?>" class="block">
                        <img src="assets/images/logo-gold.png" alt="Hotel Logo" class="h-12 w-auto transition-transform duration-300 hover:scale-105">
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex space-x-8 <?php echo $page_dir === 'rtl' ? 'space-x-reverse' : ''; ?>">
                    <a href="index.php?lang=<?php echo $lang_code; ?>" 
                       class="text-hotel-cream hover:text-hotel-gold transition-colors duration-300 relative group px-3 py-2">
                        <?php echo $lang['nav_home']; ?>
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-hotel-gold transition-all duration-300 group-hover:w-full"></span>
                    </a>
                    <a href="about.php?lang=<?php echo $lang_code; ?>" 
                       class="text-hotel-cream hover:text-hotel-gold transition-colors duration-300 relative group px-3 py-2">
                        درباره ما
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-hotel-gold transition-all duration-300 group-hover:w-full"></span>
                    </a>
                    <a href="rooms.php?lang=<?php echo $lang_code; ?>" 
                       class="text-hotel-cream hover:text-hotel-gold transition-colors duration-300 relative group px-3 py-2">
                        <?php echo $lang['nav_rooms']; ?>
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-hotel-gold transition-all duration-300 group-hover:w-full"></span>
                    </a>
                    <a href="blog.php?lang=<?php echo $lang_code; ?>" 
                       class="text-hotel-cream hover:text-hotel-gold transition-colors duration-300 relative group px-3 py-2">
                        <?php echo $lang['nav_blog']; ?>
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-hotel-gold transition-all duration-300 group-hover:w-full"></span>
                    </a>
                    <a href="contact.php?lang=<?php echo $lang_code; ?>" 
                       class="text-hotel-cream hover:text-hotel-gold transition-colors duration-300 relative group px-3 py-2">
                        <?php echo $lang['nav_contact']; ?>
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-hotel-gold transition-all duration-300 group-hover:w-full"></span>
                    </a>
                </nav>

                <!-- Language Switcher & Mobile Menu Button -->
                <div class="flex items-center space-x-4 <?php echo $page_dir === 'rtl' ? 'space-x-reverse' : ''; ?>">
                    <!-- Language Switcher -->
                    <div class="flex space-x-2 <?php echo $page_dir === 'rtl' ? 'space-x-reverse' : ''; ?>">
                        <a href="?lang=fa" 
                           class="px-2 py-1 text-sm rounded transition-colors duration-300 <?php echo $lang_code === 'fa' ? 'bg-hotel-gold text-hotel-dark font-bold' : 'text-hotel-cream hover:text-hotel-gold'; ?>">
                            FA
                        </a>
                        <a href="?lang=en" 
                           class="px-2 py-1 text-sm rounded transition-colors duration-300 <?php echo $lang_code === 'en' ? 'bg-hotel-gold text-hotel-dark font-bold' : 'text-hotel-cream hover:text-hotel-gold'; ?>">
                            EN
                        </a>
                        <a href="?lang=az" 
                           class="px-2 py-1 text-sm rounded transition-colors duration-300 <?php echo $lang_code === 'az' ? 'bg-hotel-gold text-hotel-dark font-bold' : 'text-hotel-cream hover:text-hotel-gold'; ?>">
                            AZ
                        </a>
                    </div>

                    <!-- Mobile menu button -->
                    <button @click="isOpen = !isOpen" 
                            class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-hotel-cream hover:text-hotel-gold hover:bg-hotel-dark/50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-hotel-gold transition-colors duration-300">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': isOpen, 'inline-flex': !isOpen }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': !isOpen, 'inline-flex': isOpen }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <div x-show="isOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="md:hidden">
                <div class="px-2 pt-2 pb-3 space-y-1 bg-hotel-dark/95 backdrop-blur-lg rounded-lg mt-2">
                    <a href="index.php?lang=<?php echo $lang_code; ?>" 
                       class="block px-3 py-2 text-hotel-cream hover:text-hotel-gold hover:bg-hotel-dark/50 rounded-md transition-colors duration-300">
                        <?php echo $lang['nav_home']; ?>
                    </a>
                    <a href="about.php?lang=<?php echo $lang_code; ?>" 
                       class="block px-3 py-2 text-hotel-cream hover:text-hotel-gold hover:bg-hotel-dark/50 rounded-md transition-colors duration-300">
                        درباره ما
                    </a>
                    <a href="rooms.php?lang=<?php echo $lang_code; ?>" 
                       class="block px-3 py-2 text-hotel-cream hover:text-hotel-gold hover:bg-hotel-dark/50 rounded-md transition-colors duration-300">
                        <?php echo $lang['nav_rooms']; ?>
                    </a>
                    <a href="blog.php?lang=<?php echo $lang_code; ?>" 
                       class="block px-3 py-2 text-hotel-cream hover:text-hotel-gold hover:bg-hotel-dark/50 rounded-md transition-colors duration-300">
                        <?php echo $lang['nav_blog']; ?>
                    </a>
                    <a href="contact.php?lang=<?php echo $lang_code; ?>" 
                       class="block px-3 py-2 text-hotel-cream hover:text-hotel-gold hover:bg-hotel-dark/50 rounded-md transition-colors duration-300">
                        <?php echo $lang['nav_contact']; ?>
                    </a>
                </div>
            </div>
        </div>
    </header>
