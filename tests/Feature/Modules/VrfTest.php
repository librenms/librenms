<?php

/**
 * VrfTest.php
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

use App\Models\Device;
use App\Models\Port;
use App\Models\Vrf as VrfModel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use LibreNMS\Exceptions\SnmpException;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Polling\VrfPolling;
use LibreNMS\Modules\Vrf;
use LibreNMS\OS;
use LibreNMS\Tests\DBTestCase;
use Mockery;

/**
 * @phpstan-type VrfSpec array{vrf_oid: string, name: string, rd: string|null, description: string, context: string|null, ifIndexes: int[]}
 */
final class VrfTest extends DBTestCase
{
    use DatabaseTransactions;

    private Device $device;

    protected function setUp(): void
    {
        parent::setUp();

        $this->device = Device::factory()->create(['os' => 'generic']);
    }

    public function testRediscoveryKeepsVrfIdsAndSyncsPorts(): void
    {
        $lan = Port::factory()->create(['device_id' => $this->device->device_id, 'ifIndex' => 10]);
        $wan = Port::factory()->create(['device_id' => $this->device->device_id, 'ifIndex' => 11]);
        $other = Port::factory()->create(['device_id' => $this->device->device_id, 'ifIndex' => 12]);
        $existing = VrfModel::factory()->for($this->device)->create(['vrf_oid' => '3.76.65.78', 'vrf_name' => 'LAN', 'mplsVpnVrfRouteDistinguisher' => '65000:1']);
        $gone = VrfModel::factory()->for($this->device)->create(['vrf_oid' => '4.71.79.78.69', 'vrf_name' => 'GONE']);
        $other->update(['ifVrf' => $gone->vrf_id]);

        $this->discover([
            $this->vrf('3.76.65.78', 'LAN', ['rd' => '65000:10', 'description' => 'Office', 'ifIndexes' => [10, 99]]),
            $this->vrf('3.87.65.78', 'WAN', ['ifIndexes' => [11]]),
        ]);

        $vrfs = $this->device->vrfs()->orderBy('vrf_name')->get()->keyBy('vrf_name');
        $this->assertSame(['LAN', 'WAN'], $vrfs->keys()->all());
        $this->assertSame($existing->vrf_id, $vrfs['LAN']->vrf_id, 'VRFs are updated in place');
        $this->assertSame('65000:10', $vrfs['LAN']->mplsVpnVrfRouteDistinguisher);
        $this->assertSame('Office', $vrfs['LAN']->mplsVpnVrfDescription);
        $this->assertSame($vrfs['LAN']->vrf_id, $lan->fresh()->ifVrf);
        $this->assertSame($vrfs['WAN']->vrf_id, $wan->fresh()->ifVrf);
        $this->assertSame(0, $other->fresh()->ifVrf, 'Interfaces of removed VRFs are cleared');

        // the interface moves to another VRF
        $this->discover([
            $this->vrf('3.76.65.78', 'LAN'),
            $this->vrf('3.87.65.78', 'WAN', ['ifIndexes' => [10, 11]]),
        ]);

        $this->assertSame($vrfs['WAN']->vrf_id, $lan->fresh()->ifVrf);
        $this->assertSame($vrfs['WAN']->vrf_id, $wan->fresh()->ifVrf);
    }

    public function testOnlyContextsThatAnswerAreWalked(): void
    {
        $module = $this->discover([
            $this->vrf('1', 'red', ['context' => 'ctx-red']),
            $this->vrf('2', 'blue', ['context' => 'ctx-blue']),
            $this->vrf('3', 'default', ['context' => 'ctx-default']),
        ], ['ctx-red']);

        $this->assertSame(['ctx-red', 'ctx-blue'], $module->probed, 'The default VRF context is never walked, so it is not probed');
        $this->assertSame(
            ['blue' => ['ctx-blue', false], 'default' => [null, false], 'red' => ['ctx-red', true]],
            $this->contexts(),
        );
        $this->assertSame(['', 'ctx-red'], $this->device->fresh()->getVrfContexts());
    }

