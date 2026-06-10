<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Hide Commands
    |--------------------------------------------------------------------------
    |
    | This option allows to hide certain commands from the summary output.
    | They will still be available in your application. Wildcards are supported
    |
    | Examples: "make:*", "list"
    |
    */

    'hide' => [
        'auth:*',
        'cache:*',
        'channel:*',
        'clear-compiled',
        'completion',
        'config:cache',
        'config:show',
        'config:publish',
        'db:*',
        'debugbar:*',
        'docs',
        'down',
        'model:*',
        'lang:*',
        'dusk',
        'dusk:*',
        'env',
        'env:*',
        'event:*',
        'flare:*',
        'help',
        'ide-helper:*',
        'install:*',
        'key:*',
        'list',
        'make:*',
        'migrate:*',
        'notifications:*',
        'optimize:*',
        'package:*',
        'permission:*',
        'poller:*', // legacy test commands
        'queue:*',
        'release:*',
        'route:*',
        'schedule:*',
        'schema:*',
        'serve',
        'session:*',
        'storage:*',
        'stub:*',
        'test',
        'tinker',
        'translation:*',
        'ui:*',
        'up',
        'vendor:*',
        'view:*',
        'vue-i18n:*',
        'webpush:*',
        'ziggy:*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Binary Name
    |--------------------------------------------------------------------------
    |
    | This option allows to override the Artisan binary name that is used
    | in the command usage output.
    |
    */

    'binary' => 'lnms',

];
