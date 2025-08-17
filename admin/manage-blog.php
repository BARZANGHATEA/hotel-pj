<?php
// متغیرهای اولیه
$edit_mode = false;
$post_data = null;
$post_translations = [];
$flash_message = '';
$predefined_categories = ['راهنمای سفر', 'اخبار هتل', 'نکات مفید', 'تجربه مهمانان', 'رویدادها', 'آشپزی', 'فرهنگ و هنر'];
$popular_tags = ['سفر', 'هتل', 'گردشگری', 'اقامت', 'تهران', 'لوکس', 'خدمات', 'رستوران'];

// Include header after processing redirects
require_once 'auth-check.php';

// نمایش پیام بازخورد
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

// واکشی دسته‌بندی‌های موجود از دیتابیس
$existing_categories = [];
$stmt = $conn->prepare("SELECT DISTINCT categories FROM blog_posts WHERE categories IS NOT NULL AND categories != ''");
if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    if (!empty($row['categories'])) {
        $cats = explode(',', $row['categories']);
        foreach ($cats as $cat) {
            $cat = trim($cat);
            if (!empty($cat) && !in_array($cat, $existing_categories)) {
                $existing_categories[] = $cat;
            }
        }
    }
}
$stmt->close();

// واکشی برچسب‌های موجود از دیتابیس
$existing_tags = [];
$stmt = $conn->prepare("SELECT DISTINCT tags FROM blog_posts WHERE tags IS NOT NULL AND tags != ''");
if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    if (!empty($row['tags'])) {
        $tags = explode(',', $row['tags']);
        foreach ($tags as $tag) {
            $tag = trim($tag);
            if (!empty($tag) && !in_array($tag, $existing_tags)) {
                $existing_tags[] = $tag;
            }
        }
    }
}
$stmt->close();

