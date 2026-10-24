<?php

declare(strict_types=1);

require __DIR__ . '/../config.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/auth.php';

auth_start_session();

// Redirect already-logged-in users.
if (is_logged_in()) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$emailValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token();

    $emailValue = $_POST['email']    ?? '';
    $password   = $_POST['password'] ?? '';

    $result = auth_login($pdo, $emailValue, $password);

    if ($result['success']) {
        $redirect = '../index.php';
        if (
            isset($_POST['redirect_to'])
            && is_string($_POST['redirect_to'])
            && str_starts_with($_POST['redirect_to'], '/')
        ) {
            $redirect = $_POST['redirect_to'];
        }
        header('Location: ' . $redirect);
        exit;
    }

    $error = $result['error'];
}

$pageTitle   = 'DealExit | تسجيل الدخول';
$assetPrefix = '../';
require __DIR__ . '/../includes/header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-split">

            <!-- ====== Left Panel — Decorative ====== -->
            <div class="auth-panel">
                <div class="auth-panel-content">
                    <div class="auth-panel-logo">
                        <svg viewBox="0 0 32 32" fill="none"><path d="M5 25V13.5L16 6l11 7.5V25H5Z" stroke="currentColor" stroke-width="2.4" stroke-linejoin="round"/><path d="M12 25v-8h8v8M10 13h.01M16 13h.01M22 13h.01" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg>
                    </div>
                    <h2 class="auth-panel-title">مرحبًا بعودتك إلى <span>DealExit</span></h2>
                    <p class="auth-panel-desc">سجّل دخولك وتابع استكشاف الفرص التجارية المتاحة في مصر.</p>

                    <div class="auth-panel-features">
                        <div class="auth-panel-feature">
                            <div class="auth-feature-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <div>
                                <strong>دخول آمن</strong>
                                <span>جلسات مشفرة ومحمية</span>
                            </div>
                        </div>
                        <div class="auth-panel-feature">
                            <div class="auth-feature-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <div>
                                <strong>وصول سريع</strong>
                                <span>إلى لوحة التحكم والإعلانات</span>
                            </div>
                        </div>
                        <div class="auth-panel-feature">
                            <div class="auth-feature-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            </div>
                            <div>
                                <strong>فرصك المحفوظة</strong>
                                <span>تابع المشاريع المفضلة لديك</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="auth-panel-shapes">
                    <div class="auth-shape auth-shape-1"></div>
                    <div class="auth-shape auth-shape-2"></div>
                    <div class="auth-shape auth-shape-3"></div>
                </div>
            </div>

            <!-- ====== Right Panel — Form ====== -->
            <div class="auth-form-side">
                <div class="auth-card">

                    <div class="auth-brand">
                        <h1 class="auth-title">تسجيل الدخول</h1>
                        <p class="auth-subtitle">أدخل بياناتك للوصول إلى حسابك</p>
                    </div>

                    <!-- Social Login -->
                    <div class="auth-social auth-social-row">
                        <a href="#" class="auth-social-btn" onclick="event.preventDefault(); alert('تسجيل الدخول بـ Google سيتم تفعيله قريباً');">
                            <svg viewBox="0 0 24 24" width="20" height="20">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                            </svg>
                            <span>Google</span>
                        </a>
                        <a href="#" class="auth-social-btn" onclick="event.preventDefault(); alert('تسجيل الدخول بـ GitHub سيتم تفعيله قريباً');">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                                <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0 0 24 12c0-6.63-5.37-12-12-12z"/>
                            </svg>
                            <span>GitHub</span>
                        </a>
                    </div>

                    <div class="auth-divider">
                        <span>أو سجّل بالبريد الإلكتروني</span>
                    </div>

                    <?php if ($error !== ''): ?>
                        <div class="auth-error-box">
                            <div class="auth-error-icon">
                                <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                            </div>
                            <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="login.php" novalidate>
                        <?= csrf_field() ?>

                        <?php if (isset($_GET['redirect_to']) && is_string($_GET['redirect_to'])): ?>
                            <input type="hidden" name="redirect_to"
                                   value="<?= htmlspecialchars($_GET['redirect_to'], ENT_QUOTES, 'UTF-8') ?>">
                        <?php endif; ?>

                        <div class="auth-field">
                            <label for="email" class="auth-label">البريد الإلكتروني</label>
                            <input type="email" class="auth-input" id="email" name="email"
                                   value="<?= htmlspecialchars($emailValue, ENT_QUOTES, 'UTF-8') ?>"
                                   placeholder="example@email.com"
                                   required autocomplete="email" dir="ltr">
                        </div>

                        <div class="auth-field">
                            <label for="password" class="auth-label">كلمة المرور</label>
                            <input type="password" class="auth-input" id="password" name="password"
                                   placeholder="أدخل كلمة المرور"
                                   required autocomplete="current-password">
                        </div>

                        <button type="submit" class="auth-btn auth-btn-primary">
                            تسجيل الدخول
                            <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18"><path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </button>
                    </form>

                    <p class="auth-footer-text">
                        ليس لديك حساب؟ <a href="register.php">أنشئ حساب جديد</a>
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
