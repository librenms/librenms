<?php

return [
    App\Providers\ConfigServiceProvider::class,
    App\Providers\AppServiceProvider::class,
    App\Providers\CliServiceProvider::class,
    App\Providers\ComposerServiceProvider::class,
    App\Providers\DatastoreServiceProvider::class,
    App\Providers\SnmptrapProvider::class,
    App\Providers\PluginProvider::class,
    Binaryk\LaravelRestify\LaravelRestifyServiceProvider::class,
    App\Providers\RestifyServiceProvider::class,
];
