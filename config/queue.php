<?php

/*
 * Overrides for the framework queue config, unset options use the framework defaults.
 * Each connection listed here replaces the framework definition entirely.
 */

return [

    'connections' => [

        // retry_after must be longer than the worker --timeout (600) or long running polls get run twice
        'database' => [
            'driver' => 'database',
            'connection' => env('DB_QUEUE_CONNECTION'),
            'table' => env('DB_QUEUE_TABLE', 'jobs'),
            'queue' => env('DB_QUEUE', 'default'),
            'retry_after' => (int) env('DB_QUEUE_RETRY_AFTER', 660),
            'after_commit' => false,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_QUEUE_CONNECTION', 'default'),
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => (int) env('REDIS_QUEUE_RETRY_AFTER', 660),
            'block_for' => null,
            'after_commit' => false,
        ],

    ],

    // store failed jobs in the LibreNMS database (framework default is sqlite)
    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('DB_CONNECTION', env('DBTEST') ? 'testing' : 'mysql'),
        'table' => 'failed_jobs',
    ],

];
