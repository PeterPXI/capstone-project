<?php

declare(strict_types=1);

require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/auth.php';

auth_start_session();

// Only accept POST to prevent CSRF via a simple GET link.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

validate_csrf_token();

auth_logout();

header('Location: ../index.php');
exit;