    public function testDisabledContextsAreNotCheckedOrWalked(): void
    {
        $this->discover([$this->vrf('1', 'red', ['context' => 'ctx-red']), $this->vrf('2', 'blue', ['context' => 'ctx-blue'])], ['ctx-red', 'ctx-blue']);
        $this->device->vrfs()->where('vrf_name', 'red')->update(['context_enabled' => false]);

        $module = $this->discover([$this->vrf('1', 'red', ['context' => 'ctx-red']), $this->vrf('2', 'blue', ['context' => 'ctx-blue'])], ['ctx-red', 'ctx-blue']);

        $this->assertSame(['ctx-blue'], $module->probed);
        $this->assertFalse($this->device->vrfs()->where('vrf_name', 'red')->value('context_enabled'), 'Rediscovery keeps the user setting');
        $this->assertSame(['', 'ctx-blue'], $this->device->fresh()->getVrfContexts());
    }

    public function testPollingRunsDiscoveryWithoutCheckingContexts(): void
    {
        $port = Port::factory()->create(['device_id' => $this->device->device_id, 'ifIndex' => 10]);
        $this->discover([$this->vrf('1', 'red', ['context' => 'ctx-red'])], ['ctx-red']);

        $module = $this->discover([$this->vrf('1', 'red', ['context' => 'ctx-red', 'ifIndexes' => [10]])], [], poll: true);

        $this->assertSame([], $module->probed, 'Only discovery checks contexts');
        $this->assertSame(['red' => ['ctx-red', true]], $this->contexts(), 'Polling keeps the result of discovery');
        $this->assertSame($this->device->vrfs()->value('vrf_id'), $port->fresh()->ifVrf);
    }

    public function testVrfsAreKeptWhenTheDeviceDoesNotAnswer(): void
    {
        $port = Port::factory()->create(['device_id' => $this->device->device_id, 'ifIndex' => 10]);
        $this->discover([$this->vrf('1', 'red', ['context' => 'ctx-red', 'ifIndexes' => [10]])], ['ctx-red']);
        $vrfId = $this->device->vrfs()->value('vrf_id');

        $module = $this->discover([], ['ctx-red'], timeout: true);

        $this->assertSame([], $module->probed);
        $this->assertSame(['red' => ['ctx-red', true]], $this->contexts());
        $this->assertSame($vrfId, $this->device->vrfs()->value('vrf_id'));
        $this->assertSame($vrfId, $port->fresh()->ifVrf);
    }

    public function testContextsAreWalkedUntilRepeatedFailures(): void
    {
        $red = [$this->vrf('1', 'red', ['context' => 'ctx-red'])];
        $this->discover($red, ['ctx-red']);

        foreach (range(1, Vrf::CONTEXT_FAILURE_LIMIT - 1) as $failures) {
            $this->discover($red);
            $this->assertSame(['red' => ['ctx-red', true]], $this->contexts(), "Still walked after $failures failures");
            $this->assertSame($failures, $this->device->vrfs()->value('context_failures'));
        }

        $this->discover($red);
        $this->assertSame(['red' => ['ctx-red', false]], $this->contexts());
        $this->assertSame([''], $this->device->fresh()->getVrfContexts());
        $this->assertSame(['ctx-red'], $this->device->fresh()->getSkippedVrfContexts());

        $this->discover($red, ['ctx-red']);
        $this->assertSame(['red' => ['ctx-red', true]], $this->contexts(), 'One answer is enough');
        $this->assertSame(0, $this->device->vrfs()->value('context_failures'));
    }

    public function testChangedContextsAreCheckedBeforeTheyAreWalked(): void
    {
        $this->discover([$this->vrf('1', 'red', ['context' => 'ctx-red'])], ['ctx-red']);

        $this->discover([$this->vrf('1', 'red', ['context' => 'ctx-new'])], [], poll: true);
        $this->assertSame(['red' => ['ctx-new', false]], $this->contexts(), 'Polling does not trust a new context');

        $this->discover([$this->vrf('1', 'red', ['context' => 'ctx-new'])]);
        $this->assertSame(['red' => ['ctx-new', false]], $this->contexts(), 'A new context gets no grace period');

        $this->discover([$this->vrf('1', 'red', ['context' => 'ctx-new'])], ['ctx-new']);
        $this->assertSame(['red' => ['ctx-new', true]], $this->contexts());
    }

    public function testNewVrfsAreWalkedInTheSameRun(): void
    {
        $this->discover([$this->vrf('1', 'red', ['context' => 'ctx-red'])], ['ctx-red']);

        $this->assertSame(['', 'ctx-red'], $this->device->getVrfContexts());
    }

