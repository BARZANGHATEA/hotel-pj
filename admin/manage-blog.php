<?php
include_once 'partials/header.php';

// متغیرهای اولیه
$edit_mode = false;
$post_data = null;
$post_translations = [];
$flash_message = '';

// نمایش پیام بازخورد
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

// رسیدگی به درخواست حذف
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $post_id_to_delete = intval($_GET['delete']);

    // حذف تصویر مقاله از سرور
    $stmt = $conn->prepare("SELECT image FROM blog_posts WHERE id = ?");
    $stmt->bind_param("i", $post_id_to_delete);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result && file_exists('../uploads/blog/' . $result['image'])) {
        unlink('../uploads/blog/' . $result['image']);
    }
    $stmt->close();
    
    // حذف مقاله از دیتابیس
    $stmt = $conn->prepare("DELETE FROM blog_posts WHERE id = ?");
    $stmt->bind_param("i", $post_id_to_delete);
    if ($stmt->execute()) {
        $_SESSION['flash_message'] = "مقاله با موفقیت حذف شد.";
    } else {
        $_SESSION['flash_message'] = "خطا در حذف مقاله.";
    }
    $stmt->close();
    
    header("Location: manage-blog.php");
    exit();
}

// پردازش فرم
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $translations = $_POST['translations'];

    // مدیریت آپلود تصویر شاخص
    $image_name = $_POST['existing_image'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../uploads/blog/';
        $image_name = time() . '_' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name);
    }

    if (isset($_POST['post_id']) && !empty($_POST['post_id'])) {
        // ویرایش
        $post_id = intval($_POST['post_id']);
        $stmt = $conn->prepare("UPDATE blog_posts SET image = ? WHERE id = ?");
        $stmt->bind_param("si", $image_name, $post_id);
        $stmt->execute();
        $stmt->close();

        // به‌روزرسانی ترجمه‌ها
        foreach ($translations as $lang => $data) {
            $stmt = $conn->prepare("UPDATE blog_post_translations SET title = ?, summary = ?, content = ? WHERE post_id = ? AND lang_code = ?");
            $stmt->bind_param("sssis", $data['title'], $data['summary'], $data['content'], $post_id, $lang);
            $stmt->execute();
            $stmt->close();
        }
        $_SESSION['flash_message'] = "مقاله با موفقیت به‌روزرسانی شد.";
    } else {
        // افزودن
        $stmt = $conn->prepare("INSERT INTO blog_posts (image) VALUES (?)");
        $stmt->bind_param("s", $image_name);
        $stmt->execute();
        $new_post_id = $stmt->insert_id;
        $stmt->close();

        // درج ترجمه‌ها
        foreach ($translations as $lang => $data) {
            $stmt = $conn->prepare("INSERT INTO blog_post_translations (post_id, lang_code, title, summary, content) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $new_post_id, $lang, $data['title'], $data['summary'], $data['content']);
            $stmt->execute();
            $stmt->close();
        }
        $_SESSION['flash_message'] = "مقاله جدید با موفقیت اضافه شد.";
    }
    
    header("Location: manage-blog.php");
    exit();
}

// حالت ویرایش
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_mode = true;
    $post_id_to_edit = intval($_GET['edit']);

    $stmt = $conn->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmt->bind_param("i", $post_id_to_edit);
    $stmt->execute();
    $post_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $conn->prepare("SELECT * FROM blog_post_translations WHERE post_id = ?");
    $stmt->bind_param("i", $post_id_to_edit);
    $stmt->execute();
    $translations_result = $stmt->get_result();
    while ($row = $translations_result->fetch_assoc()) {
        $post_translations[$row['lang_code']] = $row;
    }
    $stmt->close();
}
?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">
            <?php echo $edit_mode ? 'ویرایش مقاله' : 'مدیریت وبلاگ'; ?>
        </h1>
        <p class="text-gray-600 mt-2">
            <?php echo $edit_mode ? 'ویرایش محتوای مقاله' : 'نوشتن و مدیریت مقالات وبلاگ'; ?>
        </p>
    </div>
    <?php if (!$edit_mode): ?>
    <button onclick="toggleForm()" 
            class="bg-hotel-gold text-hotel-dark px-6 py-3 rounded-lg font-semibold hover:bg-hotel-gold/90 transition-colors duration-300">
        + نوشتن مقاله جدید
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

