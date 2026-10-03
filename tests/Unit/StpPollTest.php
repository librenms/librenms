<?php

namespace LibreNMS\Tests\Unit;

use App\Facades\LibrenmsConfig;
use App\Models\PortStp;
use Illuminate\Support\Collection;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\OS;
use LibreNMS\Tests\TestCase;
use SnmpQuery;

final class StpPollTest extends TestCase
{
    public function testStpPollingIsOffByDefault(): void
    {
        $this->assertFalse(LibrenmsConfig::get('poller_modules.stp'));
        $this->assertTrue(LibrenmsConfig::get('discovery_modules.stp'));
    }

    public function testPollStpPortsSkipsPortsNotDiscovered(): void
    {
        // port 5 was discovered, port 9 is returned by the device but is not in our list
        $known = new PortStp(['vlan' => 1, 'port_index' => 5]);
        $response = new SnmpResponse([
            'BRIDGE-MIB::dot1dStpPortState.5' => 'forwarding',
            'BRIDGE-MIB::dot1dStpPortEnable.5' => 'enabled',
            'BRIDGE-MIB::dot1dStpPortState.9' => 'blocking',
            'BRIDGE-MIB::dot1dStpPortEnable.9' => 'enabled',
        ]);
        $query = SnmpQuery::partialMock();
        $query->shouldReceive('context')->andReturnSelf();
        $query->shouldReceive('enumStrings')->andReturnSelf();
        $query->shouldReceive('get')->andReturn($response);

        $device = ['device_id' => 1, 'os' => 'generic'];
        $os = OS::make($device);

        $result = $os->pollStpPorts(new Collection([$known]));

        $this->assertCount(1, $result);
        $this->assertSame('forwarding', $result->first()->state);
        $this->assertSame('enabled', $result->first()->enable);
    }
}
