<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\Sensor;
use App\Models\User;
use App\Models\WirelessSensor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;

final class MenuLinksTest extends TestCase
{
    use DatabaseTransactions;

    public function testMenuLinksUseNamedRoutes(): void
    {
        $device = Device::factory()->create();
        $group = DeviceGroup::factory()->create();
        $group->devices()->attach($device);
        Sensor::factory()->for($device)->create(['sensor_class' => 'temperature']);
        $wireless = WirelessSensor::factory()->for($device)->create();

        $content = $this->actingAs(User::factory()->admin()->create(['enabled' => 1]))
            ->get(route('about'))
            ->assertOk()
            ->getContent();

        foreach ([
            route('maps.network', ['group' => $group->id]),
            route('maps.devicedependency', ['group' => $group->id]),
            route('sensor.index', ['metric' => 'all', 'status' => 'alert']),
            route('sensor.index', ['metric' => 'mempool']),
            route('sensor.index', ['metric' => 'temperature']),
            route('wireless.index', ['metric' => $wireless->sensor_class->value]),
        ] as $url) {
            $this->assertStringContainsString('href="' . e($url) . '"', $content);
        }

        $this->assertStringNotContainsString('/metric=', $content);
        $this->assertStringNotContainsString('/map/group=', $content);
    }
}
