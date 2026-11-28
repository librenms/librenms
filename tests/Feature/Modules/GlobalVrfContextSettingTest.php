<?php

namespace LibreNMS\Tests\Feature\Modules;

use App\Facades\LibrenmsConfig;
use App\Http\Controllers\Device\EditRoutingController;
use App\Models\BgpPeer;
use App\Models\Device;
use App\Models\Vrf as VrfModel;
use Illuminate\Http\Request;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Modules\Vrf;
use LibreNMS\OS;
use LibreNMS\Tests\InMemoryDbTestCase;
use Mockery;

final class GlobalVrfContextSettingTest extends InMemoryDbTestCase
{
    public function testSettingCanBeDisabledThroughConfigCommand(): void
    {
        $this->assertTrue(LibrenmsConfig::get('snmp.vrf_contexts'));

        $this->artisan('config:set', ['setting' => 'snmp.vrf_contexts', 'value' => 'false'])->assertExitCode(0);

        $this->assertFalse(LibrenmsConfig::get('snmp.vrf_contexts'));
        $this->assertDatabaseHas('config', ['config_name' => 'snmp.vrf_contexts', 'config_value' => 'false']);
    }

    public function testExistingContextsKeepTheirSettingWhenGlobalDefaultChanges(): void
    {
        $device = Device::factory()->create(['os' => 'generic']);
        $vrf = VrfModel::factory()->for($device)->create([
            'vrf_oid' => '1',
            'vrf_name' => 'blue',
            'context_name' => 'ctx-blue',
            'context_verified' => true,
            'context_enabled' => true,
        ]);
        BgpPeer::factory()->for($device)->create(['context_name' => 'ctx-blue']);
        $module = new GlobalContextTestVrf;
        $os = Mockery::mock(OS::class);
        $os->shouldReceive('getDevice')->andReturn($device);
        $os->shouldReceive('discoverVrfs')->andReturnUsing(fn () => collect([
            new VrfModel(['vrf_oid' => '1', 'vrf_name' => 'blue', 'context_name' => 'ctx-blue', 'mplsVpnVrfDescription' => '']),
        ]));
        $os->shouldReceive('discoverVrfPorts')->andReturn(collect());

        $this->assertSame(['', 'ctx-blue'], $device->getVrfContexts());
        LibrenmsConfig::set('snmp.vrf_contexts', false);
        $module->discover($os);

        $this->assertSame(['ctx-blue'], $module->probed);
        $this->assertTrue($vrf->fresh()->context_verified);
        $this->assertTrue($vrf->fresh()->context_enabled);
        $this->assertSame(['', 'ctx-blue'], $device->getVrfContexts());

        $vrf->update(['context_enabled' => false]);
        $device->unsetRelation('vrfs');
        $this->assertSame([''], $device->getVrfContexts());
        $this->assertSame(['ctx-blue'], $device->getSkippedVrfContexts());
        (new GlobalContextTestSync)->sync($device);
        $this->assertSame(1, $device->bgppeers()->count());

        $vrf->update(['context_enabled' => true]);
        $device->unsetRelation('vrfs');
        $module->discover($os);
        $this->assertSame(['ctx-blue', 'ctx-blue'], $module->probed);
        $this->assertSame(['', 'ctx-blue'], $device->getVrfContexts());
    }

    public function testNewContextsCanBeEnabledIndividually(): void
    {
        $device = Device::factory()->create(['os' => 'generic']);
        $os = Mockery::mock(OS::class);
        $os->shouldReceive('getDevice')->andReturn($device);
        $os->shouldReceive('discoverVrfs')->andReturnUsing(fn () => collect([
            new VrfModel(['vrf_oid' => '1', 'vrf_name' => 'blue', 'context_name' => 'ctx-blue', 'mplsVpnVrfDescription' => '']),
        ]));
        $os->shouldReceive('discoverVrfPorts')->andReturn(collect());
        $module = new GlobalContextTestVrf;

        LibrenmsConfig::set('snmp.vrf_contexts', false);
        $module->discover($os);
        $this->assertFalse($device->vrfs()->sole()->context_verified);
        $this->assertFalse($device->vrfs()->sole()->context_enabled);
        $this->assertSame([], $module->probed);
        $this->assertSame(['ctx-blue'], $device->getSkippedVrfContexts());

        LibrenmsConfig::set('snmp.vrf_contexts', true);
        $module->discover($os);
        $this->assertFalse($device->vrfs()->sole()->context_enabled);
        $this->assertSame([], $module->probed);

        LibrenmsConfig::set('snmp.vrf_contexts', false);
        $device->vrfs()->sole()->update(['context_enabled' => true]);
        $device->unsetRelation('vrfs');
        $module->discover($os);
        $this->assertTrue($device->vrfs()->sole()->context_verified);
        $this->assertTrue($device->vrfs()->sole()->context_enabled);
        $this->assertSame(['ctx-blue'], $module->probed);
        $this->assertSame(['', 'ctx-blue'], $device->getVrfContexts());
    }

