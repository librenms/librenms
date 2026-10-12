<?php

namespace LibreNMS\Tests\Feature\Jobs;

use App\Actions\Device\CheckDeviceAvailability;
use App\Exceptions\DiscoveryFailedException;
use App\Jobs\DiscoverDevice;
use App\Jobs\PollDevice;
use App\Models\Device;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Queue\Jobs\FakeJob;
use LibreNMS\Tests\DBTestCase;
use LibreNMS\Util\ModuleList;

final class DiscoverDeviceTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testDownDeviceIsRetriedAndNotMarkedAsDiscovered(): void
    {
        $device = Device::factory()->create(['hostname' => '192.0.2.1', 'status' => 0, 'last_discovered' => null]);
        $this->fakeAvailability(false);

        try {
            (new DiscoverDevice($device->device_id, ModuleList::fromUserOverrides([])))->withFakeQueueInteractions()->handle();
            $this->fail('A down device should be retried');
        } catch (DiscoveryFailedException) {
            $this->assertNull($device->fresh()->last_discovered, 'a down device should not be marked as discovered');
        }
    }

    public function testDownDeviceIsNotRetriedOnTheLastAttempt(): void
    {
        $device = Device::factory()->create(['hostname' => '192.0.2.1', 'status' => 0, 'last_discovered' => null]);
        $this->fakeAvailability(false);

        $job = new DiscoverDevice($device->device_id, ModuleList::fromUserOverrides([]));
        $lastAttempt = new FakeJob;
        $lastAttempt->attempts = $job->tries;
        $job->setJob($lastAttempt)->handle();

        $this->assertNull($device->fresh()->last_discovered, 'a down device should not be marked as discovered');
    }

    public function testDownDeviceIsNotRetriedWhenRunDirectly(): void
    {
        $device = Device::factory()->create(['hostname' => '192.0.2.1', 'status' => 0, 'last_discovered' => null]);
        $this->fakeAvailability(false);

        (new DiscoverDevice($device->device_id, ModuleList::fromUserOverrides([])))->handle();

        $this->assertNull($device->fresh()->last_discovered, 'a down device should not be marked as discovered');
    }

    public function testUpDeviceIsMarkedAsDiscovered(): void
    {
        $this->freezeSecond();
        $device = Device::factory()->create(['hostname' => '192.0.2.1', 'status' => 1, 'snmp_disable' => 1, 'last_discovered' => null]);
        $this->fakeAvailability(true);

        (new DiscoverDevice($device->device_id, ModuleList::fromUserOverrides(['availability'])))->withFakeQueueInteractions()->handle();

        $device->refresh();
        $this->assertEquals(now(), $device->last_discovered);
        $this->assertNotNull($device->last_discovered_timetaken);
    }

    public function testRetriesMatchPolling(): void
    {
        $discover = new DiscoverDevice(1, ModuleList::fromUserOverrides([]));
        $poll = new PollDevice(1, ModuleList::fromUserOverrides([]));

        $this->assertSame($poll->backoff(), $discover->backoff());
        $this->assertSame($poll->tries, $discover->tries);
        $this->assertCount($discover->tries - 1, $discover->backoff());
    }

    private function fakeAvailability(bool $up): void
    {
        $this->app->instance(CheckDeviceAvailability::class, new class($up)
        {
            public function __construct(private readonly bool $up)
            {
            }

            public function execute(Device $device): bool
            {
                $device->status = $this->up;
                $device->status_reason = $this->up ? '' : 'icmp';

                return $this->up;
            }
        });
    }
}
