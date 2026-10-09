<?php
// فایل: includes/functions.php
// توابع کمکی مشترک بین سایت و پنل مدیریت (امنیت، آپلود، ویدیو، رزرو)

// خروجی امن برای HTML
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// ---------- CSRF ----------
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

// توکن را از POST یا GET می‌خواند و در صورت نامعتبر بودن، درخواست را متوقف می‌کند
function verify_csrf() {
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    if (!is_string($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('درخواست نامعتبر است (CSRF). لطفاً صفحه را دوباره بارگذاری کنید.');
    }
}

// ---------- آپلود تصویر ----------
// فایل آپلود شده را بررسی می‌کند (پسوند + محتوای واقعی تصویر) و با نام تصادفی ذخیره می‌کند.
// خروجی: نام فایل ذخیره شده، یا null در صورت خطا
function save_uploaded_image(array $file, $upload_dir, $max_bytes = 10485760) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return null;
    }
    if ($file['size'] > $max_bytes) {
        return null;
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!isset($allowed[$mime]) || @getimagesize($file['tmp_name']) === false) {
        return null;
    }

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $name = time() . '_' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], rtrim($upload_dir, '/') . '/' . $name)) {
        return null;
    }
    return $name;
}

// آرایه چندتایی $_FILES['x'] را به لیست فایل‌های تکی تبدیل می‌کند
function normalize_files_array($files) {
    $result = [];
    if (!isset($files['name']) || !is_array($files['name'])) {
        return $result;
    }
    foreach ($files['name'] as $i => $name) {
        $result[] = [
            'name'     => $name,
            'type'     => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error'    => $files['error'][$i],
            'size'     => $files['size'][$i],
        ];
    }
    return $result;
}

// حذف امن یک فایل داخل یک پوشه مشخص (جلوگیری از Path Traversal)
function safe_unlink($dir, $filename) {
    if ($filename === '' || $filename === null) {
        return;
    }
    $path = rtrim($dir, '/') . '/' . basename($filename);
    if (is_file($path)) {
        unlink($path);
    }
}

// ---------- ویدیو ----------
// لینک یوتیوب/آپارات/ویمئو را به لینک embed تبدیل می‌کند؛ در غیر این صورت رشته خالی برمی‌گرداند
function video_embed_url($url) {
    $url = trim((string) $url);
    if ($url === '') {
        return '';
    }
    if (preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,})~', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
        return 'https://player.vimeo.com/video/' . $m[1];
    }
    if (preg_match('~aparat\.com/(?:v|video/video/embed/videohash)/([A-Za-z0-9]+)~', $url, $m)) {
        return 'https://www.aparat.com/video/video/embed/videohash/' . $m[1] . '/vt/frame';
    }
    return '';
}

// ---------- رزرو ----------
function booking_status_label($status) {
    $labels = [
        'pending'   => 'در انتظار بررسی',
        'confirmed' => 'تایید شده',
        'cancelled' => 'لغو شده',
    ];
    return $labels[$status] ?? $status;
}

// بررسی می‌کند آیا اتاق در بازه تاریخ داده شده رزرو تایید شده دارد یا نه
// (تاریخ خروج روز آزاد شدن اتاق است، پس بازه‌ها نیمه‌باز [check_in, check_out) در نظر گرفته می‌شوند)
function room_is_available($conn, $room_id, $check_in, $check_out, $exclude_booking_id = 0) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS c FROM bookings
        WHERE room_id = ? AND status = 'confirmed' AND id != ?
          AND check_in < ? AND check_out > ?
    ");
    $stmt->bind_param("iiss", $room_id, $exclude_booking_id, $check_out, $check_in);
    $stmt->execute();
    $count = (int) $stmt->get_result()->fetch_assoc()['c'];
    $stmt->close();
    return $count === 0;
}

// ---------- محتوای HTML ----------
// محتوای تولید شده با ویرایشگر (TinyMCE) را با فهرست مجاز تگ‌ها نمایش می‌دهد.
// اگر متن ساده باشد، خطوط جدید را به <br> تبدیل می‌کند.
function safe_html($html) {
    $html = (string) $html;
    if (!preg_match('/<(p|br|div|h[1-6]|ul|ol|li|strong|em|b|i|u|a|img|table|span|blockquote)\b/i', $html)) {
        return nl2br(e($html));
    }
    $allowed = '<p><br><b><strong><i><em><u><s><strike><h1><h2><h3><h4><h5><h6><ul><ol><li>'
             . '<a><img><blockquote><table><thead><tbody><tr><th><td><span><div><hr><pre><code><figure><figcaption>';
    $html = strip_tags($html, $allowed);
    // حذف event handlerها (onclick و ...) و style
    $html = preg_replace('/\s(on[a-z]+|style)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
    // جلوگیری از لینک‌های javascript: / data: / vbscript:
    $html = preg_replace('/\s(href|src)\s*=\s*(["\']?)\s*(javascript|data|vbscript):[^"\'\s>]*\2/i', ' $1="#"', $html);
    return $html;
}
