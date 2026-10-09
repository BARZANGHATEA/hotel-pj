<?php
// تمام پردازش‌ها (redirect و پاسخ JSON) باید قبل از هرگونه خروجی HTML انجام شوند
require_once 'auth-check.php';

$rooms_upload_dir = '../uploads/rooms/';
$gallery_upload_dir = '../uploads/rooms/gallery/';
$languages = ['fa', 'en', 'az'];

// متغیرهای اولیه
$edit_mode = false;
$room_data = null;
$room_translations = [];
$room_gallery = [];
$flash_message = '';
$flash_type = 'success';

// نمایش پیام بازخورد
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    $flash_type = $_SESSION['flash_type'] ?? 'success';
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
}

function rooms_flash($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

// آپلود چند تصویر گالری و ثبت آن‌ها در دیتابیس؛ تعداد فایل‌های رد شده را برمی‌گرداند
function upload_gallery_images($conn, $room_id, $upload_dir) {
    $rejected = 0;
    $files = normalize_files_array($_FILES['gallery_images'] ?? []);
    if (!$files) {
        return 0;
    }

    $max_stmt = $conn->prepare("SELECT IFNULL(MAX(sort_order), 0) AS mx FROM room_gallery_images WHERE room_id = ?");
    $max_stmt->bind_param('i', $room_id);
    $max_stmt->execute();
    $mx = (int) $max_stmt->get_result()->fetch_assoc()['mx'];
    $max_stmt->close();

    $ins = $conn->prepare("INSERT INTO room_gallery_images (room_id, image_path, sort_order) VALUES (?, ?, ?)");
    foreach ($files as $file) {
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $name = save_uploaded_image($file, $upload_dir);
        if ($name === null) {
            $rejected++;
            continue;
        }
        $mx++;
        $ins->bind_param('isi', $room_id, $name, $mx);
        $ins->execute();
    }
    $ins->close();
    return $rejected;
}

// AJAX endpoint: ذخیره ترتیب تصاویر گالری
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reorder_gallery') {
    verify_csrf();
    header('Content-Type: application/json; charset=utf-8');
    $room_id = intval($_POST['room_id'] ?? 0);
    $ids = array_values(array_filter(array_map('intval', explode(',', $_POST['order'] ?? ''))));
    if ($room_id > 0 && $ids) {
        $stmt = $conn->prepare("UPDATE room_gallery_images SET sort_order = ? WHERE id = ? AND room_id = ?");
        foreach ($ids as $i => $id) {
            $pos = $i + 1;
            $stmt->bind_param('iii', $pos, $id, $room_id);
            $stmt->execute();
        }
        $stmt->close();
        echo json_encode(['status' => 'ok']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit();
}

// رسیدگی به درخواست حذف تصویر گالری
if (isset($_GET['delete_gallery_image'], $_GET['edit'])) {
    verify_csrf();
    $image_id_to_delete = intval($_GET['delete_gallery_image']);
    $room_id_redirect = intval($_GET['edit']);

    $stmt = $conn->prepare("SELECT image_path FROM room_gallery_images WHERE id = ? AND room_id = ?");
    $stmt->bind_param("ii", $image_id_to_delete, $room_id_redirect);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($result) {
        safe_unlink($gallery_upload_dir, $result['image_path']);
        $delete_stmt = $conn->prepare("DELETE FROM room_gallery_images WHERE id = ?");
        $delete_stmt->bind_param("i", $image_id_to_delete);
        $delete_stmt->execute();
        $delete_stmt->close();
        rooms_flash("تصویر گالری حذف شد.");
    }

    header("Location: manage-rooms.php?edit=" . $room_id_redirect);
    exit();
}

// رسیدگی به درخواست حذف اتاق (به همراه فایل‌های تصویر)
if (!empty($_GET['delete'])) {
    verify_csrf();
    $room_id_to_delete = intval($_GET['delete']);

    $stmt = $conn->prepare("SELECT image FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id_to_delete);
    $stmt->execute();
    $room_row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $gallery_files = [];
    $stmt = $conn->prepare("SELECT image_path FROM room_gallery_images WHERE room_id = ?");
    $stmt->bind_param("i", $room_id_to_delete);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $gallery_files[] = $row['image_path'];
    }
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id_to_delete);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        // جداول وابسته (ترجمه‌ها، گالری، نظرات، رزروها) با ON DELETE CASCADE حذف می‌شوند
        if ($room_row) {
            safe_unlink($rooms_upload_dir, $room_row['image']);
        }
        foreach ($gallery_files as $file) {
            safe_unlink($gallery_upload_dir, $file);
        }
        rooms_flash("اتاق با موفقیت حذف شد.");
    } else {
        rooms_flash("خطا در حذف اتاق.", 'error');
    }
    $stmt->close();

    header("Location: manage-rooms.php");
    exit();
}

