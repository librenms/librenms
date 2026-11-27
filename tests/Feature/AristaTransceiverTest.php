<?php

namespace LibreNMS\Tests\Feature;

use App\Models\Device;
use App\Models\Port;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\OS;
use LibreNMS\Tests\DBTestCase;
use LibreNMS\Data\Source\Snmp\SnmpQueryBuilder;
use LibreNMS\Data\Source\Snmp\SnmpQueryInterface;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\OS\AristaEos;

final class AristaTransceiverTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testNativeTransceiverAssociationsAndUnsupportedLayouts(): void
    {
        foreach (['odd', 'consecutive', 'eight_by_fifty', 'standalone', 'missing_port', 'duplicate_lane', 'other_media'] as $layout) {
            $device = Device::factory()->create(['os' => 'arista_eos']);
            $suffixes = match ($layout) {
                'consecutive' => [1, 2, 3, 4],
                'eight_by_fifty' => range(1, 8),
                'standalone' => [],
                'missing_port' => [1, 3, 5],
                default => [1, 3, 5, 7],
            };
            $entities = [100 => [
                'entPhysicalName' => 'Xcvr for Ethernet25: PLR4',
                'entPhysicalDescr' => $layout === 'other_media' ? '400GBASE-SR8' : '400GBASE-PLR4',
            ]];
            // The device also exposes electrical lanes 4-7; only optical positions 0-3 map to 4x100G.
            foreach (range(0, 7) as $lane) {
                $entities[200 + $lane] = ['entPhysicalName' => "Lane $lane for Xcvr for Ethernet25",
                    'entPhysicalClass' => 'module', 'entPhysicalContainedIn' => 100, 'entPhysicalParentRelPos' => $lane];
            }
            if ($layout === 'duplicate_lane') {
                $entities[250] = $entities[201];
            }
            foreach ($suffixes as $suffix) {
                Port::factory()->create(['device_id' => $device->device_id, 'ifName' => "Ethernet25/$suffix", 'ifIndex' => 1000 + $suffix,
                    'ifSpeed' => $layout === 'eight_by_fifty' ? 50000000000 : 100000000000, 'deleted' => 0]);
            }
            if ($layout === 'standalone') {
                Port::factory()->create(['device_id' => $device->device_id, 'ifName' => 'Ethernet25', 'ifSpeed' => 400000000000, 'deleted' => 0]);
            }
            $response = \Mockery::mock(SnmpResponse::class);
            $response->shouldReceive('table')->with(1)->andReturn($entities);
            $query = \Mockery::mock(SnmpQueryInterface::class);
            $query->shouldReceive('cache', 'hideMib', 'enumStrings')->andReturnSelf();
            $query->shouldReceive('walk')->with('ENTITY-MIB::entPhysicalTable')->andReturn($response);
            $this->app->bind(SnmpQueryBuilder::class, fn () => $query);
            $data = $device->toArray();
            $os = OS::make($data);
            $this->assertInstanceOf(AristaEos::class, $os);
            $transceivers = $os->discoverTransceivers();
            if (! in_array($layout, ['odd', 'consecutive'])) {
                $this->assertCount(0, $transceivers, $layout);
                continue;
            }
            $this->assertCount(4, $transceivers, $layout);
            foreach ($suffixes as $lane => $suffix) {
                $this->assertSame("Ethernet25/$suffix", $transceivers[200 + $lane]->port->ifName);
                $this->assertSame(200 + $lane, $transceivers[200 + $lane]->entity_physical_index);
                $this->assertSame(1, $transceivers[200 + $lane]->channels);
            }
        }
    }
}
