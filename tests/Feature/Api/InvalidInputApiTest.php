<?php

namespace LibreNMS\Tests\Feature\Api;

use App\Facades\LibrenmsConfig;
use App\Facades\Rrd;
use App\Models\Bill;
use App\Models\Device;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use LibreNMS\Tests\DBTestCase;
use LibreNMS\Util\Graph;

/**
 * Client mistakes (unknown object, unsupported type) are answered with 4xx, not 500.
 */
final class InvalidInputApiTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testPortTransceiverForUnknownPort(): void
    {
        $headers = $this->headers();
        $port = Port::factory()->for(Device::factory())->create();

        $this->getJson('/api/v0/ports/999999/transceiver', $headers)
            ->assertStatus(404)
            ->assertJsonPath('message', 'Port 999999 does not exist');

        $this->getJson("/api/v0/ports/$port->port_id/transceiver", $headers)
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok');
    }

    public function testGraphTemplateNames(): void
    {
        $this->assertTrue(Graph::hasTemplate('port', 'bits'));
        $this->assertTrue(Graph::hasTemplate('bill', 'historicmonthly'));
        $this->assertFalse(Graph::hasTemplate('port', 'auth'));
        $this->assertFalse(Graph::hasTemplate('bill', 'day'));
        $this->assertFalse(Graph::hasTemplate('bill', '../port/bits'));
        $this->assertFalse(Graph::hasTemplate('bill', ''));
    }

    public function testPortGraphInvalidInput(): void
    {
        $headers = $this->headers();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create(['ifName' => 'Ethernet1', 'deleted' => 0]);

        $message = $this->get("/api/v0/devices/$device->hostname/ports/Ethernet1/bits", $headers)
            ->assertStatus(400)
            ->json('message');
        $this->assertStringContainsString('port_bits', $message);

        $this->get("/api/v0/devices/$device->hostname/ports/Ethernet1/port_auth", $headers)
            ->assertStatus(400);

        $this->get("/api/v0/devices/$device->hostname/ports/Ethernet9/port_bits", $headers)
            ->assertStatus(404)
            ->assertJsonPath('message', "Port Ethernet9 does not exist on device $device->hostname");

        $this->get('/api/v0/devices/does-not-exist.example.com/ports/Ethernet1/port_bits', $headers)
            ->assertStatus(404)
            ->assertJsonPath('message', 'Device does-not-exist.example.com does not exist');

        $this->get('/api/v0/devices/999999/ports/Ethernet1/port_bits', $headers)
            ->assertStatus(404)
            ->assertJsonPath('message', 'Device 999999 does not exist');

        // a valid request (by hostname or device id) draws the graph from that port's rrd file
        $rrd_file = basename((string) get_port_rrdfile_path($device->hostname, $port->port_id));
        Rrd::partialMock()->shouldReceive('graph')->twice()
            ->withArgs(fn (array $options) => str_contains(implode(' ', $options), $rrd_file))
            ->andReturn('graph image');

        foreach ([$device->hostname, $device->device_id] as $hostname) {
            $this->get("/api/v0/devices/$hostname/ports/Ethernet1/port_bits?graph_type=png", $headers)
                ->assertStatus(200)
                ->assertHeader('Content-Type', 'image/png')
                ->assertContent('graph image');
        }
    }

    public function testPortStatsInvalidInput(): void
    {
        $headers = $this->headers();
        $device = Device::factory()->create();
        Port::factory()->for($device)->create(['ifName' => 'Ethernet1', 'deleted' => 0]);

        $this->getJson("/api/v0/devices/$device->hostname/ports/Ethernet9", $headers)
            ->assertStatus(404)
            ->assertJsonPath('message', "Port Ethernet9 does not exist on device $device->hostname");

        $this->getJson('/api/v0/devices/does-not-exist.example.com/ports/Ethernet1', $headers)
            ->assertStatus(404)
            ->assertJsonPath('message', 'Device does-not-exist.example.com does not exist');

        $this->getJson('/api/v0/devices/999999/ports/Ethernet1', $headers)
            ->assertStatus(404)
            ->assertJsonPath('message', 'Device 999999 does not exist');

        foreach ([$device->hostname, $device->device_id] as $hostname) {
            $this->getJson("/api/v0/devices/$hostname/ports/Ethernet1", $headers)
                ->assertStatus(200)
                ->assertJsonPath('port.device_id', $device->device_id)
                ->assertJsonPath('port.ifName', 'Ethernet1');
        }
    }

    public function testPortByHostnameWithoutDevicePermission(): void
    {
        $own = Device::factory()->create();
        Port::factory()->for($own)->create(['ifName' => 'Ethernet1', 'deleted' => 0]);
        $other = Device::factory()->create();
        Port::factory()->for($other)->create(['ifName' => 'Ethernet1', 'deleted' => 0]);

        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole('user');
        $user->devicesOwned()->attach($own->device_id);
        $headers = ['X-Auth-Token' => $user->createToken('test')->plainTextToken];

        $this->getJson("/api/v0/devices/$own->hostname/ports/Ethernet1", $headers)->assertStatus(200);
        $this->getJson("/api/v0/devices/$own->hostname/ports/Ethernet9", $headers)->assertStatus(404);

        // a user who may not see the device can't tell whether the device or the port exists
        $responses = [];
        foreach (["$other->hostname/ports/Ethernet1", "$other->hostname/ports/Ethernet9", 'does-not-exist.example.com/ports/Ethernet1', '999999/ports/Ethernet1'] as $path) {
            foreach (["$path/port_bits", $path] as $url) {
                $responses[$url] = $this->getJson("/api/v0/devices/$url", $headers)->assertStatus(403)->json();
            }
        }
        $this->assertSame([['status' => 'error', 'message' => 'Insufficient permissions to access this port']], array_values(array_unique($responses, SORT_REGULAR)));
    }

    public function testBillGraphInvalidInput(): void
    {
        $headers = $this->headers();
        $bill = Bill::factory()->create();

        foreach (['day', 'hour', 'auth', 'nope'] as $type) {
            $this->get("/api/v0/bills/$bill->bill_id/graphs/$type", $headers)
                ->assertStatus(400)
                ->assertJsonPath('message', "Unsupported graph type $type");
        }

        $this->get('/api/v0/bills/abc/graphs/bits', $headers)
            ->assertStatus(400)
            ->assertJsonPath('message', 'Invalid bill id abc');

        $this->get('/api/v0/bills/999999/graphs/bits', $headers)
            ->assertStatus(404)
            ->assertJsonPath('message', 'Bill 999999 does not exist');

        $now = time();
        foreach ([
            'historicbits' => 'Graph type historicbits needs from and to as unix timestamps',
            'historicbits?from=' . ($now - 3600) => 'Graph type historicbits needs from and to as unix timestamps',
            'historicbits?from=-1d&to=now' => 'Graph type historicbits needs from and to as unix timestamps',
            'historictransfer' => 'Graph type historictransfer needs from and to as unix timestamps',
            'historictransfer?from=' . ($now - 3600) => 'Graph type historictransfer needs from and to as unix timestamps',
            "historictransfer?from=$now&to=$now&imgtype=week" => 'imgtype must be day or hour',
        ] as $type => $message) {
            $this->get("/api/v0/bills/$bill->bill_id/graphs/$type", $headers)
                ->assertStatus(400)
                ->assertJsonPath('message', $message);
        }
    }

    public function testBillGraphWithoutBillPermission(): void
    {
        LibrenmsConfig::set('enable_billing', true);
        $bill = Bill::factory()->create();
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole('user');
        $headers = ['X-Auth-Token' => $user->createToken('test')->plainTextToken];

        // a user who may not see the bill can't tell whether it exists
        foreach ([$bill->bill_id, 999999] as $bill_id) {
            $this->get("/api/v0/bills/$bill_id/graphs/bits", $headers)
                ->assertStatus(403)
                ->assertJsonPath('message', 'Insufficient permissions to access this bill');
        }
    }

    public function testBillGraphValidTypes(): void
    {
        $headers = $this->headers();
        $port = Port::factory()->for(Device::factory())->create();
        $bill = Bill::factory()->create();
        $bill->ports()->attach($port);
        $to = time();
        $from = $to - 3600;
        foreach (range(1, 6) as $i) {
            DB::table('bill_data')->insert([
                'bill_id' => $bill->bill_id,
                'timestamp' => DB::raw('FROM_UNIXTIME(' . ($from + $i * 300) . ')'),
                'period' => 300,
                'delta' => 3000,
                'in_delta' => 1000,
                'out_delta' => 2000,
            ]);
        }

        // bits is drawn by rrdtool from the rrd files of the bill's ports
        Rrd::partialMock()->shouldReceive('checkRrdExists')->andReturnTrue();
        Rrd::shouldReceive('graph')->andReturn('graph image');
        $this->get("/api/v0/bills/$bill->bill_id/graphs/bits?graph_type=png", $headers)
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'image/png')
            ->assertContent('graph image');

        // the historic graphs are drawn by jpgraph
        foreach ([
            'monthly',
            'historicmonthly',
            "historictransfer?from=$from&to=$to",
            "historictransfer?from=$from&to=$to&imgtype=hour",
            "historicbits?from=$from&to=$to",
        ] as $type) {
            $response = $this->get("/api/v0/bills/$bill->bill_id/graphs/$type", $headers);
            $response->assertStatus(200)->assertHeader('Content-Type', 'image/png');
            $this->assertStringStartsWith("\x89PNG", (string) $response->getContent(), "$type did not return a png image");
        }

        $this->get("/api/v0/bills/$bill->bill_id/graphs/monthly?output=base64", $headers)
            ->assertStatus(200)
            ->assertJsonPath('image.image', fn ($image) => str_starts_with(base64_decode($image), "\x89PNG"));
    }

    /**
     * @return array<string, string>
     */
    private function headers(): array
    {
        /** @var User $user */
        $user = User::factory()->admin()->create();

        return ['X-Auth-Token' => $user->createToken('test')->plainTextToken];
    }
}
