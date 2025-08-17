<?php
include_once 'partials/header.php';

// --- کد جدید: بخش مدیریت گالری ---

// ۱. رسیدگی به درخواست حذف یک تصویر از گالری
if (isset($_GET['delete_gallery_image']) && isset($_GET['edit'])) {
    $image_id_to_delete = intval($_GET['delete_gallery_image']);
    $room_id_redirect = intval($_GET['edit']);

    // ابتدا نام فایل را از دیتابیس می‌خوانیم تا بتوانیم آن را از سرور حذف کنیم
    $stmt = $conn->prepare("SELECT image_url FROM room_images WHERE id = ?");
    $stmt->bind_param("i", $image_id_to_delete);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        $file_to_delete = '../uploads/rooms/' . $result['image_url'];
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete); // حذف فیزیکی فایل از پوشه آپلود
        }
    }
    $stmt->close();

    // حالا رکورد را از دیتابیس حذف می‌کنیم
    $delete_stmt = $conn->prepare("DELETE FROM room_images WHERE id = ?");
    $delete_stmt->bind_param("i", $image_id_to_delete);
    $delete_stmt->execute();
    $delete_stmt->close();

    // کاربر را به همان صفحه ویرایش بازمی‌گردانیم
    header("Location: manage-rooms.php?edit=" . $room_id_redirect);
    exit();
}

// ... بقیه کدهای PHP شما (بخش پردازش فرم و ...) در ادامه قرار می‌گیرد ...
$edit_mode = false;
$room_data = null;
$room_translations = [];

// --- بخش ۱: پردازش فرم و درخواست‌ها ---

// 1.1: رسیدگی به درخواست حذف
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $room_id_to_delete = intval($_GET['delete']);

    // برای امنیت، ابتدا تصویر اصلی را پیدا و از سرور حذف می‌کنیم
    $stmt = $conn->prepare("SELECT image FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id_to_delete);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result && file_exists('../uploads/rooms/' . $result['image'])) {
        unlink('../uploads/rooms/' . $result['image']);
    }
    $stmt->close();
    
    // به لطف 'ON DELETE CASCADE' در دیتابیس، با حذف اتاق، ترجمه‌ها و تصاویر گالری هم حذف می‌شوند
    $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id_to_delete);
    if ($stmt->execute()) {
        echo "<div class='alert success'>اتاق با موفقیت حذف شد.</div>";
    } else {
        echo "<div class='alert error'>خطا در حذف اتاق.</div>";
    }
    $stmt->close();
}


// 1.2: رسیدگی به ارسال فرم (افزودن یا ویرایش)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $price = $_POST['price'];
    
    // دریافت اطلاعات ترجمه‌ها از فرم
    $translations = $_POST['translations'];

    // مدیریت آپلود تصویر اصلی
    $main_image_name = $_POST['existing_image']; // تصویر فعلی در حالت ویرایش
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === 0) {
        $upload_dir = '../uploads/rooms/';
        // ایجاد یک نام منحصر به فرد برای فایل برای جلوگیری از تداخل
        $main_image_name = time() . '_' . basename($_FILES['main_image']['name']);
        move_uploaded_file($_FILES['main_image']['tmp_name'], $upload_dir . $main_image_name);
    }

    if (isset($_POST['room_id']) && !empty($_POST['room_id'])) {
        // --- حالت ویرایش ---
        $room_id = intval($_POST['room_id']);
        $stmt = $conn->prepare("UPDATE rooms SET price_per_night = ?, image = ? WHERE id = ?");
        $stmt->bind_param("dsi", $price, $main_image_name, $room_id);
        $stmt->execute();
        $stmt->close();

        // به‌روزرسانی ترجمه‌ها
        foreach ($translations as $lang => $data) {
            $stmt = $conn->prepare("UPDATE room_translations SET name = ?, short_description = ?, description = ? WHERE room_id = ? AND lang_code = ?");
            $stmt->bind_param("sssis", $data['name'], $data['short_desc'], $data['desc'], $room_id, $lang);
            $stmt->execute();
            $stmt->close();
        }
        echo "<div class='alert success'>اتاق با موفقیت به‌روزرسانی شد.</div>";

    } else {
        // --- حالت افزودن ---
        $stmt = $conn->prepare("INSERT INTO rooms (price_per_night, image) VALUES (?, ?)");
        $stmt->bind_param("ds", $price, $main_image_name);
        $stmt->execute();
        $new_room_id = $stmt->insert_id; // گرفتن ID اتاق جدید
        $stmt->close();

        // درج ترجمه‌ها در دیتابیس
        foreach ($translations as $lang => $data) {
            $stmt = $conn->prepare("INSERT INTO room_translations (room_id, lang_code, name, short_description, description) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $new_room_id, $lang, $data['name'], $data['short_desc'], $data['desc']);
            $stmt->execute();
            $stmt->close();
        }
        echo "<div class='alert success'>اتاق جدید با موفقیت اضافه شد.</div>";
    }
}


