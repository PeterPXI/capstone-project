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
        $_POST['full_name']        ?? '',
        $_POST['email']            ?? '',
        $_POST['phone']            ?? '',
        $_POST['current_password'] ?? '',
        $_POST['new_password']     ?? '',
        $_POST['new_password_confirm'] ?? ''
    );

    if ($result['success']) {
        $success = true;
        // Refresh user data after update.
        $user = null;      // clear static cache
        $user = current_user($pdo);
        if (!$user) {
            header('Location: login.php');
            exit;
        }
    } else {
        $errors = $result['errors'];
    }
}

$pageTitle   = 'DealExit | الملف الشخصي';
$assetPrefix = '../';
require __DIR__ . '/../includes/header.php';
?>

<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm" style="border-radius:18px;">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h4 fw-bold mb-1 text-center">الملف الشخصي</h1>
                    <p class="text-secondary text-center mb-4" style="font-size:.9rem;">تعديل بياناتك الأساسية</p>

                    <?php if ($success): ?>
                        <div class="alert alert-success py-2 text-center" style="font-size:.88rem;">
                            تم تحديث بياناتك بنجاح.
                        </div>
                    <?php endif; ?>

                    <?php if ($errors !== []): ?>
                        <div class="alert alert-danger py-2">
                            <ul class="mb-0 pe-3" style="font-size:.88rem;">
                                <?php foreach ($errors as $err): ?>
                                    <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="profile.php" novalidate>
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="full_name" class="form-label fw-semibold">الاسم الكامل <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="full_name" name="full_name"
                                   value="<?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                                   required maxlength="120" autocomplete="name">
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">البريد الإلكتروني <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>"
                                   required maxlength="190" autocomplete="email" dir="ltr">
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label fw-semibold">رقم الهاتف</label>
                            <input type="tel" class="form-control" id="phone" name="phone"
                                   value="<?= htmlspecialchars($user['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                   maxlength="30" autocomplete="tel" dir="ltr">
                        </div>

                        <hr class="my-4">
                        <p class="text-secondary mb-3" style="font-size:.85rem;">اترك حقول كلمة المرور فارغة إذا لم ترد تغييرها.</p>

                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-semibold">كلمة المرور الحالية</label>
                            <input type="password" class="form-control" id="current_password" name="current_password"
                                   autocomplete="current-password">
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label fw-semibold">كلمة المرور الجديدة</label>
                            <input type="password" class="form-control" id="new_password" name="new_password"
                                   minlength="8" autocomplete="new-password">
                            <div class="form-text">8 أحرف على الأقل</div>
                        </div>

                        <div class="mb-4">
                            <label for="new_password_confirm" class="form-label fw-semibold">تأكيد كلمة المرور الجديدة</label>
                            <input type="password" class="form-control" id="new_password_confirm" name="new_password_confirm"
                                   minlength="8" autocomplete="new-password">
                        </div>

                        <button type="submit" class="btn w-100 text-white fw-bold py-2"
                                style="background:var(--de-blue);border-radius:11px;">حفظ التغييرات</button>
                    </form>

                    <div class="text-center mt-3">
                        <small class="text-secondary">
                            نوع الحساب: <strong><?= htmlspecialchars($user['account_type'], ENT_QUOTES, 'UTF-8') ?></strong>
                            &nbsp;·&nbsp;
                            عضو منذ: <strong><?= htmlspecialchars(date('Y/m/d', strtotime($user['created_at'])), ENT_QUOTES, 'UTF-8') ?></strong>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
