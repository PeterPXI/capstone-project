<?php

declare(strict_types=1);

require __DIR__ . '/../config.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/auth.php';

auth_start_session();
require_login();

$user = current_user($pdo);
if (!$user) {
    header('Location: login.php');
    exit;
}

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token();

    // Ensure the user can only update their own profile.
    $result = auth_update_profile(
        $pdo,
        (int) $user['id'],
        $_POST['full_name']            ?? '',
        $_POST['email']                ?? '',
        $_POST['phone']                ?? '',
        $_POST['current_password']     ?? '',
        $_POST['new_password']         ?? '',
        $_POST['new_password_confirm'] ?? '',
        $_FILES['avatar']              ?? null
    );

    if ($result['success']) {
        $success = true;
        // Refresh user data after update — reset static cache by calling directly.
        $stmt = $pdo->prepare(
            'SELECT id, full_name, email, phone, avatar, account_type, account_status, created_at, updated_at
             FROM users WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        if (!$user) {
            header('Location: login.php');
            exit;
        }
    } else {
        $errors = $result['errors'];
    }
}

$avatarUrl = get_avatar_url('../', $user['avatar'] ?? null);
$memberSince = date('Y/m/d', strtotime($user['created_at']));
$accountLabel = $user['account_type'] === 'admin' ? 'مدير' : 'مستخدم';

$pageTitle   = 'DealExit | الملف الشخصي';
$assetPrefix = '../';
require __DIR__ . '/../includes/header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="auth-card profile-card">

                    <!-- Profile Header -->
                    <div class="profile-header">
                        <div class="profile-avatar-wrapper">
                            <img src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8') ?>"
                                 alt="الصورة الشخصية" class="profile-avatar" id="avatarPreview">
                            <label for="avatar" class="profile-avatar-edit" title="تغيير الصورة">
                                <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 0011.172 3H8.828a2 2 0 00-1.414.586L6.293 4.707A1 1 0 015.586 5H4zm6 9a3 3 0 100-6 3 3 0 000 6z"/></svg>
                            </label>
                        </div>
                        <div class="profile-info">
                            <h1 class="profile-name"><?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?></h1>
                            <p class="profile-email"><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></p>
                            <div class="profile-badges">
                                <span class="profile-badge profile-badge-type">
                                    <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                                    <?= htmlspecialchars($accountLabel, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <span class="profile-badge profile-badge-date">
                                    <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                                    عضو منذ <?= htmlspecialchars($memberSince, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <?php if ($success): ?>
                        <div class="auth-success-box auth-success-inline">
                            <svg viewBox="0 0 20 20" fill="currentColor" width="20" height="20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span>تم تحديث بياناتك بنجاح.</span>
                        </div>
                    <?php endif; ?>

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

                    <form method="post" action="profile.php" enctype="multipart/form-data" novalidate>
                        <?= csrf_field() ?>

                        <!-- Hidden file input for avatar -->
                        <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif"
                               style="display:none" onchange="previewAvatar(this)">

                        <div class="profile-form-section">
                            <h2 class="profile-section-title">
                                <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                                المعلومات الأساسية
                            </h2>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="auth-field">
                                        <label for="full_name" class="auth-label">الاسم الكامل <span class="auth-required">*</span></label>
                                        <input type="text" class="auth-input" id="full_name" name="full_name"
                                               value="<?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                                               required maxlength="120" autocomplete="name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="auth-field">
                                        <label for="email" class="auth-label">البريد الإلكتروني <span class="auth-required">*</span></label>
                                        <input type="email" class="auth-input" id="email" name="email"
                                               value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>"
                                               required maxlength="190" autocomplete="email" dir="ltr">
                                    </div>
                                </div>
                            </div>

                            <div class="auth-field">
                                <label for="phone" class="auth-label">رقم الهاتف</label>
                                <input type="tel" class="auth-input" id="phone" name="phone"
                                       value="<?= htmlspecialchars($user['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                       maxlength="30" autocomplete="tel" dir="ltr"
                                       placeholder="01xxxxxxxxx">
                            </div>
                        </div>

                        <div class="profile-form-section">
                            <h2 class="profile-section-title">
                                <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                تغيير كلمة المرور
                            </h2>
                            <p class="profile-section-hint">اترك الحقول فارغة إذا لم ترد تغيير كلمة المرور</p>

                            <div class="auth-field">
                                <label for="current_password" class="auth-label">كلمة المرور الحالية</label>
                                <input type="password" class="auth-input" id="current_password" name="current_password"
                                       autocomplete="current-password" placeholder="أدخل كلمة المرور الحالية">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="auth-field">
                                        <label for="new_password" class="auth-label">كلمة المرور الجديدة</label>
                                        <input type="password" class="auth-input" id="new_password" name="new_password"
                                               minlength="8" autocomplete="new-password"
                                               placeholder="8 أحرف على الأقل">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="auth-field">
                                        <label for="new_password_confirm" class="auth-label">تأكيد كلمة المرور</label>
                                        <input type="password" class="auth-input" id="new_password_confirm" name="new_password_confirm"
                                               minlength="8" autocomplete="new-password"
                                               placeholder="أعد كتابة كلمة المرور الجديدة">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="auth-btn auth-btn-primary">
                            <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                            حفظ التغييرات
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        var file = input.files[0];
        if (file.size > 2 * 1024 * 1024) {
            alert('حجم الصورة يجب ألا يتجاوز 2 ميجابايت');
            input.value = '';
            return;
        }
        var reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('avatarPreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
