<?php

namespace App\Queue;

use App\Jobs\DiscoverDevice;
use App\Jobs\DispatchPollingWork;
use App\Jobs\PollDevice;
use App\Models\Device;
use App\Models\FailedJob;
use App\Models\PollerCluster;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

/**
 * Read queue and job data for display in LibreNMS pages, with any queue driver
 */
class QueueStatus
{
    /** job uuid => retry */
    private const RETRYING_KEY = 'queue-retrying';
    /** seconds a retry is listed after it was due, it is missed if its job was removed from the queue */
    private const RETRY_STALE = 3600;
    /** when DispatchPollingWork last completed a run, kept for an hour */
    private const DISPATCHED_KEY = 'queue-dispatched';

    /**
     * Jobs are queued for workers, the sync driver runs them right away
     */
    public static function available(): bool
    {
        return ! in_array(config('queue.default'), ['sync', 'null']);
    }

    /**
     * Show the queue pages, queue workers are in use
     */
    public static function showPages(): bool
    {
        return self::available() && WorkerNode::inUse();
    }

    /**
     * Queues polling work is dispatched to, with the seconds its work is due in
     *
     * @return array<string, int>
     */
    public static function pollingQueues(?bool $poll = null, ?bool $discover = null): array
    {
        $poll ??= WorkerNode::inUse('poller');
        $discover ??= WorkerNode::inUse('discovery');
        $intervals = ['scheduler' => DispatchPollingWork::INTERVAL];

        foreach (Device::where('disabled', 0)->distinct()->pluck('poller_group')->map(intval(...)) as $group) {
            if ($poll) {
                $intervals[PollDevice::queueFor($group)] = DispatchPollingWork::pollInterval();
            }
            if ($discover) {
                $intervals[DiscoverDevice::queueFor($group)] = DispatchPollingWork::discoveryInterval();
            }
        }

        return $intervals;
    }

    /**
     * Polling queues and the default queue, with job counts by state where the queue driver has them.
     * Delayed jobs are split into retries of down devices and work scheduled by DispatchPollingWork.
     *
     * @param  Collection<int, array{device_id: int|null, name: string, queue: string, attempt: int, tries: int|null, retry_at: Carbon}>|null  $retrying
     * @return Collection<int, array{name: string, pending: int|null, scheduled: int|null, retrying: int, reserved: int|null, worked: bool, age: int|null, interval: int|null}>
     */
    public function queues(?Collection $retrying = null): Collection
    {
        $queue = Queue::connection();
        $retryCounts = ($retrying ?? $this->retrying())->countBy('queue');
        $intervals = self::pollingQueues() + ['default' => null];
        $worked = $this->workedQueues(array_keys($intervals));
        ksort($intervals);

        return collect($intervals)->map(function (?int $interval, string $name) use ($queue, $retryCounts, $worked): array {
            $retries = (int) $retryCounts->get($name, 0);
            $delayed = self::size($queue, 'delayedSize', $name);
            $oldest = method_exists($queue, 'creationTimeOfOldestPendingJob') ? (int) $queue->creationTimeOfOldestPendingJob($name) : null;

            return [
                'name' => $name,
                'pending' => self::size($queue, 'pendingSize', $name),
                'scheduled' => $delayed === null ? null : $delayed - $retries,
                'retrying' => $retries,
                'reserved' => self::size($queue, 'reservedSize', $name),
                'worked' => in_array($name, $worked),
                // seconds the oldest job ready to run has been in the queue
                'age' => $oldest === null ? null : ($oldest ? max(0, now()->getTimestamp() - $oldest) : 0),
                'interval' => $interval,
            ];
        })->values();
    }

    /**
     * The given queues that a worker process on any node works or did within the last heartbeats
     *
     * @param  string[]  $queues
     * @return string[]
     */
    public function workedQueues(array $queues): array
    {
        if (empty($queues)) {
            return [];
        }

        $worked = Cache::many(array_map(fn (string $queue): string => WorkerNode::workedKey($queue), $queues));

        return array_values(array_filter($queues, fn (string $queue): bool => ! empty($worked[WorkerNode::workedKey($queue)])));
    }

    /**
     * Queue worker nodes and the polling queues they work
     *
     * @return Collection<int, array{name: string, status: 'running'|'inactive', last_report: Carbon, queues: list<string>}>
     */
    public function workers(): Collection
    {
        $queues = array_keys(self::pollingQueues() + ['default' => null]);
        $active = PollerCluster::isQueueWorker()->isActive()->pluck('id');

        return PollerCluster::isQueueWorker()->get()
            ->map(function (PollerCluster $node) use ($queues, $active): array {
                $worked = Cache::many(array_map(fn (string $queue): string => WorkerNode::workedKey($queue, $node->node_id), $queues));

                return [
                    'name' => $node->poller_name ?: $node->node_id,
                    'status' => $active->contains($node->id) ? 'running' : 'inactive',
                    'last_report' => $node->last_report,
                    'queues' => array_values(array_filter($queues, fn (string $queue): bool => ! empty($worked[WorkerNode::workedKey($queue, $node->node_id)]))),
                ];
            })
            ->sortBy('name')
            ->values();
    }

