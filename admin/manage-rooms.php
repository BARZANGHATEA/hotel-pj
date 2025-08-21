<?php
include_once 'partials/header.php';

// متغیرهای اولیه
$edit_mode = false;
$room_data = null;
$room_translations = [];
$flash_message = '';

// نمایش پیام بازخورد
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

// AJAX endpoint: reorder gallery
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reorder_gallery') {
    // Expected: order = comma-separated ids
    $order = $_POST['order'] ?? '';
    if (!empty($order)) {
        $ids = array_filter(array_map('intval', explode(',', $order)));
        $i = 1;
        $stmt = $conn->prepare("UPDATE room_gallery_images SET sort_order = ? WHERE id = ?");
        foreach ($ids as $id) {
            $stmt->bind_param('ii', $i, $id);
            $stmt->execute();
            $i++;
        }
        $stmt->close();
        echo json_encode(['status' => 'ok']);
        exit();
    }
    echo json_encode(['status' => 'error']);
    exit();
}

// رسیدگی به درخواست حذف تصویر گالری
if (isset($_GET['delete_gallery_image']) && isset($_GET['edit'])) {
    $image_id_to_delete = intval($_GET['delete_gallery_image']);
    $room_id_redirect = intval($_GET['edit']);

    // Try new gallery table first
    $stmt = $conn->prepare("SELECT image_path FROM room_gallery_images WHERE id = ?");
    $stmt->bind_param("i", $image_id_to_delete);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($result) {
        $file_to_delete = '../uploads/rooms/gallery/' . $result['image_path'];
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }
        $delete_stmt = $conn->prepare("DELETE FROM room_gallery_images WHERE id = ?");
        $delete_stmt->bind_param("i", $image_id_to_delete);
        $delete_stmt->execute();
        $delete_stmt->close();
    } else {
        // Fallback to legacy table
        $stmt = $conn->prepare("SELECT image_url FROM room_images WHERE id = ?");
        $stmt->bind_param("i", $image_id_to_delete);
        $stmt->execute();
        $legacy = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($legacy) {
            $file_to_delete = '../uploads/rooms/' . $legacy['image_url'];
            if (file_exists($file_to_delete)) {
                unlink($file_to_delete);
            }
            $delete_stmt = $conn->prepare("DELETE FROM room_images WHERE id = ?");
            $delete_stmt->bind_param("i", $image_id_to_delete);
            $delete_stmt->execute();
            $delete_stmt->close();
        }
    }

    header("Location: manage-rooms.php?edit=" . $room_id_redirect);
    exit();
}

// رسیدگی به درخواست حذف اتاق
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $room_id_to_delete = intval($_GET['delete']);

    $stmt = $conn->prepare("SELECT image FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id_to_delete);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result && file_exists('../uploads/rooms/' . $result['image'])) {
        unlink('../uploads/rooms/' . $result['image']);
    }
    $stmt->close();
    
    $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id_to_delete);
    if ($stmt->execute()) {
        $_SESSION['flash_message'] = "اتاق با موفقیت حذف شد.";
    } else {
        $_SESSION['flash_message'] = "خطا در حذف اتاق.";
    }
    $stmt->close();
    
    header("Location: manage-rooms.php");
    exit();
}

