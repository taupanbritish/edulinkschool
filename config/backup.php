<?php

return [

    'keep' => (int) env('DB_BACKUP_KEEP', 7),

    'daily_at' => env('DB_BACKUP_AT', '02:00'),

];
