<?php

namespace LibreNMS\Tests\Feature;

use App\Models\Device;
use App\Models\EntPhysical;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use LibreNMS\Modules\EntityPhysical;
use LibreNMS\OS;
use LibreNMS\Tests\TestCase;
use Mockery;

class EntityPhysicalDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Build an EntityPhysical module + a mocked OS whose discoverEntityPhysical()
     * returns $discovered, bound to $device.
     *
     * @param  Collection<int, EntPhysical>  $discovered
     */
    private function runDiscovery(Device $device, Collection $discovered): void
    {
        $os = Mockery::mock(OS::class);
        $os->shouldReceive('discoverEntityPhysical')->andReturn($discovered);
        $os->shouldReceive('getDevice')->andReturn($device);

        (new EntityPhysical)->discover($os);
    }

    public function testEmptyDiscoveryDoesNotWipeExistingInventory(): void
    {
        $device = Device::factory()->create();
        EntPhysical::factory()->count(5)->create(['device_id' => $device->device_id]);
        $this->assertEquals(5, $device->entityPhysical()->count());

        // A failed SNMP walk yields an empty collection — must NOT delete the 5 rows.
        $this->runDiscovery($device, new Collection);

        $this->assertEquals(5, $device->entityPhysical()->count(),
            'existing entPhysical inventory must be preserved when discovery returns empty');
    }

    public function testGenuineEmptyOnDeviceWithNoInventoryStaysEmpty(): void
    {
        $device = Device::factory()->create();
        $this->assertEquals(0, $device->entityPhysical()->count());

        // Empty discovery on a device that never had inventory: no-op, no crash.
        $this->runDiscovery($device, new Collection);

        $this->assertEquals(0, $device->entityPhysical()->count());
    }

    public function testNonEmptyDiscoverySyncsNormally(): void
    {
        $device = Device::factory()->create();
        EntPhysical::factory()->create([
            'device_id' => $device->device_id,
            'entPhysicalIndex' => 1,
            'entPhysicalDescr' => 'old-chassis',
        ]);

        // A good walk returns real rows — normal reconcile: index 1 updated, index 2 added.
        $discovered = new Collection([
            new EntPhysical([
                'entPhysicalIndex' => 1,
                'entPhysicalDescr' => 'new-chassis',
                'entPhysicalClass' => 'chassis',
            ]),
            new EntPhysical([
                'entPhysicalIndex' => 2,
                'entPhysicalDescr' => 'linecard',
                'entPhysicalClass' => 'module',
            ]),
        ]);
        $this->runDiscovery($device, $discovered);

        $this->assertEquals(2, $device->entityPhysical()->count());
        $this->assertEquals('new-chassis',
            $device->entityPhysical()->where('entPhysicalIndex', 1)->first()->entPhysicalDescr);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
