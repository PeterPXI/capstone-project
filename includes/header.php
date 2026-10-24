<?php

declare(strict_types=1);

$pageTitle = $pageTitle ?? 'DealExit | بيع وشراء المشاريع';
$currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$assetPrefix = $assetPrefix ?? '';

// Auth-aware navigation: start session so we can check login state.
if (file_exists(__DIR__ . '/auth.php')) {
    require_once __DIR__ . '/auth.php';
    auth_start_session();
}
if (file_exists(__DIR__ . '/csrf.php')) {
    require_once __DIR__ . '/csrf.php';
}
$isUserLoggedIn = isset($_SESSION['user_id']);
?>
<!doctype html>
<html lang="ar" dir="rtl" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="منصة تساعد أصحاب المشاريع والمشترين على الوصول إلى الصفقة المناسبة في مصر.">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= $assetPrefix ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $assetPrefix ?>assets/css/site.css">
    <link rel="stylesheet" href="<?= $assetPrefix ?>assets/css/auth.css">
</head>
<body>
<nav class="navbar lg:navbar-expand site-navbar bg-1 fg-2">
    <div class="container">
        <a class="navbar-brand site-brand" href="<?= $assetPrefix ?>index.php" aria-label="DealExit - الرئيسية">
            <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 32 32" fill="none"><path d="M5 25V13.5L16 6l11 7.5V25H5Z" stroke="currentColor" stroke-width="2.4" stroke-linejoin="round"/><path d="M12 25v-8h8v8M10 13h.01M16 13h.01M22 13h.01" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg>
            </span>
            <span>Deal<span class="brand-accent">Exit</span></span>
        </a>
        <button class="btn-icon navbar-toggler site-toggler" type="button" data-bs-toggle="drawer" data-bs-target="#mainNavigation" aria-controls="mainNavigation" aria-expanded="false" aria-label="فتح القائمة">
            <span class="navbar-toggler-icon"></span>
        </button>
        <dialog class="drawer drawer-start site-nav-drawer" tabindex="-1" id="mainNavigation" aria-labelledby="mainNavigationLabel">
            <div class="drawer-header">
                <h2 class="drawer-title" id="mainNavigationLabel">القائمة</h2>
                <button type="button" class="btn-close" data-bs-dismiss="drawer" aria-label="إغلاق القائمة"></button>
            </div>
            <div class="drawer-body">
            <ul class="navbar-nav me-auto mb-2">
                <li class="nav-item"><a class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>" <?= $currentPage === 'index.php' ? 'aria-current="page"' : '' ?> href="<?= $assetPrefix ?>index.php">الرئيسية</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $assetPrefix ?>index.php#listings">الفرص المعروضة</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $assetPrefix ?>index.php#how-it-works">كيف يعمل الموقع؟</a></li>
            </ul>
            <?php if ($isUserLoggedIn): ?>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a class="btn-outline theme-secondary" href="<?= $assetPrefix ?>authentication/profile.php" style="padding:8px 14px;border-radius:10px;border:1px solid #d8e0ec;font-size:.88rem;font-weight:700;color:var(--de-ink);">
                        <?= htmlspecialchars($_SESSION['user_name'] ?? 'حسابي', ENT_QUOTES, 'UTF-8') ?>
                    </a>
                    <form method="post" action="<?= $assetPrefix ?>authentication/logout.php" style="margin:0;">
                        <?= function_exists('csrf_field') ? csrf_field() : '' ?>
                        <button type="submit" class="btn-solid theme-primary site-nav-cta" style="padding:8px 14px;border-radius:10px;font-size:.88rem;cursor:pointer;">تسجيل الخروج</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a class="btn-outline theme-secondary" href="<?= $assetPrefix ?>authentication/login.php" style="padding:8px 14px;border-radius:10px;border:1px solid #d8e0ec;font-size:.88rem;font-weight:700;color:var(--de-ink);">تسجيل الدخول</a>
                    <a class="btn-solid theme-primary site-nav-cta" href="<?= $assetPrefix ?>authentication/register.php">إنشاء حساب <span aria-hidden="true">←</span></a>
                </div>
            <?php endif; ?>
            </div>
        </dialog>
    </div>
</nav>
<main>
