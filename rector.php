<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Enum_\EnumCaseToPascalCaseRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/LibreNMS',
        __DIR__ . '/app',
        __DIR__ . '/config',
        __DIR__ . '/html',
        __DIR__ . '/includes',
        __DIR__ . '/lang',
        __DIR__ . '/resources',
        __DIR__ . '/routes',
        __DIR__ . '/scripts',
        __DIR__ . '/tests',
    ])
    ->withPhpSets()
    ->withTypeCoverageLevel(0)
    ->withDeadCodeLevel(0)
    ->withCodeQualityLevel(0)
    ->withRules([EnumCaseToPascalCaseRector::class]);
