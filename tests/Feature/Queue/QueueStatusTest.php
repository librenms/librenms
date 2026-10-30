<?php

namespace LibreNMS\Tests\Feature\Queue;

use App\Facades\LibrenmsConfig;
use App\Jobs\DispatchPollingWork;
use App\Jobs\PollDevice;
use App\Models\Device;
use App\Models\FailedJob;
use App\Models\PollerCluster;
use App\Queue\FailedJobs;
use App\Queue\QueueStatus;
use App\Queue\RetryTracker;
use App\Queue\WorkedQueues;
use App\Queue\WorkerNode;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\RedisQueue;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use LibreNMS\Tests\TestCase;
use LibreNMS\Util\ModuleList;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;

final class QueueStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        LibrenmsConfig::set('schedule_type.poller', WorkerNode::SCHEDULE_TYPE);
    }

    public function testReleasedJobsAreRecordedUntilTheyRunAgain(): void
    {
        $this->freezeSecond();
        Device::factory()->create(['device_id' => 9, 'status' => 0]);
        $status = new RetryTracker;
        $job = $this->job('job-9', attempts: 2);

        $status->jobReleased($job, 120);

        $retrying = $status->retrying();
        $this->assertCount(1, $retrying);
        $this->assertSame(9, $retrying[0]['device_id']);
        $this->assertSame('PollDevice', $retrying[0]['name']);
        $this->assertSame('poll-1', $retrying[0]['queue']);
        $this->assertSame(2, $retrying[0]['attempt']);
        $this->assertSame(4, $retrying[0]['tries']);
        $this->assertEquals(now()->addMinutes(2), $retrying[0]['retry_at']);

        $status->jobStarted($this->job('other', attempts: 1));
        $this->assertCount(1, $status->retrying(), 'a first attempt is not a retry');
        $status->jobReleased($this->job('job-10', attempts: 1, device_id: 10), 60);
        $this->assertCount(1, $status->retrying(), 'only down devices are listed');

        $status->jobStarted($job);
        $this->assertCount(0, $status->retrying());
    }

    public function testStaleRetriesAreDropped(): void
    {
        $this->freezeSecond();
        Device::factory()->create(['device_id' => 9, 'status' => 0]);
        $status = new RetryTracker;
        $status->jobReleased($this->job('job-3', attempts: 1), 60);

        $this->travel(2)->hours();

        $this->assertCount(0, $status->retrying(), 'its job was removed from the queue');
    }

    public function testQueueSizes(): void
    {
        Device::factory()->create(['device_id' => 9, 'status' => 0, 'poller_group' => 0]);
        $queue = Mockery::mock(RedisQueue::class);
        $queue->shouldReceive('pendingSize')->andReturn(1);
        $queue->shouldReceive('delayedSize')->andReturn(2);
        $queue->shouldReceive('reservedSize')->andReturn(0);
        $queue->shouldReceive('creationTimeOfOldestPendingJob')->andReturnUsing(fn (string $name) => $name == 'poll' ? time() - 30 : null);
        Queue::shouldReceive('connection')->withNoArgs()->andReturn($queue);
        Device::factory()->create(['poller_group' => 0, 'last_polled_timetaken' => 150, 'last_discovered_timetaken' => 21600]);
        app(WorkedQueues::class)->looping('poll');
        app(RetryTracker::class)->jobReleased($this->job('job-9', attempts: 2, queue: 'poll'), 60);

        $queues = app(QueueStatus::class)->queues()->keyBy('name');

        $this->assertSame(['default', 'poll', 'scheduler'], $queues->keys()->all());
        $this->assertEquals([
            'name' => 'poll',
            'pending' => 1,
            'scheduled' => 1,
            'retrying' => 1,
            'reserved' => 0,
            'worked' => true,
            'age' => 30,
            'interval' => DispatchPollingWork::pollInterval(),
            'busy' => round(150 / DispatchPollingWork::pollFrequency(), 1),
        ], $queues['poll']);
        $this->assertFalse($queues['scheduler']['worked']);
        $this->assertSame(0, $queues['scheduler']['age']);
    }

    public function testWorkersAreShownWithTheNodeName(): void
    {
        PollerCluster::factory()->worker()->create(['node_id' => 'node-a', 'poller_name' => 'poller1.example.com', 'last_report' => now()]);
        PollerCluster::factory()->worker()->create(['node_id' => 'node-b', 'poller_name' => '', 'last_report' => now()->subDay()]);
        PollerCluster::factory()->create(['poller_name' => 'dispatcher-service']);

        $workers = app(QueueStatus::class)->workers();

        $this->assertSame(['node-b', 'poller1.example.com'], $workers->pluck('name')->all());
        $this->assertSame(['inactive', 'running'], $workers->pluck('status')->all());
        $this->assertSame(['poll', 'discovery'], $workers[1]['queues'], 'from the node settings');
    }

    public function testLastDispatched(): void
    {
        $this->freezeSecond();
        $status = app(QueueStatus::class);
        $this->assertNull($status->lastDispatched());

        $status->recordDispatched();

        $this->assertEquals(now(), $status->lastDispatched());
    }

    public function testDeviceJobsAreQueuedWithTheirDevice(): void
    {
        // every queue driver builds the payload the same way
        $created = null;
        Queue::createPayloadUsing(function ($connection, $queue, array $payload) use (&$created): array {
            $created = $payload;

            return [];
        });
        Queue::connection('database')->push(new PollDevice(42, ModuleList::fromUserOverrides([])), queue: 'poll');

        $this->assertSame(42, $created['device_id'] ?? null);
    }

    public function testFailedJobsByDeviceAndClass(): void
    {
        $this->failedJob(PollDevice::class, ['device_id' => 9], "RuntimeException: snmp crashed in /opt/librenms/app/Jobs/PollDevice.php:146\nStack trace:\n#0 {main}");
        $this->failedJob(PollDevice::class, ['device_id' => 10]);
        $dispatch = $this->failedJob(DispatchPollingWork::class);
        $failed = new FailedJobs;

        $this->assertSame(3, $failed->count());

        $device = $failed->list(device_id: 9);
        $this->assertCount(1, $device);
        $this->assertSame('PollDevice', $device[0]['name']);
        $this->assertSame(9, $device[0]['device_id']);
        $this->assertSame('failed', $device[0]['status']);
        $this->assertSame('RuntimeException: snmp crashed', $device[0]['error']);

        $this->assertSame([$dispatch->uuid], $failed->list(job: DispatchPollingWork::class)->pluck('id')->all());
    }

    public function testForgetFailedJob(): void
    {
        $job = $this->failedJob(PollDevice::class);

        (new FailedJobs)->forget($job->uuid);

        $this->assertSame(0, FailedJob::count());
    }

    #[DataProvider('exceptionProvider')]
    public function testExceptionSummaryOmitsLocationAndTrace(string $exception, string $expected): void
    {
        $this->assertSame($expected, FailedJobs::exceptionSummary($exception));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function exceptionProvider(): array
    {
        $trace = "\nStack trace:\n#0 /opt/librenms/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(123): handle()\n#1 {main}";

        return [
            'file' => [
                'App\\Exceptions\\PollingFailedException: Failed to poll device 6899: snmp down in /opt/librenms/app/Jobs/PollDevice.php:146' . $trace,
                'App\\Exceptions\\PollingFailedException: Failed to poll device 6899: snmp down',
            ],
            'message mentions a file' => [
                'TypeError: Dns::getAddresses(): Argument #1 ($hostname) must be of type string, null given, called in /opt/librenms/LibreNMS/Util/Dns.php on line 51 and defined in /opt/librenms/LibreNMS/Util/Dns.php:69' . $trace,
                'TypeError: Dns::getAddresses(): Argument #1 ($hostname) must be of type string, null given, called in /opt/librenms/LibreNMS/Util/Dns.php on line 51 and defined',
            ],
            'closure' => [
                "RuntimeException: Simulated crash in laravel-serializable-closure://function (): void {\n    throw new RuntimeException('Simulated crash');\n}:2" . $trace,
                'RuntimeException: Simulated crash',
            ],
            'multi-line message' => [
                "ErrorException: first line\nsecond line in /opt/librenms/includes/polling/ports.inc.php:798" . $trace,
                'ErrorException: first line second line',
            ],
            'no location' => [
                'Illuminate\\Queue\\TimeoutExceededException: PollDevice:1 has timed out.',
                'Illuminate\\Queue\\TimeoutExceededException: PollDevice:1 has timed out.',
            ],
        ];
    }

    private function job(string $uuid, int $attempts, string $queue = 'poll-1', int $device_id = 9): Job
    {
        $job = Mockery::mock(Job::class);
        $job->shouldReceive('uuid')->andReturn($uuid);
        $job->shouldReceive('payload')->andReturn(['displayName' => PollDevice::class, 'device_id' => $device_id]);
        $job->shouldReceive('getQueue')->andReturn($queue);
        $job->shouldReceive('attempts')->andReturn($attempts);
        $job->shouldReceive('maxTries')->andReturn(4);

        return $job;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function failedJob(string $class, array $payload = [], string $exception = 'RuntimeException: failed'): FailedJob
    {
        $job = new FailedJob;
        $job->forceFill([
            'uuid' => (string) Str::uuid(),
            'connection' => 'redis',
            'queue' => 'poll',
            'payload' => ['displayName' => $class, 'attempts' => 1] + $payload,
            'exception' => $exception,
        ])->save();

        return $job;
    }
}
