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
        'clear-compiled',
        'config:cache',
        'config:clear',
        'db:*',
        'debugbar:*',
        'down',
        'dusk',
        'dusk:*',
        'env',
        'event:*',
        'help',
        'ide-helper:*',
        'key:*',
        'list',
        'make:*',
        'migrate:*',
        'notifications:*',
        'optimize:*',
        'package:*',
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
        'ziggy:*',
    ],
];
