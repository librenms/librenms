<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Cache Store
    |--------------------------------------------------------------------------
    |
    | This option controls the default cache store that will be used by the
    | framework. This connection is utilized if another isn't explicitly
    | specified when running a cache operation inside the application.
    | Set here to allow backwards compatability with CACHE_DRIVER.
    |
    */

    'default' => env('CACHE_STORE', env('CACHE_DRIVER', 'database')),

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | Merged with the framework default stores.
    | The device store holds data for the current device and is flushed at
    | the start of each device poll/discovery.
    |
    */

    'stores' => [
        'device' => [
            'driver' => 'array',
            'serialize' => false,
        ],
    ],

];
