<?php

// merged over the framework config, connections here replace the framework ones entirely

return [

    'connections' => [

        // retry_after > longest job $timeout or jobs run twice
        'database' => [
            'driver' => 'database',
            'connection' => env('DB_QUEUE_CONNECTION'),
            'table' => env('DB_QUEUE_TABLE', 'jobs'),
            'queue' => env('DB_QUEUE', 'default'),
            'retry_after' => (int) env('DB_QUEUE_RETRY_AFTER', 3660),
            'after_commit' => false,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_QUEUE_CONNECTION', 'default'),
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => (int) env('REDIS_QUEUE_RETRY_AFTER', 3660),
            'block_for' => null,
            'after_commit' => false,
        ],

    ],

    // framework default is sqlite
    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('DB_CONNECTION', env('DBTEST') ? 'testing' : 'mysql'),
        'table' => 'failed_jobs',
    ],

];