// پردازش فرم
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $price = $_POST['price'];
    $translations = $_POST['translations'];
    $video_url = trim($_POST['video_url'] ?? '');

    $main_image_name = $_POST['existing_image'] ?? '';
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === 0) {
        $upload_dir = '../uploads/rooms/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $main_image_name = time() . '_' . basename($_FILES['main_image']['name']);
        move_uploaded_file($_FILES['main_image']['tmp_name'], $upload_dir . $main_image_name);
    }

    if (isset($_POST['room_id']) && !empty($_POST['room_id'])) {
        // ویرایش
        $room_id = intval($_POST['room_id']);
        $stmt = $conn->prepare("UPDATE rooms SET price_per_night = ?, image = ?, video_url = ? WHERE id = ?");
        $stmt->bind_param("dssi", $price, $main_image_name, $video_url, $room_id);
        // Note: bind types fixed below after ensuring types
        $stmt->bind_param("dssi", $price, $main_image_name, $video_url, $room_id);
        $stmt->execute();
        $stmt->close();

        foreach ($translations as $lang => $data) {
            $stmt = $conn->prepare("UPDATE room_translations SET name = ?, short_description = ?, description = ? WHERE room_id = ? AND lang_code = ?");
            $stmt->bind_param("sssis", $data['name'], $data['short_desc'], $data['desc'], $room_id, $lang);
            $stmt->execute();
            $stmt->close();
        }

        // Handle gallery uploads (edit mode)
        if (isset($_FILES['gallery_images']) && is_array($_FILES['gallery_images']['name'])) {
            $upload_dir = '../uploads/rooms/gallery/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            // get current max sort_order
            $max_stmt = $conn->prepare("SELECT IFNULL(MAX(sort_order),0) AS mx FROM room_gallery_images WHERE room_id = ?");
            $max_stmt->bind_param('i', $room_id);
            $max_stmt->execute();
            $mx = $max_stmt->get_result()->fetch_assoc()['mx'] ?? 0;
            $max_stmt->close();

            for ($i = 0; $i < count($_FILES['gallery_images']['name']); $i++) {
                if ($_FILES['gallery_images']['error'][$i] === 0) {
                    $name = time() . '_' . basename($_FILES['gallery_images']['name'][$i]);
                    move_uploaded_file($_FILES['gallery_images']['tmp_name'][$i], $upload_dir . $name);
                    $mx++;
                    $ins = $conn->prepare("INSERT INTO room_gallery_images (room_id, image_path, sort_order) VALUES (?, ?, ?)");
                    $ins->bind_param('isi', $room_id, $name, $mx);
                    $ins->execute();
                    $ins->close();
                }
            }
        }

        $_SESSION['flash_message'] = "اتاق با موفقیت به‌روزرسانی شد.";
    } else {
        // افزودن
        $stmt = $conn->prepare("INSERT INTO rooms (price_per_night, image, video_url) VALUES (?, ?, ?)");
        $stmt->bind_param("dss", $price, $main_image_name, $video_url);
        $stmt->execute();
        $new_room_id = $stmt->insert_id;
        $stmt->close();

        foreach ($translations as $lang => $data) {
            $stmt = $conn->prepare("INSERT INTO room_translations (room_id, lang_code, name, short_description, description) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $new_room_id, $lang, $data['name'], $data['short_desc'], $data['desc']);
            $stmt->execute();
            $stmt->close();
        }

        // Handle gallery uploads (new room)
        if (isset($_FILES['gallery_images']) && is_array($_FILES['gallery_images']['name'])) {
            $upload_dir = '../uploads/rooms/gallery/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $mx = 0;
            for ($i = 0; $i < count($_FILES['gallery_images']['name']); $i++) {
                if ($_FILES['gallery_images']['error'][$i] === 0) {
                    $name = time() . '_' . basename($_FILES['gallery_images']['name'][$i]);
                    move_uploaded_file($_FILES['gallery_images']['tmp_name'][$i], $upload_dir . $name);
                    $mx++;
                    $ins = $conn->prepare("INSERT INTO room_gallery_images (room_id, image_path, sort_order) VALUES (?, ?, ?)");
                    $ins->bind_param('isi', $new_room_id, $name, $mx);
                    $ins->execute();
                    $ins->close();
                }
            }
        }

        $_SESSION['flash_message'] = "اتاق جدید با موفقیت اضافه شد.";
    }
    
    header("Location: manage-rooms.php");
    exit();
}

// حالت ویرایش
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_mode = true;
    $room_id_to_edit = intval($_GET['edit']);

    $stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id_to_edit);
    $stmt->execute();
    $room_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $conn->prepare("SELECT * FROM room_translations WHERE room_id = ?");
    $stmt->bind_param("i", $room_id_to_edit);
    $stmt->execute();
    $translations_result = $stmt->get_result();
    while ($row = $translations_result->fetch_assoc()) {
        $room_translations[$row['lang_code']] = $row;
    }
    $stmt->close();

    // Fetch gallery images from new table
    $gstmt = $conn->prepare("SELECT id, image_path FROM room_gallery_images WHERE room_id = ? ORDER BY sort_order ASC");
    $gstmt->bind_param('i', $room_id_to_edit);
    $gstmt->execute();
    $g_result = $gstmt->get_result();
    $room_gallery = [];
    while ($r = $g_result->fetch_assoc()) {
        $room_gallery[] = $r;
    }
    $gstmt->close();
}
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
<div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
    <div class="flex items-center">
        <svg class="w-5 h-5 ml-2" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
        </svg>
        <?php echo $flash_message; ?>
    </div>
</div>
<?php endif; ?>

