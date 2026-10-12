<?php

namespace LibreNMS\Tests\Feature;

use App\Horizon\QueueStatus;
use App\Models\Device;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;
use Mockery\MockInterface;

class QueuePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['queue.default' => 'redis']);
    }

    public function test_queues_tab_shows_queues_workers_and_failed_jobs(): void
    {
        $device = Device::factory()->create();
        $this->mock(QueueStatus::class, function (MockInterface $mock) use ($device): void {
            $mock->shouldReceive('queues')->andReturn(collect([
                ['name' => 'poll-7', 'pending' => 3, 'delayed' => 40, 'reserved' => 2, 'processes' => 5, 'wait' => 12],
            ]));
            $mock->shouldReceive('workers')->andReturn(collect([
                ['name' => 'node-a', 'status' => 'running', 'supervisors' => collect([
                    ['name' => 'supervisor-poll', 'status' => 'running', 'processes' => ['redis:poll-7' => 5]],
                ])],
            ]));
            $mock->shouldReceive('failedJobs')->andReturn(collect([$this->job(['device_id' => $device->device_id])]));
            $mock->shouldReceive('failedCount')->andReturn(1);
        });

        $this->actingAs(User::factory()->admin()->create(['enabled' => true]))
            ->get(route('poller.queues'))
            ->assertOk()
            ->assertSee(__('Refresh'))
            ->assertSee('poll-7')
            ->assertSee('node-a')
            ->assertSee('supervisor-poll')
            ->assertSee('PollDevice:' . $device->device_id)
            ->assertSee('SNMP timeout')
            ->assertSee(route('poller.queues.retry', 'job-uuid'));
    }

    public function test_queues_require_admin(): void
    {
        $user = User::factory()->read()->create(['enabled' => true]);

        $this->actingAs($user)->get(route('poller.queues'))->assertForbidden();
        $this->actingAs($user)->post(route('poller.queues.retry', 'job-uuid'))->assertForbidden();
        $this->actingAs($user)->delete(route('poller.queues.forget', 'job-uuid'))->assertForbidden();
    }

    public function test_queues_require_redis(): void
    {
        config(['queue.default' => 'database']);

        $this->actingAs(User::factory()->admin()->create(['enabled' => true]))->get(route('poller.queues'))->assertNotFound();
    }

    public function test_retry_and_forget_failed_job(): void
    {
        $this->mock(QueueStatus::class, function (MockInterface $mock): void {
            $mock->shouldReceive('retry')->once()->with('job-uuid');
            $mock->shouldReceive('forget')->once()->with('job-uuid');
        });
        $admin = User::factory()->admin()->create(['enabled' => true]);

        $this->actingAs($admin)->post(route('poller.queues.retry', 'job-uuid'))->assertRedirect();
        $this->actingAs($admin)->delete(route('poller.queues.forget', 'job-uuid'))->assertRedirect();
    }

    public function test_capture_jobs_offers_recording_when_not_monitoring(): void
    {
        $device = Device::factory()->create();
        $this->mock(QueueStatus::class, function (MockInterface $mock) use ($device): void {
            $mock->shouldReceive('isMonitoring')->with("device:$device->device_id")->andReturn(false);
            $mock->shouldReceive('failedJobs')->with("device:$device->device_id")->andReturn(collect());
            $mock->shouldNotReceive('monitoredJobs');
        });

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
        $this->mock(QueueStatus::class, function (MockInterface $mock) use ($device): void {
            $mock->shouldReceive('isMonitoring')->andReturn(true);
            $mock->shouldReceive('monitoredJobs')->with("device:$device->device_id")->andReturn(collect([
                $this->job(['id' => 'completed-uuid', 'name' => 'DiscoverDevice:' . $device->device_id, 'status' => 'completed', 'error' => '', 'exception' => '']),
            ]));
            $mock->shouldReceive('failedJobs')->andReturn(collect());
        });

        $this->actingAs(User::factory()->admin()->create(['enabled' => true]))
            ->get(route('device', ['device' => $device->device_id, 'tab' => 'capture', 'section' => 'jobs']))
            ->assertOk()
            ->assertSee('DiscoverDevice:' . $device->device_id)
            ->assertSee(route('device.jobs.stop-monitoring', $device));
    }

    public function test_capture_jobs_hidden_without_redis(): void
    {
        config(['queue.default' => 'database']);
        $device = Device::factory()->create();
        $this->mock(QueueStatus::class, fn (MockInterface $mock) => $mock->shouldNotReceive('failedJobs'));

        $this->actingAs(User::factory()->admin()->create(['enabled' => true]))
            ->get(route('device', ['device' => $device->device_id, 'tab' => 'capture']))
            ->assertOk()
            ->assertDontSee(route('device.jobs.monitor', $device));
    }

    public function test_recording_requires_admin(): void
    {
        $device = Device::factory()->create();

        $this->actingAs(User::factory()->read()->create(['enabled' => true]))
            ->post(route('device.jobs.monitor', $device))
            ->assertForbidden();
    }

    public function test_start_and_stop_recording_device_jobs(): void
    {
        $device = Device::factory()->create();
        $this->mock(QueueStatus::class, function (MockInterface $mock) use ($device): void {
            $mock->shouldReceive('monitor')->once()->with("device:$device->device_id");
            $mock->shouldReceive('stopMonitoring')->once()->with("device:$device->device_id");
        });
        $admin = User::factory()->admin()->create(['enabled' => true]);

        $capture = route('device', ['device' => $device->device_id, 'tab' => 'capture', 'section' => 'jobs']);
        $this->actingAs($admin)->post(route('device.jobs.monitor', $device))->assertRedirect($capture);
        $this->actingAs($admin)->delete(route('device.jobs.stop-monitoring', $device))->assertRedirect($capture);
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
            'name' => "PollDevice:$device_id",
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
