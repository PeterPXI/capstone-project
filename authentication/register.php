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

<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card border-0 shadow-sm" style="border-radius:18px;">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h4 fw-bold mb-1 text-center">إنشاء حساب جديد</h1>
                    <p class="text-secondary text-center mb-4" style="font-size:.9rem;">انضم إلى DealExit واكتشف فرص المشاريع</p>

                    <?php if ($success): ?>
                        <div class="alert alert-success text-center">
                            تم إنشاء حسابك بنجاح!
                            <a href="login.php" class="alert-link d-block mt-1">سجّل الدخول الآن ←</a>
                        </div>
                    <?php else: ?>

                        <?php if ($errors !== []): ?>
                            <div class="alert alert-danger py-2">
                                <ul class="mb-0 pe-3" style="font-size:.88rem;">
                                    <?php foreach ($errors as $err): ?>
                                        <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="register.php" novalidate>
                            <?= csrf_field() ?>

                            <div class="mb-3">
                                <label for="full_name" class="form-label fw-semibold">الاسم الكامل <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="full_name" name="full_name"
                                       value="<?= htmlspecialchars($formData['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                                       required maxlength="120" autocomplete="name">
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">البريد الإلكتروني <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?= htmlspecialchars($formData['email'], ENT_QUOTES, 'UTF-8') ?>"
                                       required maxlength="190" autocomplete="email" dir="ltr">
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label fw-semibold">رقم الهاتف</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                       value="<?= htmlspecialchars($formData['phone'], ENT_QUOTES, 'UTF-8') ?>"
                                       maxlength="30" autocomplete="tel" dir="ltr">
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">كلمة المرور <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password"
                                       required minlength="8" autocomplete="new-password">
                                <div class="form-text">8 أحرف على الأقل</div>
                            </div>

                            <div class="mb-4">
                                <label for="password_confirm" class="form-label fw-semibold">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm"
                                       required minlength="8" autocomplete="new-password">
                            </div>

                            <button type="submit" class="btn w-100 text-white fw-bold py-2"
                                    style="background:var(--de-blue);border-radius:11px;">إنشاء الحساب</button>
                        </form>

                        <p class="text-center mt-3 mb-0" style="font-size:.88rem;">
                            لديك حساب بالفعل؟ <a href="login.php" class="fw-bold" style="color:var(--de-blue);">سجّل الدخول</a>
                        </p>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