    public function testManualContexts(): void
    {
        $this->discover([
            $this->vrf('5.86.82.70.45.65', 'VRF-A'),
            $this->vrf('1', 'red', ['context' => 'ctx-red']),
        ], ['ctx-red']);
        $this->setManualContext('VRF-A', 'ctx-a');
        $this->addContextOnlyVrf('vrf-b');
        $this->addContextOnlyVrf('missing');

        $module = $this->discover([
            $this->vrf('5.86.82.70.45.65', 'VRF-A'),
            $this->vrf('1', 'red', ['context' => 'ctx-red']),
        ], ['ctx-a', 'vrf-b', 'ctx-red']);

        $this->assertSame(['ctx-a', 'ctx-red', 'vrf-b', 'missing'], $module->probed);
        $this->assertSame(
            ['VRF-A' => ['ctx-a', true], 'missing' => ['missing', false], 'red' => ['ctx-red', true], 'vrf-b' => ['vrf-b', true]],
            $this->contexts(),
        );
        $this->assertSame(
            ['VRF-A' => [true, false], 'missing' => [true, true], 'red' => [false, false], 'vrf-b' => [true, true]],
            $this->manualFlags(),
            'Manual contexts and VRFs only known through their context are kept',
        );

        $device = $this->device->fresh();
        $this->assertEqualsCanonicalizing(['', 'ctx-a', 'ctx-red', 'vrf-b'], $device->getVrfContexts());
        $this->assertSame($device->vrfs->firstWhere('vrf_name', 'VRF-A')->vrf_id, $device->vrfIdForContext('ctx-a'));
        $this->assertSame($device->vrfs->firstWhere('vrf_name', 'red')->vrf_id, $device->vrfIdForContext('ctx-red'));
        $this->assertSame($device->vrfs->firstWhere('vrf_name', 'red')->vrf_id, $device->vrfIdForContext('red'), 'VRF names stored as context resolve');
        $this->assertNull($device->vrfIdForContext(''));
    }

    public function testManualContextFollowsItsVrf(): void
    {
        $this->discover([$this->vrf('2', 'blue')]);
        $this->setManualContext(null, 'ctx-blue');

        $this->discover([$this->vrf('2', 'BLUE')], ['ctx-blue']);
        $this->assertSame(['BLUE' => ['ctx-blue', true]], $this->contexts(), 'Renamed VRFs keep their context');

        $this->discover([$this->vrf('7', 'BLUE')], ['ctx-blue']);
        $this->assertSame(['BLUE' => ['ctx-blue', true]], $this->contexts(), 'Renumbered VRFs keep their context');
        $this->assertSame('7', $this->device->vrfs()->sole()->vrf_oid);

        // the device stops listing the VRF, it is kept through its context until it is listed again
        $this->discover([$this->vrf('8', 'green')], ['ctx-blue']);
        $this->assertSame(['BLUE' => ['ctx-blue', true], 'green' => [null, false]], $this->contexts());
        $kept = $this->device->vrfs()->firstWhere('vrf_name', 'BLUE');
        $this->assertSame(['context:ctx-blue', true, true], [$kept->vrf_oid, $kept->context_manual, $kept->vrf_manual]);

        $this->discover([$this->vrf('8', 'green'), $this->vrf('9', 'blue')], ['ctx-blue']);
        $blue = $this->device->vrfs()->firstWhere('vrf_name', 'blue');
        $this->assertSame(['9', 'ctx-blue', true, false], [$blue->vrf_oid, $blue->context_name, $blue->context_manual, $blue->vrf_manual]);
        $this->assertSame(2, $this->device->vrfs()->count());
    }

    public function testManualContextFollowsItsVrfWhenIndexesAreReused(): void
    {
        $this->discover([$this->vrf('1', 'A'), $this->vrf('2', 'B')]);
        $this->setManualContext('A', 'ctx-a');

        $this->discover([$this->vrf('1', 'B'), $this->vrf('3', 'A')], ['ctx-a']);

        $this->assertSame(['A' => ['ctx-a', true], 'B' => [null, false]], $this->contexts());
    }

