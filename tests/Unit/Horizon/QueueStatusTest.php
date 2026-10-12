<?php

namespace LibreNMS\Tests\Unit\Horizon;

use App\Horizon\QueueStatus;
use Illuminate\Queue\RedisQueue;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
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
        $redis = Mockery::mock(Connection::class);
        $redis->shouldReceive('zRangeByScore')->with('queues:poll:delayed', '-inf', '+inf', ['withscores' => true])->andReturn([
            json_encode(['displayName' => 'PollDevice:5', 'attempts' => 0, 'tags' => ['poll', 'device:5']]) => now()->addSeconds(10)->timestamp,
            json_encode(['displayName' => 'PollDevice:9', 'attempts' => 2, 'maxTries' => 4, 'tags' => ['poll', 'device:9']]) => now()->addMinutes(2)->timestamp,
        ]);
        $queue = Mockery::mock(RedisQueue::class);
        $queue->shouldReceive('getConnection')->andReturn($redis);
        $queue->shouldReceive('getQueue')->with('poll')->andReturn('queues:poll');
        $queue->shouldReceive('pendingSize')->with('poll')->andReturn(1);
        $queue->shouldReceive('delayedSize')->with('poll')->andReturn(2);
        $queue->shouldReceive('reservedSize')->with('poll')->andReturn(0);
        Queue::shouldReceive('connection')->with('redis')->andReturn($queue);

        $status = $this->queueStatus(workload: [['name' => 'poll', 'length' => 1, 'wait' => 0, 'processes' => 3, 'split_queues' => null]]);

        $retrying = $status->retrying();
        $this->assertCount(1, $retrying);
        $this->assertSame(9, $retrying[0]['device_id']);
        $this->assertSame('PollDevice:9', $retrying[0]['name']);
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

        $statuses = $this->queueStatus(jobs: $jobs, tags: $tags)->monitoredJobs('device:9')->pluck('status', 'id')->all();

        $this->assertSame(['a' => 'pending', 'b' => 'retrying', 'c' => 'completed'], $statuses);
    }

    /**
     * @param  array<int, array<string, mixed>>  $workload
     */
    private function queueStatus(array $workload = [], ?JobRepository $jobs = null, ?TagRepository $tags = null): QueueStatus
    {
        $workloadRepository = Mockery::mock(WorkloadRepository::class);
        $workloadRepository->shouldReceive('get')->andReturn($workload);

        return new QueueStatus(
            $jobs ?? Mockery::mock(JobRepository::class),
            $tags ?? Mockery::mock(TagRepository::class),
            $workloadRepository,
            Mockery::mock(MasterSupervisorRepository::class),
            Mockery::mock(SupervisorRepository::class),
        );
    }

    private function job(string $id, string $status, int $attempts): \stdClass
    {
        return (object) [
            'id' => $id,
            'name' => 'PollDevice:9',
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
