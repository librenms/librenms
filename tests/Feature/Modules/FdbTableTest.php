<?php

namespace LibreNMS\Tests\Feature\Modules;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Port;
use App\Models\PortsFdb;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use LibreNMS\Data\Source\Snmp\SnmpQueryBuilder;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Modules\FdbTable;
use LibreNMS\OS;
use LibreNMS\Tests\DBTestCase;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;

final class FdbTableTest extends DBTestCase
{
    use DatabaseTransactions;

    private const MAC_A = '00112233aa01';
    private const MAC_B = '00112233aa02';

    private Device $device;

    protected function setUp(): void
    {
        parent::setUp();

        $this->device = Device::factory()->create(['os' => 'generic']);
    }

    public function testNewEntriesAreCreatedWithTheVlanNumber(): void
    {
        $port = $this->port(10);

        $this->discover(fn () => [$this->fdb(self::MAC_A, $port->port_id, 100)]);

        $entry = $this->device->portsFdb()->sole();
        $this->assertSame(self::MAC_A, $entry->mac_address);
        $this->assertSame($port->port_id, $entry->port_id);
        $this->assertSame(100, $entry->vlan);
    }

    public function testEntriesNoLongerLearnedAreKeptWithTheirLastSeenTime(): void
    {
        $port = $this->port(10);
        $this->travelTo(Carbon::parse('2026-01-01 00:00:00'));
        $this->discover(fn () => [$this->fdb(self::MAC_A, $port->port_id), $this->fdb(self::MAC_B, $port->port_id)]);

        $this->travelTo(Carbon::parse('2026-01-02 00:00:00'));
        $this->discover(fn () => [$this->fdb(self::MAC_A, $port->port_id)]);

        $entries = $this->device->portsFdb()->get()->keyBy('mac_address');
        $this->assertCount(2, $entries, 'ports_fdb_purge is responsible for removing old entries');
        $this->assertSame('2026-01-02 00:00:00', $entries[self::MAC_A]->updated_at->toDateTimeString());
        $this->assertSame('2026-01-01 00:00:00', $entries[self::MAC_A]->created_at->toDateTimeString());
        $this->assertSame('2026-01-01 00:00:00', $entries[self::MAC_B]->updated_at->toDateTimeString());
    }

    public function testNothingChangesWhenTheDeviceReturnsNoEntries(): void
    {
        $port = $this->port(10);
        $this->discover(fn () => [$this->fdb(self::MAC_A, $port->port_id)]);

        $this->discover(fn () => []);

        $this->assertSame(1, $this->device->portsFdb()->count());
    }

    public function testMovedMacUpdatesThePortInPlace(): void
    {
        $from = $this->port(10);
        $to = $this->port(11);
        $this->discover(fn () => [$this->fdb(self::MAC_A, $from->port_id)]);
        $id = $this->device->portsFdb()->sole()->ports_fdb_id;

        $this->discover(fn () => [$this->fdb(self::MAC_A, $to->port_id)]);

        $entry = $this->device->portsFdb()->sole();
        $this->assertSame($id, $entry->ports_fdb_id);
        $this->assertSame($to->port_id, $entry->port_id);
    }

    public function testUnknownPortDoesNotOverwriteKnownPort(): void
    {
        $port = $this->port(10);
        $this->discover(fn () => [$this->fdb(self::MAC_A, $port->port_id)]);

        $this->discover(fn () => [$this->fdb(self::MAC_A, 0)]);

        $this->assertSame($port->port_id, $this->device->portsFdb()->sole()->port_id);
    }

    public function testUnknownPortIsStoredForNewEntries(): void
    {
        $this->discover(fn () => [$this->fdb(self::MAC_A, 0)]);

        $this->assertSame(0, $this->device->portsFdb()->sole()->port_id);
    }

    public function testSameMacInDifferentVlansAreSeparateEntries(): void
    {
        $port = $this->port(10);

        $this->discover(fn () => [
            $this->fdb(self::MAC_A, $port->port_id, 100),
            $this->fdb(self::MAC_A, $port->port_id, 200),
        ]);

        $this->assertSame(2, $this->device->portsFdb()->count());
    }

