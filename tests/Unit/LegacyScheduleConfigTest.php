<?php

namespace LibreNMS\Tests\Unit;

use App\ConfigRepository;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

final class LegacyScheduleConfigTest extends TestCase
{
    public function testLegacyScheduleOptions(): void
    {
        $repositoryClass = new ReflectionClass(ConfigRepository::class);
        $configProperty = new ReflectionProperty(ConfigRepository::class, 'config');
        $mapOptions = new ReflectionMethod(ConfigRepository::class, 'mapLegacyScheduleOptions');

        foreach (['alerting', 'billing', 'discovery', 'ping', 'poller', 'services'] as $type) {
            foreach ([true, 1, '1', false, 0, '0', null] as $enabled) {
                foreach (['legacy', 'cron', 'dispatcher', 'scheduler', 'disabled'] as $schedule) {
                    $repository = $repositoryClass->newInstanceWithoutConstructor();
                    $configProperty->setValue($repository, [
                        'schedule_type' => [$type => $schedule],
                    ]);
                    if ($enabled !== null) {
                        $repository->set("service_{$type}_enabled", $enabled);
                    }

                    $mapOptions->invoke($repository);

                    $expected = $schedule;
                    if ($schedule === 'legacy' && $enabled !== null) {
                        $expected = in_array($enabled, [true, 1, '1'], true) ? 'dispatcher' : 'cron';
                    }
                    $this->assertSame($expected, $repository->get("schedule_type.$type"), "$type with " . var_export($enabled, true) . " and $schedule");
                }
            }
        }
    }
}
