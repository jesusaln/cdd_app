<?php

// config/backup.php (configuración opcional)
return [
    'disk' => env('BACKUP_DISK', 'local'),

    'path' => env('BACKUP_PATH', 'backups/database/'),

    'mysqldump_path' => env('MYSQLDUMP_PATH', 'mysqldump'),

    'compression' => [
        'enabled' => env('BACKUP_COMPRESSION', true),
        'level' => env('BACKUP_COMPRESSION_LEVEL', 6), // 0-9
    ],

    'cleanup' => [
        'enabled' => env('BACKUP_CLEANUP_ENABLED', true),
        'days_old' => env('BACKUP_CLEANUP_DAYS', 30),
    ],

    'schedule' => [
        'enabled' => env('BACKUP_SCHEDULE_ENABLED', false),
        'frequency' => env('BACKUP_SCHEDULE_FREQUENCY', 'daily'), // daily, weekly, monthly
        'time' => env('BACKUP_SCHEDULE_TIME', '02:00'),
    ],
];
