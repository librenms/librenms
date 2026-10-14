<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php74\Rector\StaticCall\ExportToReflectionFunctionRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/LibreNMS',
        __DIR__ . '/app',
        __DIR__ . '/bootstrap',
        __DIR__ . '/config',
        __DIR__ . '/database',
        __DIR__ . '/html',
        __DIR__ . '/includes',
        __DIR__ . '/lang',
        __DIR__ . '/resources',
        __DIR__ . '/routes',
        __DIR__ . '/scripts',
        __DIR__ . '/tests',
    ])
    ->withRootFiles()
    ->withSkip([
        __DIR__ . '/bootstrap/cache',
        // must stay parseable on old PHP so users can be told to switch to a legacy branch
        __DIR__ . '/daily.php',
        __DIR__ . '/validate.php',
        ExportToReflectionFunctionRector::class => [
            __DIR__ . '/app/Http/Controllers/Table/Traits/SensorTrait.php', // Rector trait parent bug
        ],
    ])
    ->withPhpSets()
    ->withTypeCoverageLevel(0)
    ->withDeadCodeLevel(0)
    ->withCodeQualityLevel(0);
