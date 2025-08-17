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

// رسیدگی به درخواست حذف تصویر گالری
if (isset($_GET['delete_gallery_image']) && isset($_GET['edit'])) {
    $image_id_to_delete = intval($_GET['delete_gallery_image']);
    $room_id_redirect = intval($_GET['edit']);

    $stmt = $conn->prepare("SELECT image_url FROM room_images WHERE id = ?");
    $stmt->bind_param("i", $image_id_to_delete);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        $file_to_delete = '../uploads/rooms/' . $result['image_url'];
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }
    }
    $stmt->close();

    $delete_stmt = $conn->prepare("DELETE FROM room_images WHERE id = ?");
    $delete_stmt->bind_param("i", $image_id_to_delete);
    $delete_stmt->execute();
    $delete_stmt->close();

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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $price = $_POST['price'];
    $translations = $_POST['translations'];

    $main_image_name = $_POST['existing_image'] ?? '';
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === 0) {
        $upload_dir = '../uploads/rooms/';
        $main_image_name = time() . '_' . basename($_FILES['main_image']['name']);
        move_uploaded_file($_FILES['main_image']['tmp_name'], $upload_dir . $main_image_name);
    }

    if (isset($_POST['room_id']) && !empty($_POST['room_id'])) {
        // ویرایش
        $room_id = intval($_POST['room_id']);
        $stmt = $conn->prepare("UPDATE rooms SET price_per_night = ?, image = ? WHERE id = ?");
        $stmt->bind_param("dsi", $price, $main_image_name, $room_id);
        $stmt->execute();
        $stmt->close();

        foreach ($translations as $lang => $data) {
            $stmt = $conn->prepare("UPDATE room_translations SET name = ?, short_description = ?, description = ? WHERE room_id = ? AND lang_code = ?");
            $stmt->bind_param("sssis", $data['name'], $data['short_desc'], $data['desc'], $room_id, $lang);
            $stmt->execute();
            $stmt->close();
        }
        $_SESSION['flash_message'] = "اتاق با موفقیت به‌روزرسانی شد.";
    } else {
        // افزودن
        $stmt = $conn->prepare("INSERT INTO rooms (price_per_night, image) VALUES (?, ?)");
        $stmt->bind_param("ds", $price, $main_image_name);
        $stmt->execute();
        $new_room_id = $stmt->insert_id;
        $stmt->close();

        foreach ($translations as $lang => $data) {
            $stmt = $conn->prepare("INSERT INTO room_translations (room_id, lang_code, name, short_description, description) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $new_room_id, $lang, $data['name'], $data['short_desc'], $data['desc']);
            $stmt->execute();
            $stmt->close();
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
                <input type="number" 
                       id="price" 
                       name="price" 
                       value="<?php echo $edit_mode ? $room_data['price_per_night'] : ''; ?>" 
                       required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300"
                       placeholder="مثال: 500000">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 text-sm">تومان</span>
                </div>
            </div>
        </div>

        <!-- Main Image -->
        <div>
            <label for="main_image" class="block text-sm font-semibold text-gray-700 mb-2">تصویر اصلی</label>
            <input type="file" 
                   id="main_image" 
                   name="main_image" 
                   <?php echo !$edit_mode ? 'required' : ''; ?>
                   accept="image/*"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300">
            
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
        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
            <div class="flex space-x-3 space-x-reverse">
                <button type="submit" 
                        class="bg-hotel-gold text-hotel-dark px-6 py-3 rounded-lg font-semibold hover:bg-hotel-gold/90 transition-colors duration-300">
                    <?php echo $edit_mode ? 'به‌روزرسانی اتاق' : 'افزودن اتاق'; ?>
                </button>
                <?php if ($edit_mode): ?>
                    <a href="manage-rooms.php" 
                       class="bg-gray-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-600 transition-colors duration-300">
                        لغو ویرایش
                    </a>
                <?php else: ?>
                    <button type="button" 
                            onclick="toggleForm()"
                            class="bg-gray-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-600 transition-colors duration-300">
                        لغو
                    </button>
                <?php endif; ?>
            </div>
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
                $rooms_list = $conn->query("
                    SELECT r.id, r.image, r.price_per_night, rt.name 
                    FROM rooms r
                    LEFT JOIN room_translations rt ON r.id = rt.room_id AND rt.lang_code = 'fa'
                    ORDER BY r.id DESC
                ");
                
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

<script>
function toggleForm() {
    const form = document.getElementById('roomForm');
    form.classList.toggle('hidden');
}
</script>

<?php include_once 'partials/footer.php'; ?>
