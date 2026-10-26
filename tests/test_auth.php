<?php
/**
 * DealExit Authentication — automated test script.
 *
 * Run from CLI:  php tests/test_auth.php
 *
 * Tests registration, login, session, profile update, and security guards.
 */

declare(strict_types=1);

// ─── Bootstrap ────────
$projectRoot = dirname(__DIR__);
require $projectRoot . '/config.php';
require $projectRoot . '/includes/auth.php';
require $projectRoot . '/includes/csrf.php';

$passed = 0;
$failed = 0;
$testEmail = 'test_auth_' . bin2hex(random_bytes(4)) . '@dealexit.test';

function assert_true(bool $condition, string $label): void
{
    global $passed, $failed;
    if ($condition) {
        echo "  ✓ PASS: {$label}\n";
        $passed++;
    } else {
        echo "  ✗ FAIL: {$label}\n";
        $failed++;
    }
}

echo "\n══════════════════════════════════════════════\n";
echo "  DealExit Auth Test Suite\n";
echo "══════════════════════════════════════════════\n\n";

// ─── 1. Registration ─────────────────────────────────────────────────
echo "▸ Registration\n";

// 1a. Valid registration
$result = auth_register($pdo, 'مستخدم تجريبي', $testEmail, '01012345678', 'SecurePass1!', 'SecurePass1!');
assert_true($result['success'], 'Valid registration succeeds');

// 1b. Duplicate email
$result = auth_register($pdo, 'آخر', $testEmail, '', 'SecurePass1!', 'SecurePass1!');
assert_true(!$result['success'], 'Duplicate email rejected');
assert_true(str_contains($result['errors'][0], 'مسجّل'), 'Duplicate email error message correct');

// 1c. Empty name
$result = auth_register($pdo, '', 'x@x.com', '', 'SecurePass1!', 'SecurePass1!');
assert_true(!$result['success'], 'Empty name rejected');

// 1d. Invalid email
$result = auth_register($pdo, 'Test', 'not-an-email', '', 'SecurePass1!', 'SecurePass1!');
assert_true(!$result['success'], 'Invalid email rejected');

// 1e. Short password
$result = auth_register($pdo, 'Test', 'short@test.com', '', '123', '123');
assert_true(!$result['success'], 'Short password rejected');

// 1f. Password mismatch
$result = auth_register($pdo, 'Test', 'mismatch@test.com', '', 'SecurePass1!', 'DifferentPass');
assert_true(!$result['success'], 'Password mismatch rejected');

// 1g. Invalid phone
$result = auth_register($pdo, 'Test', 'phone@test.com', 'abc!@#', 'SecurePass1!', 'SecurePass1!');
assert_true(!$result['success'], 'Invalid phone rejected');

// 1h. XSS in name (should store safely, not execute)
$xssName = '<script>alert("xss")</script>';
$xssEmail = 'xss_test_' . bin2hex(random_bytes(3)) . '@dealexit.test';
$result = auth_register($pdo, $xssName, $xssEmail, '', 'SecurePass1!', 'SecurePass1!');
assert_true($result['success'], 'Registration with HTML in name succeeds (stored safely)');

// Verify it's stored as-is (not escaped in DB — escaping happens on output)
$stmt = $pdo->prepare('SELECT full_name FROM users WHERE email = ?');
$stmt->execute([$xssEmail]);
$row = $stmt->fetch();
assert_true($row && $row['full_name'] === $xssName, 'HTML in name stored as raw text in DB');

echo "\n▸ Login\n";

// ─── 2. Login ─────────────────────────────────────────────────────────
auth_start_session();

// 2a. Correct credentials
$result = auth_login($pdo, $testEmail, 'SecurePass1!');
assert_true($result['success'], 'Login with correct credentials succeeds');
assert_true(is_logged_in(), 'Session is active after login');
$userId = $_SESSION['user_id'];

// 2b. Wrong password
auth_logout();
auth_start_session();
$result = auth_login($pdo, $testEmail, 'WrongPassword');
assert_true(!$result['success'], 'Login with wrong password fails');
// Must not reveal whether email exists
assert_true(
    str_contains($result['error'], 'البريد الإلكتروني أو كلمة المرور'),
    'Generic error message (no email disclosure)'
);

// 2c. Non-existent email
$result = auth_login($pdo, 'does-not-exist@test.com', 'AnyPassword');
assert_true(!$result['success'], 'Login with non-existent email fails');
assert_true(
    str_contains($result['error'], 'البريد الإلكتروني أو كلمة المرور'),
    'Same generic error for non-existent email'
);

