<?php

namespace LibreNMS\Tests\Feature;

use App\Facades\LibrenmsConfig;
use App\Horizon\JobMonitor;
use App\Models\Device;
use App\Models\PollerCluster;
use App\Models\User;
use App\Queue\FailedJobs;
use App\Queue\QueueStatus;
use App\Queue\RetryTracker;
use App\Queue\WorkerNode;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;
use Mockery\MockInterface;
use Spatie\Permission\Models\Permission;

class QueuePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['queue.default' => 'redis']);
        LibrenmsConfig::set('schedule_type.poller', WorkerNode::SCHEDULE_TYPE);
    }

    public function test_queues_tab_shows_queues_workers_and_failed_jobs(): void
    {
        $device = Device::factory()->create();
        $this->mock(RetryTracker::class, fn (MockInterface $mock) => $mock->shouldReceive('retrying')->andReturn(collect([
            ['device_id' => $device->device_id, 'name' => 'PollDevice', 'queue' => 'poll-7', 'attempt' => 2, 'tries' => 4, 'retry_at' => Carbon::now()->addMinute()],
        ])));
        $this->mock(FailedJobs::class, function (MockInterface $mock) use ($device): void {
            $mock->shouldReceive('list')->andReturn(collect([$this->job(['device_id' => $device->device_id])]));
            $mock->shouldReceive('count')->andReturn(1);
        });
        $this->mock(QueueStatus::class, function (MockInterface $mock): void {
            $mock->shouldReceive('queues')->andReturn(collect([
                ['name' => 'poll-7', 'pending' => 3, 'scheduled' => 40, 'retrying' => 1, 'reserved' => 2, 'worked' => true, 'age' => 12, 'interval' => 297, 'busy' => 4.5, 'workers' => 6],
            ]));
            $mock->shouldReceive('workers')->andReturn(collect([
                ['name' => 'node-a', 'status' => 'running', 'last_report' => Carbon::now(), 'queues' => ['poll-7', 'discovery-7']],
            ]));
        });

        $this->actingAs(User::factory()->admin()->create(['enabled' => true]))
            ->get(route('poller.queues'))
            ->assertOk()
            ->assertSee(__('Refresh'))
            ->assertSee('poll-7')
            ->assertSee('node-a')
            ->assertSee('poll-7, discovery-7')
            ->assertSee('4.5 / 6')
            ->assertSee(url(config('horizon.path')))
            ->assertSee('PollDevice')
            ->assertSee('SNMP timeout')
            ->assertSeeInOrder([__('Down Devices Retrying'), 'PollDevice', '2 / 4', __('Failed Jobs')])
            ->assertSee(__('A device being down is not a failed job.'))
            ->assertSee(route('poller.queues.retry', 'job-uuid'));
    }

    public function test_queues_require_permission(): void
    {
        $user = User::factory()->create(['enabled' => true]);

        $this->actingAs($user)->get(route('poller.queues'))->assertForbidden();
        $this->actingAs($user)->post(route('poller.queues.retry', 'job-uuid'))->assertForbidden();
        $this->actingAs($user)->delete(route('poller.queues.forget', 'job-uuid'))->assertForbidden();
    }

    public function test_view_permission_cannot_manage_failed_jobs(): void
    {
        $this->mockQueuePage();
        $user = User::factory()->read()->create(['enabled' => true]);

        $this->actingAs($user)->get(route('poller.queues'))
            ->assertOk()
            ->assertDontSee(route('poller.queues.retry', 'job-uuid'));
        $this->actingAs($user)->post(route('poller.queues.retry', 'job-uuid'))->assertForbidden();
        $this->actingAs($user)->delete(route('poller.queues.forget', 'job-uuid'))->assertForbidden();
    }

    public function test_horizon_permission_does_not_grant_queue_pages(): void
    {
        $user = $this->userWithPermissions('worker.horizon');

        $this->actingAs($user)->get(route('poller.queues'))->assertForbidden();
        $this->actingAs($user)->post(route('poller.queues.retry', 'job-uuid'))->assertForbidden();
    }

    public function test_queues_require_a_queue(): void
    {
        config(['queue.default' => 'sync']);

        $this->actingAs(User::factory()->admin()->create(['enabled' => true]))->get(route('poller.queues'))->assertNotFound();
    }

    public function test_queues_without_horizon(): void
    {
        config(['queue.default' => 'database']);
        $this->mockQueuePage();

        $this->actingAs(User::factory()->admin()->create(['enabled' => true]))
            ->get(route('poller.queues'))
            ->assertOk()
            ->assertSee(__('Failed Jobs'))
            ->assertDontSee(url(config('horizon.path')));
    }

    public function test_queues_hidden_when_queue_workers_are_not_used(): void
    {
        LibrenmsConfig::set('schedule_type.poller', 'dispatcher');
        $user = User::factory()->admin()->create(['enabled' => true]);

        $this->actingAs($user)->get(route('poller.queues'))->assertNotFound();
        $this->actingAs($user)->get(route('poller.performance'))
            ->assertOk()
            ->assertDontSee(route('poller.queues'))
            ->assertDontSee(url(config('horizon.path')));
    }

    public function test_pollers_page_lists_worker_nodes_without_stats(): void
    {
        PollerCluster::factory()->worker()->create(['poller_name' => 'worker-node.example.com', 'last_report' => now()]);

        $this->actingAs(User::factory()->admin()->create(['enabled' => true]))
            ->get(route('poller.index'))
            ->assertOk()
            ->assertSee('worker-node.example.com')
            ->assertSee(route('poller.queues'));
    }

    public function test_retry_and_forget_failed_job(): void
    {
        $this->mock(FailedJobs::class, function (MockInterface $mock): void {
            $mock->shouldReceive('retry')->once()->with('job-uuid');
            $mock->shouldReceive('forget')->once()->with('job-uuid');
        });
        $user = $this->userWithPermissions('worker.update');

        $this->actingAs($user)->post(route('poller.queues.retry', 'job-uuid'))->assertRedirect();
        $this->actingAs($user)->delete(route('poller.queues.forget', 'job-uuid'))->assertRedirect();
    }

    public function test_capture_jobs_offers_recording_when_not_monitoring(): void
    {
        $device = Device::factory()->create();
        $this->mock(JobMonitor::class, function (MockInterface $mock) use ($device): void {
            $mock->shouldReceive('horizonRunning')->andReturn(true);
            $mock->shouldReceive('isMonitoring')->with("device:$device->device_id")->andReturn(false);
            $mock->shouldNotReceive('monitoredJobs');
        });
        $this->mock(FailedJobs::class, fn (MockInterface $mock) => $mock->shouldReceive('list')->with($device->device_id)->andReturn(collect()));

        $this->actingAs(User::factory()->admin()->create(['enabled' => true]))
            ->get(route('device', ['device' => $device->device_id, 'tab' => 'capture', 'section' => 'jobs']))
            ->assertOk()
            ->assertSee(route('device.jobs.monitor', $device))
            ->assertSee("activeTab: 'jobs'", false)
            ->assertSee('No failed jobs');
    }

    public function test_capture_jobs_shows_recorded_jobs(): void
    {
        $device = Device::factory()->create();
        $this->mock(JobMonitor::class, function (MockInterface $mock) use ($device): void {
            $mock->shouldReceive('horizonRunning')->andReturn(true);
            $mock->shouldReceive('isMonitoring')->andReturn(true);
            $mock->shouldReceive('monitoredJobs')->with("device:$device->device_id")->andReturn(collect([
                $this->job(['id' => 'completed-uuid', 'name' => 'DiscoverDevice:' . $device->device_id, 'status' => 'completed', 'error' => '', 'exception' => '']),
            ]));
        });
        $this->mock(FailedJobs::class, fn (MockInterface $mock) => $mock->shouldReceive('list')->andReturn(collect()));

        $this->actingAs(User::factory()->admin()->create(['enabled' => true]))
            ->get(route('device', ['device' => $device->device_id, 'tab' => 'capture', 'section' => 'jobs']))
            ->assertOk()
            ->assertSee('DiscoverDevice:' . $device->device_id)
            ->assertSee(route('device.jobs.stop-monitoring', $device));
    }

    public function test_capture_shows_failed_jobs_without_horizon(): void
    {
        config(['queue.default' => 'database']);
        $device = Device::factory()->create();
        $this->mock(FailedJobs::class, fn (MockInterface $mock) => $mock->shouldReceive('list')->andReturn(collect([$this->job(['device_id' => $device->device_id])])));

        $this->actingAs(User::factory()->admin()->create(['enabled' => true]))
            ->get(route('device', ['device' => $device->device_id, 'tab' => 'capture', 'section' => 'jobs']))
            ->assertOk()
            ->assertSee('SNMP timeout')
            ->assertDontSee(route('device.jobs.monitor', $device));
        $this->actingAs(User::factory()->admin()->create(['enabled' => true]))
            ->post(route('device.jobs.monitor', $device))
            ->assertNotFound();
    }

    public function test_capture_jobs_hidden_without_a_queue(): void
    {
        config(['queue.default' => 'sync']);
        $device = Device::factory()->create();
        $this->mock(FailedJobs::class, fn (MockInterface $mock) => $mock->shouldNotReceive('list'));

        $this->actingAs(User::factory()->admin()->create(['enabled' => true]))
            ->get(route('device', ['device' => $device->device_id, 'tab' => 'capture']))
            ->assertOk()
            ->assertDontSee(route('device.jobs.monitor', $device));
    }

    public function test_recording_requires_update_permission(): void
    {
        $device = Device::factory()->create();

        $this->actingAs(User::factory()->read()->create(['enabled' => true]))
            ->post(route('device.jobs.monitor', $device))
            ->assertForbidden();
        $this->actingAs($this->userWithPermissions('worker.horizon'))
            ->delete(route('device.jobs.stop-monitoring', $device))
            ->assertForbidden();
    }

    public function test_recording_requires_access_to_the_device(): void
    {
        $device = Device::factory()->create();
        $this->mock(JobMonitor::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('monitor');
            $mock->shouldNotReceive('stopMonitoring');
        });
        $user = $this->userWithPermissions('worker.update');

        $this->actingAs($user)->post(route('device.jobs.monitor', $device))->assertForbidden();
        $this->actingAs($user)->delete(route('device.jobs.stop-monitoring', $device))->assertForbidden();
    }

    public function test_start_and_stop_recording_device_jobs(): void
    {
        $device = Device::factory()->create();
        $this->mock(JobMonitor::class, function (MockInterface $mock) use ($device): void {
            $mock->shouldReceive('horizonRunning')->andReturn(true);
            $mock->shouldReceive('monitor')->once()->with("device:$device->device_id");
            $mock->shouldReceive('stopMonitoring')->once()->with("device:$device->device_id");
        });
        $user = $this->userWithPermissions('worker.update', 'device.viewAll');

        $capture = route('device', ['device' => $device->device_id, 'tab' => 'capture', 'section' => 'jobs']);
        $this->actingAs($user)->post(route('device.jobs.monitor', $device))->assertRedirect($capture);
        $this->actingAs($user)->delete(route('device.jobs.stop-monitoring', $device))->assertRedirect($capture);
    }

    private function userWithPermissions(string ...$permissions): User
    {
        $user = User::factory()->create(['enabled' => true]);
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }
        $user->givePermissionTo($permissions);

        return $user;
    }

    private function mockQueuePage(): void
    {
        $this->mock(RetryTracker::class, fn (MockInterface $mock) => $mock->shouldReceive('retrying')->andReturn(collect()));
        $this->mock(QueueStatus::class, function (MockInterface $mock): void {
            $mock->shouldReceive('queues')->andReturn(collect());
            $mock->shouldReceive('workers')->andReturn(collect());
        });
        $this->mock(FailedJobs::class, function (MockInterface $mock): void {
            $mock->shouldReceive('list')->andReturn(collect([$this->job()]));
            $mock->shouldReceive('count')->andReturn(1);
        });
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function job(array $overrides = []): array
    {
        $device_id = $overrides['device_id'] ?? 1;

        return array_merge([
            'id' => 'job-uuid',
            'name' => 'PollDevice',
            'queue' => 'poll',
            'status' => 'failed',
            'device_id' => $device_id,
            'attempts' => 4,
            'started' => Carbon::now()->subSeconds(5),
            'finished' => Carbon::now(),
            'runtime' => 5.0,
            'error' => 'SNMP timeout',
            'exception' => "SNMP timeout\n#0 stack",
            'retried' => false,
        ], $overrides);
    }
}
