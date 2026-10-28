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
use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Models\BgpPeer;
use App\Models\BgpPeerCbgp;
use App\Models\Device;
use App\Models\Eventlog;
use App\Models\Vrf;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Data\Source\Snmp\RawSnmpResponse;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Exceptions\SnmpException;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\BgpAddressFamilyDiscovery;
use LibreNMS\Modules\BgpPeers;
use LibreNMS\OS;
use LibreNMS\Tests\DBTestCase;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;

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
        $os = $this->os([$this->peer('10.0.0.1')]);

        $module = new BgpPeers;
        $module->discover($os);
        $module->discover($os);

        $peer = $this->device->bgppeers()->sole();
        $this->assertSame($existing->bgpPeer_id, $peer->bgpPeer_id);
        $this->assertSame('Peer AS', $peer->astext);
        $this->assertSame(65000, $this->device->fresh()->bgpLocalAs);
        $this->assertTrue($module->dataExists($this->device));
    }

    public function testDiscoveryLeavesSessionStateToPolling(): void
    {
        $this->device->bgppeers()->create($this->peerAttributes('10.0.0.1', ['bgpPeerState' => 'established', 'bgpPeerInUpdates' => 10]));
        $os = $this->os([
            $this->peer('10.0.0.1', ['bgpPeerRemoteAs' => 65002, 'bgpPeerState' => 'idle', 'bgpPeerInUpdates' => 20]),
            $this->peer('10.0.0.2', ['bgpPeerState' => 'active']),
        ]);

        (new BgpPeers)->discover($os);

        $peers = $this->device->bgppeers()->orderBy('bgpPeerIdentifier')->get();
        $this->assertSame(['10.0.0.1', '10.0.0.2'], $peers->pluck('bgpPeerIdentifier')->all());
        $this->assertSame(65002, $peers[0]->bgpPeerRemoteAs, 'Discovery updates the peer AS');
        $this->assertSame('established', $peers[0]->bgpPeerState, 'Existing session state is only updated by polling');
        $this->assertSame(10, $peers[0]->bgpPeerInUpdates);
        $this->assertSame('active', $peers[1]->bgpPeerState, 'New sessions start with their current state');
        $this->assertSame(0, $peers[1]->bgpPeerInUpdates, 'Missing counters use defaults');
    }

    public function testSessionsAreKeptPerContext(): void
    {
        $vrf = $this->contextVrf('vrf-b', 'B');
        $os = $this->os([
            $this->peer('10.0.0.1'),
            $this->peer('10.0.0.1', ['bgpPeerRemoteAs' => 65002, 'context_name' => 'vrf-b', 'vrf_id' => $vrf->vrf_id]),
        ], [
            $this->family('10.0.0.1', ['AcceptedPrefixes' => 5]),
            $this->family('10.0.0.1', ['AcceptedPrefixes' => 7, 'context_name' => 'vrf-b']),
        ]);

        (new BgpPeers)->discover($os);

        $peers = $this->device->bgppeers()->orderBy('context_name')->get();
        $this->assertSame([['', 65001, null], ['vrf-b', 65002, $vrf->vrf_id]], $peers->map(fn (BgpPeer $peer) => [$peer->context_name, $peer->bgpPeerRemoteAs, $peer->vrf_id])->all());
        $this->assertSame([5, 7], $this->device->bgpPeersCbgp()->orderBy('context_name')->pluck('AcceptedPrefixes')->all(), 'Address families are kept per context');
    }

    public function testSkippedContextsKeepTheirPeers(): void
    {
        $this->contextVrf('vrf-b', 'B', ['context_enabled' => false]);
        $this->device->bgppeers()->create($this->peerAttributes('10.0.0.1', ['context_name' => 'vrf-b']));
        $os = $this->os([$this->peer('10.0.0.1')]);

        (new BgpPeers)->discover($os);

        $this->assertSame(['', 'vrf-b'], $this->device->bgppeers()->orderBy('context_name')->pluck('context_name')->all());

        $datastore = Mockery::mock(DataStorageInterface::class);
        $datastore->shouldReceive('put')->once()->withArgs(fn ($device, $measurement) => $measurement == 'bgp');

        (new BgpPeers)->poll($os, $datastore);
    }

    public function testUnansweredDiscoveryKeepsSessions(): void
    {
        $this->device->bgppeers()->create($this->peerAttributes('10.0.0.1'));
        $os = $this->os(exception: new SnmpException('Timeout'));

        (new BgpPeers)->discover($os);

        $this->assertSame(['10.0.0.1'], $this->device->bgppeers()->pluck('bgpPeerIdentifier')->all());
    }

    public function testOnlyNewSessionsWithAnAddressAreAutoDiscovered(): void
    {
        LibrenmsConfig::set('autodiscovery.bgp', true);
        $this->device->bgppeers()->create($this->peerAttributes('10.0.0.1'));
        $this->app->instance(AutoDiscoverDevice::class, Mockery::mock(AutoDiscoverDevice::class, function ($mock): void {
            $mock->shouldReceive('execute')->once()->withArgs(fn ($hostname) => in_array($hostname, ['10.0.0.2', gethostbyaddr('10.0.0.2')]));
        }));
        $os = $this->os([
            $this->peer('10.0.0.1'),
            $this->peer('10.0.0.2'),
            $this->peer('swp27', ['bgpPeerRemoteAddr' => '', 'bgpPeerIface' => 27]),
        ]);

        (new BgpPeers)->discover($os);

        $this->assertSame(['10.0.0.1', '10.0.0.2', 'swp27'], $this->device->bgppeers()->orderBy('bgpPeerIdentifier')->pluck('bgpPeerIdentifier')->all());
    }

    public function testAddressFamiliesOfUnknownPeersAreIgnored(): void
    {
        $os = $this->os([$this->peer('10.0.0.1')], [
            $this->family('10.0.0.1', ['AcceptedPrefixes' => 5]),
            $this->family('10.0.0.1', ['AcceptedPrefixes' => 9]),
            $this->family('10.0.0.7', ['AcceptedPrefixes' => 5]),
            $this->family('10.0.0.1', ['AcceptedPrefixes' => 5, 'context_name' => 'vrf-b']),
        ]);

        (new BgpPeers)->discover($os);

        $family = $this->device->bgpPeersCbgp()->sole();
        $this->assertSame(['10.0.0.1', 'ipv4', 'unicast', 5, 0], [$family->bgpPeerIdentifier, $family->afi, $family->safi, $family->AcceptedPrefixes, $family->AcceptedPrefixes_delta], 'The first family reported wins');
    }

    public function testEmptyAddressFamilyWalkKeepsExistingFamilies(): void
    {
        $peers = [$this->peer('10.0.0.1')];
        (new BgpPeers)->discover($this->os($peers, [$this->family('10.0.0.1', ['AcceptedPrefixes' => 5])]));

        (new BgpPeers)->discover($this->os($peers));
        $this->assertSame(5, $this->device->bgpPeersCbgp()->sole()->AcceptedPrefixes);

        (new BgpPeers)->discover($this->os());
        $this->assertFalse($this->device->bgpPeersCbgp()->exists(), 'Families are removed with their sessions');
    }

    public function testFamiliesAreRemovedWhenTheOsHasNoAddressFamilies(): void
    {
        $this->device->bgpPeersCbgp()->save((new BgpPeerCbgp)->forceFill($this->family('10.0.0.1') + BgpPeerCbgp::emptyCounters()));

        (new BgpPeers)->discover($this->os([$this->peer('10.0.0.1')], null));

        $this->assertFalse($this->device->bgpPeersCbgp()->exists());
    }

    public function testPollingUpdatesSessionsAndLogsStateChanges(): void
    {
        $this->device->bgppeers()->create($this->peerAttributes('10.0.0.1', ['bgpPeerState' => 'established', 'bgpPeerFsmEstablishedTime' => 500]));
        $this->device->bgppeers()->create($this->peerAttributes('10.0.0.2', ['bgpPeerState' => 'established', 'bgpPeerFsmEstablishedTime' => 500]));
        $this->device->bgppeers()->create($this->peerAttributes('10.0.0.3', ['bgpPeerState' => 'established', 'bgpPeerFsmEstablishedTime' => 500]));
        $this->device->bgppeers()->create($this->peerAttributes('10.0.0.4'));
        $os = $this->os([
            $this->peer('10.0.0.1', ['bgpPeerState' => 'established', 'bgpPeerFsmEstablishedTime' => 800, 'bgpPeerInUpdates' => 42]),
            $this->peer('10.0.0.2', ['bgpPeerState' => 'established', 'bgpPeerFsmEstablishedTime' => 5]),
            $this->peer('10.0.0.3', ['bgpPeerState' => 'idle', 'bgpPeerFsmEstablishedTime' => 5, 'bgpPeerLastErrorCode' => 6, 'bgpPeerLastErrorSubCode' => 2]),
            $this->peer('10.0.0.5'),
        ]);

        $datastore = Mockery::mock(DataStorageInterface::class);
        $datastore->shouldReceive('put')->times(3)->withArgs(fn ($device, $measurement) => $measurement == 'bgp');

        (new BgpPeers)->poll($os, $datastore);

        $peers = $this->device->bgppeers()->orderBy('bgpPeerIdentifier')->get();
        $this->assertSame(['10.0.0.1', '10.0.0.2', '10.0.0.3', '10.0.0.4'], $peers->pluck('bgpPeerIdentifier')->all(), 'Polling does not add or remove sessions');
        $this->assertSame(42, $peers[0]->bgpPeerInUpdates);
        $this->assertSame('idle', $peers[2]->bgpPeerState);

        $events = Eventlog::where('device_id', $this->device->device_id)->where('type', 'bgpPeer')->orderBy('reference')->pluck('message', 'reference');
        $this->assertCount(2, $events, 'An established session that kept counting is not logged');
        $this->assertStringStartsWith('BGP Session Flap: 10.0.0.2', $events['10.0.0.2']);
        $this->assertStringStartsWith('BGP Session Down: 10.0.0.3', $events['10.0.0.3']);
        $this->assertStringContainsString('Administrative Shutdown', $events['10.0.0.3']);
    }

    /**
     * @return array<string, array{?string, ?string, ?int, ?int}>
     */
    public static function timosMessageCounters(): array
    {
        return [
            'normal' => ['123', '456', 123, 456],
            'Counter32 limits' => ['4294967295', '0', 4294967295, 0],
            'missing receive' => [null, '456', null, 456],
            'missing send' => ['123', null, 123, null],
            'unsupported counters' => [null, null, null, null],
            'malformed counters' => ['123 abc', 'invalid', null, null],
        ];
    }

    #[DataProvider('timosMessageCounters')]
    public function testTimosTotalMessages(?string $received, ?string $sent, ?int $expectedReceived, ?int $expectedSent): void
    {
        $this->device->update(['os' => 'timos']);
        $index = '1.1.4.192.0.2.1';
        $values = [
            'tBgpPeerNgPeerAS4Byte' => '65001',
            'tBgpPeerNgConnState' => 'established',
            'tBgpPeerNgShutdown' => '2',
            'tBgpPeerFsmEstablishedTime' => '100',
            'tBgpPeerNgOperInUpdates' => '7',
            'tBgpPeerNgOperOutUpdates' => '9',
            'tBgpPeerNgOperInTotalMessages' => $received,
            'tBgpPeerNgOperOutTotalMessages' => $sent,
            'tBgpPeerNgOperMsgOctetsRcvd' => '18446744071926153491',
            'tBgpPeerNgOperMsgOctetsSent' => '18446744073709551615',
        ];
        $walked = [];
        $backend = Mockery::mock(SnmpBackendInterface::class);
        $backend->shouldReceive('walk')
            ->andReturnUsing(function (string $target, string $oid) use ($values, $index, &$walked) {
                $walked[] = $oid;
                $column = explode('::', $oid)[1];

                return new RawSnmpResponse(isset($values[$column]) ? "$column.$index = {$values[$column]}" : '');
            });
        $backend->shouldReceive('get')
            ->andReturn(new RawSnmpResponse('BGP4-MIB::bgpLocalAs.0 = 65000'));
        $this->app->instance(SnmpBackendInterface::class, $backend);

        try {
            DeviceCache::setPrimary($this->device->device_id);
            $deviceArray = $this->device->toArray();
            $os = OS::make($deviceArray);
            $module = new BgpPeers;
            $module->discover($os);

            $peer = $this->device->bgppeers()->sole();
            $this->assertSame([$expectedReceived ?? 0, $expectedSent ?? 0], [$peer->bgpPeerInTotalMessages, $peer->bgpPeerOutTotalMessages]);
            $peer->update(['bgpPeerInTotalMessages' => 11, 'bgpPeerOutTotalMessages' => 22]);

            $datastore = Mockery::mock(DataStorageInterface::class);
            $datastore->shouldReceive('put')->once()->withArgs(function ($device, $measurement, $tags, $fields) use ($expectedReceived, $expectedSent) {
                $this->assertSame('bgp', $measurement);
                $this->assertSame($expectedReceived ?? 11, $fields['bgpPeerInTotal']);
                $this->assertSame($expectedSent ?? 22, $fields['bgpPeerOutTotal']);
                $this->assertSame([7, 9, 100], [$fields['bgpPeerInUpdates'], $fields['bgpPeerOutUpdates'], $fields['bgpPeerEstablished']]);

                return true;
            });
            $module->poll($os, $datastore);

            $peer->refresh();
            $this->assertSame([$expectedReceived ?? 11, $expectedSent ?? 22], [$peer->bgpPeerInTotalMessages, $peer->bgpPeerOutTotalMessages]);
            $this->assertContains('TIMETRA-BGP-MIB::tBgpPeerNgOperInTotalMessages', $walked);
            $this->assertContains('TIMETRA-BGP-MIB::tBgpPeerNgOperOutTotalMessages', $walked);
            $this->assertNotContains('TIMETRA-BGP-MIB::tBgpPeerNgOperMsgOctetsRcvd', $walked);
            $this->assertNotContains('TIMETRA-BGP-MIB::tBgpPeerNgOperMsgOctetsSent', $walked);
        } finally {
            DeviceCache::flush();
        }
    }

    public function testPollingRecordsPrefixChanges(): void
    {
        $peers = [$this->peer('10.0.0.1')];
        (new BgpPeers)->discover($this->os($peers, [$this->family('10.0.0.1', ['afi' => 'ipv6', 'safi' => 'vpn', 'AcceptedPrefixes' => 10, 'AdvertisedPrefixes' => 3])]));

        $datastore = Mockery::mock(DataStorageInterface::class);
        $datastore->shouldReceive('put')->withArgs(fn ($device, $measurement) => $measurement == 'bgp');
        $datastore->shouldReceive('put')->once()->withArgs(fn ($device, $measurement, $tags, $fields) => $measurement == 'cbgp' && $tags['afi'] == 'ipv6' && $tags['safi'] == 'vpn' && $fields['AcceptedPrefixes'] == 15);

        (new BgpPeers)->poll($this->os($peers, [$this->family('10.0.0.1', ['afi' => 'ipv6', 'safi' => 'vpn', 'AcceptedPrefixes' => 15, 'AdvertisedPrefixes' => 3])]), $datastore);

        $family = $this->device->bgpPeersCbgp()->sole();
        $this->assertSame([15, 10, 5], [$family->AcceptedPrefixes, $family->AcceptedPrefixes_prev, $family->AcceptedPrefixes_delta]);
        $this->assertSame([3, 3, 0], [$family->AdvertisedPrefixes, $family->AdvertisedPrefixes_prev, $family->AdvertisedPrefixes_delta]);
    }

    public function testRemovedPeersAreDeleted(): void
    {
        $this->device->bgppeers()->create($this->peerAttributes('10.0.0.1'));
        $this->device->bgppeers()->create($this->peerAttributes('10.0.0.1', ['context_name' => 'gone']));
        $os = $this->os([$this->peer('10.0.0.2')], [$this->family('10.0.0.2')]);

        $module = new BgpPeers;
        $module->discover($os);

        $this->assertSame(['10.0.0.2'], $this->device->bgppeers()->pluck('bgpPeerIdentifier')->all());

        $this->assertSame(2, $module->cleanup($this->device));
        $this->assertFalse($module->dataExists($this->device));
    }

    /**
     * An OS that reports the given sessions, new models on each call like a real walk
     *
     * @param  array<int, array<string, mixed>>  $peers  BgpPeer attributes
     * @param  array<int, array<string, mixed>>|null  $families  BgpPeerCbgp attributes, null for an OS without address families
     */
    private function os(array $peers = [], ?array $families = [], ?SnmpException $exception = null): OS
    {
        $os = $families === null ? Mockery::mock(OS::class) : Mockery::mock(OS::class, BgpAddressFamilyDiscovery::class);
        $os->shouldReceive('getDevice')->andReturn($this->device);
        $os->shouldReceive('getDeviceArray')->andReturn($this->device->toArray());
        $os->shouldReceive('discoverBgpLocalAs')->andReturn(65000);
        $os->shouldReceive('discoverBgpPeers')->andReturnUsing(fn () => $exception ? throw $exception : collect($peers)->map(fn (array $peer) => new BgpPeer($peer)));
        if ($families !== null) {
            $os->shouldReceive('discoverBgpAddressFamilies')->andReturnUsing(fn () => collect($families)->map(fn (array $family) => new BgpPeerCbgp($family)));
        }

        /** @var OS $os */
        return $os;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function peer(string $identifier, array $attributes = []): array
    {
        return array_merge(['bgpPeerIdentifier' => $identifier, 'bgpPeerRemoteAddr' => $identifier, 'bgpPeerRemoteAs' => 65001, 'context_name' => ''], $attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function family(string $identifier, array $attributes = []): array
    {
        return array_merge(['bgpPeerIdentifier' => $identifier, 'afi' => 'ipv4', 'safi' => 'unicast', 'context_name' => ''], $attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function contextVrf(string $context, string $name, array $attributes = []): Vrf
    {
        return Vrf::factory()->for($this->device)->create(array_merge([
            'vrf_name' => $name,
            'context_name' => $context,
            'context_verified' => true,
        ], $attributes));
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
