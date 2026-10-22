<?php

declare(strict_types=1);

/**
 * CSRF token helper — generates, embeds and validates per-session tokens.
 *
 * Usage:
 *   - In forms:   <?= csrf_field() ?>
 *   - On submit:  validate_csrf_token()   (exits with 403 on mismatch)
 */

/**
 * Return the current CSRF token, creating one if it does not exist yet.
 */
function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Return a hidden <input> element containing the CSRF token.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Validate the submitted CSRF token against the session token.
 * Halts execution with HTTP 403 when the token is missing or incorrect.
 */
function validate_csrf_token(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $submitted = $_POST['csrf_token'] ?? '';

    if (
        !is_string($submitted)
        || $submitted === ''
        || !isset($_SESSION['csrf_token'])
        || !hash_equals($_SESSION['csrf_token'], $submitted)
    ) {
        http_response_code(403);
        exit('طلب غير مصرّح به. يرجى المحاولة مرة أخرى.');
    }
}