// پردازش فرم افزودن / ویرایش
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $room_id = intval($_POST['room_id'] ?? 0);
    $redirect = $room_id > 0 ? "manage-rooms.php?edit=$room_id" : "manage-rooms.php";
    $price = (float) ($_POST['price'] ?? 0);
    $translations = is_array($_POST['translations'] ?? null) ? $_POST['translations'] : [];
    $video_url = trim($_POST['video_url'] ?? '');

    // اعتبارسنجی
    $errors = [];
    if ($price <= 0) {
        $errors[] = 'قیمت باید عددی بزرگ‌تر از صفر باشد.';
    }
    if (trim($translations['fa']['name'] ?? '') === '') {
        $errors[] = 'نام فارسی اتاق الزامی است.';
    }
    if ($video_url !== '' && video_embed_url($video_url) === '') {
        $errors[] = 'لینک ویدیو باید از یوتیوب، آپارات یا ویمئو باشد.';
    }

    // تصویر اصلی
    $main_image_name = '';
    if ($room_id > 0) {
        $stmt = $conn->prepare("SELECT image FROM rooms WHERE id = ?");
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$existing) {
            rooms_flash('اتاق مورد نظر یافت نشد.', 'error');
            header("Location: manage-rooms.php");
            exit();
        }
        $main_image_name = $existing['image'];
    }

    $new_main_image = null;
    if (empty($errors) && isset($_FILES['main_image']) && $_FILES['main_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $new_main_image = save_uploaded_image($_FILES['main_image'], $rooms_upload_dir);
        if ($new_main_image === null) {
            $errors[] = 'تصویر اصلی نامعتبر است (فقط JPG، PNG، GIF، WEBP تا ۱۰ مگابایت).';
        }
    }
    if (empty($errors) && $new_main_image === null && $main_image_name === '') {
        $errors[] = 'تصویر اصلی الزامی است.';
    }

    if ($errors) {
        if ($new_main_image) {
            safe_unlink($rooms_upload_dir, $new_main_image);
        }
        rooms_flash(implode(' ', $errors), 'error');
        header("Location: $redirect");
        exit();
    }

    if ($new_main_image !== null) {
        if ($main_image_name !== '') {
            safe_unlink($rooms_upload_dir, $main_image_name);
        }
        $main_image_name = $new_main_image;
    }
    $video_url = $video_url !== '' ? $video_url : null;

    if ($room_id > 0) {
        // ویرایش
        $stmt = $conn->prepare("UPDATE rooms SET price_per_night = ?, image = ?, video_url = ? WHERE id = ?");
        $stmt->bind_param("dssi", $price, $main_image_name, $video_url, $room_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // افزودن
        $stmt = $conn->prepare("INSERT INTO rooms (price_per_night, image, video_url) VALUES (?, ?, ?)");
        $stmt->bind_param("dss", $price, $main_image_name, $video_url);
        $stmt->execute();
        $room_id = $stmt->insert_id;
        $stmt->close();
    }

    // ذخیره ترجمه‌ها (اگر ترجمه‌ای وجود نداشت، ایجاد می‌شود)
    $find = $conn->prepare("SELECT id FROM room_translations WHERE room_id = ? AND lang_code = ?");
    $update = $conn->prepare("UPDATE room_translations SET name = ?, short_description = ?, description = ? WHERE id = ?");
    $insert = $conn->prepare("INSERT INTO room_translations (room_id, lang_code, name, short_description, description) VALUES (?, ?, ?, ?, ?)");
    foreach ($languages as $lang) {
        $name = trim($translations[$lang]['name'] ?? '');
        $short = trim($translations[$lang]['short_desc'] ?? '');
        $desc = trim($translations[$lang]['desc'] ?? '');

        $find->bind_param("is", $room_id, $lang);
        $find->execute();
        $row = $find->get_result()->fetch_assoc();
        if ($row) {
            $update->bind_param("sssi", $name, $short, $desc, $row['id']);
            $update->execute();
        } else {
            $insert->bind_param("issss", $room_id, $lang, $name, $short, $desc);
            $insert->execute();
        }
    }
    $find->close();
    $update->close();
    $insert->close();

    $rejected = upload_gallery_images($conn, $room_id, $gallery_upload_dir);

    $message = isset($existing) ? "اتاق با موفقیت به‌روزرسانی شد." : "اتاق جدید با موفقیت اضافه شد.";
    if ($rejected > 0) {
        $message .= " ($rejected فایل گالری به دلیل فرمت یا حجم نامعتبر رد شد.)";
    }
    rooms_flash($message, $rejected > 0 ? 'error' : 'success');
    header("Location: manage-rooms.php" . (isset($existing) ? "?edit=$room_id" : ""));
    exit();
}

// حالت ویرایش
if (!empty($_GET['edit'])) {
    $room_id_to_edit = intval($_GET['edit']);

    $stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id_to_edit);
    $stmt->execute();
    $room_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$room_data) {
        rooms_flash('اتاق مورد نظر یافت نشد.', 'error');
        header("Location: manage-rooms.php");
        exit();
    }
    $edit_mode = true;

    $stmt = $conn->prepare("SELECT * FROM room_translations WHERE room_id = ?");
    $stmt->bind_param("i", $room_id_to_edit);
    $stmt->execute();
    $translations_result = $stmt->get_result();
    while ($row = $translations_result->fetch_assoc()) {
        $room_translations[$row['lang_code']] = $row;
    }
    $stmt->close();

    $gstmt = $conn->prepare("SELECT id, image_path FROM room_gallery_images WHERE room_id = ? ORDER BY sort_order ASC, id ASC");
    $gstmt->bind_param('i', $room_id_to_edit);
    $gstmt->execute();
    $room_gallery = $gstmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $gstmt->close();
}

