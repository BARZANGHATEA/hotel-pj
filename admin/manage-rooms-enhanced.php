<?php
// شروع output buffering برای جلوگیری از خطاهای header
ob_start();

// فراخوانی فایل اتصال به دیتابیس و تنظیمات اولیه
require_once '../config/db.php';

// بررسی احراز هویت
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// متغیرهای اولیه
$edit_mode = false;
$room_data = null;
$room_translations = [];
$room_gallery = [];
$flash_message = '';

// نمایش پیام بازخورد
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

// رسیدگی به درخواست حذف تصویر گالری
if (isset($_GET['delete_gallery_image']) && isset($_GET['edit'])) {
    $image_id_to_delete = intval($_GET['delete_gallery_image']);
    $room_id_redirect = intval($_GET['edit']);

    // حذف از جدول room_gallery_images
    $stmt = $conn->prepare("SELECT image_path FROM room_gallery_images WHERE id = ?");
    $stmt->bind_param("i", $image_id_to_delete);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        $file_to_delete = '../' . $result['image_path'];
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }
    }
    $stmt->close();

    $delete_stmt = $conn->prepare("DELETE FROM room_gallery_images WHERE id = ?");
    $delete_stmt->bind_param("i", $image_id_to_delete);
    $delete_stmt->execute();
    $delete_stmt->close();

    $_SESSION['flash_message'] = "تصویر گالری با موفقیت حذف شد.";
    header("Location: manage-rooms-enhanced.php?edit=" . $room_id_redirect);
    exit();
}

// رسیدگی به درخواست حذف اتاق
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $room_id_to_delete = intval($_GET['delete']);

    // حذف تصویر اصلی
    $stmt = $conn->prepare("SELECT image FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id_to_delete);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result && file_exists('../uploads/rooms/' . $result['image'])) {
        unlink('../uploads/rooms/' . $result['image']);
    }
    $stmt->close();
    
    // حذف تصاویر گالری
    $stmt = $conn->prepare("SELECT image_path FROM room_gallery_images WHERE room_id = ?");
    $stmt->bind_param("i", $room_id_to_delete);
    $stmt->execute();
    $gallery_result = $stmt->get_result();
    while ($gallery_row = $gallery_result->fetch_assoc()) {
        if (file_exists('../' . $gallery_row['image_path'])) {
            unlink('../' . $gallery_row['image_path']);
        }
    }
    $stmt->close();
    
    // حذف اتاق (CASCADE خودکار بقیه جداول را حذف می‌کند)
    $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id_to_delete);
    if ($stmt->execute()) {
        $_SESSION['flash_message'] = "اتاق با موفقیت حذف شد.";
    } else {
        $_SESSION['flash_message'] = "خطا در حذف اتاق.";
    }
    $stmt->close();
    
    header("Location: manage-rooms-enhanced.php");
    exit();
}

