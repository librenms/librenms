<?php

namespace LibreNMS\Tests\Feature\Validations;

use App\Facades\LibrenmsConfig;
use App\Jobs\DispatchPollingWork;
use App\Models\Device;
use App\Models\PollerCluster;
use App\Queue\FailedJobs;
use App\Queue\QueueStatus;
use App\Queue\RetryTracker;
use App\Queue\WorkedQueues;
use App\Queue\WorkerNode;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;
use LibreNMS\ValidationResult;
use LibreNMS\Validations\Poller\CheckDispatcherService;
use LibreNMS\Validations\Poller\CheckQueuePolling;
use LibreNMS\Validations\Poller\CheckQueueWorkers;
use Mockery\MockInterface;
use RuntimeException;

final class QueueWorkerValidationTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['queue.default' => 'redis']);
        LibrenmsConfig::set('schedule_type.poller', WorkerNode::SCHEDULE_TYPE);
        LibrenmsConfig::set('schedule_type.discovery', WorkerNode::SCHEDULE_TYPE);
    }

    public function testQuietWithoutQueueWorkers(): void
    {
        LibrenmsConfig::set('schedule_type.poller', 'dispatcher');
        LibrenmsConfig::set('schedule_type.discovery', 'dispatcher');
        Device::factory()->create();

        $this->assertFalse((new CheckQueueWorkers)->enabled());
        $this->assertFalse((new CheckQueuePolling)->enabled());
    }

    public function testInactiveWorkerNode(): void
    {
        PollerCluster::factory()->worker()->create(['poller_name' => 'gone.example.com', 'last_report' => now()->subDay()]);
        $this->queueStatus();

        $result = (new CheckQueueWorkers)->validate();

        $this->assertSame(ValidationResult::FAILURE, $result->getStatus());
        $this->assertSame(['gone.example.com'], $result->getList());
    }

    public function testDispatcherServiceIgnoresWorkerNodes(): void
    {
        PollerCluster::factory()->worker()->create(['last_report' => now()->subDay()]);

        $this->assertSame(ValidationResult::SUCCESS, (new CheckDispatcherService)->validate()->getStatus());
    }

    public function testRequiresAQueue(): void
    {
        config(['queue.default' => 'sync']);

        $result = (new CheckQueueWorkers)->validate();

        $this->assertSame(ValidationResult::FAILURE, $result->getStatus());
        $this->assertStringContainsString('sync', $result->getMessage());
    }

    public function testAnyQueueDriver(): void
    {
        config(['queue.default' => 'database']);
        Device::factory()->create(['poller_group' => 0]);
        $this->queueStatus();

        $this->assertSame(ValidationResult::SUCCESS, (new CheckQueueWorkers)->validate()->getStatus());
    }

    public function testWorkersNotRunning(): void
    {
        $this->queueStatus(worked: []);

        $result = (new CheckQueueWorkers)->validate();

        $this->assertSame(ValidationResult::FAILURE, $result->getStatus());
        $this->assertTrue($result->hasFix());
    }

    public function testUnworkedPollerGroup(): void
    {
        Device::factory()->create(['poller_group' => 2]);
        $this->queueStatus(worked: ['scheduler', 'poll', 'discovery', 'poll-2']);

        $result = (new CheckQueueWorkers)->validate();

        $this->assertSame(ValidationResult::FAILURE, $result->getStatus());
        $this->assertSame(['discovery-2'], $result->getList());
    }

    public function testQueueFallingBehind(): void
    {
        Device::factory()->create(['poller_group' => 0]);
        $this->queueStatus(ages: [
            'poll' => DispatchPollingWork::pollInterval() + 1,
            'discovery' => 600,
        ]);

        $result = (new CheckQueueWorkers)->validate();

        $this->assertSame(ValidationResult::WARNING, $result->getStatus());
        $this->assertSame(['poll (' . (DispatchPollingWork::pollInterval() + 1) . 's)'], $result->getList());
    }

    public function testWorkersOk(): void
    {
        Device::factory()->create(['poller_group' => 0]);
        $this->queueStatus(ages: ['poll' => 5]);

        $this->assertSame(ValidationResult::SUCCESS, (new CheckQueueWorkers)->validate()->getStatus());
    }

    public function testQueueError(): void
    {
        $this->mock(QueueStatus::class, fn (MockInterface $mock) => $mock->shouldReceive('pollingQueues')->andReturn(['scheduler' => 10]));
        $this->mock(WorkedQueues::class, fn (MockInterface $mock) => $mock->shouldReceive('worked')->andThrow(new RuntimeException('Connection refused')));

        $result = (new CheckQueueWorkers)->validate();

        $this->assertSame(ValidationResult::FAILURE, $result->getStatus());
        $this->assertStringContainsString('Connection refused', $result->getMessage());
    }

    public function testNotDispatched(): void
    {
        $this->queueStatus(lastDispatched: null);

        $result = (new CheckQueuePolling)->validate();

        $this->assertSame(ValidationResult::FAILURE, $result->getStatus());
        $this->assertFalse($result->hasList());
    }

    public function testDispatcherFailing(): void
    {
        $this->queueStatus(lastDispatched: now()->subMinutes(10), failed: [['finished' => now()->subSeconds(5), 'error' => 'QueryException: Table devices is marked as crashed']]);

        $result = (new CheckQueuePolling)->validate();

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

        $result = (new CheckQueuePolling)->validate();

        $this->assertSame(ValidationResult::WARNING, $result->getStatus());
        $this->assertContains($overdue->hostname, $result->getList());
        $this->assertCount(1, $result->getList());
    }

    public function testDispatchedAndPolled(): void
    {
        $this->queueStatus();
        Device::factory()->create(['status' => 1, 'last_polled' => now()->subMinute()]);

        $this->assertSame(ValidationResult::SUCCESS, (new CheckQueuePolling)->validate()->getStatus());
    }

    /**
     * @param  string[]|null  $worked  defaults to the queues for poller group 0
     * @param  array<string, int>  $ages  seconds the oldest job waited, others have none waiting
     * @param  array<int, array{finished: Carbon, error: string}>  $failed  dispatcher runs
     */
    private function queueStatus(?array $worked = null, array $ages = [], ?Carbon $lastDispatched = new Carbon, array $failed = []): void
    {
        $this->mock(WorkedQueues::class, fn (MockInterface $mock) => $mock->shouldReceive('worked')->andReturn($worked ?? ['scheduler', 'poll', 'discovery']));
        $this->mock(FailedJobs::class, fn (MockInterface $mock) => $mock->shouldReceive('list')->with(null, DispatchPollingWork::class, 1)->andReturn(collect($failed)));
        $this->mock(QueueStatus::class, function (MockInterface $mock) use ($ages, $lastDispatched): void {
            $mock->shouldReceive('lastDispatched')->andReturn($lastDispatched);
            $mock->shouldReceive('pollingQueues')->andReturnUsing(fn () => (new QueueStatus(new RetryTracker, new WorkedQueues))->pollingQueues());
            $mock->shouldReceive('overdueDevices')->andReturnUsing(fn () => (new QueueStatus(new RetryTracker, new WorkedQueues))->overdueDevices());
            $mock->shouldReceive('ages')->andReturnUsing(fn (array $names): array => array_intersect_key($ages, array_flip($names)) + array_fill_keys($names, 0));
        });
    }
}
