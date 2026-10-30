<?php

namespace LibreNMS\Tests\Feature\Horizon;

use App\Facades\LibrenmsConfig;
use App\Horizon\Supervisors;
use App\Models\PollerCluster;
use App\Queue\WorkerNode;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Horizon\MasterSupervisor;
use Laravel\Horizon\ProvisioningPlan;
use LibreNMS\Tests\TestCase;
use Mockery;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

final class SupervisorsTest extends TestCase
{
    use RefreshDatabase;

    public function testSupervisorsForNodeSettings(): void
    {
        LibrenmsConfig::set('distributed_poller_group', '0');
        LibrenmsConfig::set('service_discovery_workers', 4);
        $node = PollerCluster::factory()->worker()->make(['poller_groups' => '1,3', 'poller_workers' => 2]);

        $this->assertSame([
            'supervisor-poll' => ['queue' => ['poll-1', 'poll-3'], 'maxProcesses' => 2],
            'supervisor-discovery' => ['queue' => ['discovery-1', 'discovery-3'], 'maxProcesses' => 4],
        ], Supervisors::forNode($node));
    }

    public function testDisabledPollingDoesNotStartASupervisor(): void
    {
        $node = PollerCluster::factory()->worker()->make(['poller_enabled' => false]);

        app(Supervisors::class)->start($node);

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

    public function testWorkerCommandRunsHorizon(): void
    {
        $this->assertInstanceOf(\Laravel\Horizon\Console\HorizonCommand::class, \Illuminate\Support\Facades\Artisan::all()['worker'] ?? null);
    }

    public function testChangedSettingsStopHorizon(): void
    {
        $node = PollerCluster::factory()->worker()->create(['poller_groups' => '1']);
        config(['librenms.node_id' => $node->node_id]);
        $supervisors = new Supervisors;
        $supervisors->start();

        $node->poller_groups = '2';
        $node->save();
        // the service starts it again with the new settings
        $master = Mockery::mock(MasterSupervisor::class);
        $master->shouldReceive('terminate')->once();

        $supervisors->looped($master);
        $supervisors->looped($master); // within the heartbeat interval, nothing to do
    }

    public function testUnchangedSettingsKeepHorizonRunning(): void
    {
        $node = PollerCluster::factory()->worker()->create(['poller_groups' => '1']);
        config(['librenms.node_id' => $node->node_id]);
        $supervisors = new Supervisors;
        $supervisors->start();
        $master = Mockery::mock(MasterSupervisor::class);
        $master->shouldNotReceive('terminate');

        $supervisors->looped($master);
    }

    public function testMasterChecksInTheNode(): void
    {
        $this->freezeSecond();
        config(['librenms.node_id' => 'worker-node-test']);
        LibrenmsConfig::set('distributed_poller_name', 'poller1.example.com');
        $supervisors = new Supervisors;
        $supervisors->start();
        $master = Mockery::mock(MasterSupervisor::class);
        $master->shouldNotReceive('terminate');

        $supervisors->looped($master);

        $node = PollerCluster::firstWhere('node_id', 'worker-node-test');
        $this->assertNotNull($node);
        $this->assertSame(WorkerNode::VERSION, $node->poller_version);
        $this->assertSame('poller1.example.com', $node->poller_name);
        $this->assertSame((string) LibrenmsConfig::get('distributed_poller_group'), $node->poller_groups);
        $this->assertEquals(now(), $node->last_report);
        $this->assertSame(PHP_VERSION, $node->poller_details['php_version'] ?? null);
        $this->assertCount(0, $node->stats, 'queue worker nodes share their queues, there are no per node stats');
    }

    public function testMastersAreNamedForTheNode(): void
    {
        config(['librenms.node_id' => '6a1f2b3c4d5e6']);

        $this->assertSame('6a1f2b3c4d5e6', MasterSupervisor::basename());
        $this->assertStringStartsWith('6a1f2b3c4d5e6-', MasterSupervisor::name());
    }
}
