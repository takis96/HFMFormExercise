<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use RuntimeException;

final class Connection
{
    public static function make(string $databasePath): PDO
    {
        $directory = dirname($databasePath);

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException("Could not create database directory: {$directory}");
        }

        $connection = new PDO('sqlite:' . $databasePath, options: [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        // SQLite enables these settings per connection, so set them every time we connect.
        $connection->exec('PRAGMA foreign_keys = ON');
        $connection->exec('PRAGMA busy_timeout = 5000');

        return $connection;
    }
}

