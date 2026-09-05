<?php

namespace LibreNMS\Tests\Feature;

use App\Models\Device;
use App\Models\EntPhysical;
use App\Models\Eventlog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use LibreNMS\Exceptions\EntityPhysicalCollectionException;
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
     * @param  bool  $collectionFailed  throw as a failed SNMP collection would
     */
    private function runDiscovery(Device $device, Collection $discovered, bool $collectionFailed = false): void
    {
        $os = Mockery::mock(OS::class);
        $os->shouldReceive('getDevice')->andReturn($device);

        if ($collectionFailed) {
            $os->shouldReceive('discoverEntityPhysical')
                ->andThrow(new EntityPhysicalCollectionException('Timeout: No Response from udp:127.0.0.1:161', 120, true));
        } else {
            $os->shouldReceive('discoverEntityPhysical')->andReturn($discovered);
        }

        (new EntityPhysical)->discover($os);
    }

    public function testFailedCollectionDoesNotWipeExistingInventory(): void
    {
        $device = Device::factory()->create();
        EntPhysical::factory()->count(5)->create(['device_id' => $device->device_id]);
        $this->assertEquals(5, $device->entityPhysical()->count());

        // The walk failed, so we know nothing about the device's inventory.
        // Syncing that non-result would delete all 5 rows.
        $this->runDiscovery($device, new Collection, collectionFailed: true);

        $this->assertEquals(5, $device->entityPhysical()->count(),
            'existing entPhysical inventory must be preserved when collection fails');
    }

    public function testGenuineEmptyDiscoveryPrunesExistingInventory(): void
    {
        $device = Device::factory()->create();
        EntPhysical::factory()->count(5)->create(['device_id' => $device->device_id]);

        // The device answered and reported no inventory: it really has none now
        // (stripped, replaced, or the MIB was removed). Those rows must go, or a
        // stale inventory is kept forever with no way to converge.
        $this->runDiscovery($device, new Collection, collectionFailed: false);

        $this->assertEquals(0, $device->entityPhysical()->count(),
            'a device that genuinely reports no inventory must have its stale rows pruned');
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

    public function testTheExceptionMessageNamesTheRowCountAndTheTimeoutHint(): void
    {
        // partial walk on a device still using the default 1s timeout: the operator
        // needs to know rows arrived (so the agent is alive, just slow) and that the
        // timeout is theirs to raise.
        $partial = new EntityPhysicalCollectionException('Timeout: No Response from udp:10.0.0.1:161', 120, true);
        $this->assertStringContainsString('120 rows', $partial->getMessage());
        $this->assertStringContainsString('keeping existing inventory', $partial->getMessage());
        $this->assertStringContainsString('devices.timeout', $partial->getMessage());

        // nothing came back at all: "after 0 rows" is noise, omit it
        $nothing = new EntityPhysicalCollectionException('Timeout: No Response from udp:10.0.0.1:161', 0, true);
        $this->assertStringNotContainsString('0 rows', $nothing->getMessage());
        $this->assertStringContainsString('keeping existing inventory', $nothing->getMessage());

        // the timeout was already raised and it still failed: do not advise raising it
        $tuned = new EntityPhysicalCollectionException('Timeout: No Response from udp:10.0.0.1:161', 120, false);
        $this->assertStringContainsString('120 rows', $tuned->getMessage());
        $this->assertStringNotContainsString('devices.timeout', $tuned->getMessage());
    }

    public function testFailedCollectionIsRecordedOnTheDeviceEventlog(): void
    {
        $device = Device::factory()->create();
        EntPhysical::factory()->count(5)->create(['device_id' => $device->device_id]);

        $this->runDiscovery($device, new Collection, collectionFailed: true);

        $this->assertDatabaseHas('eventlog', [
            'device_id' => $device->device_id,
            'type' => 'discovery',
        ]);
        $event = Eventlog::where('device_id', $device->device_id)
            ->where('type', 'discovery')->first();
        $this->assertStringContainsString('keeping existing inventory', $event->message,
            'the operator must be told on the device page, not only in librenms.log');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
