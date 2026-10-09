<?php

namespace LibreNMS\Tests\Unit\Enum;

use App\Facades\LibrenmsConfig;
use LibreNMS\Enum\MaintenanceBehavior;
use LibreNMS\Tests\TestCase;

final class MaintenanceBehaviorTest extends TestCase
{
    public function testFromConfigFallsBackToDefinitionDefault(): void
    {
        $key = 'alert.scheduled_maintenance_default_behavior';
        $default = MaintenanceBehavior::from((int) LibrenmsConfig::getDefinitions()[$key]['default']);

        LibrenmsConfig::set($key, '9');
        $this->assertSame($default, MaintenanceBehavior::fromConfig());

        LibrenmsConfig::set($key, (string) MaintenanceBehavior::RunAlerts->value);
        $this->assertSame(MaintenanceBehavior::RunAlerts, MaintenanceBehavior::fromConfig());
    }
}