$csrf = csrf_token();
include_once 'partials/header.php';
?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">
            <?php echo $edit_mode ? 'ویرایش اتاق' : 'مدیریت اتاق‌ها'; ?>
        </h1>
        <p class="text-gray-600 mt-2">
            <?php echo $edit_mode ? 'ویرایش اطلاعات اتاق' : 'افزودن و مدیریت اتاق‌های هتل'; ?>
        </p>
    </div>
    <?php if (!$edit_mode): ?>
    <button onclick="toggleForm()" 
            class="bg-hotel-gold text-hotel-dark px-6 py-3 rounded-lg font-semibold hover:bg-hotel-gold/90 transition-colors duration-300">
        + افزودن اتاق جدید
    </button>
    <?php endif; ?>
</div>

<!-- Flash Message -->
<?php if (!empty($flash_message)): ?>
<div class="<?php echo $flash_type === 'error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-green-50 border-green-200 text-green-700'; ?> border px-4 py-3 rounded-lg mb-6">
    <div class="flex items-center">
        <svg class="w-5 h-5 ml-2" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
        </svg>
        <?php echo e($flash_message); ?>
    </div>
</div>
<?php endif; ?>

<!-- Room Form -->
<div id="roomForm" class="<?php echo $edit_mode ? 'block' : 'hidden'; ?> bg-white rounded-xl shadow-sm border border-gray-200 p-8 mb-8">
    <form action="manage-rooms.php" method="POST" enctype="multipart/form-data" class="space-y-6">
        <?php echo csrf_field(); ?>
        
        <?php if ($edit_mode): ?>
            <input type="hidden" name="room_id" value="<?php echo (int) $room_data['id']; ?>">
        <?php endif; ?>

        <!-- Price Field -->
        <div>
            <label for="price" class="block text-sm font-semibold text-gray-700 mb-2">قیمت (به ازای هر شب)</label>
            <div class="relative">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 11.21 12.77 11 12 11s-1.536.21-2.121.787c-1.172.879-1.172 2.303 0 3.182z" />
                    </svg>
                </div>
                <input type="number" 
                       id="price" 
                       name="price" 
                       value="<?php echo $edit_mode ? e((float) $room_data['price_per_night']) : ''; ?>" 
                       min="1" step="any"
                       required
                       class="w-full pl-4 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300 text-right"
                       placeholder="500,000">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 text-sm">تومان</span>
                </div>
            </div>
        </div>

        <!-- Main Image -->
        <div>
            <label for="main_image_label" class="block text-sm font-semibold text-gray-700 mb-2">تصویر اصلی</label>
            <label for="main_image" class="w-full flex items-center justify-center px-4 py-3 bg-gray-50 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer hover:bg-gray-100 transition-colors">
                <div class="text-center">
                    <svg class="mx-auto h-10 w-10 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-600">
                        <span class="font-semibold text-hotel-gold">برای آپلود کلیک کنید</span> یا فایل را بکشید و رها کنید
                    </p>
                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                </div>
            </label>
            <input type="file" id="main_image" name="main_image" class="hidden" <?php echo !$edit_mode ? 'required' : ''; ?> accept="image/*">
            
            <?php if ($edit_mode && $room_data['image']): ?>
                <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600 mb-2">تصویر فعلی:</p>
                    <img src="../uploads/rooms/<?php echo e($room_data['image']); ?>" 
                         class="w-32 h-24 object-cover rounded-lg border border-gray-200" 
                         alt="Room Image">
                </div>
            <?php endif; ?>
        </div>

        <!-- Video URL -->
        <div>
            <label for="video_url" class="block text-sm font-semibold text-gray-700 mb-2">لینک ویدیو (اختیاری)</label>
            <div class="relative">
                 <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.91 11.672a.375.375 0 010 .656l-5.603 3.113a.375.375 0 01-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112z" />
                    </svg>
                </div>
                <input type="url" 
                       id="video_url" 
                       name="video_url" 
                       value="<?php echo $edit_mode ? e($room_data['video_url']) : ''; ?>"
                       class="w-full pl-4 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300"
                       placeholder="https://www.youtube.com/watch?v=...">
            </div>
            <p class="text-xs text-gray-500 mt-1">لینک یوتیوب، آپارات یا ویمئو</p>
        </div>

        <!-- Gallery Upload -->
        <div>
            <label for="gallery_images" class="block text-sm font-semibold text-gray-700 mb-2">تصاویر گالری</label>
            <label for="gallery_images" class="w-full flex items-center justify-center px-4 py-3 bg-gray-50 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer hover:bg-gray-100 transition-colors">
                <div class="text-center">
                    <svg class="mx-auto h-10 w-10 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-600">
                        <span class="font-semibold text-hotel-gold">برای آپلود چند تصویر کلیک کنید</span>
                    </p>
                </div>
            </label>
            <input type="file" 
                   id="gallery_images" 
                   name="gallery_images[]" 
                   multiple
                   accept="image/*"
                   class="hidden">

            <?php if ($edit_mode && !empty($room_gallery)): ?>
            <p class="text-sm text-gray-600 mt-4 mb-2 font-semibold">تصاویر فعلی گالری (برای مرتب‌سازی بکشید و رها کنید):</p>
            <div id="galleryList" class="mt-3 grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-4">
                <?php foreach ($room_gallery as $img): ?>
                    <div class="relative group bg-gray-50 p-1.5 rounded-lg border border-gray-200 cursor-grab" data-id="<?php echo (int) $img['id']; ?>">
                        <img src="../uploads/rooms/gallery/<?php echo e($img['image_path']); ?>" class="w-full h-24 object-cover rounded-md pointer-events-none">
                        <a href="manage-rooms.php?delete_gallery_image=<?php echo (int) $img['id']; ?>&edit=<?php echo (int) $room_data['id']; ?>&csrf_token=<?php echo e($csrf); ?>" 
                           onclick="return confirm('آیا از حذف این تصویر مطمئن هستید؟')"
                           class="absolute top-2 left-2 bg-red-600 text-white rounded-full p-1 text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                           <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"></path></svg>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" id="saveOrderBtn" data-room-id="<?php echo (int) $room_data['id']; ?>" class="mt-4 bg-hotel-gold text-hotel-dark px-5 py-2 rounded-lg text-sm font-semibold hover:bg-hotel-gold/90 transition-colors">ذخیره ترتیب</button>
            <?php endif; ?>
        </div>

        <!-- Language Tabs -->
        <div x-data="{ activeTab: 'fa' }">
            <div class="border-b border-gray-200 mb-6">
                <nav class="flex space-x-8 space-x-reverse">
                    <button type="button" 
                            @click="activeTab = 'fa'"
                            :class="activeTab === 'fa' ? 'border-hotel-gold text-hotel-gold' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-300">
                        فارسی
                    </button>
                    <button type="button" 
                            @click="activeTab = 'en'"
                            :class="activeTab === 'en' ? 'border-hotel-gold text-hotel-gold' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-300">
                        English
                    </button>
                    <button type="button" 
                            @click="activeTab = 'az'"
                            :class="activeTab === 'az' ? 'border-hotel-gold text-hotel-gold' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-300">
                        Azərbaycanca
                    </button>
                </nav>
            </div>

            <!-- Persian Tab -->
            <div x-show="activeTab === 'fa'" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">نام اتاق</label>
                    <input type="text" 
                           name="translations[fa][name]" 
                           value="<?php echo $edit_mode && isset($room_translations['fa']) ? htmlspecialchars($room_translations['fa']['name']) : ''; ?>" 
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300"
                           placeholder="مثال: اتاق لوکس دو تخته">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">توضیح کوتاه</label>
                    <input type="text" 
                           name="translations[fa][short_desc]" 
                           value="<?php echo $edit_mode && isset($room_translations['fa']) ? htmlspecialchars($room_translations['fa']['short_description']) : ''; ?>" 
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300"
                           placeholder="توضیح کوتاه درباره اتاق">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">توضیحات کامل</label>
                    <textarea name="translations[fa][desc]" 
                              rows="4" 
                              class="rich-editor w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300"
                              placeholder="توضیحات کامل درباره امکانات و ویژگی‌های اتاق"><?php echo $edit_mode && isset($room_translations['fa']) ? htmlspecialchars($room_translations['fa']['description']) : ''; ?></textarea>
                </div>
            </div>

            <!-- English Tab -->
            <div x-show="activeTab === 'en'" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Room Name</label>
                    <input type="text" 
                           name="translations[en][name]" 
                           value="<?php echo $edit_mode && isset($room_translations['en']) ? htmlspecialchars($room_translations['en']['name']) : ''; ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300"
                           placeholder="e.g., Luxury Double Room">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Short Description</label>
                    <input type="text" 
                           name="translations[en][short_desc]" 
                           value="<?php echo $edit_mode && isset($room_translations['en']) ? htmlspecialchars($room_translations['en']['short_description']) : ''; ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300"
                           placeholder="Brief description about the room">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Full Description</label>
                    <textarea name="translations[en][desc]" 
                              rows="4"
                              class="rich-editor w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300"
                              placeholder="Complete description about room amenities and features"><?php echo $edit_mode && isset($room_translations['en']) ? htmlspecialchars($room_translations['en']['description']) : ''; ?></textarea>
                </div>
            </div>

            <!-- Azerbaijani Tab -->
            <div x-show="activeTab === 'az'" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Otaq Adı</label>
                    <input type="text" 
                           name="translations[az][name]" 
                           value="<?php echo $edit_mode && isset($room_translations['az']) ? htmlspecialchars($room_translations['az']['name']) : ''; ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300"
                           placeholder="məsələn: Lüks İki Nəfərlik Otaq">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Qısa Təsvir</label>
                    <input type="text" 
                           name="translations[az][short_desc]" 
                           value="<?php echo $edit_mode && isset($room_translations['az']) ? htmlspecialchars($room_translations['az']['short_description']) : ''; ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300"
                           placeholder="Otaq haqqında qısa məlumat">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tam Təsvir</label>
                    <textarea name="translations[az][desc]" 
                              rows="4"
                              class="rich-editor w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300"
                              placeholder="Otağın imkanları və xüsusiyyətləri haqqında tam məlumat"><?php echo $edit_mode && isset($room_translations['az']) ? htmlspecialchars($room_translations['az']['description']) : ''; ?></textarea>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end pt-6 border-t border-gray-200 space-x-4 space-x-reverse">
            <a href="manage-rooms.php" 
               class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors duration-300 flex items-center space-x-2 space-x-reverse">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span><?php echo $edit_mode ? 'لغو ویرایش' : 'لغو'; ?></span>
            </a>
            <button type="submit" 
                    class="bg-hotel-gold text-hotel-dark px-6 py-3 rounded-lg font-semibold hover:bg-hotel-gold/90 transition-colors duration-300 flex items-center space-x-2 space-x-reverse">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
                <span><?php echo $edit_mode ? 'به‌روزرسانی اتاق' : 'افزودن اتاق'; ?></span>
            </button>
        </div>
    </form>
