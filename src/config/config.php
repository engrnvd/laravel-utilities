<?php
return [
    'logs' => [
        'threshold' => env('LOGS_CLEAR_THRESHOLD', 10),
        'remove_empty_dir' => env('LOGS_REMOVE_EMPTY_DIR', true),
    ],
];