    public function testRoutingCanEnableContextWhenGlobalDefaultIsDisabled(): void
    {
        $device = Device::factory()->create();
        $vrf = VrfModel::factory()->for($device)->create(['context_name' => 'ctx-blue', 'context_enabled' => false, 'context_verified' => true]);
        $controller = Mockery::mock(EditRoutingController::class)->makePartial();
        $controller->shouldReceive('authorize')->once();
        LibrenmsConfig::set('snmp.vrf_contexts', false);

        $response = $controller->updateVrf(Request::create('/', 'PATCH', ['context_enabled' => true]), $device, $vrf);

        $this->assertSame('answering', $response->getData(true)['status']);
        $this->assertTrue($vrf->fresh()->context_enabled);
    }

    public function testSyncKeepsDisabledContextsAndRemovesMissingContexts(): void
    {
        $device = Device::factory()->create();
        VrfModel::factory()->for($device)->create([
            'context_name' => 'disabled', 'context_enabled' => false, 'context_verified' => true,
        ]);
        BgpPeer::factory()->for($device)->create(['context_name' => 'disabled']);
        BgpPeer::factory()->for($device)->create(['context_name' => 'missing']);
        $sync = new GlobalContextTestSync;

        $sync->sync($device);
        $this->assertSame(['disabled'], $device->bgppeers()->pluck('context_name')->all());
    }

    public function testManuallyAddedContextUsesGlobalDefault(): void
    {
        $device = Device::factory()->create();
        $controller = Mockery::mock(EditRoutingController::class)->makePartial();
        $controller->shouldReceive('authorize')->once();
        LibrenmsConfig::set('snmp.vrf_contexts', false);

        $response = $controller->storeVrf(Request::create('/', 'POST', ['context' => 'ctx-blue']), $device);

        $this->assertFalse($device->vrfs()->sole()->context_enabled);
        $this->assertSame('disabled', $response->getData(true)['vrfs'][0]['status']);
    }

    public function testManualContextKeepsItsPreferenceWhenDeviceListsTheVrf(): void
    {
        $device = Device::factory()->create(['os' => 'generic']);
        VrfModel::factory()->for($device)->create([
            'vrf_oid' => VrfModel::contextOnlyOid('ctx-blue'),
            'vrf_name' => 'blue',
            'context_name' => 'ctx-blue',
            'context_manual' => true,
            'vrf_manual' => true,
            'context_enabled' => true,
        ]);
        $os = Mockery::mock(OS::class);
        $os->shouldReceive('getDevice')->andReturn($device);
        $os->shouldReceive('discoverVrfs')->andReturn(collect([
            new VrfModel(['vrf_oid' => '1', 'vrf_name' => 'blue', 'mplsVpnVrfDescription' => '']),
        ]));
        $os->shouldReceive('discoverVrfPorts')->andReturn(collect());
        LibrenmsConfig::set('snmp.vrf_contexts', false);
        $module = new GlobalContextTestVrf;

        $module->discover($os);

        $this->assertTrue($device->vrfs()->sole()->context_enabled);
        $this->assertSame(['ctx-blue'], $module->probed);
    }
}

class GlobalContextTestVrf extends Vrf
{
    /** @var list<string> */
    public array $probed = [];

    protected function checkContext(string $context): bool
    {
        $this->probed[] = $context;

        return true;
    }
}

class GlobalContextTestSync
{
    use SyncsModels;

    public function sync(Device $device): void
    {
        $this->syncVrfContextModels($device, 'bgppeers', collect());
    }
}