    public function testVrfsOnlyKnownThroughTheirContextDoNotReplaceListedVrfs(): void
    {
        $this->addContextOnlyVrf('2');

        $this->discover([$this->vrf('2', 'blue', ['rd' => '65000:2'])], ['2']);

        $this->assertSame(['2' => ['2', true], 'blue' => [null, false]], $this->contexts());
        $this->assertSame('65000:2', $this->device->vrfs()->firstWhere('vrf_oid', '2')->mplsVpnVrfRouteDistinguisher);
    }

    public function testPollingKeepsManualContextsOfPolledVrfs(): void
    {
        $this->discover([$this->vrf('2', 'blue')]);
        $this->setManualContext(null, 'ctx-blue');
        $this->addContextOnlyVrf('red');
        $this->device->unsetRelation('vrfs');

        // VrfPolling gets the saved VRFs, including the ones only known through their context
        $os = Mockery::mock(OS::class, VrfPolling::class);
        $os->shouldReceive('getDevice')->andReturn($this->device);
        $os->shouldReceive('pollVrfs')->andReturnUsing(fn (Collection $vrfs) => $vrfs);
        $os->shouldReceive('discoverVrfPorts')->andReturn(new Collection);
        /** @var OS $os */
        (new TestVrfModule([]))->poll($os, Mockery::mock(DataStorageInterface::class));

        $this->assertSame(['blue' => [true, false], 'red' => [true, true]], $this->manualFlags());
        $this->assertSame(['blue' => ['ctx-blue', false], 'red' => ['red', false]], $this->contexts());
    }

    public function testDeviceContextsWinOverManualContexts(): void
    {
        $this->discover([$this->vrf('2', 'blue'), $this->vrf('3', 'green')]);
        $this->setManualContext('blue', 'ctx-blue');
        $this->setManualContext('green', 'ctx-green');

        $this->discover([$this->vrf('2', 'blue', ['context' => 'ctx-device']), $this->vrf('3', 'green'), $this->vrf('4', 'red', ['context' => 'ctx-green'])], ['ctx-device', 'ctx-green']);

        $this->assertSame(['blue' => ['ctx-device', true], 'green' => [null, false], 'red' => ['ctx-green', true]], $this->contexts());
        $this->assertSame(['blue' => [false, false], 'green' => [false, false], 'red' => [false, false]], $this->manualFlags());
    }

    public function testManualVrfMovesToTheVrfTheDeviceLists(): void
    {
        $this->addContextOnlyVrf('blue');

        $this->discover([$this->vrf('2', 'Blue')], ['blue']);

        $vrf = $this->device->vrfs()->sole();
        $this->assertSame(['2', 'Blue', 'blue', true, false], [$vrf->vrf_oid, $vrf->vrf_name, $vrf->context_name, $vrf->context_manual, $vrf->vrf_manual]);

        // the device maps its own context later
        $this->discover([$this->vrf('2', 'Blue', ['context' => 'ctx-blue'])], ['ctx-blue']);

        $vrf = $this->device->vrfs()->sole();
        $this->assertSame(['2', 'ctx-blue', false, false], [$vrf->vrf_oid, $vrf->context_name, $vrf->context_manual, $vrf->vrf_manual]);
    }

    public function testCleanup(): void
    {
        $vrf = VrfModel::factory()->for($this->device)->create();
        $port = Port::factory()->create(['device_id' => $this->device->device_id, 'ifVrf' => $vrf->vrf_id]);
        $module = new Vrf;

        $this->assertTrue($module->dataExists($this->device));
        $this->assertSame(1, $module->cleanup($this->device));
        $this->assertFalse($module->dataExists($this->device));
        $this->assertSame(0, $port->fresh()->ifVrf);
    }

    public function testDump(): void
    {
        $vrf = VrfModel::factory()->for($this->device)->create(['vrf_oid' => '2', 'vrf_name' => 'vprn2', 'context_name' => null, 'bgpLocalAs' => 65000, 'mplsVpnVrfRouteDistinguisher' => '65000:2', 'mplsVpnVrfDescription' => '']);
        Port::factory()->create(['device_id' => $this->device->device_id, 'ifIndex' => 7, 'ifVrf' => $vrf->vrf_id]);
        Port::factory()->create(['device_id' => $this->device->device_id, 'ifIndex' => 3, 'ifVrf' => $vrf->vrf_id]);

        $this->assertSame([[
            'vrf_oid' => '2',
            'vrf_name' => 'vprn2',
            'context_name' => null,
            'context_verified' => false,
            'bgpLocalAs' => 65000,
            'mplsVpnVrfRouteDistinguisher' => '65000:2',
            'mplsVpnVrfDescription' => '',
            'ifIndices' => '3,7',
        ]], (new Vrf)->dump($this->device, 'discovery')['vrfs']);
    }

