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
        // Redirect to home (or a "redirect_to" param if provided).
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

<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card border-0 shadow-sm" style="border-radius:18px;">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h4 fw-bold mb-1 text-center">تسجيل الدخول</h1>
                    <p class="text-secondary text-center mb-4" style="font-size:.9rem;">مرحبًا بعودتك إلى DealExit</p>

                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger py-2" style="font-size:.88rem;">
                            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="login.php" novalidate>
                        <?= csrf_field() ?>

                        <?php if (isset($_GET['redirect_to']) && is_string($_GET['redirect_to'])): ?>
                            <input type="hidden" name="redirect_to"
                                   value="<?= htmlspecialchars($_GET['redirect_to'], ENT_QUOTES, 'UTF-8') ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">البريد الإلكتروني</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= htmlspecialchars($emailValue, ENT_QUOTES, 'UTF-8') ?>"
                                   required autocomplete="email" dir="ltr">
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">كلمة المرور</label>
                            <input type="password" class="form-control" id="password" name="password"
                                   required autocomplete="current-password">
                        </div>

                        <button type="submit" class="btn w-100 text-white fw-bold py-2"
                                style="background:var(--de-blue);border-radius:11px;">تسجيل الدخول</button>
                    </form>

                    <p class="text-center mt-3 mb-0" style="font-size:.88rem;">
                        ليس لديك حساب؟ <a href="register.php" class="fw-bold" style="color:var(--de-blue);">أنشئ حساب جديد</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
