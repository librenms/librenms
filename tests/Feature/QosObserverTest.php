<?php

namespace LibreNMS\Tests\Feature;

use App\Models\Device;
use App\Models\Qos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;

final class QosObserverTest extends TestCase
{
    use RefreshDatabase;

    private function pollQos(int $packetsOut, int $packetsDropOut): Qos
    {
        $device = Device::factory()->create();
        $qos = Qos::create([
            'device_id' => $device->device_id,
            'type' => 'cisco_cbqos_classmap',
            'title' => 'class-default',
            'rrd_id' => 'port-1-cbqos-1-2',
            'snmp_idx' => '1.2',
            'ingress' => 0,
            'egress' => 1,
        ]);
        $qos->forceFill(['last_polled' => 1000, 'last_packets_out' => 0, 'last_packets_drop_out' => 0])->save();

        $qos->forceFill(['last_polled' => 1300, 'last_packets_out' => $packetsOut, 'last_packets_drop_out' => $packetsDropOut])->save();

        return $qos->fresh();
    }

    public function testDropPercentageCalculated(): void
    {
        $qos = $this->pollQos(3000, 300);

        $this->assertEquals(10, $qos->packets_out_rate);
        $this->assertEquals(1, $qos->packets_drop_out_rate);
        $this->assertEquals(0.1, $qos->packets_drop_out_pct);
    }

    public function testDropsExceedingPrePolicyCountersDoNotOverflow(): void
    {
        // Catalyst 3850 reports drops while pre-policy counters stay near zero
        $qos = $this->pollQos(300, 300000000);

        $this->assertEquals(1, $qos->packets_out_rate);
        $this->assertEquals(1000000, $qos->packets_drop_out_rate);
        $this->assertNull($qos->packets_drop_out_pct);
    }

    public function testZeroPrePolicyCounters(): void
    {
        $qos = $this->pollQos(0, 300000000);

        $this->assertEquals(1000000, $qos->packets_drop_out_rate);
        $this->assertNull($qos->packets_drop_out_pct);
    }
}
