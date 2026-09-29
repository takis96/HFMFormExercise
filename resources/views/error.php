<?php

declare(strict_types=1);

/** @var string $pageTitle */
/** @var string $message */
/** @var string|null $returnUrl */
/** @var string|null $returnLabel */

$escape = static fn (mixed $value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8',
);
$returnUrl = $returnUrl ?? '/register';
$returnLabel = $returnLabel ?? 'Return to registration';
$headerActionUrl = $returnUrl;
$headerActionLabel = $returnLabel === 'Return to login' ? 'Login' : 'Register';
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
            <div class="status-icon error" aria-hidden="true">!</div>
            <h1><?= $escape($pageTitle) ?></h1>
            <p><?= $escape($message) ?></p>
            <a class="primary-link" href="<?= $escape($returnUrl) ?>"><?= $escape($returnLabel) ?></a>
        </section>
    </main>
</body>
</html>