    /**
     * Set a context like users do under Device > Edit > Routing, on the VRF with this name or the only VRF
     */
    private function setManualContext(?string $vrfName, string $context): void
    {
        $vrf = $vrfName === null ? $this->device->vrfs()->sole() : $this->device->vrfs()->firstWhere('vrf_name', $vrfName);
        $vrf->fill(['context_name' => $context, 'context_manual' => true])->save();
    }

    private function addContextOnlyVrf(string $context): void
    {
        $this->device->vrfs()->create([
            'vrf_oid' => VrfModel::contextOnlyOid($context),
            'vrf_name' => $context,
            'context_name' => $context,
            'context_manual' => true,
            'vrf_manual' => true,
            'mplsVpnVrfDescription' => '',
        ]);
        $this->device->unsetRelation('vrfs');
    }

    /**
     * @return array<string, array{0: bool, 1: bool}>
     */
    private function manualFlags(): array
    {
        return $this->device->vrfs()->get()->sortBy('vrf_name', SORT_STRING)
            ->mapWithKeys(fn (VrfModel $vrf) => [$vrf->vrf_name => [$vrf->context_manual, $vrf->vrf_manual]])->all();
    }

    /**
     * @return array<string, array{0: string|null, 1: bool}>
     */
    private function contexts(): array
    {
        return $this->device->vrfs()->get()->sortBy('vrf_name', SORT_STRING)
            ->mapWithKeys(fn (VrfModel $vrf) => [$vrf->vrf_name => [$vrf->context_name, $vrf->context_verified]])->all();
    }

    /**
     * @param  VrfSpec[]  $vrfs
     * @param  string[]  $answering  contexts that answer
     * @param  bool  $timeout  the device does not answer VRF discovery
     */
    private function discover(array $vrfs, array $answering = [], bool $poll = false, bool $timeout = false): TestVrfModule
    {
        $os = Mockery::mock(OS::class);
        $os->shouldReceive('getDevice')->andReturn($this->device);
        $os->shouldReceive('discoverVrfs')->andReturnUsing(function () use ($vrfs, $timeout) {
            if ($timeout) {
                throw new SnmpException('Timeout: No Response from udp:192.0.2.1:161');
            }

            return collect($vrfs)->map(fn (array $vrf) => new VrfModel([
                'vrf_oid' => $vrf['vrf_oid'],
                'vrf_name' => $vrf['name'],
                'context_name' => $vrf['context'],
                'mplsVpnVrfRouteDistinguisher' => $vrf['rd'],
                'mplsVpnVrfDescription' => $vrf['description'],
            ]));
        });
        $os->shouldReceive('discoverVrfPorts')->andReturnUsing(function (Collection $saved) use ($vrfs) {
            $ports = new Collection;
            foreach ($vrfs as $vrf) {
                foreach ($vrf['ifIndexes'] as $ifIndex) {
                    $port = $this->device->ports()->firstWhere('ifIndex', $ifIndex);
                    if ($port) {
                        $port->ifVrf = $saved->firstWhere('vrf_oid', $vrf['vrf_oid'])->vrf_id;
                        $ports->push($port);
                    }
                }
            }

            return $ports;
        });

        $module = new TestVrfModule($answering);
        /** @var OS $os */
        if ($poll) {
            $module->poll($os, Mockery::mock(DataStorageInterface::class));
        } else {
            $module->discover($os);
        }

        return $module;
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return VrfSpec
     */
    private function vrf(string $vrfOid, string $name, array $fields = []): array
    {
        return array_merge(['vrf_oid' => $vrfOid, 'name' => $name, 'rd' => null, 'description' => '', 'context' => null, 'ifIndexes' => []], $fields);
    }
}

class TestVrfModule extends Vrf
{
    /** @var string[] */
    public array $probed = [];

    /**
     * @param  string[]  $answering
     */
    public function __construct(private readonly array $answering)
    {
    }

    protected function checkContext(string $context): bool
    {
        $this->probed[] = $context;

        return in_array($context, $this->answering, true);
    }
}
