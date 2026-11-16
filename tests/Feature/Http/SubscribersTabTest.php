<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Port;
use App\Models\SubscriberCount;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

class SubscribersTabTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }

    public function testTabShowsDeviceAndPortCounts(): void
    {
        $device = Device::factory()->create();
        $pppoe = Port::factory()->for($device)->create(['ifIndex' => 812, 'ifName' => 'Bundle-Ether101.59658', 'ifDescr' => 'Bundle-Ether101.59658', 'ifAlias' => 'PPPoE customer']);
        $ipoe = Port::factory()->for($device)->create(['ifIndex' => 1663, 'ifName' => 'Bundle-Ether101.209159', 'ifDescr' => 'Bundle-Ether101.209159', 'ifAlias' => 'IPoE handover']);
        $lost = Port::factory()->for($device)->create(['ifIndex' => 900, 'ifName' => 'Bundle-Ether101.900', 'ifDescr' => 'Bundle-Ether101.900', 'ifAlias' => 'Lost customer']);

        $device->subscriberCounts()->createMany([
            ['port_id' => null, 'session_type' => 'total', 'sessions' => 135],
            ['port_id' => null, 'session_type' => 'pppoe', 'sessions' => 1],
            ['port_id' => null, 'session_type' => 'ipoe', 'sessions' => 134],
            ['port_id' => $pppoe->port_id, 'session_type' => 'pppoe', 'sessions' => 1],
            ['port_id' => $ipoe->port_id, 'session_type' => 'ipoe', 'sessions' => 134],
            ['port_id' => $lost->port_id, 'session_type' => 'ipoe', 'sessions' => 0],
        ]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device->device_id, 'tab' => 'subscribers']))
            ->assertOk()
            ->assertSee('Subscribers per interface')
            ->assertSeeInOrder(['PPPoE', 'IPoE', 'Total'])
            ->assertSee('Bundle-Ether101.209159')
            ->assertSee('IPoE handover')
            ->assertSee('device_subscribers', false)
            ->assertSee('<tr class="danger">', false);
    }

    public function testTabShowsRadiusServersAndVpdnGraphs(): void
    {
        $device = Device::factory()->create();
        $device->subscriberCounts()->create(['port_id' => null, 'session_type' => 'total', 'sessions' => 162]);
        $device->radiusServers()->createMany([
            ['address' => '192.0.2.10', 'port' => 1812, 'state' => 'up', 'dead_count' => 0, 'requests' => 1000, 'accepts' => 100, 'rejects' => 890, 'timeouts' => 10, 'round_trip_time' => 250],
            ['address' => '192.0.2.21', 'port' => 1812, 'state' => 'dead', 'dead_count' => 290, 'requests' => 200, 'timeouts' => 194, 'round_trip_time' => 840],
        ]);
        $device->graphs()->create(['graph' => 'vpdn_sessions_l2tp']);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device->device_id, 'tab' => 'subscribers']))
            ->assertOk()
            ->assertSee('Authentication (RADIUS)')
            ->assertSee('192.0.2.10:1812')
            ->assertSee('(89%)')
            ->assertSee('label-danger', false)
            ->assertSee('dead 290 times')
            ->assertSee('(97%)')
            ->assertSee('840 ms')
            ->assertSee('device_radius_auth', false)
            ->assertSee('VPDN (L2TP)')
            ->assertSee('device_vpdn_sessions_l2tp', false)
            ->assertDontSee('Subscribers per interface');
    }

    public function testPortsArePaginatedAndSearchable(): void
    {
        $device = Device::factory()->create();
        $device->subscriberCounts()->create(['port_id' => null, 'session_type' => 'pppoe', 'sessions' => 30]);
        foreach (range(1, 30) as $i) {
            $port = Port::factory()->for($device)->create(['ifIndex' => $i, 'ifName' => "BE100.customer$i", 'ifDescr' => "BE100.customer$i", 'ifAlias' => "Customer $i"]);
            $device->subscriberCounts()->create(['port_id' => $port->port_id, 'session_type' => 'pppoe', 'sessions' => $i === 7 ? 0 : 1]);
        }

        $url = route('device', ['device' => $device->device_id, 'tab' => 'subscribers']);
        $this->actingAs($this->admin());

        $this->get($url)->assertOk()
            ->assertSee('30 interfaces')
            ->assertSee('1 without sessions')
            ->assertSee('BE100.customer25<', false)
            ->assertDontSee('BE100.customer26<', false);

        $this->get("$url?page=2")->assertOk()
            ->assertSee('BE100.customer26<', false)
            ->assertDontSee('BE100.customer25<', false);

        $this->get("$url?search=Customer%2012")->assertOk()
            ->assertSee('BE100.customer12<', false)
            ->assertDontSee('BE100.customer11<', false);
    }

    public function testTabHiddenWithoutSubscribers(): void
    {
        $device = Device::factory()->create();

        $this->assertFalse(app(\App\Http\Controllers\Device\Tabs\SubscribersController::class)->visible($device));

        SubscriberCount::create(['device_id' => $device->device_id, 'port_id' => null, 'session_type' => 'total', 'sessions' => 0]);
        $this->assertTrue(app(\App\Http\Controllers\Device\Tabs\SubscribersController::class)->visible($device));
    }
}