    public function testInvalidMacsAreDropped(): void
    {
        $port = $this->port(10);

        $this->discover(fn () => [$this->fdb('', $port->port_id), $this->fdb('0011', $port->port_id), $this->fdb(self::MAC_A, $port->port_id)]);

        $this->assertSame(self::MAC_A, $this->device->portsFdb()->sole()->mac_address);
    }

    public function testDuplicateRowsAreCollapsed(): void
    {
        $port = $this->port(10);
        foreach ([1, 2] as $ignored) {
            PortsFdb::factory()->create(['device_id' => $this->device->device_id, 'port_id' => $port->port_id, 'mac_address' => self::MAC_A, 'vlan' => 0]);
        }

        $this->discover(fn () => [$this->fdb(self::MAC_A, $port->port_id)]);

        $this->assertSame(1, $this->device->portsFdb()->count());
    }

    public function testOtherDevicesAreNotTouched(): void
    {
        $other = Device::factory()->create();
        PortsFdb::factory()->create(['device_id' => $other->device_id, 'port_id' => 1, 'mac_address' => self::MAC_A, 'vlan' => 0]);
        $port = $this->port(10);

        $this->discover(fn () => [$this->fdb(self::MAC_A, $port->port_id)]);

        $this->assertSame(1, $other->portsFdb()->count());
        $this->assertSame(1, $this->device->portsFdb()->count());
    }

    public function testDumpMatchesTestDataFormat(): void
    {
        $port = $this->port(10);
        $this->discover(fn () => [$this->fdb(self::MAC_B, $port->port_id, 100), $this->fdb(self::MAC_A, $port->port_id, 100), $this->fdb('00112233aa03', 0)]);

        $module = new FdbTable;

        $this->assertNull($module->dump($this->device, 'poller'));
        $this->assertSame([
            ['mac_address' => '00112233aa03', 'vlan' => 0, 'ifIndex' => null],
            ['mac_address' => self::MAC_A, 'vlan' => 100, 'ifIndex' => 10],
            ['mac_address' => self::MAC_B, 'vlan' => 100, 'ifIndex' => 10],
        ], $module->dump($this->device, 'discovery')['ports_fdb']->toArray());
    }

    public function testDataExistsAndCleanup(): void
    {
        $module = new FdbTable;
        $this->assertFalse($module->dataExists($this->device));

        $port = $this->port(10);
        $this->discover(fn () => [$this->fdb(self::MAC_A, $port->port_id)]);
        $this->assertTrue($module->dataExists($this->device));

        $this->assertSame(1, $module->cleanup($this->device));
        $this->assertFalse($module->dataExists($this->device));
    }

    public function testModuleOnlyRunsDuringDiscovery(): void
    {
        $this->assertFalse((new FdbTable)->shouldPoll(Mockery::mock(OS::class)));
    }

    public function testQBridgeMapsFdbIdsToVlans(): void
    {
        $this->port(10);
        $this->port(11);

        $fdb = $this->osFdb('generic', [
            '|Q-BRIDGE-MIB::dot1qTpFdbPort' => [
                'Q-BRIDGE-MIB::dot1qTpFdbPort[7][0:11:22:33:aa:1]' => '1', // fdb id 7 is VLAN 100
                'Q-BRIDGE-MIB::dot1qTpFdbPort[100][0:11:22:33:aa:2]' => '2', // no fdb id, the index is the VLAN
                'Q-BRIDGE-MIB::dot1qTpFdbPort[100][0:11:22:33:aa:3]' => '0', // the switch itself
            ],
            '|Q-BRIDGE-MIB::dot1qVlanFdbId' => [
                'Q-BRIDGE-MIB::dot1qVlanFdbId[0][100]' => '7',
                'Q-BRIDGE-MIB::dot1qVlanFdbId[1][100]' => '7',
            ],
            '|BRIDGE-MIB::dot1dBasePortIfIndex' => ['BRIDGE-MIB::dot1dBasePortIfIndex[1]' => '10', 'BRIDGE-MIB::dot1dBasePortIfIndex[2]' => '11'],
        ]);

        $this->assertSame([
            [self::MAC_A, $this->portId(10), 100],
            [self::MAC_B, $this->portId(11), 100],
        ], $fdb);
    }

