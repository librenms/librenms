<?php

namespace LibreNMS\Tests\Unit;

use LibreNMS\Data\Source\Snmp\SnmpQueryBuilder;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Exceptions\SnmpException;
use LibreNMS\OS;
use LibreNMS\Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;

class Aos7SnmpFailureTest extends TestCase
{
    #[DataProvider('failedWalks')]
    public function testConnectionFailureDoesNotReturnAnEmptyInventory(string $method, string $failedOid): void
    {
        $query = Mockery::mock(SnmpQueryBuilder::class);
        $values = [
            'ALCATEL-IND1-HEALTH-MIB::healthModuleCpu1MinAvg' => ['ALCATEL-IND1-HEALTH-MIB::healthModuleCpu1MinAvg.0' => '22'],
            'ALCATEL-IND1-SYSTEM-MIB::systemVcHardwareMemorySize' => ['ALCATEL-IND1-SYSTEM-MIB::systemVcHardwareMemorySize.1' => '1024'],
            'ALCATEL-IND1-HEALTH-MIB::healthModuleMemory1MinAvg' => ['ALCATEL-IND1-HEALTH-MIB::healthModuleMemory1MinAvg.1' => '50'],
            'ALCATEL-IND1-HEALTH-MIB::healthModuleChassisId' => ['ALCATEL-IND1-HEALTH-MIB::healthModuleChassisId.0' => '0'],
        ];

        foreach (array_unique([...array_keys($values), $failedOid]) as $oid) {
            $response = $oid === $failedOid
                ? new SnmpResponse([], 'Timeout: No Response from udp:192.0.2.1:161', 1)
                : new SnmpResponse($values[$oid] ?? []);
            $query->shouldReceive('walk')->with($oid)->andReturn($response);
        }
        $this->app->instance(SnmpQueryBuilder::class, $query);

        $device = ['os' => 'aos7'];
        $this->expectException(SnmpException::class);
        OS::make($device)->$method();
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function failedWalks(): array
    {
        return [
            'CPU usage' => ['discoverProcessors', 'ALCATEL-IND1-HEALTH-MIB::healthModuleCpu1MinAvg'],
            'CPU chassis IDs' => ['discoverProcessors', 'ALCATEL-IND1-HEALTH-MIB::healthModuleChassisId'],
            'memory capacity' => ['discoverMempools', 'ALCATEL-IND1-SYSTEM-MIB::systemVcHardwareMemorySize'],
            'memory usage' => ['discoverMempools', 'ALCATEL-IND1-HEALTH-MIB::healthModuleMemory1MinAvg'],
            'memory chassis IDs' => ['discoverMempools', 'ALCATEL-IND1-HEALTH-MIB::healthModuleChassisId'],
            'NAC clients' => ['pollNac', 'ALCATEL-IND1-DA-MIB::alaDaMacVlanUserTable'],
        ];
    }

    public function testEmptyNacTableStillReturnsAnEmptyCollection(): void
    {
        $query = Mockery::mock(SnmpQueryBuilder::class);
        $query->shouldReceive('walk')->with('ALCATEL-IND1-DA-MIB::alaDaMacVlanUserTable')->andReturn(new SnmpResponse);
        $this->app->instance(SnmpQueryBuilder::class, $query);

        $device = ['os' => 'aos7'];
        $os = OS::make($device);
        $this->assertInstanceOf(\LibreNMS\Interfaces\Polling\NacPolling::class, $os);
        $this->assertCount(0, $os->pollNac());
    }

    public function testTransceiverWalkConnectionFailureDoesNotReturnEmptyInventory(): void
    {
        $query = Mockery::mock(SnmpQueryBuilder::class);
        $query->shouldReceive('walk')->with('ALCATEL-IND1-PORT-MIB::ddmPortRxOpticalPower')
            ->andReturn(new SnmpResponse([], 'Timeout: No Response from udp:192.0.2.1:161', 1));
        $this->app->instance(SnmpQueryBuilder::class, $query);

        $os = Mockery::mock(\LibreNMS\OS\Aos7::class)->makePartial();
        $os->shouldNotReceive('getDevice');

        $this->expectException(SnmpException::class);
        $os->discoverTransceivers();
    }

    public function testEmptyTransceiverWalkDoesNotLoadPortInventory(): void
    {
        $query = Mockery::mock(SnmpQueryBuilder::class);
        $query->shouldReceive('walk')->with('ALCATEL-IND1-PORT-MIB::ddmPortRxOpticalPower')
            ->andReturn(new SnmpResponse);
        $this->app->instance(SnmpQueryBuilder::class, $query);

        $os = Mockery::mock(\LibreNMS\OS\Aos7::class)->makePartial();
        $os->shouldNotReceive('getDevice');

        $this->assertCount(0, $os->discoverTransceivers());
    }
}
