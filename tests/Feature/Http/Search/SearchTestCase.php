<?php

namespace LibreNMS\Tests\Feature\Http\Search;

use App\Models\Device;
use App\Models\Ipv4Mac;
use App\Models\Port;
use App\Models\PortsFdb;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

abstract class SearchTestCase extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('user');
    }

    protected function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }

    /**
     * @param  array<string, mixed>  $params
     * @return TestResponse<\Illuminate\Http\JsonResponse>
     */
    protected function results(array $params, ?User $user = null): TestResponse
    {
        return $this->actingAs($user ?? $this->admin())
            ->getJson(route('search.results', $params))
            ->assertOk();
    }

    /**
     * @param  array<string, mixed>  $device
     * @return array{Device, Port}
     */
    protected function portOn(string $name = 'test-port', array $device = []): array
    {
        $device = Device::factory()->create($device);
        $port = Port::factory()->for($device)->create(['ifName' => $name, 'ifDescr' => $name, 'ifAlias' => $name]);

        return [$device, $port];
    }

    /**
     * A router has the IP in its ARP table, a switch has the MAC in its forwarding table
     *
     * @return array{Device, Port, Device, Port}
     */
    protected function hostBehindSwitch(string $ip, string $mac): array
    {
        [$router, $routerPort] = $this->portOn('router-vlan');
        [$switch, $switchPort] = $this->portOn('switch-port');
        Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $routerPort->port_id, 'mac_address' => $mac, 'ipv4_address' => $ip]);
        PortsFdb::factory()->create(['device_id' => $switch->device_id, 'port_id' => $switchPort->port_id, 'mac_address' => $mac]);

        return [$router, $routerPort, $switch, $switchPort];
    }

    /**
     * @return list<string>
     */
    protected function listedIps(string $html): array
    {
        preg_match_all("/highlightText\\('([^']+)'\\)/", $html, $matches);

        return $matches[1];
    }
}