    public function testQBridgeReportsSharedFdbIdsAsUnknownVlan(): void
    {
        $this->port(10);

        $fdb = $this->osFdb('generic', [
            '|Q-BRIDGE-MIB::dot1qTpFdbPort' => [
                'Q-BRIDGE-MIB::dot1qTpFdbPort[7][0:11:22:33:aa:1]' => '1',
            ],
            '|Q-BRIDGE-MIB::dot1qVlanFdbId' => [
                'Q-BRIDGE-MIB::dot1qVlanFdbId[0][100]' => '7',
                'Q-BRIDGE-MIB::dot1qVlanFdbId[0][200]' => '7',
                'Q-BRIDGE-MIB::dot1qVlanFdbId[0][300]' => '7',
                'Q-BRIDGE-MIB::dot1qVlanFdbId[1][100]' => '7',
            ],
            '|BRIDGE-MIB::dot1dBasePortIfIndex' => ['BRIDGE-MIB::dot1dBasePortIfIndex[1]' => '10'],
        ]);

        $this->assertSame([[self::MAC_A, $this->portId(10), 0]], $fdb);
    }

    public function testBridgeMibIsUsedWithoutQBridgeAndHasNoVlan(): void
    {
        $this->port(10);

        $fdb = $this->osFdb('generic', [
            '|BRIDGE-MIB::dot1dTpFdbPort' => ['BRIDGE-MIB::dot1dTpFdbPort[0:11:22:33:aa:1]' => '1'],
            '|BRIDGE-MIB::dot1dBasePortIfIndex' => ['BRIDGE-MIB::dot1dBasePortIfIndex[1]' => '10'],
        ]);

        $this->assertSame([[self::MAC_A, $this->portId(10), 0]], $fdb);
    }

    public function testBridgePortIsAssumedToBeIfIndexWhenUnmappedAndUnknownPortsAreKept(): void
    {
        $this->port(10);

        $fdb = $this->osFdb('generic', [
            '|BRIDGE-MIB::dot1dTpFdbPort' => [
                'BRIDGE-MIB::dot1dTpFdbPort[0:11:22:33:aa:1]' => '10',
                'BRIDGE-MIB::dot1dTpFdbPort[0:11:22:33:aa:2]' => '99',
            ],
        ]);

        $this->assertSame([[self::MAC_A, $this->portId(10), 0], [self::MAC_B, 0, 0]], $fdb);
    }

    public function testLengthPrefixedMacIndexesAreFixed(): void
    {
        $this->port(10);

        $fdb = $this->osFdb('generic', [
            '|BRIDGE-MIB::dot1dTpFdbPort' => ['BRIDGE-MIB::dot1dTpFdbPort[6:0:a:f7:ec:d1].97' => '1'],
            '|BRIDGE-MIB::dot1dBasePortIfIndex' => ['BRIDGE-MIB::dot1dBasePortIfIndex[1]' => '10'],
        ]);

        $this->assertSame([['000af7ecd161', $this->portId(10), 0]], $fdb);
    }

    public function testCiscoWalksEachOperationalVlanContext(): void
    {
        LibrenmsConfig::set('os.ios.ignore_vlans', [300]);
        $this->port(10);

        $fdb = $this->osFdb('ios', [
            '|CISCO-VTP-MIB::vtpVlanState' => [
                'CISCO-VTP-MIB::vtpVlanState[1][100]' => '1',
                'CISCO-VTP-MIB::vtpVlanState[1][200]' => '2', // suspended
                'CISCO-VTP-MIB::vtpVlanState[1][300]' => '1', // ignored
                'CISCO-VTP-MIB::vtpVlanState[1][1002]' => '1', // reserved
            ],
            '100|BRIDGE-MIB::dot1dTpFdbPort' => ['BRIDGE-MIB::dot1dTpFdbPort[0:11:22:33:aa:1]' => '1', 'BRIDGE-MIB::dot1dTpFdbPort[0:11:22:33:aa:2]' => '2'],
            '100|BRIDGE-MIB::dot1dBasePortIfIndex' => ['BRIDGE-MIB::dot1dBasePortIfIndex[1]' => '10'],
            '200|BRIDGE-MIB::dot1dTpFdbPort' => ['BRIDGE-MIB::dot1dTpFdbPort[0:11:22:33:bb:1]' => '1'],
            '300|BRIDGE-MIB::dot1dTpFdbPort' => ['BRIDGE-MIB::dot1dTpFdbPort[0:11:22:33:cc:1]' => '1'],
            '1002|BRIDGE-MIB::dot1dTpFdbPort' => ['BRIDGE-MIB::dot1dTpFdbPort[0:11:22:33:dd:1]' => '1'],
        ]);

        // MAC_B has no known bridge port in this context
        $this->assertSame([[self::MAC_A, $this->portId(10), 100]], $fdb);
    }

