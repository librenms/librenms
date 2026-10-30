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
     * Busy is the average number of workers the queue's devices keep busy, from their last poll or discovery,
     * workers are the ones Horizon nodes are configured to run for it (unknown for other queue workers).
     *
     * @param  Collection<int, array{device_id: int, name: string, queue: string, attempt: int, tries: int|null, retry_at: Carbon}>|null  $retrying  from RetryTracker::retrying()
     * @return Collection<int, array{name: string, pending: int, scheduled: int, retrying: int, reserved: int, worked: bool, age: int, interval: int|null, busy: float|null, workers: int|null}>
     */
    public function queues(?Collection $retrying = null): Collection
    {
        $queue = Queue::connection();
        $intervals = $this->pollingQueues() + [PollingQueues::DEFAULT => null];
        ksort($intervals);
        $names = array_keys($intervals);
        // a retry that is due has left the delayed jobs
        $retryCounts = ($retrying ?? $this->retries->retrying())->filter(fn (array $retry): bool => $retry['retry_at']->isFuture())->countBy('queue');
        $worked = $this->worked->worked($names);
        $ages = $this->ages($names);
        $busy = $this->busyWorkers();
        $workers = $this->configuredWorkers();

        return collect($intervals)->map(function (?int $interval, string $name) use ($queue, $retryCounts, $worked, $ages, $busy, $workers): array {
            $retries = (int) $retryCounts->get($name, 0);

            return [
                'name' => $name,
                'pending' => self::size($queue, 'pendingSize', $name),
                'scheduled' => max(0, self::size($queue, 'delayedSize', $name) - $retries),
                'retrying' => $retries,
                'reserved' => self::size($queue, 'reservedSize', $name),
                'worked' => in_array($name, $worked),
                'age' => $ages[$name],
                'interval' => $interval,
                'busy' => $busy[$name] ?? null,
                'workers' => $workers[$name] ?? null,
            ];
        })->values();
    }

    /**
     * Seconds the oldest job ready to run has been in each queue, 0 when none are waiting
     *
     * @param  string[]  $queues
     * @return array<string, int>
     */
    public function ages(array $queues): array
    {
        $queue = Queue::connection();
        $ages = [];

        foreach ($queues as $name) {
            $oldest = self::size($queue, 'creationTimeOfOldestPendingJob', $name);
            $ages[$name] = $oldest ? max(0, now()->getTimestamp() - $oldest) : 0;
        }

        return $ages;
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

    /**
     * Workers the active Horizon nodes run for each queue, from their settings
     *
     * @return array<string, int>
     */
    private function configuredWorkers(): array
    {
        $workers = [];

        foreach (PollerCluster::isQueueWorker()->isActive()->get() as $node) {
            foreach (array_filter(WorkerNode::queues($node)) as $type) {
                foreach ($type['queues'] as $name) {
                    $workers[$name] = ($workers[$name] ?? 0) + $type['workers'];
                }
            }
        }

        return $workers;
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

    /**
     * A queue size, the redis and database queue drivers have them
     */
    private static function size(object $queue, string $method, string $name): int
    {
        return method_exists($queue, $method) ? (int) $queue->$method($name) : 0;
    }
}
