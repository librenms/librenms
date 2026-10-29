<?php

namespace LibreNMS\Tests\Feature\Modules;

use App\Models\Mempool;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Modules\Mempools;
use LibreNMS\Polling\Method\Methods\SnmpPollingMethod;
use LibreNMS\Polling\Method\Methods\UnixAgentPollingMethod;
use LibreNMS\Tests\InMemoryDbTestCase;
use LibreNMS\Tests\Traits\FakesPollingMethods;
use Mockery;

final class MempoolsTest extends InMemoryDbTestCase
{
    use FakesPollingMethods;

    public function testAgentOnlyDeviceDiscoversAndPollsRecordedAgentMempools(): void
    {
        $os = $this->makeOs([PollingMethodType::Icmp, PollingMethodType::UnixAgent]);
        $this->app->instance(UnixAgentPollingMethod::class, UnixAgentPollingMethod::recorded(base_path('tests/unix-agent/linux.txt')));
        $module = new Mempools;

        $this->assertTrue($module->shouldDiscover($os));
        $module->discover($os);

        // the recorded device has no swap, an empty swap mempool is rejected
        $mempools = Mempool::where('device_id', $os->getDeviceId())->get()->keyBy('mempool_class');
        $this->assertSame(['system', 'buffers', 'cached', 'available'], $mempools->keys()->all());
        $this->assertSame([PollingMethodType::UnixAgent], $mempools->pluck('polling_method')->unique()->values()->all());
        $this->assertEquals(65717444 * 1024, $mempools['system']->mempool_total);

        $datastore = Mockery::mock(DataStorageInterface::class);
        $datastore->shouldReceive('put')->times(4)->withArgs(fn ($device, $measurement) => $measurement == 'mempool');

        $os->getDevice()->unsetRelation('mempools');
        $this->assertTrue($module->shouldPoll($os));
        $module->poll($os, $datastore);
    }

    public function testSnmpIsPreferredAndReplacesAgentMempools(): void
    {
        $os = $this->makeOs([PollingMethodType::UnixAgent, PollingMethodType::Snmp]);
        Mempool::factory()->create(['device_id' => $os->getDeviceId(), 'mempool_type' => 'unix-agent', 'mempool_index' => '1', 'polling_method' => PollingMethodType::UnixAgent]);
        $this->fakeMethod(SnmpPollingMethod::class, true);
        $this->fakeSnmpMempools();
        $this->fakeMethod(UnixAgentPollingMethod::class, true, ['output' => $this->agentMemOutput(100, 50)]);

        (new Mempools)->discover($os);

        $mempools = Mempool::where('device_id', $os->getDeviceId())->get();
        $this->assertSame(['hrstorage'], $mempools->pluck('mempool_type')->all());
        $this->assertSame(PollingMethodType::Snmp, $mempools->first()?->polling_method);
        $this->assertFalse($this->wasChecked($os, PollingMethodType::UnixAgent));
    }

    public function testDoesNotFallBackWhenSnmpIsDown(): void
    {
        $os = $this->makeOs([PollingMethodType::Snmp, PollingMethodType::UnixAgent]);
        $this->fakeMethod(SnmpPollingMethod::class, false);
        $this->fakeMethod(UnixAgentPollingMethod::class, true, ['output' => $this->agentMemOutput(100, 50)]);

        $this->assertFalse((new Mempools)->shouldDiscover($os));
        $this->assertFalse($this->wasChecked($os, PollingMethodType::UnixAgent));
    }

    public function testMempoolsAreKeptWhenNoMethodHasAny(): void
    {
        $os = $this->makeOs([PollingMethodType::UnixAgent]);
        Mempool::factory()->create(['device_id' => $os->getDeviceId(), 'mempool_type' => 'unix-agent', 'mempool_index' => '1', 'polling_method' => PollingMethodType::UnixAgent]);
        $this->fakeMethod(UnixAgentPollingMethod::class, true); // incomplete output without the mem section

        $module = new Mempools;
        $this->assertTrue($module->shouldDiscover($os));
        $module->discover($os);

        $this->assertSame(1, Mempool::where('device_id', $os->getDeviceId())->count());
    }

    public function testFallsBackWhenSnmpHasNoMempools(): void
    {
        $os = $this->makeOs([PollingMethodType::Snmp, PollingMethodType::UnixAgent]);
        Mempool::factory()->create(['device_id' => $os->getDeviceId(), 'mempool_type' => 'unix-agent', 'mempool_index' => '1', 'polling_method' => PollingMethodType::UnixAgent]);
        $this->fakeMethod(SnmpPollingMethod::class, true);
        $this->fakeSnmpMempools(null);
        $this->fakeMethod(UnixAgentPollingMethod::class, true, ['output' => $this->agentMemOutput(100, 50)]);

        (new Mempools)->discover($os);

        $mempool = Mempool::where('device_id', $os->getDeviceId())->sole();
        $this->assertSame('unix-agent', $mempool->mempool_type);
        $this->assertSame(PollingMethodType::UnixAgent, $mempool->polling_method);
        $this->assertEquals(50, $mempool->mempool_perc);
    }

    public function testDoesNotPollMempoolsOfDisabledMethods(): void
    {
        $os = $this->makeOs([PollingMethodType::UnixAgent]);
        Mempool::factory()->create(['device_id' => $os->getDeviceId(), 'mempool_type' => 'hrstorage', 'mempool_index' => '1', 'polling_method' => PollingMethodType::Snmp]);

        $this->assertFalse((new Mempools)->shouldPoll($os));
        $this->assertFalse($this->wasChecked($os, PollingMethodType::UnixAgent));
    }

    public function testPollSkipsMempoolsOfUnavailableMethods(): void
    {
        $os = $this->makeOs([PollingMethodType::Snmp, PollingMethodType::UnixAgent]);
        Mempool::factory()->create(['device_id' => $os->getDeviceId(), 'mempool_type' => 'hrstorage', 'mempool_index' => '1', 'polling_method' => PollingMethodType::Snmp]);
        Mempool::factory()->create(['device_id' => $os->getDeviceId(), 'mempool_type' => 'unix-agent', 'mempool_index' => '1', 'polling_method' => PollingMethodType::UnixAgent]);
        $this->fakeMethod(SnmpPollingMethod::class, false);
        $this->fakeMethod(UnixAgentPollingMethod::class, true, ['output' => $this->agentMemOutput(100, 40)]);

        $datastore = Mockery::mock(DataStorageInterface::class);
        $datastore->shouldReceive('put')->once()->withArgs(fn ($device, $measurement, $tags) => $tags['mempool_type'] == 'unix-agent');

        (new Mempools)->poll($os, $datastore);

        $this->assertEquals(60, Mempool::where('mempool_type', 'unix-agent')->value('mempool_perc'));
    }
}
