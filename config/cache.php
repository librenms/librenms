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

];
