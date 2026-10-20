<?php

declare(strict_types=1);

$pageTitle = 'DealExit | لوحة الإدارة';
$assetPrefix = '../';
require __DIR__ . '/../config.php';

$counts = ['pending' => 0, 'published' => 0, 'inquiries' => 0];
try {
    $counts['pending'] = (int) $pdo->query("SELECT COUNT(*) FROM listings WHERE status = 'pending_review'")->fetchColumn();
    $counts['published'] = (int) $pdo->query("SELECT COUNT(*) FROM listings WHERE status = 'published'")->fetchColumn();
    $counts['inquiries'] = (int) $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")->fetchColumn();
} catch (PDOException $exception) {
    error_log('DealExit admin dashboard query failed: ' . $exception->getMessage());
}

require __DIR__ . '/../includes/header.php';
?>
<section class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4"><div><p class="text-primary fw-semibold mb-1">إدارة DealExit</p><h1 class="h2 mb-0">نظرة عامة</h1></div><a href="../index.php" class="btn btn-outline-secondary">عرض الموقع</a></div>
    <div class="row g-4">
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><p class="text-secondary mb-2">قيد المراجعة</p><p class="display-6 fw-bold mb-0"><?= $counts['pending'] ?></p></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><p class="text-secondary mb-2">فرص منشورة</p><p class="display-6 fw-bold mb-0"><?= $counts['published'] ?></p></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><p class="text-secondary mb-2">استفسارات جديدة</p><p class="display-6 fw-bold mb-0"><?= $counts['inquiries'] ?></p></div></div></div>
    </div>
    <div class="alert alert-warning mt-4 mb-0"><strong>تنبيه:</strong> هذه واجهة تأسيسية فقط. أضف تسجيل دخول وصلاحيات قبل نشر لوحة الإدارة.</div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
