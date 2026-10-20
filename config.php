<?php

declare(strict_types=1);

/**
 * Local database configuration. Keep credentials outside version control in
 * environment variables when deploying the project.
 */
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbName = getenv('DB_NAME') ?: 'dealexit';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPassword = getenv('DB_PASSWORD') ?: '';
$dbCharset = 'utf8mb4';

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}",
        $dbUser,
        $dbPassword,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $exception) {
    // Never expose database details to site visitors.
    http_response_code(500);
    error_log('DealExit database connection failed: ' . $exception->getMessage());
    exit('Unable to connect to the database. Please try again later.');
}
