<?php

/**
 * BgpPeerDiscoveryTest.php
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

namespace LibreNMS\Tests\Feature\Discovery;

use App\Models\BgpPeer;
use App\Models\BgpPeerCbgp;
use App\Models\Device;
use App\Models\Port;
use App\Models\Vrf;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\OS\Aos7;
use LibreNMS\OS\Cumulus;
use LibreNMS\OS\Generic;
use LibreNMS\OS\Junos;
use LibreNMS\OS\Shared\Cisco;
use LibreNMS\OS\Timos;
use LibreNMS\OS\Vrp;
use LibreNMS\Tests\DBTestCase;

final class BgpPeerDiscoveryTest extends DBTestCase
{
    use DatabaseTransactions;

    private Device $device;

    protected function setUp(): void
    {
        parent::setUp();

        $this->device = Device::factory()->create(['os' => 'generic']);
    }

    public function testBgp4MibIsReadInEachVrfContext(): void
    {
        $vrf = Vrf::factory()->for($this->device)->create(['vrf_name' => 'blue', 'context_name' => 'vrf-blue', 'context_verified' => true]);
        $os = FakeBgpGeneric::fake($this->device, [
            '' => ['bgpPeerRemoteAs' => ['192.0.2.1' => '65001']],
            'vrf-blue' => ['bgpPeerRemoteAs' => ['192.0.2.1' => '65002', '0.0.0.0' => '65003']],
        ]);

        $peers = $os->discoverBgpPeers();

        $this->assertSame([
            ['192.0.2.1', '', null, 65001],
            ['192.0.2.1', 'vrf-blue', $vrf->vrf_id, 65002],
        ], $peers->map(fn (BgpPeer $peer) => [$peer->bgpPeerIdentifier, $peer->context_name, $peer->vrf_id, $peer->bgpPeerRemoteAs])->all());
    }

    public function testValuesAreDecoded(): void
    {
        $os = FakeBgpGeneric::fake($this->device, ['' => [
            'bgpPeerRemoteAs' => ['192.0.2.1' => '-94967294', '192.0.2.2' => '65001', '192.0.2.3' => 'none'],
            'bgpPeerState' => ['192.0.2.1' => 'openSent', '192.0.2.2' => 'closed'],
            'bgpPeerLocalAddr' => ['192.0.2.1' => 'C0 00 02 FE', '192.0.2.2' => 'fe80::1'],
            'bgpPeerLastError' => ['192.0.2.1' => '04 0B ', '192.0.2.2' => ''],
            'bgpPeerInTotalMessages' => ['192.0.2.1' => (string) (2 ** 32 + 5)],
        ]]);

        [$first, $second] = $os->discoverBgpPeers()->all();

        $this->assertCount(2, $os->discoverBgpPeers(), 'Sessions without a remote AS are skipped');
        $this->assertSame([4200000002, 'opensent', '192.0.2.254', 4, 11, 5, '192.0.2.1'], [
            $first->bgpPeerRemoteAs, $first->bgpPeerState, $first->bgpLocalAddr, $first->bgpPeerLastErrorCode, $first->bgpPeerLastErrorSubCode, $first->bgpPeerInTotalMessages, $first->bgpPeerRemoteAddr,
        ]);
        $this->assertSame(['idle', 'fe80:0000:0000:0000:0000:0000:0000:0001', null], [$second->bgpPeerState, $second->bgpLocalAddr, $second->bgpPeerLastErrorCode]);
    }

    /**
     * Scoped and unnumbered sessions are identified by interface, so parallel links to one neighbor stay separate
     */
    public function testLinkLocalAndUnnumberedSessionsAreIdentifiedByInterface(): void
    {
        Port::factory()->for($this->device)->create(['ifIndex' => 27, 'ifName' => 'swp27']);
        Port::factory()->for($this->device)->create(['ifIndex' => 28, 'ifName' => 'swp28']);
        $vrf = Vrf::factory()->for($this->device)->create(['vrf_oid' => '254', 'vrf_name' => 'default']);
        $os = FakeBgpCumulus::fake($this->device, ['' => [
            'bgpPeerRemoteAs' => ['254.0.0.0.0.27' => '65001', '254.0.0.0.0.28' => '65001', '254.1.192.0.2.1' => '65002', '7.1.192.0.2.1' => '65002'],
            'bgpPeerRemoteAddr' => ['254.0.0.0.0.27' => '', '254.0.0.0.0.28' => 'FE 80 00 00 00 00 00 00 00 00 00 00 00 00 00 01', '254.1.192.0.2.1' => 'C0 00 02 01', '7.1.192.0.2.1' => 'C0 00 02 01'],
            'bgpPeerIfindex' => ['254.0.0.0.0.27' => '27', '254.0.0.0.0.28' => '28', '254.1.192.0.2.1' => '0'],
        ]]);

        $peers = $os->discoverBgpPeers();

        $this->assertSame([
            ['swp27', '', 27, $vrf->vrf_id],
            ['swp28', 'fe80:0000:0000:0000:0000:0000:0000:0001', 28, $vrf->vrf_id],
            ['192.0.2.1', '192.0.2.1', null, $vrf->vrf_id],
            ['192.0.2.1', '192.0.2.1', null, null],
        ], $peers->map(fn (BgpPeer $peer) => [$peer->bgpPeerIdentifier, $peer->bgpPeerRemoteAddr, $peer->bgpPeerIface, $peer->vrf_id])->all());
    }

    public function testInterfaceNamesAreResolved(): void
    {
        Port::factory()->for($this->device)->create(['ifIndex' => 7, 'ifName' => 'swp7']);
        $os = FakeBgpCumulus::fake($this->device, ['' => [
            'bgpPeerRemoteAs' => ['10.0.0.2' => '65001', '10.0.0.3' => '65001'],
            'bgpPeerIface' => ['10.0.0.2' => 'swp7', '10.0.0.3' => 'missing'],
            'bgpPeerLocalAddr' => ['10.0.0.3' => '10.9.9.9'],
        ]]);

        $this->assertSame([7, null], $os->discoverBgpPeers()->pluck('bgpPeerIface')->all());
        $this->assertContains(':CUMULUS-BGPUN-MIB::bgpPeerRemoteAs', $os->walked, 'CUMULUS-BGPUN-MIB is read when CUMULUS-BGPVRF-MIB has no sessions');
    }

    public function testLinkLocalZoneIsTheInterface(): void
    {
        $fe80 = '254.128.0.0.0.0.0.0.42.153.58.255.254.30.176.127';
        $os = FakeBgpCisco::fake($this->device, ['' => [
            'cbgpPeer2RemoteAs' => ["4.20.$fe80.0.0.15.161" => '65001', "4.20.$fe80.0.0.15.162" => '65001'],
        ]]);

        $this->assertSame(['4001', '4002'], $os->discoverBgpPeers()->pluck('bgpPeerIdentifier')->all());
    }

    public function testBgp4MibIsOnlyReadWhenTheVendorTableHasNoSessions(): void
    {
        $vendor = FakeBgpCisco::fake($this->device, ['' => [
            'cbgpPeer2RemoteAs' => ['1.4.192.0.2.1' => '65001'],
            'bgpPeerRemoteAs' => ['192.0.2.9' => '65009'],
        ]]);
        $this->assertSame(['192.0.2.1'], $vendor->discoverBgpPeers()->pluck('bgpPeerIdentifier')->all());
        $this->assertNotContains(':BGP4-MIB::bgpPeerRemoteAs', $vendor->walked);

        $fallback = FakeBgpCisco::fake($this->device, ['' => ['bgpPeerRemoteAs' => ['192.0.2.9' => '65009']]]);
        $this->assertSame(['192.0.2.9'], $fallback->discoverBgpPeers()->pluck('bgpPeerIdentifier')->all());
    }

    public function testNamesAreAddedToBgp4MibSessions(): void
    {
        $os = FakeBgpAos7::fake($this->device, ['' => [
            'alaBgpPeerName' => ['192.0.2.9' => 'peer nine', '192.0.2.8' => 'no session'],
            'bgpPeerRemoteAs' => ['192.0.2.9' => '65009'],
        ]]);

        $peers = $os->discoverBgpPeers();

        $this->assertSame([['192.0.2.9', 'peer nine']], $peers->map(fn (BgpPeer $peer) => [$peer->bgpPeerIdentifier, $peer->bgpPeerDescr])->all());
    }

    /**
     * Huawei reports a row per address family and keeps counters and descriptions in other tables
     */
    public function testHuaweiTablesAreMergedByInstanceAndAddress(): void
    {
        $vrf = Vrf::factory()->for($this->device)->create(['vrf_name' => 'red']);
        $os = FakeBgpVrp::fake($this->device, ['' => [
            'hwBgpPeerRemoteAs' => ['0.1.1.1.4.192.0.2.1' => '65001', '0.1.128.1.4.192.0.2.1' => '65001', '5.1.1.1.4.192.0.2.1' => '65002'],
            'hwBgpPeerVrfName' => ['0.1.1.1.4.192.0.2.1' => 'Public', '5.1.1.1.4.192.0.2.1' => 'red'],
            'hwBgpPeerState' => ['0.1.1.1.4.192.0.2.1' => 'established', '0.1.128.1.4.192.0.2.1' => 'idle', '5.1.1.1.4.192.0.2.1' => 'active'],
            'hwBgpPeerInUpdateMsgs' => ['0.0.4.192.0.2.1' => '10', '0.5.4.192.0.2.1' => '20', '0.9.4.192.0.2.9' => '30'],
            'hwBgpPeerSessionExtDescription' => ['0.1.4.192.0.2.1' => 'public peer', '5.1.4.192.0.2.9' => 'unknown peer'],
        ]]);

        $peers = $os->discoverBgpPeers();

        $this->assertSame([
            ['192.0.2.1', null, 65001, 'established', 10, 'public peer'],
            ['192.0.2.1', $vrf->vrf_id, 65002, 'active', 20, null],
        ], $peers->map(fn (BgpPeer $peer) => [$peer->bgpPeerIdentifier, $peer->vrf_id, $peer->bgpPeerRemoteAs, $peer->bgpPeerState, $peer->bgpPeerInUpdates, $peer->bgpPeerDescr])->all());
    }

    public function testJuniperAddressFamiliesAreIndexedByPeerIndex(): void
    {
        $os = FakeBgpJunos::fake($this->device, ['' => [
            'jnxBgpM2PeerRemoteAs' => ['0.1.192.0.2.254.1.192.0.2.1' => '65001', '0.1.192.0.2.254.1.192.0.2.2' => '65002'],
            'jnxBgpM2PeerIndex' => ['0.1.192.0.2.254.1.192.0.2.1' => '3', '0.1.192.0.2.254.1.192.0.2.2' => '4'],
            'jnxBgpM2PrefixInPrefixesAccepted' => ['3.1.1' => '20', '3.25.65' => '5', '4.2.128' => '7', '9.1.1' => '1'],
        ]]);

        $families = $os->discoverBgpAddressFamilies($os->discoverBgpPeers());

        $this->assertSame(
            ['192.0.2.1 ipv4 unicast 20', '192.0.2.1 l2vpn vpls 5', '192.0.2.2 ipv6 vpn 7'],
            $families->map(fn (BgpPeerCbgp $family) => "$family->bgpPeerIdentifier $family->afi $family->safi $family->AcceptedPrefixes")->all(),
        );
    }

    public function testTimosPrefixCountsArePerIpVersion(): void
    {
        $v4 = '1.1.4.192.0.2.1';
        $v6 = '1.2.16.32.1.13.184.0.0.0.0.0.0.0.0.0.0.0.1';
        $os = FakeBgpTimos::fake($this->device, ['' => [
            'tBgpPeerNgPeerAS4Byte' => [$v4 => '65001', $v6 => '65002'],
            'tBgpPeerNgShutdown' => [$v4 => 'true', $v6 => '2'],
            'tBgpPeerNgOperReceivedPrefixes' => [$v4 => '10', $v6 => '0'],
            'tBgpPeerNgOperV6ReceivedPrefixes' => [$v4 => '0', $v6 => '20'],
        ]]);

        $peers = $os->discoverBgpPeers();
        $families = $os->discoverBgpAddressFamilies($peers);

        $this->assertSame(['stop', 'start'], $peers->pluck('bgpPeerAdminStatus')->all());
        $this->assertSame(
            ['192.0.2.1 ipv4 10', '2001:db8::1 ipv6 20'],
            $families->map(fn (BgpPeerCbgp $family) => "$family->bgpPeerIdentifier $family->afi $family->AcceptedPrefixes")->all(),
        );
    }

    public function testCiscoWithdrawnPrefixesAreSubtractedFromAdvertised(): void
    {
        $os = FakeBgpCisco::fake($this->device, ['' => [
            'cbgpPeer2RemoteAs' => ['1.4.192.0.2.1' => '65001'],
            'cbgpPeer2AcceptedPrefixes' => ['1.4.192.0.2.1.1.1' => '5'],
            'cbgpPeer2AdvertisedPrefixes' => ['1.4.192.0.2.1.1.1' => '50'],
            'cbgpPeer2WithdrawnPrefixes' => ['1.4.192.0.2.1.1.1' => '8'],
            'cbgpPeerAcceptedPrefixes' => ['192.0.2.1.1.1' => '99'],
        ]]);

        $family = $os->discoverBgpAddressFamilies($os->discoverBgpPeers())->sole();

        $this->assertSame([5, 42, 0], [$family->AcceptedPrefixes, $family->AdvertisedPrefixes, $family->WithdrawnPrefixes]);
        $this->assertNotContains(':CISCO-BGP4-MIB::cbgpPeerAcceptedPrefixes', $os->walked, 'The older table is only read when cbgpPeer2 has no address families');
    }
}

