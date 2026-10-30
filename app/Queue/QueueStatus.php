<?php

namespace App\Queue;

use App\Jobs\DiscoverDevice;
use App\Jobs\DispatchPollingWork;
use App\Jobs\PollDevice;
use App\Models\Device;
use App\Models\PollerCluster;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

/**
 * Queues and the workers working them, for the queue pages and validations
 */
class QueueStatus
{
    /** when DispatchPollingWork last completed a run, kept for an hour */
    private const DISPATCHED_KEY = 'queue-dispatched';

    public function __construct(
        private readonly RetryTracker $retries,
        private readonly WorkedQueues $worked,
    ) {
    }

    /**
     * Jobs go to a queue for the workers, the sync driver runs them right away
     */
    public static function jobsAreQueued(): bool
    {
        return ! in_array(config('queue.default'), ['sync', 'null']);
    }

    /**
     * Show the queue pages, queue workers are in use
     */
    public static function showPages(): bool
    {
        return self::jobsAreQueued() && WorkerNode::inUse();
    }

    /**
     * A queue size, the redis and database queue drivers have them
     */
    public static function size(object $queue, string $method, string $name): int
    {
        return method_exists($queue, $method) ? (int) $queue->$method($name) : 0;
    }

    /**
     * Queues polling work is dispatched to for the enabled devices, with the seconds its work is due in
     *
     * @return array<string, int>
     */
    public function pollingQueues(): array
    {
        $intervals = [PollingQueues::SCHEDULER => DispatchPollingWork::INTERVAL];
        $groups = Device::where('disabled', 0)->distinct()->pluck('poller_group')->map(intval(...))->all();

        foreach (PollingQueues::forGroups($groups, poll: WorkerNode::inUse('poller'), discover: false) as $queue) {
            $intervals[$queue] = DispatchPollingWork::pollInterval();
        }
        foreach (PollingQueues::forGroups($groups, poll: false, discover: WorkerNode::inUse('discovery')) as $queue) {
            $intervals[$queue] = DispatchPollingWork::discoveryInterval();
        }

        return $intervals;
    }

    /**
     * Polling queues and the default queue with job counts by state.
     * Delayed jobs are split into retries of down devices and work scheduled by DispatchPollingWork.
     * Busy is the average number of workers the queue's devices keep busy, from their last poll or discovery.
     *
     * @param  array<string, int>|null  $intervals  from pollingQueues()
     * @return Collection<int, array{name: string, pending: int, scheduled: int, retrying: int, reserved: int, worked: bool, age: int, interval: int|null, busy: float|null}>
     */
    public function queues(?array $intervals = null): Collection
    {
        $queue = Queue::connection();
        $retryCounts = $this->retries->retrying()->countBy('queue');
        $intervals = ($intervals ?? $this->pollingQueues()) + [PollingQueues::DEFAULT => null];
        $worked = $this->worked->worked(array_keys($intervals));
        $busy = $this->busyWorkers();
        ksort($intervals);

        return collect($intervals)->map(function (?int $interval, string $name) use ($queue, $retryCounts, $worked, $busy): array {
            $retries = (int) $retryCounts->get($name, 0);
            $oldest = self::size($queue, 'creationTimeOfOldestPendingJob', $name);

            return [
                'name' => $name,
                'pending' => self::size($queue, 'pendingSize', $name),
                'scheduled' => self::size($queue, 'delayedSize', $name) - $retries,
                'retrying' => $retries,
                'reserved' => self::size($queue, 'reservedSize', $name),
                'worked' => in_array($name, $worked),
                // seconds the oldest job ready to run has been in the queue
                'age' => $oldest ? max(0, now()->getTimestamp() - $oldest) : 0,
                'interval' => $interval,
                'busy' => $busy[$name] ?? null,
            ];
        })->values();
    }

    /**
     * Queue worker nodes on the pollers page (Horizon) and the queues their settings give them
     *
     * @return Collection<int, array{name: string, status: 'running'|'inactive', last_report: Carbon, queues: array<string>}>
     */
    public function workers(): Collection
    {
        $active = PollerCluster::isQueueWorker()->isActive()->pluck('id');

        return PollerCluster::isQueueWorker()->get()
            ->map(fn (PollerCluster $node): array => [
                'name' => (string) ($node->poller_name ?: $node->node_id),
                'status' => $active->contains($node->id) ? 'running' : 'inactive',
                'last_report' => $node->last_report,
                'queues' => collect(WorkerNode::queues($node))->filter()->flatMap(fn (array $type): array => $type['queues'])->all(),
            ])
            ->sortBy('name')
            ->values();
    }

    /**
     * Average workers busy per polling queue: the time its devices took on their last run over the interval they run in
     *
     * @return array<string, float>
     */
    private function busyWorkers(): array
    {
        $busy = [];
        $groups = Device::where('disabled', 0)
            ->groupBy('poller_group')
            ->select('poller_group')
            ->selectRaw('SUM(`last_polled_timetaken`) AS `poll`, SUM(`last_discovered_timetaken`) AS `discovery`')
            ->toBase()
            ->get();

        foreach ($groups as $group) {
            $busy[PollDevice::queueFor((int) $group->poller_group)] = round((float) $group->poll / DispatchPollingWork::pollFrequency(), 1);
            $busy[DiscoverDevice::queueFor((int) $group->poller_group)] = round((float) $group->discovery / DispatchPollingWork::discoveryFrequency(), 1);
        }

        return $busy;
    }

    public function recordDispatched(): void
    {
        Cache::put(self::DISPATCHED_KEY, now()->getTimestamp(), 3600);
    }

    /**
     * When DispatchPollingWork last completed a run, null if it has not in the last hour
     */
    public function lastDispatched(): ?Carbon
    {
        $time = Cache::get(self::DISPATCHED_KEY);

        return $time ? Carbon::createFromTimestamp((int) $time) : null;
    }
}
