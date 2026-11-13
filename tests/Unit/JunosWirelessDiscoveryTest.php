<?php

namespace LibreNMS\Tests\Unit;

use LibreNMS\Data\Source\Snmp\SnmpQueryBuilder;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\OS;
use LibreNMS\OS\Junos;
use LibreNMS\Tests\TestCase;
use Mockery;

final class JunosWirelessDiscoveryTest extends TestCase
{
    public function testCellularSignalsUseWirelessTypesAndStableIdentity(): void
    {
        $query = Mockery::mock(SnmpQueryBuilder::class);
        $query->shouldReceive('cache')->andReturnSelf();
        $query->shouldReceive('walk')->andReturn(new SnmpResponse([
            'JUNIPER-WIRELESS-WAN-MIB::jnxWirelessWANNetworkInfoNetwork.161' => 'SoftBank',
            'JUNIPER-WIRELESS-WAN-MIB::jnxWirelessWANNetworkInfoCurrentServiceMode.161' => 'LTE',
            'JUNIPER-WIRELESS-WAN-MIB::jnxWirelessWANNetworkInfoSNR.161' => '-1',
            'JUNIPER-WIRELESS-WAN-MIB::jnxWirelessWANNetworkInfoRSRP.161' => '-98',
            'JUNIPER-WIRELESS-WAN-MIB::jnxWirelessWANNetworkInfoRSRQ.161' => '-12',
            'JUNIPER-WIRELESS-WAN-MIB::jnxWirelessWANNetworkInfoRSSI.161' => '-65 dB',
            'JUNIPER-WIRELESS-WAN-MIB::jnxWirelessWANNetworkInfoSNR[.162]' => '0',
            'JUNIPER-WIRELESS-WAN-MIB::jnxWirelessWANNetworkInfoSNR.163' => 'N/A',
            'JUNIPER-WIRELESS-WAN-MIB::jnxWirelessWANNetworkInfoRSSI.163' => '0',
        ]));
        $this->app->instance(SnmpQueryBuilder::class, $query);
        $device = ['device_id' => 1, 'os' => 'junos', 'hostname' => 'fixture.invalid'];
        $os = OS::make($device);
        $this->assertInstanceOf(Junos::class, $os);
        $this->assertContains(WirelessSnrDiscovery::class, class_implements($os));
        foreach (['Snr' => -1, 'Rsrp' => -98, 'Rsrq' => -12, 'Rssi' => -65] as $type => $value) {
            $models = array_map(fn ($sensor) => $sensor->toModel(), $os->{'discoverWireless' . $type}());
            $this->assertCount($type === 'Snr' ? 2 : 1, $models);
            $this->assertEquals($value, $models[0]->sensor_current);
            $this->assertSame(strtolower($type), $models[0]->sensor_class->value);
            $this->assertEquals(161, $models[0]->sensor_index);
            $this->assertStringContainsString('SoftBank LTE', $models[0]->sensor_descr);
            if ($type === 'Snr') {
                $this->assertNull($models[0]->sensor_limit);
                $this->assertEquals(0, $models[1]->sensor_current);
                $this->assertEquals(162, $models[1]->sensor_index);
                $this->assertStringContainsString('Cellular Unknown', $models[1]->sensor_descr);
            }
        }
    }
}
