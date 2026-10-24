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
use LibreNMS\Discovery\Vrf\VrfTableReader;
use LibreNMS\Modules\Vrf;
use LibreNMS\OS;
use LibreNMS\Tests\DBTestCase;
use Mockery;

/**
 * @phpstan-import-type VrfEntry from VrfTableReader
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
        $this->device->unsetRelation('vrfs');

        $module = $this->discover([$this->vrf('1', 'red', ['context' => 'ctx-red']), $this->vrf('2', 'blue', ['context' => 'ctx-blue'])], ['ctx-red', 'ctx-blue']);

        $this->assertSame(['ctx-blue'], $module->probed);
        $this->assertFalse($this->device->vrfs()->where('vrf_name', 'red')->value('context_enabled'), 'Rediscovery keeps the user setting');
        $this->assertSame(['', 'ctx-blue'], $this->device->fresh()->getVrfContexts());
    }

    public function testManualContexts(): void
    {
        $this->device->setAttrib(Vrf::MANUAL_CONTEXTS_ATTRIB, json_encode(['VRF-A', 'vrf-b', 'ctx-red', 'missing', 'default']));

        $module = $this->discover([
            $this->vrf('5.86.82.70.45.65', 'VRF-A'),
            $this->vrf('1', 'red', ['context' => 'ctx-red']),
        ], ['VRF-A', 'vrf-b', 'ctx-red']);

        $this->assertSame(['VRF-A', 'ctx-red', 'vrf-b', 'missing'], $module->probed);
        $this->assertSame(
            ['VRF-A' => ['VRF-A', true], 'missing' => ['missing', false], 'red' => ['ctx-red', true], 'vrf-b' => ['vrf-b', true]],
            $this->contexts(),
            'Manual contexts expose the VRF with the same name, or a VRF named after them',
        );

        $device = $this->device->fresh();
        $this->assertSame(['', 'VRF-A', 'ctx-red', 'vrf-b'], $device->getVrfContexts());
        $this->assertSame($device->vrfs->firstWhere('vrf_name', 'VRF-A')->vrf_id, $device->vrfIdForContext('VRF-A'));
        $this->assertSame($device->vrfs->firstWhere('vrf_name', 'red')->vrf_id, $device->vrfIdForContext('ctx-red'));
        $this->assertSame($device->vrfs->firstWhere('vrf_name', 'red')->vrf_id, $device->vrfIdForContext('red'), 'VRF names stored as context resolve');
        $this->assertNull($device->vrfIdForContext(''));

        // removing a manual context removes the VRF it created
        $this->device->setAttrib(Vrf::MANUAL_CONTEXTS_ATTRIB, json_encode(['VRF-A']));
        $this->discover([$this->vrf('5.86.82.70.45.65', 'VRF-A'), $this->vrf('1', 'red', ['context' => 'ctx-red'])], ['VRF-A', 'ctx-red']);
        $this->assertSame(['VRF-A', 'red'], array_keys($this->contexts()));
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
     * @return array<string, array{0: string|null, 1: bool}>
     */
    private function contexts(): array
    {
        return $this->device->vrfs()->get()->sortBy('vrf_name', SORT_STRING)
            ->mapWithKeys(fn (VrfModel $vrf) => [$vrf->vrf_name => [$vrf->context_name, $vrf->context_verified]])->all();
    }

    /**
     * @param  VrfEntry[]  $vrfs
     * @param  string[]  $answering  contexts that answer
     */
    private function discover(array $vrfs, array $answering = []): TestVrfModule
    {
        $reader = Mockery::mock(VrfTableReader::class);
        $reader->shouldReceive('read')->andReturn(array_column($vrfs, null, 'vrf_oid'));

        $os = Mockery::mock(OS::class);
        $os->shouldReceive('getDevice')->andReturn($this->device);
        $os->shouldReceive('getName')->andReturn('generic');

        $module = new TestVrfModule($answering, $reader);
        /** @var OS $os */
        $module->discover($os);

        return $module;
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return VrfEntry
     */
    private function vrf(string $vrfOid, string $name, array $fields = []): array
    {
        return array_merge(['vrf_oid' => $vrfOid, 'name' => $name, 'rd' => null, 'description' => '', 'local_as' => null, 'context' => null, 'ifIndexes' => []], $fields);
    }
}

class TestVrfModule extends Vrf
{
    /** @var string[] */
    public array $probed = [];

    /**
     * @param  string[]  $answering
     */
    public function __construct(private readonly array $answering, private readonly VrfTableReader $reader)
    {
    }

    protected function reader(OS $os): VrfTableReader
    {
        return $this->reader;
    }

    protected function contextAnswers(string $context): bool
    {
        $this->probed[] = $context;

        return in_array($context, $this->answering, true);
    }
}
