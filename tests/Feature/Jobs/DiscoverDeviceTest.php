<?php

namespace LibreNMS\Tests\Feature\Jobs;

use App\Actions\Device\CheckDeviceAvailability;
use App\Events\DiscoveringModule;
use App\Exceptions\DiscoveryFailedException;
use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Jobs\DiscoverDevice;
use App\Jobs\DispatchPollingWork;
use App\Jobs\PollDevice;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Queue\Jobs\FakeJob;
use Illuminate\Support\Facades\Event;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\Methods\UnixAgentPollingMethod;
use LibreNMS\Polling\PerDeviceMethodResults;
use LibreNMS\Tests\DBTestCase;
use LibreNMS\Tests\Traits\FakesPollingMethods;
use LibreNMS\Util\ModuleList;

final class DiscoverDeviceTest extends DBTestCase
{
    use DatabaseTransactions;
    use FakesPollingMethods;

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
        $device = Device::factory()->create(['hostname' => '192.0.2.1', 'status' => 1, 'last_discovered' => null]);
        $this->fakeAvailability(true);

        (new DiscoverDevice($device->device_id, ModuleList::fromUserOverrides(['availability'])))->withFakeQueueInteractions()->handle();

        $device->refresh();
        $this->assertEquals(now(), $device->last_discovered);
        $this->assertNotNull($device->last_discovered_timetaken);
    }

    public function testOnlyEnabledModulesAreDiscovered(): void
    {
        Event::fake([DiscoveringModule::class]);
        LibrenmsConfig::set('discovery_modules', ['mempools' => false]);
        $device = Device::factory()->create(['hostname' => '192.0.2.1', 'status' => 1]);
        DevicePollingMethod::factory()->for($device)->create(['method_type' => PollingMethodType::UnixAgent]);
        $this->fakeAvailability(true);
        $this->fakeMethod(UnixAgentPollingMethod::class, true, ['output' => $this->agentMemOutput(100, 50)]);

        (new DiscoverDevice($device->device_id, ModuleList::fromUserOverrides([])))->handle();

        Event::assertNotDispatched(DiscoveringModule::class, fn (DiscoveringModule $event): bool => $event->module === 'mempools');
        $this->assertSame(0, $device->mempools()->count());

        $device->setAttrib('discover_mempools', 1);
        DeviceCache::flush(); // the job loads the device again in a new run
        (new DiscoverDevice($device->device_id, ModuleList::fromUserOverrides([])))->handle();

        Event::assertDispatched(DiscoveringModule::class, fn (DiscoveringModule $event): bool => $event->module === 'mempools');
        $this->assertSame(1, $device->mempools()->count());
    }

    public function testDeletedDeviceIsSkipped(): void
    {
        $device = Device::factory()->create();
        $device_id = $device->device_id;
        $device->delete();
        $count = Device::count();

        (new DiscoverDevice($device_id, ModuleList::fromUserOverrides([])))->withFakeQueueInteractions()->handle();
        (new PollDevice($device_id, ModuleList::fromUserOverrides([])))->withFakeQueueInteractions()->handle();

        $this->assertSame($count, Device::count(), 'a deleted device should not be recreated');
    }

    public function testDeviceMovedToAnotherPollerGroupIsSkipped(): void
    {
        $device = Device::factory()->create(['hostname' => '192.0.2.1', 'poller_group' => 2]);
        $availability = $this->fakeAvailability(true);
        $modules = ModuleList::fromUserOverrides(['availability']);

        (new DiscoverDevice($device->device_id, $modules))->onQueue('discovery-1')->withFakeQueueInteractions()->handle();
        (new PollDevice($device->device_id, $modules))->onQueue('poll')->withFakeQueueInteractions()->handle();

        $this->assertSame(0, $availability->checks, 'a job queued for the old poller group should not run');
    }

    public function testUniquePerQueue(): void
    {
        $modules = ModuleList::fromUserOverrides([]);

        $this->assertNotEquals(
            (new PollDevice(1, $modules))->onQueue('poll-1')->uniqueId(),
            (new PollDevice(1, $modules))->onQueue('poll-2')->uniqueId(),
        );
        $this->assertNotEquals(
            (new DiscoverDevice(1, $modules))->onQueue('discovery')->uniqueId(),
            (new DiscoverDevice(1, $modules))->onQueue('discovery-2')->uniqueId(),
        );
    }

    public function testRetriesMatchPolling(): void
    {
        $discover = new DiscoverDevice(1, ModuleList::fromUserOverrides([]));
        $poll = new PollDevice(1, ModuleList::fromUserOverrides([]));

        $this->assertSame($poll->backoff(), $discover->backoff());
        $this->assertSame($poll->tries, $discover->tries);
        $this->assertCount($discover->tries - 1, $discover->backoff());
    }

    public function testPollLockCoversRetries(): void
    {
        $poll = new PollDevice(1, ModuleList::fromUserOverrides([]));

        // a slow poll does not outlast it, a lock left behind holds up the device for a timeout and a poll interval at most
        $this->assertSame($poll->timeout + DispatchPollingWork::pollFrequency(), $poll->uniqueFor());
        $this->assertGreaterThan(array_sum($poll->backoff()) + DispatchPollingWork::pollInterval(), $poll->uniqueFor(), 'retries of a down device');
    }

    private function fakeAvailability(bool $up): object
    {
        return $this->app->instance(CheckDeviceAvailability::class, new class($up)
        {
            public int $checks = 0;

            public function __construct(private readonly bool $up)
            {
            }

            public function execute(PerDeviceMethodResults $methodResults, bool $commit = false): bool
            {
                $device = $methodResults->device;
                $this->checks++;
                $device->status = $this->up;
                $device->status_reason = $this->up ? '' : 'icmp';

                return $this->up;
            }
        });
    }
}