// 2d. Suspended account
$pdo->prepare("UPDATE users SET account_status = 'suspended' WHERE email = ?")->execute([$testEmail]);
$result = auth_login($pdo, $testEmail, 'SecurePass1!');
assert_true(!$result['success'], 'Login with suspended account fails');
assert_true(str_contains($result['error'], 'موقوف'), 'Suspended account error message');
// Restore active
$pdo->prepare("UPDATE users SET account_status = 'active' WHERE email = ?")->execute([$testEmail]);

echo "\n▸ Session & Logout\n";

// ─── 3. Session & Logout ─────────────────────────────────────────────
auth_start_session();
auth_login($pdo, $testEmail, 'SecurePass1!');
assert_true(is_logged_in(), 'Logged in before logout');

auth_logout();
auth_start_session();
assert_true(!is_logged_in(), 'Session destroyed after logout');

echo "\n▸ Profile Update\n";

// ─── 4. Profile update ───────────────────────────────────────────────
auth_start_session();
auth_login($pdo, $testEmail, 'SecurePass1!');
$uid = (int) $_SESSION['user_id'];

// 4a. Update name only
$result = auth_update_profile($pdo, $uid, 'اسم جديد', $testEmail, '01098765432', '', '', '');
assert_true($result['success'], 'Update name and phone succeeds');

// Verify in DB
$stmt = $pdo->prepare('SELECT full_name, phone FROM users WHERE id = ?');
$stmt->execute([$uid]);
$row = $stmt->fetch();
assert_true($row['full_name'] === 'اسم جديد', 'Name updated in DB');
assert_true($row['phone'] === '01098765432', 'Phone updated in DB');

// 4b. Change password
$result = auth_update_profile($pdo, $uid, 'اسم جديد', $testEmail, '', 'SecurePass1!', 'NewPass123!', 'NewPass123!');
assert_true($result['success'], 'Password change succeeds');

// Verify new password works
auth_logout();
auth_start_session();
$result = auth_login($pdo, $testEmail, 'NewPass123!');
assert_true($result['success'], 'Login with new password works');

// 4c. Wrong current password
$result = auth_update_profile($pdo, $uid, 'X', $testEmail, '', 'WrongCurrent', 'AnotherPass!', 'AnotherPass!');
assert_true(!$result['success'], 'Wrong current password rejected');

// 4d. Duplicate email
$otherEmail = 'other_' . bin2hex(random_bytes(3)) . '@dealexit.test';
auth_register($pdo, 'Other User', $otherEmail, '', 'TestPass123', 'TestPass123');
$result = auth_update_profile($pdo, $uid, 'X', $otherEmail, '', '', '', '');
assert_true(!$result['success'], 'Duplicate email on update rejected');

// 4e. SQL Injection attempt
$sqli = "'; DROP TABLE users; --";
$result = auth_update_profile($pdo, $uid, $sqli, $testEmail, '', '', '', '');
assert_true($result['success'], 'SQL injection in name handled safely');
// Confirm table still exists
$stmt = $pdo->query('SELECT COUNT(*) FROM users');
assert_true($stmt !== false, 'users table still exists after SQLi attempt');

echo "\n▸ Access Control\n";

// ─── 5. Access Control ───────────────────────────────────────────────
auth_logout();
auth_start_session();
assert_true(!is_logged_in(), 'Not logged in');

// current_user should return null
$cu = current_user($pdo);
assert_true($cu === null, 'current_user() returns null when logged out');

// After login, current_user should return data
auth_login($pdo, $testEmail, 'NewPass123!');
$cu = current_user($pdo);
assert_true($cu !== null && $cu['email'] === $testEmail, 'current_user() returns correct user');

// account_type should not be changeable through profile update
assert_true($cu['account_type'] === 'user', 'User has type "user" (not admin)');

echo "\n▸ CSRF\n";

// ─── 6. CSRF ──────────────────────────────────────────────────────────
$token = csrf_token();
assert_true(strlen($token) === 64, 'CSRF token is 64 hex chars');
$token2 = csrf_token();
assert_true($token === $token2, 'CSRF token is stable within session');

$field = csrf_field();
assert_true(str_contains($field, 'csrf_token'), 'csrf_field() contains input name');
assert_true(str_contains($field, $token), 'csrf_field() contains token value');

echo "\n══════════════════════════════════════════════\n";

// ─── Cleanup ──────────────────────────────────────────────────────────
$pdo->prepare("DELETE FROM users WHERE email LIKE '%@dealexit.test'")->execute();

echo "  Results: {$passed} passed, {$failed} failed\n";
echo "══════════════════════════════════════════════\n\n";

exit($failed > 0 ? 1 : 0);