// Include header after processing all redirects
include_once 'partials/header.php';

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
    $categories = $_POST['categories'] ?? '';
    $tags = $_POST['tags'] ?? '';

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
        $stmt = $conn->prepare("UPDATE blog_posts SET image = ?, categories = ?, tags = ? WHERE id = ?");
        $stmt->bind_param("sssi", $image_name, $categories, $tags, $post_id);
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
        $stmt = $conn->prepare("INSERT INTO blog_posts (image, categories, tags) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $image_name, $categories, $tags);
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

<!-- TinyMCE Rich Text Editor -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<!-- Page Header -->
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">
            <?php echo $edit_mode ? 'ویرایش مقاله' : 'مدیریت وبلاگ'; ?>
        </h1>
        <p class="text-gray-600 mt-2">
            <?php echo $edit_mode ? 'ویرایش محتوای مقاله' : 'نوشتن و مدیریت مقالات وبلاگ با ویرایشگر حرفه‌ای'; ?>
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

        <!-- Image Upload Section -->
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

        <!-- Enhanced Categories and Tags -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Categories (Enhanced with suggestions) -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">دسته‌بندی‌ها</label>
                
                <!-- Predefined Categories -->
                <div class="mb-3">
                    <p class="text-xs text-gray-600 mb-2">دسته‌بندی‌های پیشنهادی:</p>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($predefined_categories as $cat): ?>
                            <button type="button" onclick="addPredefinedCategory('<?php echo $cat; ?>')" 
                                    class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs hover:bg-blue-200 transition-colors duration-300">
                                <?php echo $cat; ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Selected Categories Display -->
                <div id="categoryChips" class="flex flex-wrap gap-2 min-h-[36px] p-2 bg-gray-50 rounded-lg border border-gray-200 mb-2"></div>
                
                <!-- Category Input -->
                <div class="flex gap-2">
                    <input id="categoryInput" 
                           type="text" 
                           list="categoryDatalist"
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300" 
                           placeholder="دسته‌بندی جدید یا انتخاب از لیست">
                    <button type="button" onclick="addCategory()" 
                            class="px-4 py-2 bg-hotel-gold text-hotel-dark rounded-lg font-semibold hover:bg-hotel-gold/90 transition-colors duration-300">
                        افزودن
                    </button>
                </div>
                
                <!-- Datalist for autocomplete -->
                <datalist id="categoryDatalist">
                    <?php foreach (array_merge($predefined_categories, $existing_categories) as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>">
                    <?php endforeach; ?>
                </datalist>
                
                <input type="hidden" name="categories" id="categoriesField" value="<?php echo $edit_mode && $post_data ? htmlspecialchars($post_data['categories']) : ''; ?>">
            </div>

            <!-- Tags (Enhanced with suggestions) -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">برچسب‌ها</label>
                
                <!-- Popular Tags -->
                <div class="mb-3">
                    <p class="text-xs text-gray-600 mb-2">برچسب‌های محبوب:</p>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($popular_tags as $tag): ?>
                            <button type="button" onclick="addPredefinedTag('<?php echo $tag; ?>')" 
                                    class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs hover:bg-green-200 transition-colors duration-300">
                                #<?php echo $tag; ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Selected Tags Display -->
                <div id="tagChips" class="flex flex-wrap gap-2 min-h-[36px] p-2 bg-gray-50 rounded-lg border border-gray-200 mb-2"></div>
                
                <!-- Tag Input -->
                <div class="flex gap-2">
                    <input id="tagInput" 
                           type="text" 
                           list="tagDatalist"
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent transition-colors duration-300" 
                           placeholder="برچسب جدید (Enter برای افزودن)">
                    <button type="button" onclick="addTag()" 
                            class="px-4 py-2 bg-hotel-gold text-hotel-dark rounded-lg font-semibold hover:bg-hotel-gold/90 transition-colors duration-300">
                        افزودن
                    </button>
                </div>
                
                <!-- Datalist for autocomplete -->
                <datalist id="tagDatalist">
                    <?php foreach (array_merge($popular_tags, $existing_tags) as $tag): ?>
                        <option value="<?php echo htmlspecialchars($tag); ?>">
                    <?php endforeach; ?>
                </datalist>
                
                <input type="hidden" name="tags" id="tagsField" value="<?php echo $edit_mode && $post_data ? htmlspecialchars($post_data['tags']) : ''; ?>">
            </div>
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
                    <textarea id="content_fa" 
                              name="translations[fa][content]" 
                              rows="12" 
                              required
                              class="rich-editor w-full"><?php echo $edit_mode && isset($post_translations['fa']) ? htmlspecialchars($post_translations['fa']['content']) : ''; ?></textarea>
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
                    <textarea id="content_en" 
                              name="translations[en][content]" 
                              rows="12"
                              class="rich-editor w-full"><?php echo $edit_mode && isset($post_translations['en']) ? htmlspecialchars($post_translations['en']['content']) : ''; ?></textarea>
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
                    <textarea id="content_az" 
                              name="translations[az][content]" 
                              rows="12"
                              class="rich-editor w-full"><?php echo $edit_mode && isset($post_translations['az']) ? htmlspecialchars($post_translations['az']['content']) : ''; ?></textarea>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
            <div class="flex items-center space-x-4 space-x-reverse">
                <?php if ($edit_mode): ?>
                    <a href="manage-blog.php" 
                       class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-300">
                        انصراف
                    </a>
                <?php else: ?>
                    <button type="button" onclick="toggleForm()" 
                            class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-300">
                        انصراف
                    </button>
                <?php endif; ?>
                
                <button type="button" onclick="saveDraft()" 
                        class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-300">
                    ذخیره پیش‌نویس
                </button>
            </div>
            
            <button type="submit" 
                    class="px-8 py-3 bg-hotel-gold text-hotel-dark rounded-lg font-semibold hover:bg-hotel-gold/90 transition-colors duration-300">
                <?php echo $edit_mode ? 'به‌روزرسانی مقاله' : 'انتشار مقاله'; ?>
            </button>
        </div>
    </form>
</div>

<!-- Blog Posts List -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">مقالات موجود</h2>
        
        <!-- Category Filter -->
        <div class="flex items-center space-x-4 space-x-reverse">
            <select id="categoryFilter" onchange="filterByCategory()" 
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hotel-gold focus:border-transparent">
                <option value="">همه دسته‌بندی‌ها</option>
                <?php foreach (array_unique(array_merge($predefined_categories, $existing_categories)) as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo $cat; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-right py-3 px-4 font-semibold text-gray-700">تصویر</th>
                    <th class="text-right py-3 px-4 font-semibold text-gray-700">عنوان</th>
                    <th class="text-right py-3 px-4 font-semibold text-gray-700">دسته‌بندی</th>
                    <th class="text-right py-3 px-4 font-semibold text-gray-700">برچسب‌ها</th>
                    <th class="text-right py-3 px-4 font-semibold text-gray-700">تاریخ</th>
                    <th class="text-center py-3 px-4 font-semibold text-gray-700">عملیات</th>
                </tr>
            </thead>
            <tbody id="postsTableBody">
                <?php
                $stmt = $conn->prepare("
                    SELECT p.id, p.image, p.categories, p.tags, p.created_at, pt.title
                    FROM blog_posts p
                    LEFT JOIN blog_post_translations pt ON p.id = pt.post_id AND pt.lang_code = 'fa'
                    ORDER BY p.created_at DESC
                ");
                $stmt->execute();
                $result = $stmt->get_result();

                while ($post = $result->fetch_assoc()):
                    $post_date = date("Y/m/d", strtotime($post['created_at']));
                ?>
                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors duration-300" 
                    data-categories="<?php echo htmlspecialchars($post['categories']); ?>">
                    <td class="py-4 px-4">
                        <?php if ($post['image']): ?>
                            <img src="../uploads/blog/<?php echo $post['image']; ?>" 
                                 class="w-16 h-12 object-cover rounded-lg" 
                                 alt="Post Image">
                        <?php else: ?>
                            <div class="w-16 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="py-4 px-4">
                        <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($post['title'] ?: 'بدون عنوان'); ?></div>
                    </td>
                    <td class="py-4 px-4">
                        <?php if ($post['categories']): ?>
                            <div class="flex flex-wrap gap-1">
                                <?php foreach (explode(',', $post['categories']) as $cat): ?>
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                        <?php echo htmlspecialchars(trim($cat)); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="py-4 px-4">
                        <?php if ($post['tags']): ?>
                            <div class="flex flex-wrap gap-1">
                                <?php foreach (explode(',', $post['tags']) as $tag): ?>
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                        #<?php echo htmlspecialchars(trim($tag)); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="py-4 px-4 text-gray-600 text-sm">
                        <?php echo $post_date; ?>
                    </td>
                    <td class="py-4 px-4 text-center">
                        <div class="flex items-center justify-center space-x-2 space-x-reverse">
                            <a href="manage-blog.php?edit=<?php echo $post['id']; ?>" 
                               class="text-blue-600 hover:text-blue-800 transition-colors duration-300" 
                               title="ویرایش">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            <button onclick="previewPost(<?php echo $post['id']; ?>)" 
                                    class="text-green-600 hover:text-green-800 transition-colors duration-300" 
                                    title="پیش‌نمایش">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                            <button onclick="confirmDelete(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars($post['title']); ?>')" 
                                    class="text-red-600 hover:text-red-800 transition-colors duration-300" 
                                    title="حذف">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; $stmt->close(); ?>
            </tbody>
        </table>
    </div>

    <!-- Empty State -->
    <?php
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM blog_posts");
    $stmt->execute();
    $post_count = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();
    
    if ($post_count == 0):
    ?>
    <div class="text-center py-12">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">هنوز مقاله‌ای نوشته نشده</h3>
        <p class="text-gray-600 mb-4">اولین مقاله خود را بنویسید و با دنیا به اشتراک بگذارید</p>
        <button onclick="toggleForm()" 
                class="bg-hotel-gold text-hotel-dark px-6 py-3 rounded-lg font-semibold hover:bg-hotel-gold/90 transition-colors duration-300">
            نوشتن اولین مقاله
        </button>
    </div>
    <?php endif; ?>
</div>

<!-- Include Blog Admin JavaScript -->
<script src="../assets/js/blog-admin.js"></script>

<!-- Custom Styles for Rich Editor -->
<style>
.tox-tinymce {
    border-radius: 0.5rem !important;
    border: 1px solid #d1d5db !important;
}

.tox-toolbar {
    border-bottom: 1px solid #e5e7eb !important;
}

.tox-edit-area {
    border-radius: 0 0 0.5rem 0.5rem !important;
}

.tox-statusbar {
    border-top: 1px solid #e5e7eb !important;
    border-radius: 0 0 0.5rem 0.5rem !important;
}

/* RTL Support for Persian content */
.tox-edit-area iframe {
    direction: rtl;
}

/* Custom scrollbar for chips containers */
#categoryChips::-webkit-scrollbar,
#tagChips::-webkit-scrollbar {
    height: 4px;
}

#categoryChips::-webkit-scrollbar-track,
#tagChips::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 2px;
}

#categoryChips::-webkit-scrollbar-thumb,
#tagChips::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 2px;
}

#categoryChips::-webkit-scrollbar-thumb:hover,
#tagChips::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Animation for chips */
.chip-enter {
    animation: chipEnter 0.3s ease-out;
}

@keyframes chipEnter {
    from {
        opacity: 0;
        transform: scale(0.8);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* Responsive table */
@media (max-width: 768px) {
    .overflow-x-auto table {
        font-size: 0.875rem;
    }
    
    .overflow-x-auto td,
    .overflow-x-auto th {
        padding: 0.5rem 0.25rem;
    }
}

/* Loading state for buttons */
.btn-loading {
    position: relative;
    color: transparent !important;
}

.btn-loading::after {
    content: "";
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-left: -8px;
    margin-top: -8px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style>

<?php include_once 'partials/footer.php'; ?>
