<?php

/**
 * BgpPeersTest.php
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Feature\Modules;

use App\Actions\Device\AutoDiscoverDevice;
use App\Facades\LibrenmsConfig;
use App\Models\BgpPeer;
use App\Models\Device;
use App\Models\Eventlog;
use App\Models\Port;
use App\Models\Vrf;
use App\Models\VrfLite;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use LibreNMS\Discovery\Bgp\BgpTableReader;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Modules\BgpPeers;
use LibreNMS\OS;
use LibreNMS\Tests\DBTestCase;
use LibreNMS\Util\IP;
use Mockery;

final class BgpPeersTest extends DBTestCase
{
    use DatabaseTransactions;

    private Device $device;

    protected function setUp(): void
    {
        parent::setUp();

        LibrenmsConfig::set('autodiscovery.bgp', false);
        LibrenmsConfig::set('astext.65001', 'Peer AS');
        LibrenmsConfig::set('astext.65002', 'Other AS');
        LibrenmsConfig::set('alerts.bgp.whitelist', null);

        $this->device = Device::factory()->create(['os' => 'generic']);
    }

    public function testRepeatedDiscoveryKeepsTheSameRow(): void
    {
        $existing = $this->device->bgppeers()->create($this->peerAttributes('10.0.0.1'));
        $this->fakeReader(['' => [['ip' => '10.0.0.1', 'remote_as' => 65001]]]);

        $module = new BgpPeers;
        $module->discover($this->os());
        $module->discover($this->os());

        $peer = $this->device->bgppeers()->sole();
        $this->assertSame($existing->bgpPeer_id, $peer->bgpPeer_id);
        $this->assertSame('Peer AS', $peer->astext);
        $this->assertSame(65000, $this->device->fresh()->bgpLocalAs);
        $this->assertTrue($module->dataExists($this->device));
    }

    public function testDiscoveryLeavesSessionStateToPolling(): void
    {
        $this->device->bgppeers()->create($this->peerAttributes('10.0.0.1', ['bgpPeerState' => 'established', 'bgpPeerInUpdates' => 10]));
        $this->fakeReader(['' => [
            ['ip' => '10.0.0.1', 'remote_as' => 65002, 'state' => 'idle', 'in_updates' => 20],
            ['ip' => '10.0.0.2', 'remote_as' => 65001, 'state' => 'active'],
        ]]);

        (new BgpPeers)->discover($this->os());

        $peers = $this->device->bgppeers()->orderBy('bgpPeerIdentifier')->get();
        $this->assertSame(['10.0.0.1', '10.0.0.2'], $peers->pluck('bgpPeerIdentifier')->all());
        $this->assertSame(65002, $peers[0]->bgpPeerRemoteAs, 'Discovery updates the peer AS');
        $this->assertSame('established', $peers[0]->bgpPeerState, 'Existing session state is only updated by polling');
        $this->assertSame(10, $peers[0]->bgpPeerInUpdates);
        $this->assertSame('active', $peers[1]->bgpPeerState, 'New sessions start with their current state');
        $this->assertSame(0, $peers[1]->bgpPeerInUpdates, 'Missing counters use defaults');
    }

    public function testEachContextIsRead(): void
    {
        $this->device->setAttrib('routing_snmp_contexts', json_encode(['vrf-b']));
        $reader = $this->fakeReader([
            '' => [['ip' => '10.0.0.1', 'remote_as' => 65001]],
            'vrf-b' => [['ip' => '10.0.0.1', 'remote_as' => 65002]],
        ]);

        (new BgpPeers)->discover($this->os());

        $this->assertSame(['', 'vrf-b'], $reader->contexts);
        $this->assertEquals(
            ['10.0.0.1|' => 65001, '10.0.0.1|vrf-b' => 65002],
            $this->device->bgppeers()->get()->mapWithKeys(fn (BgpPeer $peer) => ["$peer->bgpPeerIdentifier|$peer->context_name" => $peer->bgpPeerRemoteAs])->all(),
        );
    }

    public function testDefaultContextIsAlwaysRead(): void
    {
        VrfLite::factory()->create(['device_id' => $this->device->device_id, 'context_name' => 'vrf-a', 'vrf_name' => 'A']);
        VrfLite::factory()->create(['device_id' => $this->device->device_id, 'context_name' => 'default', 'vrf_name' => 'default']);
        $reader = $this->fakeReader([
            '' => [['ip' => '10.0.0.1', 'remote_as' => 65001]],
            'vrf-a' => [['ip' => '10.0.0.1', 'remote_as' => 65002]],
        ], [
            ['ip' => '10.0.0.1', 'afi' => 'ipv4', 'safi' => 'unicast', 'accepted' => 5],
        ]);

        (new BgpPeers)->discover($this->os());

        $this->assertSame(['', 'vrf-a'], $reader->contexts, 'The default VRF context is not read twice');
        $this->assertSame(['', 'vrf-a'], $this->device->bgppeers()->orderBy('context_name')->pluck('context_name')->all());
        $this->assertSame(['', 'vrf-a'], $this->device->bgpPeersCbgp()->orderBy('context_name')->pluck('context_name')->all(), 'Address families are kept per context');
    }

    public function testUnnumberedSessionsAreIdentifiedByInterface(): void
    {
        LibrenmsConfig::set('autodiscovery.bgp', true);
        $this->app->instance(AutoDiscoverDevice::class, Mockery::mock(AutoDiscoverDevice::class, function ($mock): void {
            $mock->shouldNotReceive('execute');
        }));
        Port::factory()->create(['device_id' => $this->device->device_id, 'ifIndex' => 27, 'ifName' => 'swp27']);
        Port::factory()->create(['device_id' => $this->device->device_id, 'ifIndex' => 28, 'ifName' => 'swp28']);
        $this->fakeReader(['' => [
            ['remote_as' => 65001, 'iface' => '27'],
            ['ip' => 'fe80::1', 'remote_as' => 65001, 'iface' => '28'],
        ]]);

        (new BgpPeers)->discover($this->os());

        $peers = $this->device->bgppeers()->orderBy('bgpPeerIdentifier')->get();
        $this->assertSame(['swp27', 'swp28'], $peers->pluck('bgpPeerIdentifier')->all());
        $this->assertSame(['', 'fe80:0000:0000:0000:0000:0000:0000:0001'], $peers->pluck('bgpPeerRemoteAddr')->all());
        $this->assertEquals([27, 28], $peers->pluck('bgpPeerIface')->all());
    }

    public function testTablesWithVrfAreOnlyReadFromDefaultContext(): void
    {
        $this->device->setAttrib('routing_snmp_contexts', json_encode(['vrf-b']));
        $vrf = Vrf::factory()->create(['device_id' => $this->device->device_id, 'vrf_oid' => '7', 'vrf_name' => 'blue']);
        $named = Vrf::factory()->create(['device_id' => $this->device->device_id, 'vrf_oid' => '8', 'vrf_name' => 'red']);
        $reader = $this->fakeReader(['' => [
            ['ip' => '10.0.0.1', 'remote_as' => 65001],
            ['ip' => '10.0.0.1', 'remote_as' => 65001, 'vrf' => '7'],
            ['ip' => '10.0.0.1', 'remote_as' => 65001, 'vrf' => '9', 'vrf_name' => 'red'],
        ]], reportsAllVrfs: true);

        (new BgpPeers)->discover($this->os());

        $this->assertSame([''], $reader->contexts);
        $this->assertEquals([null, $vrf->vrf_id, $named->vrf_id], $this->device->bgppeers()->orderBy('bgpPeer_id')->pluck('vrf_id')->all());
    }

    public function testInterfaceIsResolved(): void
    {
        Port::factory()->create(['device_id' => $this->device->device_id, 'ifIndex' => 7, 'ifName' => 'swp7']);
        $this->fakeReader(['' => [
            ['ip' => '10.0.0.1', 'remote_as' => 65001, 'iface' => '5'],
            ['ip' => '10.0.0.2', 'remote_as' => 65001, 'iface' => 'swp7'],
            ['ip' => '10.0.0.3', 'remote_as' => 65001, 'iface' => 'missing', 'local_address' => '10.9.9.9'],
        ]]);

        (new BgpPeers)->discover($this->os());

        $this->assertEquals([5, 7, null], $this->device->bgppeers()->orderBy('bgpPeerIdentifier')->pluck('bgpPeerIface')->all());
    }

    public function testAddressFamiliesOfUnknownPeersAreIgnored(): void
    {
        $this->fakeReader(['' => [['ip' => '10.0.0.1', 'remote_as' => 65001]]], [
            ['ip' => '10.0.0.1', 'afi' => 'ipv4', 'safi' => 'unicast', 'accepted' => 5],
            ['ip' => '10.0.0.7', 'afi' => 'ipv4', 'safi' => 'unicast', 'accepted' => 5],
        ]);

        (new BgpPeers)->discover($this->os());

        $family = $this->device->bgpPeersCbgp()->sole();
        $this->assertSame(['10.0.0.1', 'ipv4', 'unicast', 5, 0], [$family->bgpPeerIdentifier, $family->afi, $family->safi, $family->AcceptedPrefixes, $family->AcceptedPrefixes_delta]);
    }

    public function testWithdrawnPrefixesAreSubtractedFromAdvertised(): void
    {
        $this->fakeReader(['' => [['ip' => '10.0.0.1', 'remote_as' => 65001]]], [
            ['ip' => '10.0.0.1', 'afi' => 'ipv4', 'safi' => 'unicast', 'advertised' => 50, 'withdrawn' => 8],
        ]);

        (new BgpPeers)->discover($this->os());

        $family = $this->device->bgpPeersCbgp()->sole();
        $this->assertSame([42, 0], [$family->AdvertisedPrefixes, $family->WithdrawnPrefixes]);
    }

    public function testPollingUpdatesSessionsAndLogsStateChanges(): void
    {
        $this->device->bgppeers()->create($this->peerAttributes('10.0.0.1', ['bgpPeerState' => 'established', 'bgpPeerFsmEstablishedTime' => 500]));
        $this->device->bgppeers()->create($this->peerAttributes('10.0.0.2', ['bgpPeerState' => 'established', 'bgpPeerFsmEstablishedTime' => 500]));
        $this->device->bgppeers()->create($this->peerAttributes('10.0.0.3', ['bgpPeerState' => 'established', 'bgpPeerFsmEstablishedTime' => 500]));
        $this->fakeReader(['' => [
            ['ip' => '10.0.0.1', 'remote_as' => 65001, 'state' => 'established', 'established_time' => 800, 'in_updates' => 42],
            ['ip' => '10.0.0.2', 'remote_as' => 65001, 'state' => 'established', 'established_time' => 5],
            ['ip' => '10.0.0.3', 'remote_as' => 65001, 'state' => 'idle', 'established_time' => 5, 'last_error_code' => 6, 'last_error_subcode' => 2],
        ]]);

        $datastore = Mockery::mock(DataStorageInterface::class);
        $datastore->shouldReceive('put')->times(3)->withArgs(fn ($device, $measurement) => $measurement == 'bgp');

        (new BgpPeers)->poll($this->os(), $datastore);

        $peers = $this->device->bgppeers()->orderBy('bgpPeerIdentifier')->get();
        $this->assertSame(42, $peers[0]->bgpPeerInUpdates);
        $this->assertSame('idle', $peers[2]->bgpPeerState);

        $events = Eventlog::where('device_id', $this->device->device_id)->where('type', 'bgpPeer')->orderBy('reference')->pluck('message', 'reference');
        $this->assertCount(2, $events, 'An established session that kept counting is not logged');
        $this->assertStringStartsWith('BGP Session Flap: 10.0.0.2', $events['10.0.0.2']);
        $this->assertStringStartsWith('BGP Session Down: 10.0.0.3', $events['10.0.0.3']);
        $this->assertStringContainsString('Administrative Shutdown', $events['10.0.0.3']);
    }

    public function testPollingRecordsPrefixChanges(): void
    {
        $this->device->bgppeers()->create($this->peerAttributes('10.0.0.1'));
        $peers = ['' => [['ip' => '10.0.0.1', 'remote_as' => 65001]]];
        $this->fakeReader($peers, [['ip' => '10.0.0.1', 'afi' => 'ipv6', 'safi' => 'vpn', 'accepted' => 10, 'advertised' => 3]]);
        (new BgpPeers)->discover($this->os());

        $this->fakeReader($peers, [['ip' => '10.0.0.1', 'afi' => 'ipv6', 'safi' => 'vpn', 'accepted' => 15, 'advertised' => 3]]);
        $datastore = Mockery::mock(DataStorageInterface::class);
        $datastore->shouldReceive('put')->withArgs(fn ($device, $measurement) => $measurement == 'bgp');
        $datastore->shouldReceive('put')->once()->withArgs(fn ($device, $measurement, $tags, $fields) => $measurement == 'cbgp' && $tags['afi'] == 'ipv6' && $tags['safi'] == 'vpn' && $fields['AcceptedPrefixes'] == 15);

        (new BgpPeers)->poll($this->os(), $datastore);

        $family = $this->device->bgpPeersCbgp()->sole();
        $this->assertSame([15, 10, 5], [$family->AcceptedPrefixes, $family->AcceptedPrefixes_prev, $family->AcceptedPrefixes_delta]);
        $this->assertSame([3, 3, 0], [$family->AdvertisedPrefixes, $family->AdvertisedPrefixes_prev, $family->AdvertisedPrefixes_delta]);
    }

    public function testRemovedPeersAreDeleted(): void
    {
        $this->device->bgppeers()->create($this->peerAttributes('10.0.0.1'));
        $this->device->bgppeers()->create($this->peerAttributes('10.0.0.1', ['context_name' => 'gone']));
        $this->fakeReader(['' => [['ip' => '10.0.0.2', 'remote_as' => 65001]]], [['ip' => '10.0.0.2', 'afi' => 'ipv4', 'safi' => 'unicast']]);

        $module = new BgpPeers;
        $module->discover($this->os());

        $this->assertSame(['10.0.0.2'], $this->device->bgppeers()->pluck('bgpPeerIdentifier')->all());

        $this->assertSame(2, $module->cleanup($this->device));
        $this->assertFalse($module->dataExists($this->device));
    }

    /**
     * @param  array<string, array<int, array<string, mixed>>>  $peers  reader rows with an ip instead of an address, by context
     * @param  array<int, array<string, mixed>>  $families
     */
    private function fakeReader(array $peers, array $families = [], bool $reportsAllVrfs = false): FakeBgpReader
    {
        $reader = new FakeBgpReader($peers, $families, $reportsAllVrfs);
        $this->app->bind(BgpTableReader::class, fn () => $reader);

        return $reader;
    }

    private function os(): OS
    {
        $os = Mockery::mock(OS::class);
        $os->shouldReceive('getDevice')->andReturn($this->device);
        $os->shouldReceive('getDeviceArray')->andReturn($this->device->toArray());
        $os->shouldReceive('getDiscovery')->andReturn([]);

        /** @var OS $os */
        return $os;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function peerAttributes(string $ip, array $attributes = []): array
    {
        return array_merge([
            'context_name' => '',
            'bgpPeerIdentifier' => $ip,
            'bgpPeerRemoteAs' => 65001,
            'bgpPeerState' => 'established',
            'bgpPeerAdminStatus' => 'start',
            'bgpLocalAddr' => '0.0.0.0',
            'bgpPeerRemoteAddr' => $ip,
            'bgpPeerInUpdates' => 0,
            'bgpPeerOutUpdates' => 0,
            'bgpPeerInTotalMessages' => 0,
            'bgpPeerOutTotalMessages' => 0,
            'bgpPeerFsmEstablishedTime' => 0,
            'bgpPeerInUpdateElapsedTime' => 0,
            'astext' => 'AS65001',
        ], $attributes);
    }
}

class FakeBgpReader extends BgpTableReader
{
    /** @var string[] contexts that were read */
    public array $contexts = [];

    /**
     * @param  array<string, array<int, array<string, mixed>>>  $peers  rows with an ip instead of an address, by context
     * @param  array<int, array<string, mixed>>  $families
     */
    public function __construct(
        private readonly array $peers,
        private readonly array $families,
        private readonly bool $allVrfs,
    ) {
        parent::__construct();
    }

    public function reportsAllVrfs(): bool
    {
        return $this->allVrfs;
    }

    public function read(string $context): array
    {
        $this->contexts[] = $context;
        $withAddress = fn (array $row) => (isset($row['ip']) ? ['address' => IP::parse($row['ip'])] : []) + Arr::except($row, 'ip');

        return [
            'peers' => array_map($withAddress, $this->peers[$context] ?? []),
            'address_families' => array_map($withAddress, $this->families),
        ];
    }

    public function localAs(array $peers): ?int
    {
        return 65000;
    }
}