// پردازش فرم
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $price = $_POST['price'];
    $video_url = $_POST['video_url'] ?? '';
    $translations = $_POST['translations'];

    // پردازش تصویر اصلی
    $main_image_name = $_POST['existing_image'] ?? '';
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === 0) {
        $upload_dir = '../uploads/rooms/';
        $main_image_name = time() . '_' . basename($_FILES['main_image']['name']);
        move_uploaded_file($_FILES['main_image']['tmp_name'], $upload_dir . $main_image_name);
    }

    if (isset($_POST['room_id']) && !empty($_POST['room_id'])) {
        // ویرایش
        $room_id = intval($_POST['room_id']);
        $stmt = $conn->prepare("UPDATE rooms SET price_per_night = ?, image = ?, video_url = ? WHERE id = ?");
        $stmt->bind_param("dssi", $price, $main_image_name, $video_url, $room_id);
        $stmt->execute();
        $stmt->close();

        // به‌روزرسانی ترجمه‌ها
        foreach ($translations as $lang => $data) {
            $stmt = $conn->prepare("UPDATE room_translations SET name = ?, short_description = ?, description = ? WHERE room_id = ? AND lang_code = ?");
            $stmt->bind_param("sssis", $data['name'], $data['short_desc'], $data['desc'], $room_id, $lang);
            $stmt->execute();
            $stmt->close();
        }
        
        $_SESSION['flash_message'] = "اتاق با موفقیت به‌روزرسانی شد.";
    } else {
        // افزودن
        $stmt = $conn->prepare("INSERT INTO rooms (price_per_night, image, video_url) VALUES (?, ?, ?)");
        $stmt->bind_param("dss", $price, $main_image_name, $video_url);
        $stmt->execute();
        $new_room_id = $stmt->insert_id;
        $stmt->close();

        // افزودن ترجمه‌ها
        foreach ($translations as $lang => $data) {
            $stmt = $conn->prepare("INSERT INTO room_translations (room_id, lang_code, name, short_description, description) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $new_room_id, $lang, $data['name'], $data['short_desc'], $data['desc']);
            $stmt->execute();
            $stmt->close();
        }
        
        $room_id = $new_room_id;
        $_SESSION['flash_message'] = "اتاق جدید با موفقیت اضافه شد.";
    }

    // پردازش تصاویر گالری
    if (isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
        $upload_dir = '../uploads/rooms/gallery/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // دریافت آخرین sort_order
        $stmt = $conn->prepare("SELECT MAX(sort_order) as max_order FROM room_gallery_images WHERE room_id = ?");
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $next_order = ($result['max_order'] ?? 0) + 1;
        $stmt->close();

        foreach ($_FILES['gallery_images']['name'] as $key => $filename) {
            if ($_FILES['gallery_images']['error'][$key] === 0) {
                $gallery_image_name = time() . '_' . $key . '_' . basename($filename);
                $gallery_image_path = 'uploads/rooms/gallery/' . $gallery_image_name;
                
                if (move_uploaded_file($_FILES['gallery_images']['tmp_name'][$key], $upload_dir . $gallery_image_name)) {
                    $stmt = $conn->prepare("INSERT INTO room_gallery_images (room_id, image_path, sort_order) VALUES (?, ?, ?)");
                    $stmt->bind_param("isi", $room_id, $gallery_image_path, $next_order);
                    $stmt->execute();
                    $stmt->close();
                    $next_order++;
                }
            }
        }
    }
    
    header("Location: manage-rooms-enhanced.php");
    exit();
}

// حالت ویرایش
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_mode = true;
    $room_id_to_edit = intval($_GET['edit']);

    // دریافت اطلاعات اتاق
    $stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id_to_edit);
    $stmt->execute();
    $room_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // دریافت ترجمه‌ها
    $stmt = $conn->prepare("SELECT * FROM room_translations WHERE room_id = ?");
    $stmt->bind_param("i", $room_id_to_edit);
    $stmt->execute();
    $translations_result = $stmt->get_result();
    while ($row = $translations_result->fetch_assoc()) {
        $room_translations[$row['lang_code']] = $row;
    }
    $stmt->close();

    // دریافت تصاویر گالری
    $stmt = $conn->prepare("SELECT * FROM room_gallery_images WHERE room_id = ? ORDER BY sort_order ASC");
    $stmt->bind_param("i", $room_id_to_edit);
    $stmt->execute();
    $gallery_result = $stmt->get_result();
    while ($row = $gallery_result->fetch_assoc()) {
        $room_gallery[] = $row;
    }
    $stmt->close();
}

// تابع برای تبدیل URL ویدیو به embed
function getVideoEmbedUrl($url) {
    if (empty($url)) return '';
    
    // YouTube
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $matches)) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }
    
    // Vimeo
    if (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
        return 'https://player.vimeo.com/video/' . $matches[1];
    }
    
    return $url;
}
?><!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت اتاق‌ها - پنل مدیریت هتل</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'hotel-gold': '#FFD700',
                        'hotel-dark': '#1F2937',
                        'hotel-sand': '#F4E1C1',
                        'hotel-cream': '#F5F5F0'
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Vazirmatn', sans-serif; }
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
        }
        .sortable-item {
            cursor: move;
        }
        .sortable-item:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: false }">
    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
