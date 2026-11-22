<?php

namespace LibreNMS\Tests\Feature;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Tests\DBTestCase;

final class DeviceCanPingTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testCanPingOnlyIncludesDevicesWithEnabledIcmp(): void
    {
        $enabled = Device::factory()->create();
        DevicePollingMethod::factory()->create([
            'device_id' => $enabled->device_id,
            'method_type' => PollingMethodType::Icmp,
            'enabled' => true,
        ]);

        $disabled = Device::factory()->create();
        DevicePollingMethod::factory()->create([
            'device_id' => $disabled->device_id,
            'method_type' => PollingMethodType::Icmp,
            'enabled' => false,
        ]);

        $snmpOnly = Device::factory()->create();
        DevicePollingMethod::factory()->create([
            'device_id' => $snmpOnly->device_id,
            'method_type' => PollingMethodType::Snmp,
            'enabled' => true,
        ]);

        $deviceDisabled = Device::factory()->create(['disabled' => 1]);
        DevicePollingMethod::factory()->create([
            'device_id' => $deviceDisabled->device_id,
            'method_type' => PollingMethodType::Icmp,
            'enabled' => true,
        ]);

        $ids = Device::canPing()
            ->whereIn('devices.device_id', [$enabled->device_id, $disabled->device_id, $snmpOnly->device_id, $deviceDisabled->device_id])
            ->pluck('devices.device_id')
            ->all();

        $this->assertSame([$enabled->device_id], $ids);
    }
}
