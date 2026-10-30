<?php

namespace LibreNMS\Tests\Feature\Queue;

use App\Facades\LibrenmsConfig;
use App\Jobs\DiscoverDevice;
use App\Jobs\PollDevice;
use App\Models\PollerCluster;
use App\Models\User;
use App\Queue\QueueMetrics;
use App\Queue\QueueStatus;
use App\Queue\WorkerNode;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\Events\Looping;
use Illuminate\Queue\RedisQueue;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use LibreNMS\Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;

final class WorkerNodeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  class-string<PollDevice|DiscoverDevice>  $job
     * @param  int[]  $groups
     * @param  string[]  $expected
     */
    #[DataProvider('queueNameProvider')]
    public function testQueueNamesMatchDispatcher(string $job, array $groups, array $expected): void
    {
        $this->assertSame($expected, WorkerNode::queueNames($job::queueFor(...), $groups));
    }

    /**
     * @return array<string, array{class-string, int[], string[]}>
     */
    public static function queueNameProvider(): array
    {
        return [
            'default group' => [PollDevice::class, [0], ['poll']],
            'single group' => [DiscoverDevice::class, [3], ['discovery-3']],
            'multiple groups' => [PollDevice::class, [0, 1, 2], ['poll', 'poll-1', 'poll-2']],
            'duplicates' => [PollDevice::class, [1, 1], ['poll-1']],
        ];
    }

    public function testNodeWithoutSettingsUsesGlobalSettings(): void
    {
        LibrenmsConfig::set('distributed_poller_group', '0,2');
        LibrenmsConfig::set('service_poller_workers', 8);
        LibrenmsConfig::set('service_discovery_workers', 4);

        $this->assertSame([
            'poller' => ['queues' => ['poll', 'poll-2'], 'workers' => 8],
            'discovery' => ['queues' => ['discovery', 'discovery-2'], 'workers' => 4],
        ], WorkerNode::queues(PollerCluster::factory()->worker()->make(['poller_groups' => ''])));
    }

    public function testNodeSettingsOverrideGlobalSettings(): void
    {
        LibrenmsConfig::set('distributed_poller_group', '0');
        LibrenmsConfig::set('service_poller_workers', 8);
        $node = PollerCluster::factory()->worker()->make(['poller_groups' => '1,3', 'poller_workers' => 2, 'discovery_enabled' => false]);

        $queues = WorkerNode::queues($node);

        $this->assertSame(['queues' => ['poll-1', 'poll-3'], 'workers' => 2], $queues['poller']);
        $this->assertNull($queues['discovery']);
    }

    public function testWorkerNodeSettings(): void
    {
        // the dispatcher service is disabled when the scheduler dispatches work
        LibrenmsConfig::set('schedule_type.poller', WorkerNode::SCHEDULE_TYPE);
        LibrenmsConfig::set('schedule_type.discovery', WorkerNode::SCHEDULE_TYPE);

        $definition = collect(PollerCluster::factory()->worker()->make()->configDefinition(false))->keyBy('name');

        $this->assertSame(['poller_groups', 'poller_enabled', 'poller_workers', 'discovery_enabled', 'discovery_workers'], $definition->keys()->all());
        $this->assertTrue($definition['poller_enabled']['default']);
        $this->assertTrue($definition['poller_enabled']['value']);
        $this->assertTrue($definition['discovery_enabled']['value']);
    }

    public function testSettingsPageShowsWorkerSettings(): void
    {
        $user = User::factory()->admin()->create(['enabled' => true]);
        PollerCluster::factory()->worker()->create(['poller_name' => 'worker-node']);

        $this->actingAs($user)->get(route('poller.settings'))
            ->assertOk()
            ->assertSee('worker-node')
            ->assertSee('poller_workers')
            ->assertDontSee('ping_frequency');
    }

    public function testWorkerNodeRejectsServiceSettings(): void
    {
        $user = User::factory()->admin()->create(['enabled' => true]);
        $node = PollerCluster::factory()->worker()->create();

        $this->actingAs($user)->putJson(route('poller.settings.update', ['id' => $node->id, 'setting' => 'ping_frequency']), ['value' => 60])
            ->assertStatus(422);
        $this->actingAs($user)->putJson(route('poller.settings.update', ['id' => $node->id, 'setting' => 'poller_workers']), ['value' => 6])
            ->assertOk();

        $this->assertSame(6, $node->fresh()?->poller_workers);
    }

    public function testAnyWorkerRecordsItsQueues(): void
    {
        config(['librenms.node_id' => 'node1']);

        // queue:work and Horizon's workers both fire Looping
        Event::dispatch(new Looping('redis', 'poll,poll-2'));

        $status = app(QueueStatus::class);
        $this->assertSame(['poll', 'poll-2'], $status->workedQueues(['scheduler', 'poll', 'poll-2', 'discovery']));
    }

    public function testShortRecordsDoNotReplaceLongerOnes(): void
    {
        $this->freezeSecond();
        config(['librenms.node_id' => 'node1']);
        $busy = app(WorkerNode::class);
        $busy->looping('discovery');
        $job = Mockery::mock(Job::class);
        $job->shouldReceive('timeout')->andReturn(3600);
        $busy->jobStarted($job);

        // another process on the queue loops and then stops
        $this->app->forgetInstance(WorkerNode::class);
        app(WorkerNode::class)->looping('discovery');
        $this->travel(10)->minutes();

        $this->assertSame(['discovery'], app(QueueStatus::class)->workedQueues(['discovery']), 'the busy process still works the queue');
    }

    public function testMissingNodeIdIsLoggedOncePerProcess(): void
    {
        config(['librenms.node_id' => null]);
        Log::shouldReceive('warning')->once();
        $worker = app(WorkerNode::class);

        $worker->heartbeat();
        $worker->heartbeat();
    }

    public function testChecksInAndReportsStats(): void
    {
        $this->freezeSecond();
        config(['librenms.node_id' => 'worker-node-test']);
        $queue = Mockery::mock(RedisQueue::class);
        $queue->shouldReceive('pendingSize')->with('poll')->andReturn(3);
        $queue->shouldReceive('pendingSize')->with('discovery')->andReturn(1);
        Queue::shouldReceive('connection')->withNoArgs()->andReturn($queue);
        $metrics = app(QueueMetrics::class);
        $poll = $this->job('1', PollDevice::class);
        $metrics->jobStarted($poll);
        $metrics->jobFinished($poll, true);
        $failed = $this->job('2', PollDevice::class);
        $metrics->jobStarted($failed);
        $metrics->jobFinished($failed, false);

        $worker = app(WorkerNode::class);
        $worker->looping('poll');
        app(WorkerNode::class)->looping('discovery'); // another process on the node, the node already checked in

        $node = PollerCluster::with('stats')->firstWhere('node_id', 'worker-node-test');
        $this->assertNotNull($node);
        $this->assertSame(WorkerNode::VERSION, $node->poller_version);
        $this->assertSame((string) LibrenmsConfig::get('distributed_poller_group'), $node->poller_groups);
        $this->assertEquals(now(), $node->last_report);
        $this->assertSame(PHP_VERSION, $node->poller_details['php_version'] ?? null);

        $stats = $node->stats->keyBy('poller_type');
        $this->assertSame(['discovery', 'poller'], $stats->keys()->sort()->values()->all());
        $this->assertSame(3, $stats['poller']->depth);
        $this->assertSame(1, $stats['poller']->devices, 'only completed jobs count as devices');
        $this->assertGreaterThanOrEqual(0, $stats['poller']->worker_seconds);
        $this->assertEquals(LibrenmsConfig::get('service_poller_workers'), $stats['poller']->workers);
        $this->assertSame(0, $stats['discovery']->devices);
        $this->assertSame(1, $stats['discovery']->depth);

        // totals were taken by the report
        $this->assertSame(0, $metrics->pullNodeStats('worker-node-test')['poller']['devices']);
    }

    public function testOtherJobsAreNotNodeStats(): void
    {
        config(['librenms.node_id' => 'node1']);
        $metrics = app(QueueMetrics::class);

        $other = $this->job('3', \App\Jobs\PingCheck::class);
        $metrics->jobStarted($other);
        $metrics->jobFinished($other, true);
        $metrics->flush();

        $this->assertSame(['poller' => ['devices' => 0, 'seconds' => 0.0], 'discovery' => ['devices' => 0, 'seconds' => 0.0]], $metrics->pullNodeStats('node1'));
    }

    private function job(string $uuid, string $class): Job
    {
        $job = Mockery::mock(Job::class);
        $job->shouldReceive('uuid')->andReturn($uuid);
        $job->shouldReceive('getQueue')->andReturn('poll');
        $job->shouldReceive('payload')->andReturn(['data' => ['commandName' => $class]]);

        return $job;
    }
}
