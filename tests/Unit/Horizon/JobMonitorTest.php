<?php

namespace LibreNMS\Tests\Unit\Horizon;

use App\Horizon\JobMonitor;
use Illuminate\Support\Collection;
use Laravel\Horizon\Contracts\JobRepository;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;
use Laravel\Horizon\Contracts\TagRepository;
use LibreNMS\Tests\TestCase;
use Mockery;

final class JobMonitorTest extends TestCase
{
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

        $monitored = (new JobMonitor($jobs, $tags, Mockery::mock(MasterSupervisorRepository::class)))->monitoredJobs('device:9');

        $this->assertSame(['a' => 'pending', 'b' => 'retrying', 'c' => 'completed'], $monitored->pluck('status', 'id')->all());
        $this->assertSame('PollDevice', $monitored[0]['name']);
        $this->assertSame(9, $monitored[0]['device_id']);
    }

    public function testOnlyAvailableWhenHorizonIsRunning(): void
    {
        config(['queue.default' => 'redis']);
        $masters = Mockery::mock(MasterSupervisorRepository::class);
        $masters->shouldReceive('names')->andReturn([], ['node1-AbCd']);
        $monitor = new JobMonitor(Mockery::mock(JobRepository::class), Mockery::mock(TagRepository::class), $masters);

        $this->assertFalse($monitor->available(), 'other queue workers do not record jobs');
        $this->assertTrue($monitor->available());

        config(['queue.default' => 'sqs']);
        $this->assertFalse($monitor->available());
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
