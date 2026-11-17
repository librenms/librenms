<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Port;
use App\Models\Qos;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class QosTabTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
    }

    public function testTreeLinksUseQueryString(): void
    {
        $device = Device::factory()->create();
        $qos = $this->qos($device);

        $this->actingAs($this->admin())
            ->get(route('device.qos', ['device' => $device]))
            ->assertOk()
            ->assertSee('queue-one')
            ->assertSee(route('device.qos', ['device' => $device, 'show' => $qos->qos_id]), false)
            ->assertDontSee('qos_id=' . $qos->qos_id, false);
    }

    public function testShowFromQuerySelectsGraphs(): void
    {
        $device = Device::factory()->create();
        $qos = $this->qos($device);

        $this->actingAs($this->admin())
            ->get(route('device.qos', ['device' => $device, 'show' => $qos->qos_id]))
            ->assertOk()
            ->assertSee('<span title="tip">queue-one</span>', false)
            ->assertSee('qos_id=' . $qos->qos_id, false);
    }

    public function testLegacyPathShowRedirectsToQuery(): void
    {
        $device = Device::factory()->create();
        $qos = $this->qos($device);

        $this->actingAs($this->admin())
            ->get("/device/device=$device->device_id/tab=qos/show=$qos->qos_id/")
            ->assertStatus(301)
            ->assertRedirect(route('device.qos', ['device' => $device, 'show' => $qos->qos_id]));
    }

    public function testNonNumericShowIsRejected(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('device.qos', ['device' => $device, 'show' => 'abc']))
            ->assertSessionHasErrors('show');
    }

    public function testPortQosTreeLinksKeepPortView(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $device->device_id]);
        $qos = $this->qos($device, $port->port_id);

        $this->actingAs($this->admin())
            ->get(route('device.port', ['device' => $device, 'port' => $port->port_id, 'view' => 'qos']))
            ->assertOk()
            ->assertSee(e(route('device.port', ['device' => $device, 'port' => $port->port_id, 'view' => 'qos', 'show' => $qos->qos_id])), false);
    }

    public function testPortQosTreeLinksDropUnrelatedQuery(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $device->device_id]);
        $qos = $this->qos($device, $port->port_id);

        $this->actingAs($this->admin())
            ->get(route('device.port', ['device' => $device, 'port' => $port->port_id, 'view' => 'qos', 'interval' => '5']))
            ->assertOk()
            ->assertSee(e(route('device.port', ['device' => $device, 'port' => $port->port_id, 'view' => 'qos', 'show' => $qos->qos_id])), false)
            ->assertDontSee('interval=5&amp;show=', false);
    }

    private function qos(Device $device, ?int $portId = null): Qos
    {
        return Qos::create([
            'device_id' => $device->device_id,
            'port_id' => $portId,
            'type' => 'routeros_tree',
            'title' => 'queue-one',
            'tooltip' => 'tip',
            'rrd_id' => 'queue-one',
            'snmp_idx' => '1',
            'ingress' => 0,
            'egress' => 1,
        ]);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
