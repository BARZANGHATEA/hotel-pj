<?php
// Script to run database updates for blog functionality
require_once '../config/db.php';

header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html>
<html lang='fa' dir='rtl'>
<head>
    <meta charset='UTF-8'>
    <title>بروزرسانی پایگاه داده</title>
    <style>
        body { font-family: Vazir, Tahoma, Arial, sans-serif; background: #f8fafc; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #22c55e; background: #f0fdf4; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: #ef4444; background: #fef2f2; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #3b82f6; background: #eff6ff; padding: 10px; border-radius: 5px; margin: 10px 0; }
        pre { background: #1e293b; color: #e2e8f0; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>بروزرسانی ساختار پایگاه داده</h1>
";

// Check if user is admin
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo "<div class='error'>دسترسی غیرمجاز. لطفاً ابتدا وارد سیستم شوید.</div>";
    echo "</div></body></html>";
    exit;
}

// Read the SQL update file
$sql_file = '../config/update_blog_table.sql';
if (!file_exists($sql_file)) {
    echo "<div class='error'>فایل بروزرسانی یافت نشد: $sql_file</div>";
    echo "</div></body></html>";
    exit;
}

$sql_content = file_get_contents($sql_file);
$queries = array_filter(array_map('trim', explode(';', $sql_content)));

$count = count($queries);
echo "<div class='info'>در حال اجرای {$count} کوئری بروزرسانی...</div>";

foreach ($queries as $index => $query) {
    if (empty($query)) continue;
    
    echo "<h3>کوئری " . ($index + 1) . ":</h3>";
    echo "<pre>" . htmlspecialchars($query) . ";</pre>";
    
    try {
        $result = $conn->query($query . ';');
        if ($result === false) {
            echo "<div class='error'>خطا در اجرای کوئری: " . htmlspecialchars($conn->error) . "</div>";
        } else {
            echo "<div class='success'>✓ کوئری با موفقیت اجرا شد</div>";
            
            // Show results for SELECT queries
            if (stripos($query, 'SELECT') === 0 && $result->num_rows > 0) {
                echo "<h4>نتایج:</h4>";
                echo "<table style='width: 100%; border-collapse: collapse;'>";
                
                // Header row
                echo "<tr style='background: #374151; color: white;'>";
                $fields = $result->fetch_fields();
                foreach ($fields as $field) {
                    echo "<th style='padding: 8px; border: 1px solid #4b5563; text-align: right;'>" . htmlspecialchars($field->name) . "</th>";
                }
                echo "</tr>";
                
                // Data rows
                $result->data_seek(0);
                while ($row = $result->fetch_assoc()) {
                    echo "<tr style='background: #f9fafb;'>";
                    foreach ($row as $value) {
                        echo "<td style='padding: 8px; border: 1px solid #e5e7eb; text-align: right;'>" . htmlspecialchars($value) . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
    } catch (Exception $e) {
        echo "<div class='error'>خطا: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    
    echo "<hr>";
}

echo "<div class='success'>بروزرسانی پایگاه داده تکمیل شد!</div>";
echo "<p><a href='manage-blog.php' style='color: #3b82f6; text-decoration: none;'>← بازگشت به مدیریت وبلاگ</a></p>";
echo "</div></body></html>";
?>
