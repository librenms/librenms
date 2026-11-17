<?php

namespace LibreNMS\Tests\Unit;

use LibreNMS\Data\Source\Snmp\SnmpQueryBuilder;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\OS;
use LibreNMS\Tests\TestCase;
use Mockery;

class Aos7ProcessorTest extends TestCase
{
    public function testUnconfiguredChassisUsesLegacyCmmCpu(): void
    {
        $processors = $this->discover([0 => '0', 1 => '0'], [0 => '22', 1 => '24']);

        $this->assertCount(1, $processors);
        $this->assertSame('0', $processors[0]->processor_index);
        $this->assertSame('Processor', $processors[0]->processor_descr);
        $this->assertSame('.1.3.6.1.4.1.6486.801.1.2.1.16.1.1.1.1.1.11.0', $processors[0]->processor_oid);
        $this->assertEquals(22, $processors[0]->processor_usage);
    }

    public function testUnconfiguredChassisDoesNotInventFallbackWhenCmmIsMissing(): void
    {
        $processors = $this->discover([1 => '0'], [1 => '24']);

        $this->assertCount(0, $processors);
    }

    public function testConfiguredStackTakesPrecedenceOverUnconfiguredRows(): void
    {
        $processors = $this->discover([0 => '0', 1 => '1', 1001 => '2'], [0 => '22', 1 => '24', 1001 => '17']);

        $this->assertSame(['vc.1', 'vc.2'], $processors->pluck('processor_index')->all());
    }

    public function testInvalidChassisOrUsageDoesNotCreateFallbackCpu(): void
    {
        $this->assertCount(0, $this->discover([0 => '-1', 1 => 'invalid'], [0 => '22', 1 => '24']));
        $this->assertCount(0, $this->discover([0 => '0', 1 => '0'], [0 => '101', 1 => '-1']));
    }

    public function testProcessorAndMemoryDiscoveryReuseChassisIds(): void
    {
        $query = Mockery::mock(SnmpQueryBuilder::class);
        foreach ([
            ['ALCATEL-IND1-HEALTH-MIB::healthModuleChassisId', 1001, '2'],
            ['ALCATEL-IND1-HEALTH-MIB::healthModuleCpu1MinAvg', 1001, '22'],
            ['ALCATEL-IND1-HEALTH-MIB::healthModuleMemory1MinAvg', 1001, '65'],
            ['ALCATEL-IND1-SYSTEM-MIB::systemVcHardwareMemorySize', 2, '880918528'],
        ] as [$oid, $index, $value]) {
            $query->shouldReceive('walk')->with($oid)->once()->andReturn(new SnmpResponse(["$oid.$index" => $value]));
        }
        $this->app->instance(SnmpQueryBuilder::class, $query);

        $device = ['os' => 'aos7'];
        $os = OS::make($device);
        $this->assertSame(['vc.2'], $os->discoverProcessors()->pluck('processor_index')->all());
        $this->assertSame(['vc.2'], $os->discoverMempools()->pluck('mempool_index')->all());
    }

    /**
     * @param  array<int, string>  $chassisIds
     * @param  array<int, string>  $usage
     * @return \Illuminate\Support\Collection<int, \App\Models\Processor>
     */
    private function discover(array $chassisIds, array $usage): \Illuminate\Support\Collection
    {
        $prefix = 'ALCATEL-IND1-HEALTH-MIB::';
        $query = Mockery::mock(SnmpQueryBuilder::class);
        foreach (['healthModuleCpu1MinAvg' => $usage, 'healthModuleChassisId' => $chassisIds] as $oid => $values) {
            $response = [];
            foreach ($values as $index => $value) {
                $response["$prefix$oid.$index"] = $value;
            }
            $query->shouldReceive('walk')->with("$prefix$oid")->andReturn(new SnmpResponse($response));
        }
        $this->app->instance(SnmpQueryBuilder::class, $query);

        $device = ['os' => 'aos7'];

        return OS::make($device)->discoverProcessors();
    }
}
