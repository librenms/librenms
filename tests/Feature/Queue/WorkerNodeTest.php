<?php

namespace LibreNMS\Tests\Feature\Queue;

use App\Facades\LibrenmsConfig;
use App\Jobs\DiscoverDevice;
use App\Jobs\PollDevice;
use App\Models\PollerCluster;
use App\Models\User;
use App\Queue\PollingQueues;
use App\Queue\WorkedQueues;
use App\Queue\WorkerNode;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\Events\Looping;
use Illuminate\Support\Facades\Event;
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
        $this->assertSame($expected, PollingQueues::forGroups($groups, poll: $job == PollDevice::class, discover: $job == DiscoverDevice::class));
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
        // queue:work and Horizon's workers both fire Looping
        Event::dispatch(new Looping('redis', 'poll,poll-2'));

        $this->assertSame(['poll', 'poll-2'], app(WorkedQueues::class)->worked(['scheduler', 'poll', 'poll-2', 'discovery']));
    }

    public function testRecordsExpireWithoutWorkers(): void
    {
        $this->freezeSecond();
        app(WorkedQueues::class)->looping('poll');

        $this->travel(5)->minutes();

        $this->assertSame([], app(WorkedQueues::class)->worked(['poll']));
    }

    public function testBusyWorkerStillWorksItsQueues(): void
    {
        $this->freezeSecond();
        $busy = new WorkedQueues;
        $busy->looping('discovery');
        $job = Mockery::mock(Job::class);
        $job->shouldReceive('timeout')->andReturn(3600);
        $busy->jobStarted($job);

        // another process on the queue loops and then stops, it does not shorten the busy one's record
        (new WorkedQueues)->looping('discovery');
        $this->travel(30)->minutes();

        $this->assertSame(['discovery'], app(WorkedQueues::class)->worked(['discovery']));
    }
}
