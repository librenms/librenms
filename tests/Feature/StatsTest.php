<?php

namespace LibreNMS\Tests\Feature;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Tests\DBTestCase;
use LibreNMS\Util\Stats;

final class StatsTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testPortAssociationModeUsesTheDefaultWithoutAnOverride(): void
    {
        LibrenmsConfig::set('default_port_association_mode', 'ifIndex');
        $this->snmpDevice([]);
        $this->snmpDevice([]);
        $this->snmpDevice(['port_association_mode' => 'ifName']);
        DevicePollingMethod::factory()->create([
            'device_id' => Device::factory()->create()->device_id,
            'method_type' => PollingMethodType::Icmp, // ping only devices have no ports
        ]);

        $portAssoc = collect((new Stats)->dump()['data']['port_assoc'])->pluck('total', 'port_association_mode')->all();

        $this->assertEquals([1 => 2, 2 => 1], $portAssoc); // ifIndex => 2, ifName => 1, reported as the mode id
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    private function snmpDevice(array $settings): void
    {
        DevicePollingMethod::factory()->create([
            'device_id' => Device::factory()->create()->device_id,
            'method_type' => PollingMethodType::Snmp,
            'settings' => $settings,
        ]);
    }
}
