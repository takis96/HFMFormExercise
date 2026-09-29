<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class UserRepository
{
    // Database access stays here, away from request handling and HTML rendering.
    public function __construct(private readonly PDO $database)
    {
    }

    public function emailExists(string $email): bool
    {
        $statement = $this->database->prepare(
            'SELECT EXISTS(SELECT 1 FROM users WHERE email = :email)'
        );
        $statement->execute(['email' => $email]);

        return (bool) $statement->fetchColumn();
    }

    /**
     * @return array{id: int, first_name: string, email: string, password_hash: string}|null
     */
    public function findByEmail(string $email): ?array
    {
        $statement = $this->database->prepare(
            'SELECT id, first_name, email, password_hash
             FROM users
             WHERE email = :email
             LIMIT 1'
        );
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return is_array($user) ? $user : null;
    }

    // Refresh an old hash during login without asking the user to reset their password.
    public function updatePasswordHash(int $userId, string $passwordHash): void
    {
        $statement = $this->database->prepare(
            'UPDATE users
             SET password_hash = :password_hash, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $userId,
            'password_hash' => $passwordHash,
        ]);
    }

    /** @param array<string, string> $data */
    public function create(array $data): int
    {
        // Placeholders keep submitted values separate from the SQL itself.
        $statement = $this->database->prepare(
            'INSERT INTO users (
                first_name, last_name, country, country_code, phone, email, password_hash
            ) VALUES (
                :first_name, :last_name, :country, :country_code, :phone, :email, :password_hash
            )'
        );

        $statement->execute([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'country' => $data['country'],
            'country_code' => $data['country_code'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'password_hash' => $data['password_hash'],
        ]);

        return (int) $this->database->lastInsertId();
    }
}