// 1.3: بررسی برای حالت ویرایش (پر کردن فرم)
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_mode = true;
    $room_id_to_edit = intval($_GET['edit']);

    // گرفتن اطلاعات اصلی اتاق
    $stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id_to_edit);
    $stmt->execute();
    $room_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // گرفتن تمام ترجمه‌های مربوط به این اتاق
    $stmt = $conn->prepare("SELECT * FROM room_translations WHERE room_id = ?");
    $stmt->bind_param("i", $room_id_to_edit);
    $stmt->execute();
    $translations_result = $stmt->get_result();
    while ($row = $translations_result->fetch_assoc()) {
        $room_translations[$row['lang_code']] = $row;
    }
    $stmt->close();
}


// --- بخش ۲: فرم HTML برای افزودن/ویرایش ---
?>

<div class="content-header">
    <h1><?php echo $edit_mode ? 'ویرایش اتاق' : 'افزودن اتاق جدید'; ?></h1>
</div>

<div class="card">
    <form action="manage-rooms.php" method="POST" enctype="multipart/form-data">
        
        <?php if ($edit_mode): ?>
            <input type="hidden" name="room_id" value="<?php echo $room_data['id']; ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="price">قیمت (به ازای هر شب)</label>
            <input type="number" id="price" name="price" value="<?php echo $edit_mode ? $room_data['price_per_night'] : ''; ?>" required>
        </div>

        <div class="form-group">
            <label for="main_image">تصویر اصلی</label>
            <input type="file" id="main_image" name="main_image" <?php echo !$edit_mode ? 'required' : ''; ?>>
            <?php if ($edit_mode && $room_data['image']): ?>
                <p>تصویر فعلی: <?php echo $room_data['image']; ?></p>
                <img src="../uploads/rooms/<?php echo $room_data['image']; ?>" width="100" alt="Room Image">
                <input type="hidden" name="existing_image" value="<?php echo $room_data['image']; ?>">
            <?php endif; ?>
        </div>

        <div class="lang-tabs">
            <button type="button" class="tab-link active" onclick="openLang(event, 'fa')">فارسی</button>
            <button type="button" class="tab-link" onclick="openLang(event, 'en')">English</button>
            <button type="button" class="tab-link" onclick="openLang(event, 'az')">Azərbaycanca</button>
        </div>

        <div id="fa" class="lang-content" style="display: block;">
            <h3>فارسی</h3>
            <div class="form-group">
                <label>نام اتاق</label>
                <input type="text" name="translations[fa][name]" value="<?php echo $edit_mode ? htmlspecialchars($room_translations['fa']['name']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label>توضیح کوتاه</label>
                <input type="text" name="translations[fa][short_desc]" value="<?php echo $edit_mode ? htmlspecialchars($room_translations['fa']['short_description']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label>توضیحات کامل</label>
                <textarea name="translations[fa][desc]" rows="5" required><?php echo $edit_mode ? htmlspecialchars($room_translations['fa']['description']) : ''; ?></textarea>
            </div>
        </div>

        <div id="en" class="lang-content">
            <h3>English</h3>
            <div class="form-group">
                <label>Room Name</label>
                <input type="text" name="translations[en][name]" value="<?php echo $edit_mode && isset($room_translations['en']) ? htmlspecialchars($room_translations['en']['name']) : ''; ?>">
            </div>
             <div class="form-group">
                <label>Short Description</label>
                <input type="text" name="translations[en][short_desc]" value="<?php echo $edit_mode && isset($room_translations['en']) ? htmlspecialchars($room_translations['en']['short_description']) : ''; ?>">
            </div>
            <div class="form-group">
                <label>Full Description</label>
                <textarea name="translations[en][desc]" rows="5"><?php echo $edit_mode && isset($room_translations['en']) ? htmlspecialchars($room_translations['en']['description']) : ''; ?></textarea>
            </div>
        </div>

        <div id="az" class="lang-content">
            <h3>Azərbaycanca</h3>
            <div class="form-group">
                <label>Otaq Adı</label>
                <input type="text" name="translations[az][name]" value="<?php echo $edit_mode && isset($room_translations['az']) ? htmlspecialchars($room_translations['az']['name']) : ''; ?>">
            </div>
            <div class="form-group">
                <label>Qısa Təsvir</label>
                <input type="text" name="translations[az][short_desc]" value="<?php echo $edit_mode && isset($room_translations['az']) ? htmlspecialchars($room_translations['az']['short_description']) : ''; ?>">
            </div>
            <div class="form-group">
                <label>Tam Təsvir</label>
                <textarea name="translations[az][desc]" rows="5"><?php echo $edit_mode && isset($room_translations['az']) ? htmlspecialchars($room_translations['az']['description']) : ''; ?></textarea>
            </div>
        </div>

        <button type="submit" class="btn"><?php echo $edit_mode ? 'به‌روزرسانی اتاق' : 'افزودن اتاق'; ?></button>
        <?php if ($edit_mode): ?>
            <a href="manage-rooms.php" class="btn btn-secondary">لغو ویرایش</a>
        <?php endif; ?>
    </form>