    public function testCiscoDoesNotCreateVlans(): void
    {
        $this->port(10);

        $fdb = $this->osFdb('ios', [
            '|CISCO-VTP-MIB::vtpVlanState' => ['CISCO-VTP-MIB::vtpVlanState[1][100]' => '1'],
            '100|BRIDGE-MIB::dot1dTpFdbPort' => ['BRIDGE-MIB::dot1dTpFdbPort[0:11:22:33:aa:1]' => '1'],
            '100|BRIDGE-MIB::dot1dBasePortIfIndex' => ['BRIDGE-MIB::dot1dBasePortIfIndex[1]' => '10'],
        ]);

        $this->assertSame([[self::MAC_A, $this->portId(10), 100]], $fdb);
        $this->assertSame(0, $this->device->vlans()->count());
    }

    /**
     * @param  array<string, array<string, string>>  $vendorData
     */
    #[DataProvider('vendorMibs')]
    public function testVendorMibsOverrideStandardMibs(string $os, array $vendorData): void
    {
        $this->port(10);

        $standard = [
            '|Q-BRIDGE-MIB::dot1qTpFdbPort' => ['Q-BRIDGE-MIB::dot1qTpFdbPort[100][0:11:22:33:ff:ff]' => '10'],
        ];

        $this->assertSame([[self::MAC_A, $this->portId(10), 100]], $this->osFdb($os, $vendorData + $standard));
    }

    /**
     * @param  array<string, array<string, string>>  $vendorData
     */
    #[DataProvider('vendorMibs')]
    public function testStandardMibsAreUsedWhenVendorMibsHaveNoData(string $os, array $vendorData): void
    {
        $this->port(10);

        $this->assertSame([['00112233ffff', $this->portId(10), 100]], $this->osFdb($os, [
            '|Q-BRIDGE-MIB::dot1qTpFdbPort' => ['Q-BRIDGE-MIB::dot1qTpFdbPort[100][0:11:22:33:ff:ff]' => '10'],
        ]));
    }

    /**
     * @return array<string, array{string, array<string, array<string, string>>}>
     */
    public static function vendorMibs(): array
    {
        return [
            'aos6' => ['aos6', [
                '|ALCATEL-IND1-MAC-ADDRESS-MIB::slMacAddressDisposition' => ['ALCATEL-IND1-MAC-ADDRESS-MIB::slMacAddressDisposition[10][100][0:11:22:33:aa:1]' => '1'],
            ]],
            'aos7' => ['aos7', [
                '|ALCATEL-IND1-MAC-ADDRESS-MIB::slMacAddressGblManagement' => ['ALCATEL-IND1-MAC-ADDRESS-MIB::slMacAddressGblManagement[1][1][1][100][0][0:11:22:33:aa:1]' => '1'],
                '|BRIDGE-MIB::dot1dBasePortIfIndex' => ['BRIDGE-MIB::dot1dBasePortIfIndex[1]' => '10'],
            ]],
            'timos' => ['timos', [
                '|TIMETRA-SERV-MIB::tlsFdbLocale' => ['TIMETRA-SERV-MIB::tlsFdbLocale[5][0:11:22:33:aa:1]' => '1'],
                '|TIMETRA-SERV-MIB::tlsFdbPortId' => ['TIMETRA-SERV-MIB::tlsFdbPortId[5][0:11:22:33:aa:1]' => '10'],
                '|TIMETRA-SERV-MIB::tlsFdbEncapValue' => ['TIMETRA-SERV-MIB::tlsFdbEncapValue[5][0:11:22:33:aa:1]' => '100'],
            ]],
            'vrp' => ['vrp', [
                '|HUAWEI-L2MAM-MIB::hwDynFdbPort' => ['HUAWEI-L2MAM-MIB::hwDynFdbPort[0:11:22:33:aa:1][100]["0"]' => '10'],
            ]],
            'cisco' => ['ios', [
                '|CISCO-VTP-MIB::vtpVlanState' => ['CISCO-VTP-MIB::vtpVlanState[1][100]' => '1'],
                '100|BRIDGE-MIB::dot1dTpFdbPort' => ['BRIDGE-MIB::dot1dTpFdbPort[0:11:22:33:aa:1]' => '1'],
                '100|BRIDGE-MIB::dot1dBasePortIfIndex' => ['BRIDGE-MIB::dot1dBasePortIfIndex[1]' => '10'],
            ]],
        ];
    }

