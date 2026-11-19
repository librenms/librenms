<?php

namespace LibreNMS\Tests\Unit;

use LibreNMS\Data\Source\Snmp\SnmpQueryBuilder;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Exceptions\SnmpException;
use LibreNMS\Interfaces\Polling\NacPolling;
use LibreNMS\OS;
use LibreNMS\Tests\TestCase;
use Mockery;

class Aos7SnmpFailureTest extends TestCase
{
    public function testNacWalkConnectionFailureDoesNotReturnAnEmptyInventory(): void
    {
        $query = Mockery::mock(SnmpQueryBuilder::class);
        $query->shouldReceive('walk')->with('ALCATEL-IND1-DA-MIB::alaDaMacVlanUserTable')
            ->andReturn(new SnmpResponse([], 'Timeout: No Response from udp:192.0.2.1:161', 1));
        $this->app->instance(SnmpQueryBuilder::class, $query);

        $device = ['os' => 'aos7'];
        $os = OS::make($device);
        $this->assertInstanceOf(NacPolling::class, $os);
        $this->expectException(SnmpException::class);
        $os->pollNac();
    }

    public function testEmptyNacTableStillReturnsAnEmptyCollection(): void
    {
        $query = Mockery::mock(SnmpQueryBuilder::class);
        $query->shouldReceive('walk')->with('ALCATEL-IND1-DA-MIB::alaDaMacVlanUserTable')->andReturn(new SnmpResponse);
        $this->app->instance(SnmpQueryBuilder::class, $query);

        $device = ['os' => 'aos7'];
        $os = OS::make($device);
        $this->assertInstanceOf(NacPolling::class, $os);
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
