<?php

namespace LibreNMS\Tests\Unit\Queue;

use App\Facades\LibrenmsConfig;
use App\Jobs\DispatchPollingWork;
use App\Jobs\PollDevice;
use App\Models\Device;
use App\Queue\QueueMetrics;
use Illuminate\Contracts\Queue\Job;
use LibreNMS\Data\Store\Datastore;
use LibreNMS\Tests\TestCase;
use Mockery;

final class QueueMetricsTest extends TestCase
{
    public function testSnapshotWritesTotalsByJobAndQueue(): void
    {
        LibrenmsConfig::set('rrd.enable', false);
        $metrics = new QueueMetrics;
        $this->runJob($metrics, $this->job('1', PollDevice::class, 'poll'));
        $this->runJob($metrics, $this->job('2', PollDevice::class, 'poll-2'));
        $this->runJob($metrics, $this->job('3', DispatchPollingWork::class, 'scheduler'), completed: false);

        $writes = $this->snapshot($metrics);

        $this->assertSame(['job:PollDevice', 'queue:poll', 'queue:poll-2', 'job:DispatchPollingWork', 'queue:scheduler'], array_keys($writes));
        $this->assertSame(2, $writes['job:PollDevice']['throughput']);
        $this->assertSame(1, $writes['queue:poll']['throughput']);
        $this->assertSame(1, $writes['job:DispatchPollingWork']['throughput'], 'jobs that threw an exception ran');
        $this->assertIsFloat($writes['job:PollDevice']['runtime']);

        // the totals were taken, nothing ran since
        $this->assertSame(['throughput' => 0, 'runtime' => null], $this->snapshot($metrics)['job:PollDevice']);
    }

    public function testJobsThatDidNotStartInThisProcessAreNotCounted(): void
    {
        $metrics = new QueueMetrics;
        $metrics->jobFinished($this->job('1', PollDevice::class, 'poll'), true);

        $this->app->instance('Datastore', Mockery::mock(Datastore::class, fn ($mock) => $mock->shouldNotReceive('write')));

        $metrics->snapshot();
    }

    /**
     * @return array<string, array<string, mixed>> fields written keyed by type:name
     */
    private function snapshot(QueueMetrics $metrics): array
    {
        $writes = [];
        $datastore = Mockery::mock(Datastore::class);
        $datastore->shouldReceive('write')->andReturnUsing(function (string $measurement, array $fields, array $tags, array $meta) use (&$writes): void {
            $this->assertSame('queue', $measurement);
            $this->assertSame(['queue', $tags['type'], $tags['name']], $meta['rrd_name']);
            $this->assertInstanceOf(Device::class, $meta['device']);
            $this->assertSame(QueueMetrics::HOSTNAME, $meta['device']->hostname);
            $this->assertTrue($meta['rrd_def']->isValidDataset('runtime'));
            $writes["{$tags['type']}:{$tags['name']}"] = $fields;
        });
        $this->app->instance('Datastore', $datastore);

        $metrics->snapshot();

        return $writes;
    }

    private function runJob(QueueMetrics $metrics, Job $job, bool $completed = true): void
    {
        $metrics->jobStarted($job);
        $metrics->jobFinished($job, $completed);
    }

    private function job(string $uuid, string $class, string $queue): Job
    {
        $job = Mockery::mock(Job::class);
        $job->shouldReceive('uuid')->andReturn($uuid);
        $job->shouldReceive('getQueue')->andReturn($queue);
        $job->shouldReceive('payload')->andReturn(['data' => ['commandName' => $class]]);

        return $job;
    }
}
