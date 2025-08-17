<?php
include_once 'partials/header.php';

$edit_mode = false;
$post_data = null;
$post_translations = [];

// --- بخش ۱: پردازش فرم و درخواست‌ها ---

// 1.1: رسیدگی به درخواست حذف
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
    
    // حذف مقاله از دیتابیس (ترجمه‌ها هم به صورت خودکار حذف می‌شوند)
    $stmt = $conn->prepare("DELETE FROM blog_posts WHERE id = ?");
    $stmt->bind_param("i", $post_id_to_delete);
    if ($stmt->execute()) {
        echo "<div class='alert success'>مقاله با موفقیت حذف شد.</div>";
    } else {
        echo "<div class='alert error'>خطا در حذف مقاله.</div>";
    }
    $stmt->close();
}


// 1.2: رسیدگی به ارسال فرم (افزودن یا ویرایش)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $translations = $_POST['translations'];

    // مدیریت آپلود تصویر شاخص
    $image_name = $_POST['existing_image'] ?? ''; // تصویر فعلی در حالت ویرایش
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../uploads/blog/';
        $image_name = time() . '_' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name);
    }

    if (isset($_POST['post_id']) && !empty($_POST['post_id'])) {
        // --- حالت ویرایش ---
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
        echo "<div class='alert success'>مقاله با موفقیت به‌روزرسانی شد.</div>";

    } else {
        // --- حالت افزودن ---
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
        echo "<div class='alert success'>مقاله جدید با موفقیت اضافه شد.</div>";
    }
}


// 1.3: بررسی برای حالت ویرایش (پر کردن فرم)
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_mode = true;
    $post_id_to_edit = intval($_GET['edit']);

    // گرفتن اطلاعات اصلی مقاله
    $stmt = $conn->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmt->bind_param("i", $post_id_to_edit);
    $stmt->execute();
    $post_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // گرفتن تمام ترجمه‌های مربوط به این مقاله
    $stmt = $conn->prepare("SELECT * FROM blog_post_translations WHERE post_id = ?");
    $stmt->bind_param("i", $post_id_to_edit);
    $stmt->execute();
    $translations_result = $stmt->get_result();
    while ($row = $translations_result->fetch_assoc()) {
        $post_translations[$row['lang_code']] = $row;
    }
    $stmt->close();
}


// --- بخش ۲: فرم HTML برای افزودن/ویرایش ---
?>

<div class="content-header">
    <h1><?php echo $edit_mode ? 'ویرایش مقاله' : 'افزودن مقاله جدید'; ?></h1>
</div>

<div class="card">
    <form action="manage-blog.php" method="POST" enctype="multipart/form-data">
        
        <?php if ($edit_mode): ?>
            <input type="hidden" name="post_id" value="<?php echo $post_data['id']; ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="image">تصویر شاخص</label>
            <input type="file" id="image" name="image" <?php echo !$edit_mode ? 'required' : ''; ?>>
            <?php if ($edit_mode && $post_data['image']): ?>
                <p>تصویر فعلی:</p>
                <img src="../uploads/blog/<?php echo $post_data['image']; ?>" width="100" alt="Post Image">
                <input type="hidden" name="existing_image" value="<?php echo $post_data['image']; ?>">
            <?php endif; ?>
        </div>

        <div class="lang-tabs">
            <button type="button" class="tab-link active" onclick="openLang(event, 'fa')">فارسی</button>
            <button type="button" class="tab-link" onclick="openLang(event, 'en')">English</button>
            <button type="button" class="tab-link" onclick="openLang(event, 'az')">Azərbaycanca</button>
        </div>

        <?php 
        $languages = ['fa' => 'فارسی', 'en' => 'English', 'az' => 'Azərbaycanca'];
        foreach ($languages as $lang => $lang_name):
            $is_active = $lang === 'fa' ? 'style="display: block;"' : '';
            $title = $edit_mode && isset($post_translations[$lang]) ? htmlspecialchars($post_translations[$lang]['title']) : '';
            $summary = $edit_mode && isset($post_translations[$lang]) ? htmlspecialchars($post_translations[$lang]['summary']) : '';
            $content = $edit_mode && isset($post_translations[$lang]) ? htmlspecialchars($post_translations[$lang]['content']) : '';
        ?>
        <div id="<?php echo $lang; ?>" class="lang-content" <?php echo $is_active; ?>>
            <h3><?php echo $lang_name; ?></h3>
            <div class="form-group">
                <label>عنوان</label>
                <input type="text" name="translations[<?php echo $lang; ?>][title]" value="<?php echo $title; ?>" <?php echo $lang === 'fa' ? 'required' : ''; ?>>
            </div>
            <div class="form-group">
                <label>خلاصه</label>
                <textarea name="translations[<?php echo $lang; ?>][summary]" rows="3" <?php echo $lang === 'fa' ? 'required' : ''; ?>><?php echo $summary; ?></textarea>
            </div>
            <div class="form-group">
                <label>محتوای کامل</label>
                <textarea name="translations[<?php echo $lang; ?>][content]" rows="8" <?php echo $lang === 'fa' ? 'required' : ''; ?>><?php echo $content; ?></textarea>
            </div>
        </div>
        <?php endforeach; ?>

        <button type="submit" class="btn"><?php echo $edit_mode ? 'به‌روزرسانی مقاله' : 'افزودن مقاله'; ?></button>
        <?php if ($edit_mode): ?>
            <a href="manage-blog.php" class="btn btn-secondary">لغو ویرایش</a>
        <?php endif; ?>
    </form>
</div>

<?php
// --- بخش ۳: جدول نمایش لیست مقالات ---

// کوئری برای گرفتن لیست مقالات به همراه عنوان فارسی آنها
$posts_list = $conn->query("
    SELECT p.id, p.image, pt.title 
    FROM blog_posts p
    LEFT JOIN blog_post_translations pt ON p.id = pt.post_id AND pt.lang_code = 'fa'
    ORDER BY p.id DESC
");
?>
<div class="content-header" style="margin-top: 40px;">
    <h1>لیست مقالات</h1>
</div>

<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>تصویر</th>
                <th>عنوان مقاله (فارسی)</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($post = $posts_list->fetch_assoc()): ?>
            <tr>
                <td><img src="../uploads/blog/<?php echo htmlspecialchars($post['image']); ?>" width="80" alt="Post"></td>
                <td><?php echo htmlspecialchars($post['title']); ?></td>
                <td>
                    <a href="manage-blog.php?edit=<?php echo $post['id']; ?>" class="btn btn-sm">ویرایش</a>
                    <a href="manage-blog.php?delete=<?php echo $post['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('آیا از حذف این مقاله مطمئن هستید؟')">حذف</a>
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
    .btn { background-color: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
    .btn:hover { background-color: #218838; }
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