<!-- Room Form -->
<div id="roomForm" class="<?php echo $edit_mode ? 'block' : 'hidden'; ?> bg-white rounded-xl shadow-sm border border-gray-200 p-8 mb-8">
    <form action="manage-rooms.php" method="POST" enctype="multipart/form-data" class="space-y-6">
        
        <?php if ($edit_mode): ?>
            <input type="hidden" name="room_id" value="<?php echo $room_data['id']; ?>">
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
                       value="<?php echo $edit_mode ? $room_data['price_per_night'] : ''; ?>" 
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
                    <img src="../uploads/rooms/<?php echo $room_data['image']; ?>" 
                         class="w-32 h-24 object-cover rounded-lg border border-gray-200" 
                         alt="Room Image">
                    <input type="hidden" name="existing_image" value="<?php echo $room_data['image']; ?>">
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
                       value="<?php echo $edit_mode ? htmlspecialchars($room_data['video_url']) : ''; ?>"
                       class="w-full pl-4 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300"
                       placeholder="https://www.youtube.com/watch?v=...">
            </div>
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
                    <div class="relative group bg-gray-50 p-1.5 rounded-lg border border-gray-200 cursor-grab">
                        <img src="../uploads/rooms/gallery/<?php echo htmlspecialchars($img['image_path']); ?>" class="w-full h-24 object-cover rounded-md">
                        <a href="manage-rooms.php?delete_gallery_image=<?php echo $img['id']; ?>&edit=<?php echo $room_data['id']; ?>" 
                           onclick="return confirm('آیا از حذف این تصویر مطمئن هستید؟')"
                           class="absolute top-2 left-2 bg-red-600 text-white rounded-full p-1 text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                           <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"></path></svg>
                        </a>
                        <input type="hidden" class="gallery-id" value="<?php echo $img['id']; ?>">
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" id="saveOrderBtn" class="mt-4 bg-hotel-gold text-hotel-dark px-5 py-2 rounded-lg text-sm font-semibold hover:bg-hotel-gold/90 transition-colors">ذخیره ترتیب</button>
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
                              required
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300"
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
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300"
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
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300"
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
                        <img src="../uploads/rooms/<?php echo htmlspecialchars($room['image']); ?>" 
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
                            <a href="manage-rooms.php?edit=<?php echo $room['id']; ?>" 
                               class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                </svg>
                                ویرایش
                            </a>
                            <a href="manage-rooms.php?delete=<?php echo $room['id']; ?>" 
                               onclick="return confirm('آیا مطمئن هستید که می‌خواهید این اتاق را حذف کنید؟')"
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

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
function toggleForm() {
    const form = document.getElementById('roomForm');
    form.classList.toggle('hidden');
}

// Reorder gallery images
document.getElementById('saveOrderBtn').addEventListener('click', function() {
    const order = Array.from(document.querySelectorAll('.gallery-id'))
                       .map(el => el.value)
                       .join(',');
    
    fetch('manage-rooms.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=reorder_gallery&order=' + order
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'ok') {
            alert('Order saved successfully!');
        } else {
            alert('Error saving order.');
        }
    })
    .catch(error => console.error('Error:', error));
});

if (document.getElementById('galleryList')) {
    const sortable = Sortable.create(document.getElementById('galleryList'), {
        animation: 150,
        ghostClass: 'bg-blue-100'
    });

    document.getElementById('saveOrderBtn').addEventListener('click', function() {
        const order = sortable.toArray().map(id => {
            // We need to find the hidden input value from the item's data-id
            const itemEl = sortable.el.querySelector(`[data-id="${id}"]`);
            // This is tricky because SortableJS doesn't give us the element directly.
            // A better way is to get all IDs in their new order.
            return id; // SortableJS toArray() returns data-id attributes.
        });
        
        // Let's get the IDs from the hidden inputs in their new order
        const galleryItems = document.querySelectorAll('#galleryList > div');
        const orderedIds = Array.from(galleryItems).map(item => item.querySelector('.gallery-id').value);

        fetch('manage-rooms.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=reorder_gallery&order=' + orderedIds.join(',')
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'ok') {
                // Maybe show a success message
                alert('ترتیب با موفقیت ذخیره شد.');
            } else {
                alert('خطا در ذخیره ترتیب.');
            }
        });
    });
}
</script>

<?php include_once 'partials/footer.php'; ?>

<!-- TinyMCE Integration -->
<script src="https://cdn.tiny.cloud/1/3fhpj4fbwaga5z3i2uk4yyi9bbfzl62i3nnykuzxyesrio3v/tinymce/8/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  tinymce.init({
    selector: 'textarea',
    plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
  });
</script>
