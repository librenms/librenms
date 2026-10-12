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
     * @param  int|null  $deferred  minutes until a deferred discovery may be tried again
     */
    #[DataProvider('discoveryProvider')]
    public function testDiscoveryDispatch(int $status, ?int $discovered, bool $polled, ?int $deferred, bool $expected): void
    {
        Queue::fake();
        $device = Device::factory()->create([
            'status' => $status,
            'last_discovered' => $discovered === null ? null : now()->subMinutes($discovered),
            'last_discovered_timetaken' => $discovered === null ? null : 0,
            'last_polled' => $polled ? now()->subMinute() : null,
            'discovery_deferred_until' => $deferred === null ? null : now()->addMinutes($deferred),
        ]);

        (new DispatchPollingWork(poll: false, discover: true))->handle();

        if ($expected) {
            Queue::assertPushed(DiscoverDevice::class, fn (DiscoverDevice $job) => $job->device_id == $device->device_id);
        } else {
            Queue::assertNotPushed(DiscoverDevice::class, fn (DiscoverDevice $job) => $job->device_id == $device->device_id);
        }
    }

    /**
     * @return array<string, array{int, int|null, bool, int|null, bool}>
     */
    public static function discoveryProvider(): array
    {
        return [
            'never discovered' => [1, null, false, null, true],
            'never discovered, deferred' => [1, null, false, 5, false],
            'never discovered, deferral expired' => [1, null, false, -1, true],
            'down, never discovered' => [0, null, false, null, true],
            'down, never discovered, deferred' => [0, null, false, 5, false],
            'down, rediscover requested' => [0, null, true, null, false],
            'rediscover requested' => [1, null, true, null, true],
            'due' => [1, 420, true, null, true],
            'due, deferred' => [1, 420, true, 5, false],
            'not due' => [1, 60, true, null, false],
            'down' => [0, 420, true, null, false],
        ];
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

    public function testDeferredDiscoveryEndingBeforeTheNextDispatchIsDelayed(): void
    {
        Queue::fake();
        $device = Device::factory()->create([
            'status' => 0,
            'last_discovered' => null,
            'discovery_deferred_until' => now()->addSeconds(8),
        ]);

        (new DispatchPollingWork(poll: false, discover: true))->handle();

        Queue::assertPushed(DiscoverDevice::class, fn (DiscoverDevice $job) => $job->device_id == $device->device_id && $job->delay >= 7 && $job->delay <= 9);
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
            'awaiting discovery' => [1, false, null, false],
            'down, awaiting discovery' => [0, false, null, false],
            'discovered, never polled' => [1, true, null, true],
            'due' => [1, true, 10, true],
            'down, due' => [0, true, 10, true],
            'not due' => [1, true, 1, false],
            'rediscover requested, due' => [1, false, 10, true],
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