    /**
     * Jobs waiting to be retried, these were released because the device was down
     *
     * @return Collection<int, array{device_id: int|null, name: string, queue: string, attempt: int, tries: int|null, retry_at: Carbon}>
     */
    public function retrying(): Collection
    {
        /** @var array<string, array{device_id: int|null, name: string, queue: string, attempt: int, tries: int|null, retry_at: int}> $retries */
        $retries = (array) Cache::get(self::RETRYING_KEY, []);

        return collect(self::withoutStale($retries))
            ->map(fn (array $retry): array => ['retry_at' => Carbon::createFromTimestamp($retry['retry_at'])] + $retry)
            ->sortBy('retry_at')
            ->values();
    }

    /**
     * Record a job released to run again after an exception, the delayed queue is too large to search for them
     */
    public function jobReleased(Job $job, int $backoff): void
    {
        $payload = $job->payload();
        $retry = [
            'device_id' => self::deviceId($payload),
            'name' => class_basename((string) ($payload['displayName'] ?? '')),
            'queue' => $job->getQueue(),
            'attempt' => $job->attempts(),
            'tries' => $job->maxTries(),
            'retry_at' => now()->getTimestamp() + $backoff,
        ];

        $this->updateRetrying(fn (array $retries): array => [(string) $job->uuid() => $retry] + $retries);
    }

    public function jobStarted(Job $job): void
    {
        // only a job that ran before can be a retry
        if ($job->attempts() > 1) {
            $this->updateRetrying(fn (array $retries): array => Arr::except($retries, (string) $job->uuid()));
        }
    }

    /**
     * @param  callable(array<string, array<string, mixed>>): array<string, array<string, mixed>>  $update
     */
    private function updateRetrying(callable $update): void
    {
        Cache::lock(self::RETRYING_KEY . ':lock', 10)->block(5, function () use ($update): void {
            Cache::forever(self::RETRYING_KEY, self::withoutStale($update((array) Cache::get(self::RETRYING_KEY, []))));
        });
    }

    /**
     * @template T of array
     *
     * @param  array<string, T>  $retries
     * @return array<string, T>
     */
    private static function withoutStale(array $retries): array
    {
        return array_filter($retries, fn (array $retry): bool => $retry['retry_at'] >= now()->getTimestamp() - self::RETRY_STALE);
    }

    public static function recordDispatched(): void
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
     * Failed jobs, newest first
     *
     * @param  int|null  $device_id  only jobs for this device
     * @param  class-string|null  $job  only jobs of this class
     * @return Collection<int, array<string, mixed>>
     */
    public function failedJobs(?int $device_id = null, ?string $job = null, int $limit = 50): Collection
    {
        return FailedJob::query()
            ->when($device_id !== null, fn ($query) => $query->where('payload->device_id', $device_id))
            ->when($job !== null, fn ($query) => $query->where('payload->displayName', $job))
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map($this->present(...));
    }

    public function failedCount(): int
    {
        return FailedJob::count();
    }

    public function retry(string $uuid): void
    {
        Artisan::call('queue:retry', ['id' => [$uuid]]);
    }

    public function forget(string $uuid): void
    {
        app('queue.failer')->forget($uuid);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(FailedJob $job): array
    {
        $payload = (array) $job->payload;
        $exception = mb_convert_encoding((string) $job->exception, 'UTF-8');

        return [
            'id' => $job->uuid,
            'name' => class_basename((string) ($payload['displayName'] ?? '')),
            'queue' => $job->queue,
            'status' => 'failed',
            'device_id' => self::deviceId($payload),
            'attempts' => $payload['attempts'] ?? null,
            'started' => null,
            'finished' => $job->failed_at,
            'runtime' => null,
            'error' => self::exceptionSummary($exception),
            'exception' => $exception,
            'retried' => false,
        ];
    }

    /**
     * The device a job payload is for, added to device jobs when they are queued (or a Horizon device tag)
     *
     * @param  array<string, mixed>  $payload
     */
    public static function deviceId(array $payload): ?int
    {
        if (isset($payload['device_id'])) {
            return (int) $payload['device_id'];
        }

        $tag = Arr::first((array) ($payload['tags'] ?? []), fn ($tag): bool => str_starts_with((string) $tag, 'device:'));

        return $tag ? (int) Str::after((string) $tag, ':') : null;
    }

    /**
     * The exception class and message, without the location and stack trace
     */
    public static function exceptionSummary(string $exception): string
    {
        $head = Str::before($exception, "\nStack trace:");

        // a closure location is its source code, which spans lines
        // a file location is the last "in /path:line", the message itself may mention files
        if (preg_match('#^(.*?) in laravel-serializable-closure://.*:\d+$#s', $head, $matches)
            || preg_match('#^(.*) in /[^\n]*:\d+$#s', $head, $matches)) {
            $head = $matches[1];
        }

        return trim((string) preg_replace('/\s+/', ' ', $head));
    }

    /**
     * Queue sizes where the queue driver has them (redis, database, sqs)
     */
    private static function size(object $queue, string $method, string $name): ?int
    {
        if (! method_exists($queue, $method)) {
            return null;
        }

        $size = $queue->$method($name);

        return $size === null ? null : (int) $size;
    }
}
