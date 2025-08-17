<?php 
// هدر را فراخوانی می‌کنیم (که خودش auth-check را هم دارد)
include_once 'partials/header.php'; 

// کوئری برای شمارش تعداد کل اتاق‌ها
$total_rooms_result = $conn->query("SELECT COUNT(id) as total FROM rooms");
$total_rooms = $total_rooms_result->fetch_assoc()['total'];

// کوئری برای شمارش تعداد کل مقالات
$total_posts_result = $conn->query("SELECT COUNT(id) as total FROM blog_posts");
$total_posts = $total_posts_result->fetch_assoc()['total'];

?>

<div class="content-header">
    <h1>داشبورد</h1>
</div>

<div class="card">
    <h2>خوش آمدید، <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</h2>
    <p>از این پنل می‌توانید بخش‌های مختلف سایت را مدیریت کنید.</p>
    
    <div style="display: flex; gap: 20px; margin-top: 20px;">
        <div class="stat-card">
            <h3>تعداد اتاق‌ها</h3>
            <p><?php echo $total_rooms; ?></p>
        </div>
        <div class="stat-card">
            <h3>تعداد مقالات</h3>
            <p><?php echo $total_posts; ?></p>
        </div>
    </div>
</div>

<style>
    .stat-card {
        flex: 1;
        background-color: #f1f1f1;
        padding: 20px;
        text-align: center;
        border-radius: 8px;
    }
    .stat-card h3 {
        margin-bottom: 10px;
        color: #555;
    }
    .stat-card p {
        font-size: 28px;
        font-weight: 700;
        color: #007bff;
    }
</style>

<?php 
// فوتر را فراخوانی می‌کنیم
include_once 'partials/footer.php'; 
?>