</div>

<!-- Rooms List -->
<?php if (!$edit_mode): ?>
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">لیست اتاق‌ها</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">تصویر</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">نام اتاق</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">قیمت</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">عملیات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php
                $rooms_list = $conn->query("SELECT r.id, r.image, r.price_per_night, rt.name 
                    FROM rooms r
                    LEFT JOIN room_translations rt ON r.id = rt.room_id AND rt.lang_code = 'fa'
                    ORDER BY r.id DESC");
                
                if ($rooms_list->num_rows > 0):
                    while ($room = $rooms_list->fetch_assoc()):
                ?>
                <tr class="hover:bg-gray-50 transition-colors duration-200">
                    <td class="px-6 py-4">
                        <img src="../uploads/rooms/<?php echo e($room['image']); ?>" 
                             class="w-16 h-12 object-cover rounded-lg border border-gray-200" 
                             alt="Room">
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($room['name']); ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-gray-900 font-semibold"><?php echo number_format($room['price_per_night']); ?> تومان</div>
                        <div class="text-sm text-gray-500">در شب</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <a href="../room-details.php?id=<?php echo (int) $room['id']; ?>" target="_blank"
                               class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-200 transition-colors duration-200">
                                مشاهده
                            </a>
                            <a href="manage-rooms.php?edit=<?php echo (int) $room['id']; ?>" 
                               class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                </svg>
                                ویرایش
                            </a>
                            <a href="manage-rooms.php?delete=<?php echo (int) $room['id']; ?>&csrf_token=<?php echo e($csrf); ?>" 
                               onclick="return confirm('آیا مطمئن هستید؟ تمام تصاویر، نظرات و رزروهای این اتاق نیز حذف خواهند شد.')"
                               class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700 transition-colors duration-200">
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                </svg>
                                حذف
                            </a>
                        </div>
                    </td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">هیچ اتاقی یافت نشد</h3>
                            <p class="text-gray-500">برای شروع، اولین اتاق خود را اضافه کنید.</p>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
function toggleForm() {
    document.getElementById('roomForm').classList.toggle('hidden');
}

// مرتب‌سازی تصاویر گالری با کشیدن و رها کردن
(function () {
    const list = document.getElementById('galleryList');
    const saveBtn = document.getElementById('saveOrderBtn');
    if (!list || !saveBtn) return;

    const sortable = Sortable.create(list, { animation: 150, ghostClass: 'bg-blue-100' });

    saveBtn.addEventListener('click', function () {
        const body = new URLSearchParams({
            action: 'reorder_gallery',
            room_id: saveBtn.dataset.roomId,
            order: sortable.toArray().join(','),
            csrf_token: <?php echo json_encode($csrf); ?>
        });
        saveBtn.disabled = true;
        fetch('manage-rooms.php', { method: 'POST', body: body })
            .then(r => r.json())
            .then(data => alert(data.status === 'ok' ? 'ترتیب با موفقیت ذخیره شد.' : 'خطا در ذخیره ترتیب.'))
            .catch(() => alert('خطا در ارتباط با سرور.'))
            .finally(() => { saveBtn.disabled = false; });
    });
})();

// ویرایشگر متن فقط برای توضیحات کامل (TinyMCE در هدر پنل بارگذاری شده است)
if (window.tinymce) {
    tinymce.init({
        selector: 'textarea.rich-editor',
        directionality: 'rtl',
        menubar: false,
        plugins: 'autolink charmap link lists table wordcount',
        toolbar: 'undo redo | blocks | bold italic underline | link table | align | numlist bullist | removeformat',
    });
}
</script>

<?php include_once 'partials/footer.php'; ?>
