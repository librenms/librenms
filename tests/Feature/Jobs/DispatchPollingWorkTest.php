<?php

namespace LibreNMS\Tests\Feature\Jobs;

use App\Jobs\DiscoverDevice;
use App\Jobs\DispatchPollingWork;
use App\Jobs\PollDevice;
use App\Models\Device;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use LibreNMS\Tests\DBTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class DispatchPollingWorkTest extends DBTestCase
{
    use DatabaseTransactions;

    /**
     * @param  int|null  $discovered  minutes since the last discovery started
     * @param  bool  $polled  the device has been polled before
     */
    #[DataProvider('discoveryProvider')]
    public function testDiscoveryDispatch(int $status, ?int $discovered, bool $polled, bool $expected): void
    {
        Queue::fake();
        $device = Device::factory()->create([
            'status' => $status,
            'last_discovered' => $discovered === null ? null : now()->subMinutes($discovered),
            'last_discovered_timetaken' => $discovered === null ? null : 0,
            'last_polled' => $polled ? now()->subMinute() : null,
        ]);

        (new DispatchPollingWork(poll: false, discover: true))->handle();

        if ($expected) {
            Queue::assertPushed(DiscoverDevice::class, fn (DiscoverDevice $job) => $job->device_id == $device->device_id);
        } else {
            Queue::assertNotPushed(DiscoverDevice::class, fn (DiscoverDevice $job) => $job->device_id == $device->device_id);
        }
    }

    /**
     * @return array<string, array{int, int|null, bool, bool}>
     */
    public static function discoveryProvider(): array
    {
        return [
            'new' => [1, null, false, true],
            'down, new' => [0, null, false, true],
            'rediscover requested' => [1, null, true, true],
            'down, rediscover requested' => [0, null, true, false],
            'due' => [1, 420, true, true],
            'not due' => [1, 60, true, false],
            'down, due' => [0, 420, true, false],
        ];
    }

    public function testScheduledOnSchedulerQueue(): void
    {
        $this->assertSame('scheduler', (new DispatchPollingWork)->queue);
    }

    public function testDiscoveryDueBeforeTheNextDispatchIsDelayed(): void
    {
        Queue::fake();
        $device = Device::factory()->create([
            'status' => 1,
            'last_discovered' => now()->subSeconds(21600 - 8),
            'last_discovered_timetaken' => 0,
        ]);

        (new DispatchPollingWork(poll: false, discover: true))->handle();

        // due 3 seconds early, at 21597 seconds
        Queue::assertPushed(DiscoverDevice::class, fn (DiscoverDevice $job) => $job->device_id == $device->device_id && $job->delay >= 4 && $job->delay <= 6);
    }

    /**
     * @param  bool  $discovered  the device has completed discovery
     * @param  int|null  $polled  minutes since the last poll started
     */
    #[DataProvider('pollProvider')]
    public function testPollDispatch(int $status, bool $discovered, ?int $polled, bool $expected): void
    {
        Queue::fake();
        $device = Device::factory()->create([
            'status' => $status,
            'last_discovered' => $discovered ? now()->subMinute() : null,
            'last_polled' => $polled === null ? null : now()->subMinutes($polled),
            'last_polled_timetaken' => $polled === null ? null : 0,
        ]);

        (new DispatchPollingWork(poll: true, discover: false))->handle();

        if ($expected) {
            Queue::assertPushed(PollDevice::class, fn (PollDevice $job) => $job->device_id == $device->device_id);
        } else {
            Queue::assertNotPushed(PollDevice::class, fn (PollDevice $job) => $job->device_id == $device->device_id);
        }
    }

    /**
     * @return array<string, array{int, bool, int|null, bool}>
     */
    public static function pollProvider(): array
    {
        return [
            'new' => [1, false, null, false],
            'down, new' => [0, false, null, false],
            'discovered, never polled' => [1, true, null, true],
            'due' => [1, true, 10, true],
            'down, due' => [0, true, 10, true],
            'not due' => [1, true, 1, false],
            'rediscover requested, due' => [1, false, 10, true],
            'down, rediscover requested, due' => [0, false, 10, true],
        ];
    }

    public function testPollDueBeforeTheNextDispatchIsDelayed(): void
    {
        Queue::fake();
        $device = Device::factory()->create([
            'status' => 1,
            'last_discovered' => now()->subMinute(),
            'last_polled' => now()->subSeconds(300 - 8),
            'last_polled_timetaken' => 0,
        ]);

        (new DispatchPollingWork(poll: true, discover: false))->handle();

        // due 3 seconds early, at 297 seconds
        Queue::assertPushed(PollDevice::class, fn (PollDevice $job) => $job->device_id == $device->device_id && $job->delay >= 4 && $job->delay <= 6);
    }
}
