<?php

namespace LibreNMS\Tests\Feature\Jobs;

use App\Actions\Device\CheckDeviceAvailability;
use App\Jobs\DiscoverDevice;
use App\Jobs\DispatchPollingWork;
use App\Models\Device;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Queue\Jobs\FakeJob;
use LibreNMS\Tests\DBTestCase;
use LibreNMS\Util\ModuleList;
use RuntimeException;

final class DiscoverDeviceTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testFailedQueuedDiscoveryIsMarkedAsRun(): void
    {
        $device = Device::factory()->create(['hostname' => '192.0.2.1', 'status' => 1, 'last_discovered' => null]);
        $this->app->instance(CheckDeviceAvailability::class, new class
        {
            public function execute(): never
            {
                throw new RuntimeException('discovery failed');
            }
        });

        try {
            DiscoverDevice::dispatch($device->device_id, ModuleList::fromUserOverrides([]))->onConnection('sync');
            $this->fail('Discovery should have failed');
        } catch (RuntimeException $e) {
            $this->assertSame('discovery failed', $e->getMessage());
        }

        $this->assertNotNull($device->fresh()->last_discovered, 'a failed discovery should not be dispatched again until the next interval');
    }

    public function testDownDeviceIsRetriedAndNotMarkedAsDiscovered(): void
    {
        $device = Device::factory()->create(['hostname' => '192.0.2.1', 'status' => 0, 'last_discovered' => null]);
        $this->app->instance(CheckDeviceAvailability::class, new class
        {
            public function execute(Device $device): bool
            {
                $device->status = false;
                $device->status_reason = 'icmp';

                return false;
            }
        });

        $job = (new DiscoverDevice($device->device_id, ModuleList::fromUserOverrides([])))->withFakeQueueInteractions();
        $job->handle();
        $job->assertReleased(DispatchPollingWork::downRetryBackoff()[0]);
        $this->assertNull($device->fresh()->last_discovered, 'a down device should not be marked as discovered');

        $lastAttempt = new FakeJob;
        $job = new DiscoverDevice($device->device_id, ModuleList::fromUserOverrides([]));
        $lastAttempt->attempts = $job->tries;
        $job->setJob($lastAttempt)->handle();
        $job->assertNotReleased();
        $this->assertNull($device->fresh()->last_discovered, 'a down device should not be marked as discovered');
    }
}
