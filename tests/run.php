<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Repositories\UserRepository;
use App\Validation\LoginValidator;
use App\Validation\RegistrationValidator;

require dirname(__DIR__) . '/vendor/autoload.php';

$passed = 0;
$failed = 0;

// This small runner keeps the exercise testable without adding a test framework.
$test = static function (string $name, callable $callback) use (&$passed, &$failed): void {
    try {
        $callback();
        $passed++;
        echo "PASS  {$name}\n";
    } catch (Throwable $exception) {
        $failed++;
        echo "FAIL  {$name}: {$exception->getMessage()}\n";
    }
};

$assert = static function (bool $condition, string $message = 'Assertion failed'): void {
    // Throwing here lets the runner report the failed test and continue with the next one.
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$validInput = [
    'first_name' => 'Maria',
    'last_name' => 'Antoniou',
    'country' => 'cyprus',
    'country_code' => '357',
    'phone' => '99123456',
    'email' => 'maria@example.com',
    'password' => 'Secure1!',
    'terms' => '1',
];

$validator = new RegistrationValidator();

$test('valid registration data passes', static function () use ($validator, $validInput, $assert): void {
    $result = $validator->validate($validInput);
    $assert($result['errors'] === [], 'Expected no validation errors.');
});

$test('invalid fields receive specific errors', static function () use ($validator, $validInput, $assert): void {
    $input = array_merge($validInput, [
        'first_name' => 'Ana',
        'country_code' => 'abc',
        'email' => 'not-an-email',
        'password' => 'password',
        'terms' => '',
    ]);
    $errors = $validator->validate($input)['errors'];

    foreach (['first_name', 'country_code', 'email', 'password', 'terms'] as $field) {
        $assert(isset($errors[$field]), "Expected an error for {$field}.");
    }
});

$test('country code must match country', static function () use ($validator, $validInput, $assert): void {
    $input = array_merge($validInput, ['country_code' => '30']);
    $errors = $validator->validate($input)['errors'];
    $assert(isset($errors['country_code']), 'Expected a country code mismatch error.');
});


$loginValidator = new LoginValidator();

$test('valid login data passes', static function () use ($loginValidator, $assert): void {
    $result = $loginValidator->validate([
        'email' => 'maria@example.com',
        'password' => 'Secure1!',
    ]);
    $assert($result['errors'] === [], 'Expected no login validation errors.');
});

$test('login reports invalid email and missing password', static function () use ($loginValidator, $assert): void {
    $errors = $loginValidator->validate([
        'email' => 'wrong-format',
        'password' => '',
    ])['errors'];

    $assert(isset($errors['email']), 'Expected an email format error.');
    $assert(isset($errors['password']), 'Expected a password required error.');
});

$test('repository stores a password hash', static function () use ($validInput, $assert): void {
    // In-memory SQLite keeps this test isolated from the developer's local database.
    $database = Connection::make(':memory:');
    $schema = file_get_contents(dirname(__DIR__) . '/database/migrations/001_create_users.sql');
    $database->exec((string) $schema);

    $repository = new UserRepository($database);
    $passwordHash = password_hash($validInput['password'], PASSWORD_DEFAULT);
    $repository->create(array_merge($validInput, ['password_hash' => $passwordHash]));

    $storedHash = $database->query('SELECT password_hash FROM users LIMIT 1')->fetchColumn();
    $assert(is_string($storedHash), 'Expected a stored password hash.');
    $assert($storedHash !== $validInput['password'], 'Password was stored as plain text.');
    $assert(password_verify($validInput['password'], $storedHash), 'Stored hash could not be verified.');
    $assert($repository->emailExists($validInput['email']), 'Stored email was not found.');

    $user = $repository->findByEmail($validInput['email']);
    $assert($user !== null, 'Stored user could not be loaded for login.');
    $assert($user['first_name'] === $validInput['first_name'], 'Loaded the wrong user.');
});

echo "\n{$passed} passed, {$failed} failed\n";
exit($failed === 0 ? 0 : 1);
