<?php

namespace LibreNMS\Tests\Feature\Validations;

use App\Facades\LibrenmsConfig;
use App\Horizon\QueueStatus;
use App\Jobs\DispatchPollingWork;
use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;
use LibreNMS\ValidationResult;
use LibreNMS\Validations\Poller\CheckHorizon;
use LibreNMS\Validations\Poller\CheckScheduledPolling;
use Mockery\MockInterface;
use RuntimeException;

final class ScheduledPollingValidationTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['queue.default' => 'redis']);
        LibrenmsConfig::set('schedule_type.poller', 'scheduler');
        LibrenmsConfig::set('schedule_type.discovery', 'scheduler');
    }

    public function testDisabledWithoutSchedulerPolling(): void
    {
        LibrenmsConfig::set('schedule_type.poller', 'dispatcher');
        LibrenmsConfig::set('schedule_type.discovery', 'dispatcher');

        $this->assertFalse((new CheckHorizon)->enabled());
    }

    public function testRequiresRedisQueue(): void
    {
        config(['queue.default' => 'database']);

        $result = (new CheckHorizon)->validate();

        $this->assertSame(ValidationResult::FAILURE, $result->getStatus());
        $this->assertStringContainsString('database', $result->getMessage());
    }

    public function testHorizonNotRunning(): void
    {
        $this->queueStatus(workers: []);

        $result = (new CheckHorizon)->validate();

        $this->assertSame(ValidationResult::FAILURE, $result->getStatus());
        $this->assertTrue($result->hasFix());
    }

    public function testUnworkedPollerGroup(): void
    {
        Device::factory()->create(['poller_group' => 2]);
        $this->queueStatus(worked: ['scheduler', 'poll', 'discovery', 'poll-2']);

        $result = (new CheckHorizon)->validate();

        $this->assertSame(ValidationResult::FAILURE, $result->getStatus());
        $this->assertSame(['discovery-2'], $result->getList());
    }

    public function testQueueFallingBehind(): void
    {
        Device::factory()->create(['poller_group' => 0]);
        $this->queueStatus(queues: [
            ['name' => 'poll', 'wait' => DispatchPollingWork::pollInterval() + 1],
            ['name' => 'discovery', 'wait' => 600],
        ]);

        $result = (new CheckHorizon)->validate();

        $this->assertSame(ValidationResult::WARNING, $result->getStatus());
        $this->assertSame(['poll (' . (DispatchPollingWork::pollInterval() + 1) . 's)'], $result->getList());
    }

    public function testHorizonOk(): void
    {
        Device::factory()->create(['poller_group' => 0]);
        $this->queueStatus(queues: [['name' => 'poll', 'wait' => 5]]);

        $this->assertSame(ValidationResult::SUCCESS, (new CheckHorizon)->validate()->getStatus());
    }

    public function testRedisError(): void
    {
        $this->mock(QueueStatus::class, fn (MockInterface $mock) => $mock->shouldReceive('workers')->andThrow(new RuntimeException('Connection refused')));

        $result = (new CheckHorizon)->validate();

        $this->assertSame(ValidationResult::FAILURE, $result->getStatus());
        $this->assertStringContainsString('Connection refused', $result->getMessage());
    }

    public function testNotDispatched(): void
    {
        $this->queueStatus(lastDispatched: null);

        $result = (new CheckScheduledPolling)->validate();

        $this->assertSame(ValidationResult::FAILURE, $result->getStatus());
        $this->assertFalse($result->hasList());
    }

    public function testDispatcherFailing(): void
    {
        $this->queueStatus(lastDispatched: now()->subMinutes(10), failed: [['finished' => now()->subSeconds(5), 'error' => 'QueryException: Table devices is marked as crashed']]);

        $result = (new CheckScheduledPolling)->validate();

        $this->assertSame(ValidationResult::FAILURE, $result->getStatus());
        $this->assertStringContainsString('10 minutes ago', $result->getMessage());
        $this->assertStringContainsString('marked as crashed', $result->getList()[0]);
    }

    public function testOverdueDevices(): void
    {
        $this->queueStatus();
        $overdue = Device::factory()->create(['status' => 1, 'last_polled' => now()->subHour()]);
        Device::factory()->create(['status' => 1, 'last_polled' => now()->subMinute()]);
        Device::factory()->create(['status' => 0, 'last_polled' => now()->subHour()]);
        Device::factory()->create(['status' => 1, 'disabled' => 1, 'last_polled' => now()->subHour()]);

        $result = (new CheckScheduledPolling)->validate();

        $this->assertSame(ValidationResult::WARNING, $result->getStatus());
        $this->assertContains($overdue->hostname, $result->getList());
        $this->assertCount(1, $result->getList());
    }

    public function testDispatchedAndPolled(): void
    {
        $this->queueStatus();
        Device::factory()->create(['status' => 1, 'last_polled' => now()->subMinute()]);

        $this->assertSame(ValidationResult::SUCCESS, (new CheckScheduledPolling)->validate()->getStatus());
    }

    /**
     * @param  string[]|null  $worked  defaults to the queues for poller group 0
     * @param  array<int, array{name: string, wait: int}>  $queues
     * @param  string[]  $workers
     * @param  array<int, array{finished: Carbon, error: string}>  $failed  dispatcher runs
     */
    private function queueStatus(?array $worked = null, array $queues = [], array $workers = ['node-a'], ?Carbon $lastDispatched = new Carbon, array $failed = []): void
    {
        $this->mock(QueueStatus::class, function (MockInterface $mock) use ($worked, $queues, $workers, $lastDispatched, $failed): void {
            $mock->shouldReceive('lastDispatched')->andReturn($lastDispatched);
            $mock->shouldReceive('failedJobs')->with('dispatcher')->andReturn(collect($failed));
            $mock->shouldReceive('workers')->andReturn(collect($workers)->map(fn (string $name): array => ['name' => $name, 'status' => 'running', 'supervisors' => collect()]));
            $mock->shouldReceive('workedQueues')->andReturn($worked ?? ['scheduler', 'poll', 'discovery']);
            $mock->shouldReceive('queues')->andReturn(collect($queues));
        });
    }
}
