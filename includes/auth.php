<?php

declare(strict_types=1);

/**
 * Authentication helpers for DealExit.
 *
 * Provides functions for session management, login/logout,
 * registration, profile updates, and access control.
 *
 * Relies on the shared $pdo connection from config.php.
 */

/* ------------------------------------------------------------------ */
/*  Session bootstrap                                                 */
/* ------------------------------------------------------------------ */

/**
 * Start the session with secure cookie settings suitable for XAMPP / dev.
 * Call this once at the very top of every page that needs auth awareness.
 */
function auth_start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    session_set_cookie_params([
        'lifetime' => 0,            // session cookie — expires when browser closes
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps,
        'httponly'  => true,
        'samesite' => 'Strict',
    ]);

    session_start();
}

/* ------------------------------------------------------------------ */
/*  Session / auth state helpers                                      */
/* ------------------------------------------------------------------ */

/**
 * Is the current visitor logged in?
 */
function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Return the currently authenticated user's row from the DB, or null.
 * The result is cached per-request so multiple calls don't re-query.
 *
 * @param PDO $pdo
 * @return array<string,mixed>|null
 */
function current_user(PDO $pdo): ?array
{
    static $cache = null;

    if (!is_logged_in()) {
        return null;
    }

    if ($cache !== null && ($cache['id'] ?? null) === $_SESSION['user_id']) {
        return $cache;
    }

    $stmt = $pdo->prepare(
        'SELECT id, full_name, email, phone, account_type, account_status, created_at, updated_at
         FROM users WHERE id = ? LIMIT 1'
    );
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || $user['account_status'] !== 'active') {
        // Account no longer valid — force log-out.
        auth_logout();
        return null;
    }

    $cache = $user;
    return $cache;
}

/* ------------------------------------------------------------------ */
/*  Guard helpers                                                     */
/* ------------------------------------------------------------------ */

/**
 * Redirect to the login page if the visitor is not authenticated.
 * Call at the top of any protected page.
 */
function require_login(string $loginUrl = '../authentication/login.php'): void
{
    if (!is_logged_in()) {
        header('Location: ' . $loginUrl);
        exit;
    }
}

/**
 * Redirect to the login page if the visitor is not an admin.
 */
function require_admin(PDO $pdo, string $loginUrl = '../authentication/login.php'): void
{
    $user = current_user($pdo);
    if (!$user || $user['account_type'] !== 'admin') {
        header('Location: ' . $loginUrl);
        exit;
    }
}

/* ------------------------------------------------------------------ */
/*  Registration                                                      */
/* ------------------------------------------------------------------ */

/**
 * Register a new user account.
 *
 * @param PDO    $pdo
 * @param string $fullName
 * @param string $email
 * @param string $phone
 * @param string $password
 * @param string $passwordConfirm
 *
 * @return array{success: bool, errors: string[]}
 */
function auth_register(
    PDO    $pdo,
    string $fullName,
    string $email,
    string $phone,
    string $password,
    string $passwordConfirm
): array {
    $errors = [];

    // --- Validate full_name ---
    $fullName = trim($fullName);
    if ($fullName === '') {
        $errors[] = 'الاسم الكامل مطلوب.';
    } elseif (mb_strlen($fullName, 'UTF-8') > 120) {
        $errors[] = 'الاسم الكامل يجب ألا يتجاوز 120 حرفًا.';
    }

    // --- Validate email ---
    $email = trim($email);
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'يرجى إدخال بريد إلكتروني صالح.';
    } elseif (mb_strlen($email, 'UTF-8') > 190) {
        $errors[] = 'البريد الإلكتروني طويل جدًا.';
    }

    // --- Validate phone ---
    $phone = trim($phone);
    if ($phone !== '' && !preg_match('/^[0-9\+\-\s]{7,30}$/', $phone)) {
        $errors[] = 'رقم الهاتف غير صالح.';
    }

    // --- Validate password ---
    if (mb_strlen($password, 'UTF-8') < 8) {
        $errors[] = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.';
    }
    if ($password !== $passwordConfirm) {
        $errors[] = 'كلمة المرور وتأكيدها غير متطابقتين.';
    }

    if ($errors !== []) {
        return ['success' => false, 'errors' => $errors];
    }

    // --- Check duplicate email ---
    $stmt = $pdo->prepare('SELECT 1 FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = 'هذا البريد الإلكتروني مسجّل بالفعل.';
        return ['success' => false, 'errors' => $errors];
    }

    // --- Insert ---
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare(
        'INSERT INTO users (full_name, email, phone, password_hash, account_type, account_status)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$fullName, $email, $phone ?: null, $hash, 'user', 'active']);

    return ['success' => true, 'errors' => []];
}