    public function testEdgeswitchBindingsAddToTheStandardFdb(): void
    {
        $this->port(10);
        $this->port(11);

        $fdb = $this->osFdb('edgeswitch', [
            '|EdgeSwitch-SWITCHING-MIB::agentDynamicDsBindingTable' => [
                'EdgeSwitch-SWITCHING-MIB::agentDynamicDsBindingIfIndex[0:11:22:33:aa:1]' => '10',
                'EdgeSwitch-SWITCHING-MIB::agentDynamicDsBindingVlanId[0:11:22:33:aa:1]' => '100',
                'EdgeSwitch-SWITCHING-MIB::agentDynamicDsBindingIfIndex[0:11:22:33:aa:2]' => '10',
                'EdgeSwitch-SWITCHING-MIB::agentDynamicDsBindingVlanId[0:11:22:33:aa:2]' => '100',
            ],
            '|Q-BRIDGE-MIB::dot1qTpFdbPort' => [
                'Q-BRIDGE-MIB::dot1qTpFdbPort[100][0:11:22:33:aa:2]' => '11',
                'Q-BRIDGE-MIB::dot1qTpFdbPort[100][0:11:22:33:ff:ff]' => '11',
            ],
        ]);

        // the order matters, the module keeps the last entry for a key so the standard FDB wins
        $this->assertSame([
            [self::MAC_A, $this->portId(10), 100],
            [self::MAC_B, $this->portId(10), 100],
            [self::MAC_B, $this->portId(11), 100],
            ['00112233ffff', $this->portId(11), 100],
        ], $fdb);
    }

    public function testEdgeswitchUsesTheStandardFdbWithoutBindings(): void
    {
        $this->port(10);

        $this->assertSame([[self::MAC_A, $this->portId(10), 100]], $this->osFdb('edgeswitch', [
            '|Q-BRIDGE-MIB::dot1qTpFdbPort' => ['Q-BRIDGE-MIB::dot1qTpFdbPort[100][0:11:22:33:aa:1]' => '10'],
        ]));
    }

    public function testVrpStickyTableDoesNotRemoveDynamicEntries(): void
    {
        $this->port(10);

        $fdb = $this->osFdb('vrp', [
            '|HUAWEI-L2MAM-MIB::hwDynFdbPort' => ['HUAWEI-L2MAM-MIB::hwDynFdbPort[0:11:22:33:aa:1][100]["0"]' => '10'],
            '|HUAWEI-L2MAM-MIB::hwCfgMacAddrQueryIfIndex' => ['HUAWEI-L2MAM-MIB::hwCfgMacAddrQueryIfIndex[100][0][0][0][0:11:22:33:aa:1][other][0][0][0][0][0]' => '10'],
        ]);

        $this->assertSame([[self::MAC_A, $this->portId(10), 100]], $fdb);
    }

