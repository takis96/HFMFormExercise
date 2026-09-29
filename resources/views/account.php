<?php

declare(strict_types=1);

/** @var string $pageTitle */
/** @var string $firstName */
/** @var string $csrfToken */

$escape = static fn (mixed $value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8',
);
$headerActionUrl = '/register';
$headerActionLabel = 'Register';
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
            <h1>Welcome, <?= $escape($firstName) ?>!</h1>
            <p>You are securely logged in. This protected page is only available to authenticated users.</p>

            <form class="logout-form" method="post" action="/logout">
                <input type="hidden" name="_token" value="<?= $escape($csrfToken) ?>">
                <button class="logout-button" type="submit">Logout</button>
            </form>
        </section>
    </main>
</body>
</html>