/**
 * Serves walkBgpTable() from canned column data by context, column name and numeric index
 */
trait FakesBgpTables
{
    /** @var array<string, array<string, array<string, string>>> */
    private array $snmp = [];

    /** @var string[] context:MIB::first column of each walked table */
    public array $walked = [];

    /**
     * @param  array<string, array<string, array<string, string>>>  $snmp
     */
    public static function fake(Device $device, array $snmp): static
    {
        $array = $device->toArray();
        $os = new static($array);
        $os->snmp = $snmp;

        return $os;
    }

    protected function walkBgpTable(string $context, string $mib, array $columns): array
    {
        $this->walked[] = "$context:$mib::" . reset($columns);
        $data = $this->snmp[$context] ?? [];

        $rows = [];
        foreach (empty($data[reset($columns)]) ? [] : $columns as $modelColumn => $column) {
            foreach ($data[$column] ?? [] as $index => $value) {
                if ($value !== '') {
                    $rows[$index][$modelColumn] = $value;
                }
            }
        }

        return $rows;
    }
}

final class FakeBgpGeneric extends Generic
{
    use FakesBgpTables;
}

final class FakeBgpCisco extends Cisco
{
    use FakesBgpTables;
}

final class FakeBgpCumulus extends Cumulus
{
    use FakesBgpTables;
}

final class FakeBgpAos7 extends Aos7
{
    use FakesBgpTables;
}

final class FakeBgpVrp extends Vrp
{
    use FakesBgpTables;
}

final class FakeBgpJunos extends Junos
{
    use FakesBgpTables;
}

final class FakeBgpTimos extends Timos
{
    use FakesBgpTables;
}
