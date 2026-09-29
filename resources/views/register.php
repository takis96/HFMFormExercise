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
$value = static fn (string $field): string => $escape($old[$field] ?? '');
$error = static fn (string $field): string => $errors[$field] ?? '';
// Add accessibility attributes only to fields that failed server validation.
$invalid = static fn (string $field): string => isset($errors[$field])
    ? 'aria-invalid="true" aria-describedby="' . $field . '_error"'
    : '';
$headerActionUrl = '/login';
$headerActionLabel = 'Login';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Create your HFM exercise account.">
    <title><?= $escape($pageTitle) ?> | HFM</title>
    <link rel="stylesheet" href="/assets/css/auth.css">
    <script src="/assets/js/register.js" defer></script>
</head>
<body>
    <?php require __DIR__ . '/partials/header.php'; ?>

    <main class="auth-hero">
        <h1 class="page-title">Register</h1>

        <section class="form-card" aria-labelledby="registration-heading">
            <h2 class="form-card-title" id="registration-heading">Create your account</h2>

            <section
                class="error-summary"
                id="form-error-summary"
                role="alert"
                tabindex="-1"
                <?= $errors === [] ? 'hidden' : '' ?>
            >
                <h3>Please check the following details:</h3>
                <ul data-error-list>
                    <?php foreach ($errors as $field => $message): ?>
                        <li>
                            <a href="#<?= $escape($field) ?>"><?= $escape($message) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>

            <form id="registration-form" method="post" action="/register" novalidate>
                <input type="hidden" name="_token" value="<?= $escape($csrfToken) ?>">

                <div class="form-grid">
                    <div class="field">
                        <label class="sr-only" for="first_name">First name</label>
                        <input
                            class="control"
                            id="first_name"
                            name="first_name"
                            value="<?= $value('first_name') ?>"
                            placeholder="First name"
                            autocomplete="given-name"
                            minlength="4"
                            maxlength="100"
                            required
                            <?= $invalid('first_name') ?>
                        >
                        <p class="field-error" id="first_name_error" <?= $error('first_name') === '' ? 'hidden' : '' ?>>
                            <?= $escape($error('first_name')) ?>
                        </p>
                    </div>

                    <div class="field">
                        <label class="sr-only" for="last_name">Last name</label>
                        <input
                            class="control"
                            id="last_name"
                            name="last_name"
                            value="<?= $value('last_name') ?>"
                            placeholder="Last name"
                            autocomplete="family-name"
                            minlength="4"
                            maxlength="100"
                            required
                            <?= $invalid('last_name') ?>
                        >
                        <p class="field-error" id="last_name_error" <?= $error('last_name') === '' ? 'hidden' : '' ?>>
                            <?= $escape($error('last_name')) ?>
                        </p>
                    </div>

                    <div class="field">
                        <label class="sr-only" for="country">Country</label>
                        <select
                            class="control"
                            id="country"
                            name="country"
                            required
                            <?= $invalid('country') ?>
                        >
                            <option value="">Select a country</option>
                            <option value="cyprus" <?= $value('country') === 'cyprus' ? 'selected' : '' ?>>Cyprus</option>
                            <option value="greece" <?= $value('country') === 'greece' ? 'selected' : '' ?>>Greece</option>
                            <option value="united_kingdom" <?= $value('country') === 'united_kingdom' ? 'selected' : '' ?>>United Kingdom</option>
                        </select>
                        <p class="field-error" id="country_error" <?= $error('country') === '' ? 'hidden' : '' ?>>
                            <?= $escape($error('country')) ?>
                        </p>
                    </div>

                    <div class="field">
                        <div class="phone-controls">
                            <div class="code-control">
                                <label class="sr-only" for="country_code">Country code</label>
                                <span class="code-prefix" aria-hidden="true">+</span>
                                <input
                                    class="control"
                                    id="country_code"
                                    name="country_code"
                                    value="<?= $value('country_code') ?>"
                                    placeholder="Code"
                                    inputmode="numeric"
                                    pattern="[0-9]+"
                                    maxlength="3"
                                    required
                                    <?= $invalid('country_code') ?>
                                >
                            </div>

                            <div>
                                <label class="sr-only" for="phone">Phone number</label>
                                <input
                                    class="control"
                                    id="phone"
                                    name="phone"
                                    value="<?= $value('phone') ?>"
                                    placeholder="Phone number"
                                    inputmode="numeric"
                                    autocomplete="tel-national"
                                    pattern="[0-9]+"
                                    minlength="6"
                                    maxlength="15"
                                    required
                                    <?= $invalid('phone') ?>
                                >
                            </div>
                        </div>
                        <p class="field-error" id="country_code_error" <?= $error('country_code') === '' ? 'hidden' : '' ?>>
                            <?= $escape($error('country_code')) ?>
                        </p>
                        <p class="field-error" id="phone_error" <?= $error('phone') === '' ? 'hidden' : '' ?>>
                            <?= $escape($error('phone')) ?>
                        </p>
                    </div>

                    <div class="field">
                        <label class="sr-only" for="email">Email address</label>
                        <input
                            class="control"
                            id="email"
                            name="email"
                            type="email"
                            value="<?= $value('email') ?>"
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
                                autocomplete="new-password"
                                minlength="8"
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
                        <p class="field-hint">Use 8+ characters with a capital letter, number and symbol.</p>
                        <p class="field-error" id="password_error" <?= $error('password') === '' ? 'hidden' : '' ?>>
                            <?= $escape($error('password')) ?>
                        </p>
                    </div>

                    <div class="field field-full terms-field">
                        <label class="terms-label" for="terms">
                            <input
                                id="terms"
                                name="terms"
                                type="checkbox"
                                value="1"
                                required
                                <?= $value('terms') === '1' ? 'checked' : '' ?>
                                <?= $invalid('terms') ?>
                            >
                            <span>
                                I have read and accepted the
                                <a href="#privacy-policy">Privacy Policy</a>
                                and
                                <a href="#terms-and-conditions">Terms and Conditions</a>.
                            </span>
                        </label>
                        <p class="field-error" id="terms_error" <?= $error('terms') === '' ? 'hidden' : '' ?>>
                            <?= $escape($error('terms')) ?>
                        </p>
                    </div>

                    <button class="submit-button" type="submit">Join now</button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
