<?php

namespace LibreNMS\Tests\Feature\Horizon;

use App\Facades\LibrenmsConfig;
use App\Horizon\HorizonNode;
use App\Jobs\DiscoverDevice;
use App\Jobs\PollDevice;
use App\Models\PollerCluster;
use App\Models\User;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\RedisQueue;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Laravel\Horizon\MasterSupervisor;
use Laravel\Horizon\ProvisioningPlan;
use LibreNMS\Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

final class HorizonNodeTest extends TestCase
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
        $this->assertSame($expected, HorizonNode::queueNames($job::queueFor(...), $groups));
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
        ], HorizonNode::supervisors(PollerCluster::factory()->horizon()->make(['poller_groups' => ''])));
    }

    public function testNodeSettingsOverrideGlobalSettings(): void
    {
        LibrenmsConfig::set('distributed_poller_group', '0');
        LibrenmsConfig::set('service_poller_workers', 8);
        $node = PollerCluster::factory()->horizon()->make(['poller_groups' => '1,3', 'poller_workers' => 2]);

        $supervisors = HorizonNode::supervisors($node);

        $this->assertSame(['queue' => ['poll-1', 'poll-3'], 'maxProcesses' => 2], $supervisors['supervisor-poll']);
        $this->assertSame(['discovery-1', 'discovery-3'], $supervisors['supervisor-discovery']['queue'] ?? null);
    }

    public function testHorizonNodeSettings(): void
    {
        // the dispatcher service is disabled when the scheduler dispatches work
        LibrenmsConfig::set('schedule_type.poller', 'scheduler');
        LibrenmsConfig::set('schedule_type.discovery', 'scheduler');

        $definition = collect(PollerCluster::factory()->horizon()->make()->configDefinition(false))->keyBy('name');

        $this->assertSame(['poller_groups', 'poller_enabled', 'poller_workers', 'discovery_enabled', 'discovery_workers'], $definition->keys()->all());
        $this->assertTrue($definition['poller_enabled']['default']);
        $this->assertTrue($definition['poller_enabled']['value']);
        $this->assertTrue($definition['discovery_enabled']['value']);
    }

    public function testSettingsPageShowsHorizonSettings(): void
    {
        $user = User::factory()->admin()->create(['enabled' => true]);
        PollerCluster::factory()->horizon()->create(['poller_name' => 'horizon-node']);

        $this->actingAs($user)->get(route('poller.settings'))
            ->assertOk()
            ->assertSee('horizon-node')
            ->assertSee('poller_workers')
            ->assertDontSee('ping_frequency');
    }

    public function testHorizonNodeRejectsServiceSettings(): void
    {
        $user = User::factory()->admin()->create(['enabled' => true]);
        $node = PollerCluster::factory()->horizon()->create();

        $this->actingAs($user)->putJson(route('poller.settings.update', ['id' => $node->id, 'setting' => 'ping_frequency']), ['value' => 60])
            ->assertStatus(422);
        $this->actingAs($user)->putJson(route('poller.settings.update', ['id' => $node->id, 'setting' => 'poller_workers']), ['value' => 6])
            ->assertOk();

        $this->assertSame(6, $node->fresh()?->poller_workers);
    }

    public function testDisabledPollingDoesNotStartASupervisor(): void
    {
        $node = PollerCluster::factory()->horizon()->make(['poller_enabled' => false]);

        app(HorizonNode::class)->start($node);

        $plan = (new ProvisioningPlan('test', config('horizon.environments'), config('horizon.defaults')))->toSupervisorOptions();
        $this->assertArrayNotHasKey('supervisor-poll', $plan['*']);
        $this->assertArrayHasKey('supervisor-discovery', $plan['*']);
    }

    public function testSupervisorsAreConfiguredWhenHorizonStarts(): void
    {
        LibrenmsConfig::set('distributed_poller_group', '0');
        $node = PollerCluster::factory()->horizon()->create(['poller_groups' => '4', 'poller_workers' => 3]);
        config(['librenms.node_id' => $node->node_id]);

        Event::dispatch(new CommandStarting('horizon:status', new ArrayInput([]), new NullOutput));
        $this->assertSame(['poll'], config('horizon.defaults.supervisor-poll.queue'), 'other commands should not configure supervisors');

        Event::dispatch(new CommandStarting('horizon', new ArrayInput([]), new NullOutput));
        $this->assertSame(['poll-4'], config('horizon.defaults.supervisor-poll.queue'));
        $this->assertSame(3, config('horizon.defaults.supervisor-poll.maxProcesses'));
        $this->assertSame(['discovery-4'], config('horizon.defaults.supervisor-discovery.queue'));
    }

    public function testRestartsWhenSettingsChange(): void
    {
        $node = PollerCluster::factory()->horizon()->create(['poller_groups' => '1']);
        config(['librenms.node_id' => $node->node_id]);
        $horizon = new HorizonNode;
        $horizon->start();

        $node->poller_groups = '2';
        $node->save();
        $master = Mockery::mock(MasterSupervisor::class);
        $master->shouldReceive('terminate')->once();

        $horizon->looped($master);
    }

    public function testChecksInAndReportsStats(): void
    {
        $this->freezeSecond();
        config(['librenms.node_id' => 'horizon-node-test']);
        $redis = Mockery::mock();
        $redis->shouldReceive('command')->once()->with('hgetall', ['node-stats:horizon-node-test'])->andReturn(['poller:devices' => '7', 'poller:seconds' => '12.3456']);
        $redis->shouldReceive('command')->once()->with('hincrby', ['node-stats:horizon-node-test', 'poller:devices', -7]);
        $redis->shouldReceive('command')->once()->with('hincrbyfloat', ['node-stats:horizon-node-test', 'poller:seconds', -12.3456]);
        Redis::shouldReceive('connection')->with('horizon')->andReturn($redis);
        $queue = Mockery::mock(RedisQueue::class);
        $queue->shouldReceive('pendingSize')->with('poll')->andReturn(3);
        $queue->shouldReceive('pendingSize')->with('discovery')->andReturn(1);
        Queue::shouldReceive('connection')->with('redis')->andReturn($queue);
        $horizon = new HorizonNode;
        $horizon->start();
        $master = Mockery::mock(MasterSupervisor::class);
        $master->shouldNotReceive('terminate');

        $horizon->looped($master);
        $horizon->looped($master); // within the heartbeat interval, nothing to do

        $node = PollerCluster::with('stats')->firstWhere('node_id', 'horizon-node-test');
        $this->assertNotNull($node);
        $this->assertSame(HorizonNode::VERSION, $node->poller_version);
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
        $horizon = new HorizonNode;

        $other = $this->job('2', \App\Jobs\PingCheck::class);
        $horizon->jobStarted($other);
        $horizon->jobFinished($other, true);

        $poll = $this->job('1', PollDevice::class);
        $horizon->jobStarted($poll);
        $horizon->jobFinished($poll, true);
    }

    private function job(string $id, string $class): Job
    {
        $job = Mockery::mock(Job::class);
        $job->shouldReceive('getJobId')->andReturn($id);
        $job->shouldReceive('payload')->andReturn(['data' => ['commandName' => $class]]);

        return $job;
    }
}
