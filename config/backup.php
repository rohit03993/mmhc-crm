<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Backup storage (under storage/app/)
    |--------------------------------------------------------------------------
    */

    'relative_path' => env('BACKUP_RELATIVE_PATH', 'site-backups'),

    /*
    |--------------------------------------------------------------------------
    | MySQL client binaries (null = resolve from PATH)
    |--------------------------------------------------------------------------
    */

    'mysqldump_path' => env('BACKUP_MYSQLDUMP_PATH'),
    'mysql_path' => env('BACKUP_MYSQL_PATH'),

    /*
    |--------------------------------------------------------------------------
    | Retention for Artisan cleanup (admin UI lists all; prune via schedule)
    |--------------------------------------------------------------------------
    */

    'keep_days' => (int) env('BACKUP_KEEP_DAYS', 14),

];
