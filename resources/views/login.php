<?php

declare(strict_types=1);

/** @var string $pageTitle */
/** @var string $csrfToken */
/** @var array<string, string> $errors */
/** @var array<string, string> $old */

// Values can be redisplayed after validation, so escape them before they enter HTML.
$escape = static fn (mixed $value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8',
);
$error = static fn (string $field): string => $errors[$field] ?? '';
// Add accessibility attributes only to fields that failed server validation.
$invalid = static fn (string $field): string => isset($errors[$field])
    ? 'aria-invalid="true" aria-describedby="' . $field . '_error"'
    : '';
$headerActionUrl = '/register';
$headerActionLabel = 'Register';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Log in to your HFM exercise account.">
    <title><?= $escape($pageTitle) ?> | HFM</title>
    <link rel="stylesheet" href="/assets/css/auth.css">
    <script src="/assets/js/login.js" defer></script>
</head>
<body>
    <?php require __DIR__ . '/partials/header.php'; ?>

    <main class="auth-hero">
        <h1 class="page-title">Login</h1>

        <section class="form-card login-card" aria-labelledby="login-heading">
            <h2 class="form-card-title" id="login-heading">Welcome back</h2>

            <section
                class="error-summary"
                id="form-error-summary"
                role="alert"
                tabindex="-1"
                <?= $errors === [] ? 'hidden' : '' ?>
            >
                <h3>Please check the following details:</h3>
                <ul data-error-list>
                    <?php // A generic credential error links to email because neither field is singled out. ?>
                    <?php foreach ($errors as $field => $message): ?>
                        <li>
                            <a href="#<?= $field === 'credentials' ? 'email' : $escape($field) ?>">
                                <?= $escape($message) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>

            <form id="login-form" method="post" action="/login" novalidate>
                <input type="hidden" name="_token" value="<?= $escape($csrfToken) ?>">

                <div class="login-grid">
                    <div class="field">
                        <label class="sr-only" for="email">Email address</label>
                        <input
                            class="control"
                            id="email"
                            name="email"
                            type="email"
                            value="<?= $escape($old['email'] ?? '') ?>"
                            placeholder="Email address"
                            autocomplete="email"
                            maxlength="254"
                            required
                            <?= $invalid('email') ?>
                        >
                        <p class="field-error" id="email_error" <?= $error('email') === '' ? 'hidden' : '' ?>>
                            <?= $escape($error('email')) ?>
                        </p>
                    </div>

                    <div class="field">
                        <label class="sr-only" for="password">Password</label>
                        <div class="password-control">
                            <input
                                class="control"
                                id="password"
                                name="password"
                                type="password"
                                placeholder="Password"
                                autocomplete="current-password"
                                required
                                <?= $invalid('password') ?>
                            >
                            <button
                                class="password-toggle"
                                id="password-toggle"
                                type="button"
                                aria-controls="password"
                                aria-pressed="false"
                            >Show</button>
                        </div>
                        <p class="field-error" id="password_error" <?= $error('password') === '' ? 'hidden' : '' ?>>
                            <?= $escape($error('password')) ?>
                        </p>
                    </div>

                    <p
                        class="form-level-error"
                        id="credentials_error"
                        <?= $error('credentials') === '' ? 'hidden' : '' ?>
                    ><?= $escape($error('credentials')) ?></p>

                    <button class="submit-button login-button" type="submit">Login</button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