<!-- Blog Form -->
<div id="blogForm" class="<?php echo $edit_mode ? 'block' : 'hidden'; ?> bg-white rounded-xl shadow-sm border border-gray-200 p-8 mb-8">
    <form action="manage-blog.php" method="POST" enctype="multipart/form-data" class="space-y-6">
        
        <?php if ($edit_mode): ?>
            <input type="hidden" name="post_id" value="<?php echo $post_data['id']; ?>">
        <?php endif; ?>

        <!-- Featured Image -->
        <div>
            <label for="image" class="block text-sm font-semibold text-gray-700 mb-2">تصویر شاخص</label>
            <input type="file" 
                   id="image" 
                   name="image" 
                   <?php echo !$edit_mode ? 'required' : ''; ?>
                   accept="image/*"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300">
            
            <?php if ($edit_mode && $post_data['image']): ?>
                <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600 mb-2">تصویر فعلی:</p>
                    <img src="../uploads/blog/<?php echo $post_data['image']; ?>" 
                         class="w-48 h-32 object-cover rounded-lg border border-gray-200" 
                         alt="Post Image">
                    <input type="hidden" name="existing_image" value="<?php echo $post_data['image']; ?>">
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
                    <label class="block text-sm font-semibold text-gray-700 mb-2">عنوان مقاله</label>
                    <input type="text" 
                           name="translations[fa][title]" 
                           value="<?php echo $edit_mode && isset($post_translations['fa']) ? htmlspecialchars($post_translations['fa']['title']) : ''; ?>" 
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300"
                           placeholder="عنوان جذاب برای مقاله">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">خلاصه مقاله</label>
                    <textarea name="translations[fa][summary]" 
                              rows="3" 
                              required
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300"
                              placeholder="خلاصه‌ای کوتاه از محتوای مقاله"><?php echo $edit_mode && isset($post_translations['fa']) ? htmlspecialchars($post_translations['fa']['summary']) : ''; ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">محتوای کامل</label>
                    <textarea name="translations[fa][content]" 
                              rows="8" 
                              required
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300"
                              placeholder="محتوای کامل مقاله را اینجا بنویسید..."><?php echo $edit_mode && isset($post_translations['fa']) ? htmlspecialchars($post_translations['fa']['content']) : ''; ?></textarea>
                </div>
            </div>

            <!-- English Tab -->
            <div x-show="activeTab === 'en'" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Article Title</label>
                    <input type="text" 
                           name="translations[en][title]" 
                           value="<?php echo $edit_mode && isset($post_translations['en']) ? htmlspecialchars($post_translations['en']['title']) : ''; ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300"
                           placeholder="Engaging title for the article">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Article Summary</label>
                    <textarea name="translations[en][summary]" 
                              rows="3"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300"
                              placeholder="Brief summary of the article content"><?php echo $edit_mode && isset($post_translations['en']) ? htmlspecialchars($post_translations['en']['summary']) : ''; ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Full Content</label>
                    <textarea name="translations[en][content]" 
                              rows="8"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300"
                              placeholder="Write the complete article content here..."><?php echo $edit_mode && isset($post_translations['en']) ? htmlspecialchars($post_translations['en']['content']) : ''; ?></textarea>
                </div>
            </div>

            <!-- Azerbaijani Tab -->
            <div x-show="activeTab === 'az'" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Məqalə Başlığı</label>
                    <input type="text" 
                           name="translations[az][title]" 
                           value="<?php echo $edit_mode && isset($post_translations['az']) ? htmlspecialchars($post_translations['az']['title']) : ''; ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300"
                           placeholder="Məqalə üçün cəlbedici başlıq">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Məqalə Xülasəsi</label>
                    <textarea name="translations[az][summary]" 
                              rows="3"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300"
                              placeholder="Məqalə məzmununun qısa xülasəsi"><?php echo $edit_mode && isset($post_translations['az']) ? htmlspecialchars($post_translations['az']['summary']) : ''; ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tam Məzmun</label>
                    <textarea name="translations[az][content]" 
                              rows="8"
