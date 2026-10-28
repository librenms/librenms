<?php

namespace LibreNMS\Tests\Unit\Horizon;

use App\Horizon\QueueStatus;
use Illuminate\Queue\Jobs\RedisJob;
use Illuminate\Queue\Jobs\SyncJob;
use Illuminate\Queue\RedisQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Laravel\Horizon\Contracts\JobRepository;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;
use Laravel\Horizon\Contracts\SupervisorRepository;
use Laravel\Horizon\Contracts\TagRepository;
use Laravel\Horizon\Contracts\WorkloadRepository;
use LibreNMS\Tests\TestCase;
use Mockery;

final class QueueStatusTest extends TestCase
{
    public function testRetriesAreSeparatedFromScheduledJobs(): void
    {
        $this->freezeSecond();
        $retries = [
            'job-9' => json_encode(['device_id' => 9, 'name' => 'PollDevice', 'queue' => 'poll', 'attempt' => 2, 'tries' => 4, 'retry_at' => now()->addMinutes(2)->timestamp]),
            // its job was removed from the queue
            'job-3' => json_encode(['device_id' => 3, 'name' => 'PollDevice', 'queue' => 'poll', 'attempt' => 1, 'tries' => 4, 'retry_at' => now()->subHours(2)->timestamp]),
        ];
        $redis = Mockery::mock();
        $redis->shouldReceive('command')->with('hgetall', ['retrying'])->andReturn($retries);
        $redis->shouldReceive('command')->once()->with('hdel', ['retrying', 'job-3']);
        Redis::shouldReceive('connection')->with('horizon')->andReturn($redis);
        $queue = Mockery::mock(RedisQueue::class);
        $queue->shouldReceive('pendingSize')->with('poll')->andReturn(1);
        $queue->shouldReceive('delayedSize')->with('poll')->andReturn(2);
        $queue->shouldReceive('reservedSize')->with('poll')->andReturn(0);
        Queue::shouldReceive('connection')->with('redis')->andReturn($queue);

        $status = $this->queueStatus(workload: [['name' => 'poll', 'length' => 1, 'wait' => 0, 'processes' => 3, 'split_queues' => null]]);

        $retrying = $status->retrying();
        $this->assertCount(1, $retrying);
        $this->assertSame(9, $retrying[0]['device_id']);
        $this->assertSame('PollDevice', $retrying[0]['name']);
        $this->assertSame(2, $retrying[0]['attempt']);
        $this->assertSame(4, $retrying[0]['tries']);
        $this->assertEquals(now()->addMinutes(2), $retrying[0]['retry_at']);

        $this->assertSame([[
            'name' => 'poll',
            'pending' => 1,
            'scheduled' => 1,
            'retrying' => 1,
            'reserved' => 0,
            'processes' => 3,
            'wait' => 0,
        ]], $status->queues($retrying)->all());
    }

    public function testReleasedJobsAreRecordedUntilTheyRunAgain(): void
    {
        $this->freezeSecond();
        $job = Mockery::mock(RedisJob::class);
        $job->shouldReceive('payload')->andReturn(['displayName' => \App\Jobs\PollDevice::class, 'tags' => ['poll', 'device:9']]);
        $job->shouldReceive('getJobId')->andReturn('job-9');
        $job->shouldReceive('getQueue')->andReturn('poll-1');
        $job->shouldReceive('attempts')->andReturn(2);
        $job->shouldReceive('maxTries')->andReturn(4);
        $redis = Mockery::mock();
        $redis->shouldReceive('command')->once()->with('hset', ['retrying', 'job-9', json_encode([
            'device_id' => 9, 'name' => 'PollDevice', 'queue' => 'poll-1', 'attempt' => 2, 'tries' => 4, 'retry_at' => now()->timestamp + 120,
        ])]);
        $redis->shouldReceive('command')->once()->with('hdel', ['retrying', 'job-9']);
        Redis::shouldReceive('connection')->with('horizon')->andReturn($redis);

        QueueStatus::jobReleased($job, 120);
        QueueStatus::jobStarted($job);
        // jobs that do not run on the redis queue are not tracked
        QueueStatus::jobStarted(Mockery::mock(SyncJob::class));
    }

