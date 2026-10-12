<?php

namespace LibreNMS\Tests\Feature\Jobs;

use App\Jobs\DiscoverDevice;
use App\Jobs\DispatchPollingWork;
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
     */
    #[DataProvider('discoveryProvider')]
    public function testDiscoveryDispatch(int $status, ?int $discovered, bool $expected): void
    {
        Queue::fake();
        $device = Device::factory()->create([
            'status' => $status,
            'last_discovered' => $discovered === null ? null : now()->subMinutes($discovered),
            'last_discovered_timetaken' => $discovered === null ? null : 0,
        ]);

        (new DispatchPollingWork(poll: false, discover: true))->handle();

        if ($expected) {
            Queue::assertPushed(DiscoverDevice::class, fn (DiscoverDevice $job) => $job->device_id == $device->device_id);
        } else {
            Queue::assertNotPushed(DiscoverDevice::class);
        }
    }

    /**
     * @return array<string, array{int, int|null, bool}>
     */
    public static function discoveryProvider(): array
    {
        return [
            'never discovered' => [1, null, true],
            'down, never discovered' => [0, null, true],
            'due' => [1, 420, true],
            'not due' => [1, 60, false],
            'down' => [0, 420, false],
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
}