</div>

<?php
// --- بخش ۳: جدول نمایش لیست اتاق‌ها ---

// کوئری برای گرفتن لیست اتاق‌ها به همراه نام فارسی آنها
$rooms_list = $conn->query("
    SELECT r.id, r.image, r.price_per_night, rt.name 
    FROM rooms r
    LEFT JOIN room_translations rt ON r.id = rt.room_id AND rt.lang_code = 'fa'
    ORDER BY r.id DESC
");
?>
<div class="content-header" style="margin-top: 40px;">
    <h1>لیست اتاق‌ها</h1>
</div>

<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>تصویر</th>
                <th>نام اتاق (فارسی)</th>
                <th>قیمت</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($room = $rooms_list->fetch_assoc()): ?>
            <tr>
                <td><img src="../uploads/rooms/<?php echo htmlspecialchars($room['image']); ?>" width="80" alt="Room"></td>
                <td><?php echo htmlspecialchars($room['name']); ?></td>
                <td><?php echo number_format($room['price_per_night']); ?> تومان</td>
                <td>
                    <a href="manage-rooms.php?edit=<?php echo $room['id']; ?>" class="btn btn-sm">ویرایش</a>
                    <a href="manage-rooms.php?delete=<?php echo $room['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('آیا از حذف این اتاق مطمئن هستید؟')">حذف</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<style>
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
    .form-group input, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
    .btn { background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
    .btn:hover { background-color: #0056b3; }
    .btn-secondary { background-color: #6c757d; }
    .btn-danger { background-color: #dc3545; }
    .btn-sm { padding: 5px 10px; font-size: 12px; }
    .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
    .alert.success { background-color: #d4edda; color: #155724; }
    .alert.error { background-color: #f8d7da; color: #721c24; }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th, .data-table td { padding: 12px; border: 1px solid #ddd; text-align: right; }
    .data-table th { background-color: #f2f2f2; }
    .lang-tabs { overflow: hidden; border-bottom: 1px solid #ccc; margin-bottom: 15px; }
    .lang-tabs .tab-link { background-color: inherit; float: right; border: none; outline: none; cursor: pointer; padding: 10px 15px; transition: 0.3s; }
    .lang-tabs .tab-link:hover { background-color: #ddd; }
    .lang-tabs .tab-link.active { background-color: #ccc; }
    .lang-content { display: none; padding: 10px 0; }
</style>

<script>
    function openLang(evt, langName) {
        var i, langcontent, tablinks;
        langcontent = document.getElementsByClassName("lang-content");
        for (i = 0; i < langcontent.length; i++) {
            langcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tab-link");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
        document.getElementById(langName).style.display = "block";
        evt.currentTarget.className += " active";
    }
</script>


<?php include_once 'partials/footer.php'; ?>