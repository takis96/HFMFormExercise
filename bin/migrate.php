<?php

declare(strict_types=1);

use App\Database\Connection;

$rootPath = dirname(__DIR__);
$autoloadPath = $rootPath . '/vendor/autoload.php';

if (!is_file($autoloadPath)) {
    fwrite(STDERR, "Dependencies are missing. Run \"composer install\" first.\n");
    exit(1);
}

require $autoloadPath;

$config = require $rootPath . '/config/app.php';
$database = Connection::make($config['database_path']);
$database->exec(
    'CREATE TABLE IF NOT EXISTS migrations (
        migration TEXT PRIMARY KEY,
        applied_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )'
);

// The history table makes migrations safe to run more than once.
$applied = $database->query('SELECT migration FROM migrations')->fetchAll(PDO::FETCH_COLUMN);
// Sorting keeps migrations running in their numbered order.
$migrationFiles = glob($rootPath . '/database/migrations/*.sql') ?: [];
sort($migrationFiles);

foreach ($migrationFiles as $migrationFile) {
    $migration = basename($migrationFile);

    if (in_array($migration, $applied, true)) {
        continue;
    }

    $sql = file_get_contents($migrationFile);

    if ($sql === false) {
        throw new RuntimeException("Could not read migration: {$migration}");
    }

    // Apply the schema change and its history record together so they cannot drift apart.
    $database->beginTransaction();

    try {
        $database->exec($sql);
        $statement = $database->prepare('INSERT INTO migrations (migration) VALUES (:migration)');
        $statement->execute(['migration' => $migration]);
        $database->commit();
        echo "Applied {$migration}\n";
    } catch (Throwable $exception) {
        $database->rollBack();
        throw $exception;
    }
}

echo "Database is up to date.\n";

