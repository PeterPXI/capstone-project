<?php

declare(strict_types=1);

require __DIR__ . '/../config.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/auth.php';

auth_start_session();

// Redirect already-logged-in users to home page.
if (is_logged_in()) {
    header('Location: ../index.php');
    exit;
}

$errors  = [];
$success = false;

// Preserve form values on validation failure.
$formData = [
    'full_name' => '',
    'email'     => '',
    'phone'     => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token();

    $formData['full_name'] = $_POST['full_name'] ?? '';
    $formData['email']     = $_POST['email']     ?? '';
    $formData['phone']     = $_POST['phone']     ?? '';
    $password              = $_POST['password']          ?? '';
    $passwordConfirm       = $_POST['password_confirm']  ?? '';

    $result = auth_register(
        $pdo,
        $formData['full_name'],
        $formData['email'],
        $formData['phone'],
        $password,
        $passwordConfirm
    );

    if ($result['success']) {
        $success = true;
    } else {
        $errors = $result['errors'];
    }
}

$pageTitle   = 'DealExit | إنشاء حساب';
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
                    <h2 class="auth-panel-title">ابدأ رحلتك مع <span>DealExit</span></h2>
                    <p class="auth-panel-desc">منصة واحدة تجمع أصحاب المشاريع والمشترين. سجّل الآن واكتشف فرصتك القادمة.</p>

                    <div class="auth-panel-features">
                        <div class="auth-panel-feature">
                            <div class="auth-feature-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <strong>حماية كاملة</strong>
                                <span>بياناتك مشفرة ومؤمّنة بالكامل</span>
                            </div>
                        </div>
                        <div class="auth-panel-feature">
                            <div class="auth-feature-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <div>
                                <strong>اكتشف الفرص</strong>
                                <span>تصفّح مئات المشاريع المعروضة</span>
                            </div>
                        </div>
                        <div class="auth-panel-feature">
                            <div class="auth-feature-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div>
                                <strong>تواصل مباشر</strong>
                                <span>تواصل مع البائعين والمشترين</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Decorative shapes -->
                <div class="auth-panel-shapes">
                    <div class="auth-shape auth-shape-1"></div>
                    <div class="auth-shape auth-shape-2"></div>
                    <div class="auth-shape auth-shape-3"></div>
                </div>
            </div>

            <!-- ====== Right Panel — Form ====== -->
            <div class="auth-form-side">
                <div class="auth-card">

                    <?php if ($success): ?>
                        <!-- Success state -->
                        <div class="auth-success-box">
                            <div class="auth-success-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="32" height="32"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <h3>تم إنشاء حسابك بنجاح!</h3>
                            <p>يمكنك الآن تسجيل الدخول والبدء في استكشاف الفرص</p>
                            <a href="login.php" class="auth-btn auth-btn-primary">
                                سجّل الدخول الآن
                                <span aria-hidden="true">←</span>
                            </a>
                        </div>

                    <?php else: ?>

                        <div class="auth-brand">
                            <h1 class="auth-title">إنشاء حساب جديد</h1>
                            <p class="auth-subtitle">انضم إلى آلاف المستخدمين على DealExit</p>
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

                        <?php if ($errors !== []): ?>
                            <div class="auth-error-box">
                                <div class="auth-error-icon">
                                    <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                </div>
                                <ul>
                                    <?php foreach ($errors as $err): ?>
                                        <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="register.php" novalidate>
                            <?= csrf_field() ?>

                            <div class="auth-field">
                                <label for="full_name" class="auth-label">الاسم الكامل <span class="auth-required">*</span></label>
                                <input type="text" class="auth-input" id="full_name" name="full_name"
                                       value="<?= htmlspecialchars($formData['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                                       placeholder="أدخل اسمك الكامل"
                                       required maxlength="120" autocomplete="name">
                            </div>

                            <div class="auth-field">
                                <label for="email" class="auth-label">البريد الإلكتروني <span class="auth-required">*</span></label>
                                <input type="email" class="auth-input" id="email" name="email"
                                       value="<?= htmlspecialchars($formData['email'], ENT_QUOTES, 'UTF-8') ?>"
                                       placeholder="example@email.com"
                                       required maxlength="190" autocomplete="email" dir="ltr">
                            </div>

                            <div class="auth-field">
                                <label for="phone" class="auth-label">رقم الهاتف</label>
                                <input type="tel" class="auth-input" id="phone" name="phone"
                                       value="<?= htmlspecialchars($formData['phone'], ENT_QUOTES, 'UTF-8') ?>"
                                       placeholder="01xxxxxxxxx"
                                       maxlength="30" autocomplete="tel" dir="ltr">
                            </div>

                            <div class="auth-fields-row">
                                <div class="auth-field">
                                    <label for="password" class="auth-label">كلمة المرور <span class="auth-required">*</span></label>
                                    <input type="password" class="auth-input" id="password" name="password"
                                           placeholder="8 أحرف على الأقل"
                                           required minlength="8" autocomplete="new-password">
                                </div>
                                <div class="auth-field">
                                    <label for="password_confirm" class="auth-label">تأكيد كلمة المرور <span class="auth-required">*</span></label>
                                    <input type="password" class="auth-input" id="password_confirm" name="password_confirm"
                                           placeholder="أعد كتابة كلمة المرور"
                                           required minlength="8" autocomplete="new-password">
                                </div>
                            </div>

                            <button type="submit" class="auth-btn auth-btn-primary">
                                إنشاء الحساب
                                <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"/></svg>
                            </button>
                        </form>

                        <p class="auth-footer-text">
                            لديك حساب بالفعل؟ <a href="login.php">سجّل الدخول</a>
                        </p>

                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
