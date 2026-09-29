<?php

declare(strict_types=1);

$databasePath = getenv('DB_DATABASE');

return [
    'name' => 'HFM Registration Exercise',
    'environment' => getenv('APP_ENV') ?: 'local',
    'database_path' => $databasePath ?: dirname(__DIR__) . '/storage/database/app.sqlite',
];