    public function testReleasedJobsAreShownAsRetrying(): void
    {
        $tags = Mockery::mock(TagRepository::class);
        $tags->shouldReceive('paginate')->with('device:9', 0, 50)->andReturn(['a', 'b', 'c']);
        $jobs = Mockery::mock(JobRepository::class);
        $jobs->shouldReceive('getJobs')->with(['a', 'b', 'c'])->andReturn(new Collection([
            $this->job('a', 'pending', 0),
            $this->job('b', 'pending', 1),
            $this->job('c', 'completed', 4),
        ]));

        $monitored = $this->queueStatus(jobs: $jobs, tags: $tags)->monitoredJobs('device:9');

        $this->assertSame(['a' => 'pending', 'b' => 'retrying', 'c' => 'completed'], $monitored->pluck('status', 'id')->all());
        $this->assertSame('PollDevice', $monitored[0]['name']);
    }

    public function testWorkedQueuesCombineAllNodes(): void
    {
        $supervisors = Mockery::mock(SupervisorRepository::class);
        $supervisors->shouldReceive('all')->andReturn([
            (object) ['name' => 'node1:supervisor-poll', 'options' => ['queue' => 'poll,poll-1']],
            (object) ['name' => 'node1:supervisor-scheduler', 'options' => ['queue' => 'scheduler']],
            (object) ['name' => 'node2:supervisor-poll', 'options' => ['queue' => 'poll-1,poll-2']],
        ]);

        $this->assertSame(['poll', 'poll-1', 'scheduler', 'poll-2'], $this->queueStatus(supervisors: $supervisors)->workedQueues());
    }

    public function testLastDispatchedIsTheNewestCompletedDispatcherRun(): void
    {
        $jobs = Mockery::mock(JobRepository::class);
        $jobs->shouldReceive('getSilenced')->andReturn(new Collection([
            (object) ['name' => 'App\\Jobs\\Other', 'completed_at' => '1700000020.5'],
            (object) ['name' => \App\Jobs\DispatchPollingWork::class, 'completed_at' => '1700000010.5'],
            (object) ['name' => \App\Jobs\DispatchPollingWork::class, 'completed_at' => '1700000000.5'],
        ]));

        $this->assertSame(1700000010, $this->queueStatus(jobs: $jobs)->lastDispatched()?->timestamp);
    }

    public function testNeverDispatched(): void
    {
        $jobs = Mockery::mock(JobRepository::class);
        $jobs->shouldReceive('getSilenced')->andReturn(new Collection);

        $this->assertNull($this->queueStatus(jobs: $jobs)->lastDispatched());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('exceptionProvider')]
    public function testExceptionSummaryOmitsLocationAndTrace(string $exception, string $expected): void
    {
        $this->assertSame($expected, QueueStatus::exceptionSummary($exception));
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

    /**
     * @param  array<int, array<string, mixed>>  $workload
     */
    private function queueStatus(array $workload = [], ?JobRepository $jobs = null, ?TagRepository $tags = null, ?SupervisorRepository $supervisors = null): QueueStatus
    {
        $workloadRepository = Mockery::mock(WorkloadRepository::class);
        $workloadRepository->shouldReceive('get')->andReturn($workload);

        return new QueueStatus(
            $jobs ?? Mockery::mock(JobRepository::class),
            $tags ?? Mockery::mock(TagRepository::class),
            $workloadRepository,
            Mockery::mock(MasterSupervisorRepository::class),
            $supervisors ?? Mockery::mock(SupervisorRepository::class),
        );
    }

    private function job(string $id, string $status, int $attempts): \stdClass
    {
        return (object) [
            'id' => $id,
            'name' => \App\Jobs\PollDevice::class,
            'queue' => 'poll',
            'status' => $status,
            'payload' => json_encode(['attempts' => $attempts, 'tags' => ['poll', 'device:9']]),
            'exception' => null,
            'reserved_at' => null,
            'completed_at' => null,
            'failed_at' => null,
            'retried_by' => null,
        ];
    }
}
