<?php

namespace LibreNMS\Tests\Feature\Horizon;

use App\Facades\LibrenmsConfig;
use App\Horizon\QueueStatus;
use App\Horizon\WorkerNode;
use App\Jobs\DiscoverDevice;
use App\Jobs\PollDevice;
use App\Models\PollerCluster;
use App\Models\User;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\RedisQueue;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Laravel\Horizon\Contracts\JobRepository;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;
use Laravel\Horizon\Contracts\SupervisorRepository;
use Laravel\Horizon\Contracts\TagRepository;
use Laravel\Horizon\Contracts\WorkloadRepository;
use Laravel\Horizon\MasterSupervisor;
use Laravel\Horizon\ProvisioningPlan;
use LibreNMS\Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

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
            'supervisor-poll' => ['queue' => ['poll', 'poll-2'], 'maxProcesses' => 8],
            'supervisor-discovery' => ['queue' => ['discovery', 'discovery-2'], 'maxProcesses' => 4],
        ], WorkerNode::supervisors(PollerCluster::factory()->worker()->make(['poller_groups' => ''])));
    }

    public function testNodeSettingsOverrideGlobalSettings(): void
    {
        LibrenmsConfig::set('distributed_poller_group', '0');
        LibrenmsConfig::set('service_poller_workers', 8);
        $node = PollerCluster::factory()->worker()->make(['poller_groups' => '1,3', 'poller_workers' => 2]);

        $supervisors = WorkerNode::supervisors($node);

        $this->assertSame(['queue' => ['poll-1', 'poll-3'], 'maxProcesses' => 2], $supervisors['supervisor-poll']);
        $this->assertSame(['discovery-1', 'discovery-3'], $supervisors['supervisor-discovery']['queue'] ?? null);
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

    public function testSettingsPageShowsHorizonSettings(): void
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

    public function testDisabledPollingDoesNotStartASupervisor(): void
    {
        $node = PollerCluster::factory()->worker()->make(['poller_enabled' => false]);

        app(WorkerNode::class)->start($node);

        $plan = (new ProvisioningPlan('test', config('horizon.environments'), config('horizon.defaults')))->toSupervisorOptions();
        $this->assertArrayNotHasKey('supervisor-poll', $plan['*']);
        $this->assertArrayHasKey('supervisor-discovery', $plan['*']);
    }

    public function testSupervisorsAreConfiguredWhenHorizonStarts(): void
    {
        LibrenmsConfig::set('distributed_poller_group', '0');
        $node = PollerCluster::factory()->worker()->create(['poller_groups' => '4', 'poller_workers' => 3]);
        // CommandStartingListener also runs, it rejects users other than the LibreNMS user
        config(['librenms.node_id' => $node->node_id, 'librenms.user' => posix_getpwuid(posix_geteuid())['name']]);

        Event::dispatch(new CommandStarting('horizon:status', new ArrayInput([]), new NullOutput));
        $this->assertSame(['poll'], config('horizon.defaults.supervisor-poll.queue'), 'other commands should not configure supervisors');

        Event::dispatch(new CommandStarting('horizon', new ArrayInput([]), new NullOutput));
        $this->assertSame(['poll-4'], config('horizon.defaults.supervisor-poll.queue'));
        $this->assertSame(3, config('horizon.defaults.supervisor-poll.maxProcesses'));
        $this->assertSame(['discovery-4'], config('horizon.defaults.supervisor-discovery.queue'));
    }

    public function testRestartIsRequestedOnceWithoutTheWorkerCommand(): void
    {
        $node = PollerCluster::factory()->worker()->create(['poller_groups' => '1']);
        config(['librenms.node_id' => $node->node_id]);
        $worker = new WorkerNode;
        $worker->start();

        $node->poller_groups = '2';
        $node->save();
        // the node still checks in and reports stats
        $redis = Mockery::mock();
        $redis->shouldReceive('command')->with('hgetall', Mockery::any())->andReturn([]);
        Redis::shouldReceive('connection')->with('horizon')->andReturn($redis);
        $queue = Mockery::mock(RedisQueue::class);
        $queue->shouldReceive('pendingSize')->andReturn(0);
        Queue::shouldReceive('connection')->with('redis')->andReturn($queue);
        // not started by the worker command, there is nothing to signal
        $log = Log::spy();

        $worker->looped();
        $this->travel(WorkerNode::HEARTBEAT + 1)->seconds();
        $worker->looped();
        $this->travel(WorkerNode::RELOAD_RETRY + 1)->seconds();
        $worker->looped();

        $log->shouldHaveReceived('warning')->once()->withArgs(fn (string $message): bool => str_contains($message, 'restart it to apply'));
    }

    public function testMastersAreNamedForTheNode(): void
    {
        config(['librenms.node_id' => '6a1f2b3c4d5e6']);

        $this->assertSame('6a1f2b3c4d5e6', MasterSupervisor::basename());
        $this->assertStringStartsWith('6a1f2b3c4d5e6-', MasterSupervisor::name());
    }

    public function testWorkersAreShownWithTheNodeName(): void
    {
        PollerCluster::factory()->worker()->create(['node_id' => '6a1f2b3c4d5e6', 'poller_name' => 'poller1.example.com']);
        $masters = Mockery::mock(MasterSupervisorRepository::class);
        // a reload briefly runs two Horizons on the node
        $masters->shouldReceive('all')->andReturn([
            (object) ['name' => '6a1f2b3c4d5e6-AbCd', 'status' => 'running'],
            (object) ['name' => '6a1f2b3c4d5e6-EfGh', 'status' => 'running'],
            (object) ['name' => 'unknown-IjKl', 'status' => 'paused'],
        ]);
        $supervisors = Mockery::mock(SupervisorRepository::class);
        $supervisors->shouldReceive('all')->andReturn([
            (object) ['name' => '6a1f2b3c4d5e6-AbCd:supervisor-poll', 'master' => '6a1f2b3c4d5e6-AbCd', 'status' => 'running', 'processes' => ['redis:poll' => 2]],
            (object) ['name' => '6a1f2b3c4d5e6-EfGh:supervisor-poll', 'master' => '6a1f2b3c4d5e6-EfGh', 'status' => 'running', 'processes' => ['redis:poll' => 3]],
        ]);
        $status = new QueueStatus(Mockery::mock(JobRepository::class), Mockery::mock(TagRepository::class), Mockery::mock(WorkloadRepository::class), $masters, $supervisors);

        $workers = $status->workers();

        $this->assertSame(['poller1.example.com', 'unknown'], $workers->pluck('name')->all());
        $this->assertSame('running', $workers[0]['status']);
        $this->assertSame([['redis:poll' => 2], ['redis:poll' => 3]], $workers[0]['supervisors']->pluck('processes')->all());
    }

    public function testChecksInAndReportsStats(): void
    {
        $this->freezeSecond();
        config(['librenms.node_id' => 'worker-node-test']);
        $redis = Mockery::mock();
        $redis->shouldReceive('command')->once()->with('hgetall', ['node-stats:worker-node-test'])->andReturn(['poller:devices' => '7', 'poller:seconds' => '12.3456']);
        $redis->shouldReceive('command')->once()->with('hincrby', ['node-stats:worker-node-test', 'poller:devices', -7]);
        $redis->shouldReceive('command')->once()->with('hincrbyfloat', ['node-stats:worker-node-test', 'poller:seconds', -12.3456]);
        Redis::shouldReceive('connection')->with('horizon')->andReturn($redis);
        $queue = Mockery::mock(RedisQueue::class);
        $queue->shouldReceive('pendingSize')->with('poll')->andReturn(3);
        $queue->shouldReceive('pendingSize')->with('discovery')->andReturn(1);
        Queue::shouldReceive('connection')->with('redis')->andReturn($queue);
        $worker = new WorkerNode;
        $worker->start();

        $worker->looped();
        $worker->looped(); // within the heartbeat interval, nothing to do

        $node = PollerCluster::with('stats')->firstWhere('node_id', 'worker-node-test');
        $this->assertNotNull($node);
        $this->assertSame(WorkerNode::VERSION, $node->poller_version);
        $this->assertSame((string) LibrenmsConfig::get('distributed_poller_group'), $node->poller_groups);
        $this->assertEquals(now(), $node->last_report);
        $this->assertSame(PHP_VERSION, $node->poller_details['php_version'] ?? null);

        $stats = $node->stats->keyBy('poller_type');
        $this->assertSame(['discovery', 'poller'], $stats->keys()->sort()->values()->all());
        $this->assertSame(3, $stats['poller']->depth);
        $this->assertSame(7, $stats['poller']->devices);
        $this->assertEquals(12.346, $stats['poller']->worker_seconds);
        $this->assertEquals(LibrenmsConfig::get('service_poller_workers'), $stats['poller']->workers);
        $this->assertSame(0, $stats['discovery']->devices);
        $this->assertSame(1, $stats['discovery']->depth);
    }

    public function testWorkersCountFinishedJobs(): void
    {
        config(['queue.default' => 'redis', 'librenms.node_id' => 'node1']);
        $redis = Mockery::mock();
        $redis->shouldReceive('command')->once()->with('hincrbyfloat', Mockery::on(fn (array $args): bool => $args[0] == 'node-stats:node1' && $args[1] == 'poller:seconds' && is_float($args[2])));
        $redis->shouldReceive('command')->once()->with('hincrby', ['node-stats:node1', 'poller:devices', 1]);
        Redis::shouldReceive('connection')->with('horizon')->andReturn($redis);
        $worker = new WorkerNode;

        $other = $this->job('2', \App\Jobs\PingCheck::class);
        $worker->jobStarted($other);
        $worker->jobFinished($other, true);

        $poll = $this->job('1', PollDevice::class);
        $worker->jobStarted($poll);
        $worker->jobFinished($poll, true);
    }

    private function job(string $id, string $class): Job
    {
        $job = Mockery::mock(Job::class);
        $job->shouldReceive('getJobId')->andReturn($id);
        $job->shouldReceive('payload')->andReturn(['data' => ['commandName' => $class]]);

        return $job;
    }
}
