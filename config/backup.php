<?php

return [

    'keep' => (int) env('DB_BACKUP_KEEP', 7),

    'daily_at' => env('DB_BACKUP_AT', '02:00'),

    'restore_timeout' => (int) env('DB_RESTORE_TIMEOUT', 600),

    'upload_max' => (int) env('DB_RESTORE_UPLOAD_MAX', 512000),

];
