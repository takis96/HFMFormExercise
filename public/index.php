<?php

declare(strict_types=1);

// Let PHP's development server return static assets without routing them through the app.
if (PHP_SAPI === 'cli-server') {
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $publicFile = __DIR__ . $requestPath;

    if ($requestPath !== '/' && is_file($publicFile)) {
        return false;
    }
}

try {
    $app = require dirname(__DIR__) . '/bootstrap/app.php';
    // From this point the router decides which controller should handle the URL.
    $app['router']->dispatch(
        strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
        $_SERVER['REQUEST_URI'] ?? '/',
    );
} catch (Throwable $exception) {
    // Log the full exception, but only expose details in the local environment.
    error_log((string) $exception);
    http_response_code(500);

    $environment = getenv('APP_ENV') ?: 'local';
    echo $environment === 'local'
        ? '<pre>' . htmlspecialchars((string) $exception, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</pre>'
        : 'Something went wrong.';
}