    public function testJetstreamDoesNotTreatBridgePortsAsIfIndexes(): void
    {
        $this->port(1); // Vlan-interface1, dot1dBasePortIfIndex maps bridge port 1 to it on real devices

        $fdb = $this->osFdb('jetstream', [
            '|Q-BRIDGE-MIB::dot1qTpFdbPort' => ['Q-BRIDGE-MIB::dot1qTpFdbPort[100][0:11:22:33:aa:1]' => '1'],
            '|BRIDGE-MIB::dot1dBasePortIfIndex' => ['BRIDGE-MIB::dot1dBasePortIfIndex[1]' => '1'],
        ]);

        $this->assertSame([[self::MAC_A, 0, 100]], $fdb);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function rowIndexedOs(): array
    {
        return ['netonix' => ['netonix'], 'fortiswitch' => ['fortiswitch']];
    }

    #[DataProvider('rowIndexedOs')]
    public function testRowIndexedOsFallsBackToStandardMibs(string $os): void
    {
        $this->port(10);

        $fdb = $this->osFdb($os, [
            '|Q-BRIDGE-MIB::dot1qTpFdbPort' => ['Q-BRIDGE-MIB::dot1qTpFdbPort[100][0:11:22:33:aa:1]' => '1'],
            '|BRIDGE-MIB::dot1dBasePortIfIndex' => ['BRIDGE-MIB::dot1dBasePortIfIndex[1]' => '10'],
        ]);

        $this->assertSame([[self::MAC_A, $this->portId(10), 100]], $fdb);
    }

    public function testArubaDoesNotWalkAContextWithoutAVlan(): void
    {
        $this->port(10);
        $this->port(11);

        $fdb = $this->osFdb('arubaos', [
            '|BRIDGE-MIB::dot1dTpFdbPort' => ['BRIDGE-MIB::dot1dTpFdbPort[0:11:22:33:aa:1]' => '1'],
            '|BRIDGE-MIB::dot1dBasePortIfIndex' => ['BRIDGE-MIB::dot1dBasePortIfIndex[1]' => '10'],
            '0|BRIDGE-MIB::dot1dBasePortIfIndex' => ['BRIDGE-MIB::dot1dBasePortIfIndex[1]' => '11'],
        ]);

        $this->assertSame([[self::MAC_A, $this->portId(10), 0]], $fdb);
    }

    public function testAosFallsBackToAggregateAndLagPorts(): void
    {
        $this->port(40000002);

        $fdb = $this->osFdb('aos7', [
            '|ALCATEL-IND1-MAC-ADDRESS-MIB::slMacAddressGblManagement' => ['ALCATEL-IND1-MAC-ADDRESS-MIB::slMacAddressGblManagement[1][1][4099][0][0][0:11:22:33:aa:1]' => '1'],
        ]);

        $this->assertSame([[self::MAC_A, $this->portId(40000002), 0]], $fdb);
    }

    public function testVrpReadsStickyMacs(): void
    {
        $this->port(10);

        $fdb = $this->osFdb('vrp', [
            '|HUAWEI-L2MAM-MIB::hwCfgMacAddrQueryIfIndex' => ['HUAWEI-L2MAM-MIB::hwCfgMacAddrQueryIfIndex[100][0][0][0][0:11:22:33:aa:1][showall][0][0][0][0][0]' => '10'],
        ]);

        $this->assertSame([[self::MAC_A, $this->portId(10), 100]], $fdb);
    }

    public function testTimosIgnoresEntriesThatAreNotLocalOrHaveNoVlan(): void
    {
        $this->port(10);

        $fdb = $this->osFdb('timos', [
            '|TIMETRA-SERV-MIB::tlsFdbLocale' => [
                'TIMETRA-SERV-MIB::tlsFdbLocale[5][0:11:22:33:aa:1]' => '1',
                'TIMETRA-SERV-MIB::tlsFdbLocale[5][0:11:22:33:bb:1]' => '2', // sdp
                'TIMETRA-SERV-MIB::tlsFdbLocale[5][0:11:22:33:bb:2]' => '1', // null encap
                'TIMETRA-SERV-MIB::tlsFdbLocale[5][0:11:22:33:bb:3]' => '1', // unknown port
            ],
            '|TIMETRA-SERV-MIB::tlsFdbPortId' => [
                'TIMETRA-SERV-MIB::tlsFdbPortId[5][0:11:22:33:aa:1]' => '10',
                'TIMETRA-SERV-MIB::tlsFdbPortId[5][0:11:22:33:bb:1]' => '10',
                'TIMETRA-SERV-MIB::tlsFdbPortId[5][0:11:22:33:bb:2]' => '10',
                'TIMETRA-SERV-MIB::tlsFdbPortId[5][0:11:22:33:bb:3]' => '99',
            ],
            '|TIMETRA-SERV-MIB::tlsFdbEncapValue' => [
                'TIMETRA-SERV-MIB::tlsFdbEncapValue[5][0:11:22:33:aa:1]' => '100',
                'TIMETRA-SERV-MIB::tlsFdbEncapValue[5][0:11:22:33:bb:1]' => '100',
                'TIMETRA-SERV-MIB::tlsFdbEncapValue[5][0:11:22:33:bb:2]' => '0',
                'TIMETRA-SERV-MIB::tlsFdbEncapValue[5][0:11:22:33:bb:3]' => '100',
            ],
        ]);

        $this->assertSame([[self::MAC_A, $this->portId(10), 100]], $fdb);
    }

    public function testArubaUsesBridgePortsFromTheVlanContext(): void
    {
        $this->port(10);
        $this->port(11);

        $fdb = $this->osFdb('arubaos', [
            '|Q-BRIDGE-MIB::dot1qTpFdbPort' => [
                'Q-BRIDGE-MIB::dot1qTpFdbPort[100][0:11:22:33:aa:1]' => '1',
                'Q-BRIDGE-MIB::dot1qTpFdbPort[100][0:11:22:33:aa:2]' => '2',
                'Q-BRIDGE-MIB::dot1qTpFdbPort[100][0:11:22:33:aa:3]' => '11', // unmapped bridge ports are not ifIndexes
            ],
            '|BRIDGE-MIB::dot1dBasePortIfIndex' => ['BRIDGE-MIB::dot1dBasePortIfIndex[1]' => '10'],
            '100|BRIDGE-MIB::dot1dBasePortIfIndex' => ['BRIDGE-MIB::dot1dBasePortIfIndex[2]' => '11'],
        ]);

        $this->assertSame([
            [self::MAC_A, $this->portId(10), 100],
            [self::MAC_B, $this->portId(11), 100],
            ['00112233aa03', 0, 100],
        ], $fdb);
    }

    public function testFortiswitchJoinsBridgePortByValue(): void
    {
        $this->port(87);

        $fdb = $this->osFdb('fortiswitch', [
            '|BRIDGE-MIB::dot1dTpFdbAddress' => ['BRIDGE-MIB::dot1dTpFdbAddress[2]' => '0:11:22:33:aa:1', 'BRIDGE-MIB::dot1dTpFdbAddress[3]' => '0:11:22:33:aa:2'],
            '|BRIDGE-MIB::dot1dTpFdbPort' => ['BRIDGE-MIB::dot1dTpFdbPort[2]' => '32781', 'BRIDGE-MIB::dot1dTpFdbPort[3]' => '32782'],
            '|BRIDGE-MIB::dot1dBasePort' => ['BRIDGE-MIB::dot1dBasePort[37]' => '32781'],
            '|BRIDGE-MIB::dot1dBasePortIfIndex' => ['BRIDGE-MIB::dot1dBasePortIfIndex[37]' => '87'],
        ]);

        // entries on unknown ports are skipped
        $this->assertSame([[self::MAC_A, $this->portId(87), 0]], $fdb);
    }

    public function testRowIndexesPrintedWithAMalformedOidAreDetected(): void
    {
        $this->port(10);

        $fdb = $this->osFdb('generic', [
            '|BRIDGE-MIB::dot1dTpFdbAddress' => ['BRIDGE-MIB::dot1dTpFdbAddress[.1' => '0:11:22:33:aa:1'],
            '|BRIDGE-MIB::dot1dTpFdbPort' => ['BRIDGE-MIB::dot1dTpFdbPort[.1' => '1'],
            '|BRIDGE-MIB::dot1dBasePortIfIndex' => ['BRIDGE-MIB::dot1dBasePortIfIndex[1]' => '10'],
        ]);

        $this->assertSame([[self::MAC_A, $this->portId(10), 0]], $fdb);
    }

    public function testNetonixReadsTableByRow(): void
    {
        $this->port(10);

        $fdb = $this->osFdb('netonix', [
            '|BRIDGE-MIB::dot1dTpFdbAddress' => ['BRIDGE-MIB::dot1dTpFdbAddress[1]' => '0:11:22:33:aa:1', 'BRIDGE-MIB::dot1dTpFdbAddress[2]' => '0:11:22:33:aa:2'],
            '|BRIDGE-MIB::dot1dTpFdbPort' => ['BRIDGE-MIB::dot1dTpFdbPort[1]' => '1', 'BRIDGE-MIB::dot1dTpFdbPort[2]' => '0'],
            '|BRIDGE-MIB::dot1dBasePortIfIndex' => ['BRIDGE-MIB::dot1dBasePortIfIndex[1]' => '10'],
        ]);

        $this->assertSame([[self::MAC_A, $this->portId(10), 0]], $fdb);
    }

    public function testZynosGs1900LengthPrefixedQBridgeIndex(): void
    {
        $this->port(7);
        $this->device->hardware = 'GS1900-24E';

        $fdb = $this->osFdb('zynos', [
            '|Q-BRIDGE-MIB::dot1qTpFdbPort' => ['Q-BRIDGE-MIB::dot1qTpFdbPort[100][6:0:11:22:33:aa].1' => '7'],
        ]);

        $this->assertSame([[self::MAC_A, $this->portId(7), 100]], $fdb);
    }

    public function testJetstreamBridgePortsAreOffsetFromIfIndex(): void
    {
        $this->port(49153);

        $fdb = $this->osFdb('jetstream', [
            '|Q-BRIDGE-MIB::dot1qTpFdbPort' => ['Q-BRIDGE-MIB::dot1qTpFdbPort[100][0:11:22:33:aa:1]' => '1'],
        ]);

        $this->assertSame([[self::MAC_A, $this->portId(49153), 100]], $fdb);
    }

    /**
     * Run the module with an OS that returns the given entries
     */
    private function discover(callable $entries): void
    {
        $os = Mockery::mock(OS::class);
        $os->shouldReceive('getDevice')->andReturn($this->device);
        $os->shouldReceive('discoverFdbTable')->andReturnUsing(fn () => new Collection($entries()));

        /** @var OS $os */
        (new FdbTable)->discover($os);
    }

    /**
     * Run the OS class discovery against fake snmp data
     *
     * @param  array<string, array<string, string>>  $walks  Responses keyed by "context|oid"
     * @return list<array{string, int, int}> mac_address, port_id and vlan
     */
    private function osFdb(string $os, array $walks): array
    {
        $this->device->os = $os;
        $this->device->save();

        $this->app->bind(SnmpQueryBuilder::class, fn () => $this->fakeSnmp($walks));

        $deviceArray = $this->device->toArray();

        return OS::make($deviceArray)->discoverFdbTable()
            ->map(fn (PortsFdb $fdb) => [$fdb->mac_address, $fdb->port_id, (int) $fdb->vlan])
            ->all();
    }

    /**
     * @param  array<string, array<string, string>>  $walks
     */
    private function fakeSnmp(array $walks): SnmpQueryBuilder
    {
        $context = '';
        $query = Mockery::mock(SnmpQueryBuilder::class);
        foreach (['cache', 'allowUnordered', 'numericIndex', 'mibDir'] as $option) {
            $query->shouldReceive($option)->andReturnSelf();
        }
        $query->shouldReceive('context')->andReturnUsing(function (string $name) use (&$context, $query) {
            $context = $name;

            return $query;
        });
        $query->shouldReceive('walk')->andReturnUsing(function (array|string $oids) use (&$context, $walks) {
            $values = [];
            foreach ((array) $oids as $oid) {
                $values += $walks["$context|$oid"] ?? [];
            }

            return new SnmpResponse($values);
        });

        return $query;
    }

    private function port(int $ifIndex): Port
    {
        return Port::factory()->create(['device_id' => $this->device->device_id, 'ifIndex' => $ifIndex]);
    }

    private function portId(int $ifIndex): int
    {
        return $this->device->ports()->where('ifIndex', $ifIndex)->value('port_id');
    }

    private function fdb(string $mac, int $portId, int $vlan = 0): PortsFdb
    {
        return new PortsFdb(['mac_address' => $mac, 'port_id' => $portId, 'vlan' => $vlan]);
    }
}
