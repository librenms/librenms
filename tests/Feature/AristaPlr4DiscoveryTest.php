<?php

namespace LibreNMS\Tests\Feature;

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Jobs\DiscoverDevice;
use App\Models\Device;
use App\Models\Sensor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Data\Source\Icmp\Fping;
use LibreNMS\Data\Source\Icmp\FpingResponse;
use LibreNMS\Tests\DBTestCase;
use LibreNMS\Util\ModuleList;
use LibreNMS\Util\ModuleTestHelper;

final class AristaPlr4DiscoveryTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testRediscoveryUsesPolledPortInventoryWithoutChangingSensorIdentity(): void
    {
        $this->requireSnmpsim();
        $this->travelTo(new \DateTime('2022-01-01 00:00:00'));
        $this->app->bind(Fping::class, function () {
            $mock = \Mockery::mock(Fping::class);
            $mock->shouldReceive('ping')->andReturn(FpingResponse::artificialUp());

            return $mock;
        });
        $originalCi = getenv('CI');
        $discoveryModules = LibrenmsConfig::get('discovery_modules');
        $pollerModules = LibrenmsConfig::get('poller_modules');
        // Keep the simulated device after the helper's first discovery/poll cycle.
        putenv('CI=1');
        try {
            $helper = new ModuleTestHelper(new ModuleList(['os' => true, 'ports' => true, 'entity-physical' => true, 'transceivers' => true, 'sensors' => true]), 'arista_eos', 'plr4');
            $helper->setQuiet();
            $this->assertNotNull($helper->generateTestData($this->getSnmpsimIp(), $this->getSnmpsimPort()));
            $device = Device::where('hostname', $this->getSnmpsimIp())->firstOrFail();
            $before = Sensor::where('device_id', $device->device_id)->get()->keyBy('sensor_id');
            $this->assertCount(8, $before->where('sensor_class', 'dbm'));
            $this->assertFalse($before->contains(fn ($sensor) => str_contains($sensor->sensor_descr, 'optical lane')));

            DeviceCache::flush();
            ob_start();
            try {
                new DiscoverDevice($device->device_id, new ModuleList(['transceivers' => true, 'sensors' => true]))->handle();
            } finally {
                ob_end_clean();
            }
            $transceivers = $device->transceivers()->with('port')->get()->keyBy('entity_physical_index');
            $this->assertCount(4, $transceivers);
            $after = Sensor::where('device_id', $device->device_id)->get()->keyBy('sensor_id');
            $this->assertSame($before->keys()->all(), $after->keys()->all());
            foreach ($before as $id => $sensor) {
                $updated = $after[$id];
                $this->assertSame($sensor->sensor_oid, $updated->sensor_oid);
                $this->assertSame($sensor->sensor_index, $updated->sensor_index);
                $this->assertSame($sensor->sensor_current, $updated->sensor_current);
                if ($sensor->sensor_class !== 'dbm') {
                    $this->assertSame($sensor->sensor_descr, $updated->sensor_descr);

                    continue;
                }
                preg_match('/DOM (RX|TX) Power\s+For Ethernet25\/([1-4])$/', $sensor->sensor_descr, $match);
                $this->assertCount(3, $match);
                $this->assertSame('transceiver', $updated->group);
                $this->assertTrue($transceivers->has($updated->entPhysicalIndex));
                $this->assertSame('Ethernet25/' . (2 * (int) $match[2] - 1), $transceivers[$updated->entPhysicalIndex]->port->ifName);
                $lane = (int) $match[2];
                $this->assertSame('DOM ' . $match[1] . ' Power For Ethernet25/' . (2 * $lane - 1) . ' (optical lane ' . $lane . ')', $updated->sensor_descr);
            }
        } finally {
            putenv($originalCi === false ? 'CI' : 'CI=' . $originalCi);
            LibrenmsConfig::set('discovery_modules', $discoveryModules);
            LibrenmsConfig::set('poller_modules', $pollerModules);
        }
    }
}
