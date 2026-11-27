<?php

namespace LibreNMS\Tests\Feature\Modules;

use App\Data\TimeSeries\MetricIdentity;
use App\Facades\DeviceCache;
use App\Models\Device;
use App\Models\JuniAtmVp;
use App\Models\Port;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Data\Source\Snmp\SnmpQueryBuilder;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Interfaces\Data\WriteInterface;
use LibreNMS\Modules\JunoseAtmVp;
use LibreNMS\OS;
use LibreNMS\Tests\DBTestCase;
use Mockery;

final class JunoseAtmVpTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testPollingWritesTheVpMetricTheGraphsRead(): void
    {
        $device = Device::factory()->create(['os' => 'junose', 'hostname' => 'erx.example.com']);
        DeviceCache::setPrimary($device->device_id);
        $port = Port::factory()->for($device)->create(['ifIndex' => 42]);
        JuniAtmVp::factory()->create(['port_id' => $port->port_id, 'vp_id' => 7]);
        $deviceArray = DeviceCache::refresh($device->device_id)->toArray();
        $os = OS::make($deviceArray);

        $query = Mockery::mock(SnmpQueryBuilder::class);
        $query->shouldReceive('mibs', 'hideMib', 'cache')->andReturnSelf();
        $query->shouldReceive('walk')->andReturn(new SnmpResponse([
            'juniAtmVpStatsInCells.42.7' => '100',
            'juniAtmVpStatsOutPacketOctets.42.7' => '2000',
        ]));
        $this->app->instance(SnmpQueryBuilder::class, $query);

        $datastore = Mockery::mock(WriteInterface::class);
        $datastore->shouldReceive('write')->once()->withArgs(function (string $measurement, array $fields, array $tags, array $meta): bool {
            $this->assertSame('atm_vp', $measurement);
            $this->assertSame(['hostname' => 'erx.example.com', 'ifIndex' => 42, 'vp' => 7], $tags);
            $this->assertSame('100', $fields['incells']);
            $this->assertSame('2000', $fields['outpacketoctets']);
            $this->assertNull($fields['inpackets']);
            $this->assertInstanceOf(MetricIdentity::class, $meta['metric']);
            $this->assertArrayHasKey('rrd_def', $meta);

            return true;
        });

        (new JunoseAtmVp)->poll($os, $datastore);
    }
}
