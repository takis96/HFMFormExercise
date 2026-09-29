<?php

declare(strict_types=1);

/** @var string $pageTitle */

$escape = static fn (mixed $value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8',
);
$headerActionUrl = '/login';
$headerActionLabel = 'Login';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $escape($pageTitle) ?> | HFM</title>
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>
    <?php require __DIR__ . '/partials/header.php'; ?>

    <main class="auth-hero">
        <section class="status-card">
            <div class="status-icon" aria-hidden="true">✓</div>
            <h1>Thank you for signing up!</h1>
            <p>Your account has been created successfully. You can now continue to the login page.</p>
            <a class="primary-link" href="/login">Continue to login</a>
        </section>
    </main>
</body>
</html>