/* ------------------------------------------------------------------ */
/*  Login / Logout                                                    */
/* ------------------------------------------------------------------ */

/**
 * Attempt to log a user in.
 *
 * @return array{success: bool, error: string}
 */
function auth_login(PDO $pdo, string $email, string $password): array
{
    $email = trim($email);
    $genericError = 'البريد الإلكتروني أو كلمة المرور غير صحيحة.';

    if ($email === '' || $password === '') {
        return ['success' => false, 'error' => $genericError];
    }

    $stmt = $pdo->prepare(
        'SELECT id, full_name, email, password_hash, account_type, account_status
         FROM users WHERE email = ? LIMIT 1'
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'error' => $genericError];
    }

    if ($user['account_status'] !== 'active') {
        return ['success' => false, 'error' => 'هذا الحساب موقوف. تواصل مع الإدارة للمزيد من المعلومات.'];
    }

    // Regenerate session ID to prevent session fixation.
    session_regenerate_id(true);

    $_SESSION['user_id']      = $user['id'];
    $_SESSION['user_name']    = $user['full_name'];
    $_SESSION['account_type'] = $user['account_type'];

    return ['success' => true, 'error' => ''];
}

/**
 * Log the current user out and destroy the session.
 */
function auth_logout(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            [
                'expires'  => time() - 42000,
                'path'     => $p['path'],
                'domain'   => $p['domain'],
                'secure'   => $p['secure'],
                'httponly'  => $p['httponly'],
                'samesite' => $p['samesite'] ?? 'Strict',
            ]
        );
    }

    session_destroy();
}

/* ------------------------------------------------------------------ */
/*  Profile update                                                    */
/* ------------------------------------------------------------------ */

/**
 * Update the authenticated user's profile (name, email, phone, password).
 * Does NOT allow changing account_type or account_status.
 *
 * @return array{success: bool, errors: string[]}
 */
function auth_update_profile(
    PDO    $pdo,
    int    $userId,
    string $fullName,
    string $email,
    string $phone,
    string $currentPassword,
    string $newPassword,
    string $newPasswordConfirm
): array {
    $errors = [];

    // --- Validate full_name ---
    $fullName = trim($fullName);
    if ($fullName === '') {
        $errors[] = 'الاسم الكامل مطلوب.';
    } elseif (mb_strlen($fullName, 'UTF-8') > 120) {
        $errors[] = 'الاسم الكامل يجب ألا يتجاوز 120 حرفًا.';
    }

    // --- Validate email ---
    $email = trim($email);
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'يرجى إدخال بريد إلكتروني صالح.';
    } elseif (mb_strlen($email, 'UTF-8') > 190) {
        $errors[] = 'البريد الإلكتروني طويل جدًا.';
    }

    // --- Validate phone ---
    $phone = trim($phone);
    if ($phone !== '' && !preg_match('/^[0-9\+\-\s]{7,30}$/', $phone)) {
        $errors[] = 'رقم الهاتف غير صالح.';
    }

    // --- Check email uniqueness (excluding current user) ---
    if ($errors === []) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
        $stmt->execute([$email, $userId]);
        if ($stmt->fetch()) {
            $errors[] = 'هذا البريد الإلكتروني مستخدم من حساب آخر.';
        }
    }

    // --- Password change (optional) ---
    $changePassword = ($newPassword !== '');
    if ($changePassword) {
        if (mb_strlen($newPassword, 'UTF-8') < 8) {
            $errors[] = 'كلمة المرور الجديدة يجب أن تكون 8 أحرف على الأقل.';
        }
        if ($newPassword !== $newPasswordConfirm) {
            $errors[] = 'كلمة المرور الجديدة وتأكيدها غير متطابقتين.';
        }

        // Verify current password before allowing a change.
        $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        if (!$row || !password_verify($currentPassword, $row['password_hash'])) {
            $errors[] = 'كلمة المرور الحالية غير صحيحة.';
        }
    }

    if ($errors !== []) {
        return ['success' => false, 'errors' => $errors];
    }

    // --- Build update query ---
    if ($changePassword) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            'UPDATE users SET full_name = ?, email = ?, phone = ?, password_hash = ? WHERE id = ?'
        );
        $stmt->execute([$fullName, $email, $phone ?: null, $hash, $userId]);
    } else {
        $stmt = $pdo->prepare(
            'UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?'
        );
        $stmt->execute([$fullName, $email, $phone ?: null, $userId]);
    }

    // Keep session name in sync.
    $_SESSION['user_name'] = $fullName;

    return ['success' => true, 'errors' => []];
}
