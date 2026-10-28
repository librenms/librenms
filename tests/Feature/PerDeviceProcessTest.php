<?php

namespace LibreNMS\Tests\Feature;

use App\Facades\DeviceCache;
use App\Models\Device;
use App\PerDeviceProcess;
use App\Polling\Measure\MeasurementManager;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\Event;
use LibreNMS\Enum\ProcessType;
use LibreNMS\Tests\InMemoryDbTestCase;
use LibreNMS\Util\ModuleList;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class PerDeviceProcessTest extends InMemoryDbTestCase
{
    public function testDownDeviceShowsReason(): void
    {
        $device = Device::factory()->create(['status' => 0, 'status_reason' => 'icmp']);

        $this->assertStringContainsString('Device was down (icmp), unable to poll.', $this->runProcess((string) $device->device_id));
    }

    public function testDownDevicesWithoutReason(): void
    {
        Device::factory()->create(['status' => 0, 'status_reason' => '']);
        $device = Device::factory()->create(['status' => 0, 'status_reason' => '']);

        $this->assertStringContainsString('Device was down (unknown reason), unable to poll.', $this->runProcess((string) $device->device_id));
        $this->assertStringContainsString('All devices were down, unable to poll.', $this->runProcess('all'));
    }

    public function testEachDeviceGetsFreshScopedInstances(): void
    {
        $devices = Device::factory()->count(2)->create(['status' => 1]);
        CompletingJob::$deviceCaches = [];

        $process = new PerDeviceProcess(ProcessType::Poller, 'all', CompletingJob::class, 'test.completed', ModuleList::fromUserOverrides([]));
        $process->run();

        $this->assertCount($devices->count(), CompletingJob::$deviceCaches);
        $this->assertCount($devices->count(), array_unique(CompletingJob::$deviceCaches), 'each device should get a new device cache');
    }

    public function testFiltersByPollerGroups(): void
    {
        $group1 = Device::factory()->create(['status' => 1, 'poller_group' => 1, 'last_discovered' => null]);
        $group2 = Device::factory()->create(['status' => 1, 'poller_group' => 2, 'last_discovered' => null]);
        $group3 = Device::factory()->create(['status' => 1, 'poller_group' => 3, 'last_discovered' => null]);

        $this->assertSame([$group1->device_id, $group2->device_id], $this->processedDevices('new', [1, 2]));
        $this->assertSame([$group3->device_id], $this->processedDevices('all', [3]));
        $this->assertSame([], $this->processedDevices((string) $group3->device_id, [1]));
        $this->assertCount(3, $this->processedDevices('new'));
    }

    /**
     * @param  int[]  $groups
     * @return int[]
     */
    private function processedDevices(string $spec, array $groups = []): array
    {
        CompletingJob::$deviceIds = [];
        (new PerDeviceProcess(ProcessType::Discovery, $spec, CompletingJob::class, 'test.completed', ModuleList::fromUserOverrides([]), pollerGroups: $groups))->run();
        sort(CompletingJob::$deviceIds);

        return CompletingJob::$deviceIds;
    }

    private function runProcess(string $spec): string
    {
        // the job does nothing, so no device completes
        $process = new PerDeviceProcess(ProcessType::Poller, $spec, NoopJob::class, 'test.completed', ModuleList::fromUserOverrides([]));
        $process->run();

        $output = new BufferedOutput;
        $this->assertSame(6, $process->processResults(app(MeasurementManager::class), new OutputStyle(new ArrayInput([]), $output)));

        return $output->fetch();
    }
}

class NoopJob
{
    public function handle(): void
    {
    }
}

class CompletingJob
{
    /** @var int[] */
    public static array $deviceCaches = [];
    /** @var int[] */
    public static array $deviceIds = [];

    public function __construct(private readonly int $device_id)
    {
    }

    public function handle(): void
    {
        self::$deviceCaches[] = spl_object_id(DeviceCache::getFacadeRoot());
        self::$deviceIds[] = $this->device_id;
        Event::dispatch('test.completed', [(object) ['device' => Device::find($this->device_id)]]);
    }
}
