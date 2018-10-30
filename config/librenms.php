<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User
    |--------------------------------------------------------------------------
    |
    | This value is the user LibreNMS runs as. It is used to secure permissions
    | and grant access to things needed. Defaults to librenms.
    */

    'user' => env('LIBRENMS_USER', 'librenms'),

    /*
    |--------------------------------------------------------------------------
    | User
    |--------------------------------------------------------------------------
    |
    | This value is the group LibreNMS runs as. It is used to secure permissions
    | and grant access to things needed. Defaults to the same as LIBRENMS_USER.
    */

    'group' => env('LIBRENMS_GROUP', env('LIBRENMS_USER', 'librenms')),

];
