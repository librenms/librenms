<?php

namespace LibreNMS\Tests\Unit\Enum;

use App\Facades\LibrenmsConfig;
use LibreNMS\Enum\MaintenanceBehavior;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class MaintenanceBehaviorTest extends TestCase
{
    #[DataProvider('configValues')]
    public function testFromConfig(mixed $value, MaintenanceBehavior $expected): void
    {
        LibrenmsConfig::set('alert.scheduled_maintenance_default_behavior', $value);

        $this->assertSame($expected, MaintenanceBehavior::fromConfig());
    }

    public static function configValues(): array
    {
        return [
            'skip string' => ['1', MaintenanceBehavior::SkipAlerts],
            'mute string' => ['2', MaintenanceBehavior::MuteAlerts],
            'run int' => [3, MaintenanceBehavior::RunAlerts],
            'out of range' => ['9', MaintenanceBehavior::SkipAlerts],
            'garbage' => ['foo', MaintenanceBehavior::SkipAlerts],
            'null' => [null, MaintenanceBehavior::SkipAlerts],
        ];
    }
}
