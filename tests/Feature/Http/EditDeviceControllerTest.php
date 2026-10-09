<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Tests\DBTestCase;

final class EditDeviceControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    private const DETAILS = ['sysName' => 'manual', 'hardware' => 'Rack PDU', 'os' => ''];

    public function testDetailsCanBeSetWithoutSnmp(): void
    {
        $device = Device::factory()->create(['os' => 'linux', 'sysName' => 'old', 'hardware' => 'old']);
        DevicePollingMethod::factory()->create(['device_id' => $device->device_id, 'method_type' => PollingMethodType::Icmp]);
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $this->actingAs($admin)->get(route('device.edit', $device))
            ->assertOk()
            ->assertSee('name="hardware"', false);

        $this->actingAs($admin)->put(route('device.edit.update', $device), self::DETAILS)->assertRedirect();

        $device->refresh();
        $this->assertSame('manual', $device->sysName);
        $this->assertSame('Rack PDU', $device->hardware);
        $this->assertSame('ping', $device->os); // no OS given

        $this->actingAs($admin)->put(route('device.edit.update', $device), ['os' => 'not-an-os'])->assertSessionHasErrors('os');
    }

    public function testDetailsAreDiscoveredWithSnmp(): void
    {
        $device = Device::factory()->create(['os' => 'linux', 'sysName' => 'detected', 'hardware' => 'detected']);
        DevicePollingMethod::factory()->create(['device_id' => $device->device_id, 'method_type' => PollingMethodType::Snmp, 'enabled' => false]);
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $this->actingAs($admin)->get(route('device.edit', $device))
            ->assertOk()
            ->assertDontSee('name="hardware"', false);

        $this->actingAs($admin)->put(route('device.edit.update', $device), self::DETAILS)->assertRedirect();

        $device->refresh();
        $this->assertSame('detected', $device->sysName);
        $this->assertSame('detected', $device->hardware);
        $this->assertSame('linux', $device->os);
    }
}
