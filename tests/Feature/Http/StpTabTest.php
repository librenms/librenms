<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Stp;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class StpTabTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
    }

    public function testFirstVlanIsDefault(): void
    {
        $device = $this->device();

        $this->actingAs($this->admin())
            ->get(route('device.stp', ['device' => $device]))
            ->assertOk()
            ->assertSee('proto-ten')
            ->assertDontSee('proto-twenty')
            ->assertSee('stp?vlan=20', false);
    }

    public function testVlanFromQuery(): void
    {
        $device = $this->device();

        $this->actingAs($this->admin())
            ->get(route('device.stp', ['device' => $device, 'vlan' => 20]))
            ->assertOk()
            ->assertSee('proto-twenty')
            ->assertDontSee('proto-ten');
    }

    public function testLegacyPathVlanRedirectsToQuery(): void
    {
        $device = $this->device();

        $this->actingAs($this->admin())
            ->get('/device/device=' . $device->device_id . '/tab=stp/vlan=20/')
            ->assertRedirect(route('device.stp', ['device' => $device, 'vlan' => 20]))
            ->assertStatus(301);
    }

    public function testLegacyDeviceSegmentRedirects(): void
    {
        $device = $this->device();

        $this->actingAs($this->admin())
            ->get("/device/device=$device->device_id/stp")
            ->assertRedirect(route('device.stp', ['device' => $device]));
    }

    public function testNonNumericVlanIsRejected(): void
    {
        $device = $this->device();

        $this->actingAs($this->admin())
            ->get(route('device.stp', ['device' => $device, 'vlan' => 'abc']))
            ->assertSessionHasErrors('vlan');
    }

    private function device(): Device
    {
        $device = Device::factory()->create();
        Stp::factory()->create(['device_id' => $device->device_id, 'vlan' => 10, 'protocolSpecification' => 'proto-ten']);
        Stp::factory()->create(['device_id' => $device->device_id, 'vlan' => 20, 'protocolSpecification' => 'proto-twenty']);

        return $device;
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
