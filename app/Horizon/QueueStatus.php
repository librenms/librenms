<?php

namespace App\Horizon;

use App\Jobs\DispatchPollingWork;
use App\Models\PollerCluster;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Jobs\RedisJob;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Laravel\Horizon\Contracts\JobRepository;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;
use Laravel\Horizon\Contracts\SupervisorRepository;
use Laravel\Horizon\Contracts\TagRepository;
use Laravel\Horizon\Contracts\WorkloadRepository;
use Laravel\Horizon\Jobs\RetryFailedJob;
use Laravel\Horizon\Jobs\StopMonitoringTag;

/**
 * Read Horizon's queue and job data for display in LibreNMS pages
 */
class QueueStatus
{
    /** job id => retry, in the horizon connection */
    private const RETRYING_KEY = 'retrying';
    /** seconds a retry is listed after it was due, it is missed if its job was removed from the queue */
    private const RETRY_STALE = 3600;

    public function __construct(
        private readonly JobRepository $jobs,
        private readonly TagRepository $tags,
        private readonly WorkloadRepository $workload,
        private readonly MasterSupervisorRepository $masters,
        private readonly SupervisorRepository $supervisors,
    ) {
    }

    /**
     * Horizon only works with the redis queue driver
     */
    public static function available(): bool
    {
        return config('queue.default') == 'redis';
    }

    /**
     * Show the queue pages, queue workers are in use and their queues can be read
     */
    public static function showPages(): bool
    {
        return self::available() && WorkerNode::inUse();
    }

    public static function deviceTag(int $device_id): string
    {
        return "device:$device_id";
    }

    /**
     * Queues being worked, with job counts by state.
     * Delayed jobs are split into retries of down devices and work scheduled by DispatchPollingWork.
     *
     * @param  Collection<int, array{device_id: int|null, name: string, queue: string, attempt: int, tries: int|null, retry_at: Carbon}>|null  $retrying
     * @return Collection<int, array{name: string, pending: int, scheduled: int, retrying: int, reserved: int, processes: int, wait: int}>
     */
    public function queues(?Collection $retrying = null): Collection
    {
        $queue = Queue::connection('redis');
        $retryCounts = ($retrying ?? $this->retrying())->countBy('queue');

        return collect($this->workload->get())
            ->sortBy('name')
            ->map(function (array $workload) use ($queue, $retryCounts): array {
                $retries = (int) $retryCounts->get($workload['name'], 0);

                return [
                    'name' => $workload['name'],
                    'pending' => $queue->pendingSize($workload['name']),
                    'scheduled' => (int) $queue->delayedSize($workload['name']) - $retries,
                    'retrying' => $retries,
                    'reserved' => $queue->reservedSize($workload['name']),
                    'processes' => (int) $workload['processes'],
                    'wait' => (int) $workload['wait'],
                ];
            })
            ->values();
    }

    /**
     * Jobs waiting to be retried, these were released because the device was down
     *
     * @return Collection<int, array{device_id: int|null, name: string, queue: string, attempt: int, tries: int|null, retry_at: Carbon}>
     */
    public function retrying(): Collection
    {
        $redis = Redis::connection('horizon');
        /** @var Collection<string, array{device_id: int|null, name: string, queue: string, attempt: int, tries: int|null, retry_at: int}> $retries */
        $retries = collect((array) $redis->command('hgetall', [self::RETRYING_KEY]))
            ->map(fn ($raw): array => (array) json_decode((string) $raw, true));

        $stale = $retries->filter(fn (array $retry): bool => $retry['retry_at'] < time() - self::RETRY_STALE);
        if ($stale->isNotEmpty()) {
            $redis->command('hdel', [self::RETRYING_KEY, ...$stale->keys()->all()]);
        }

        return $retries->diffKeys($stale)
            ->map(fn (array $retry): array => ['retry_at' => Carbon::createFromTimestamp($retry['retry_at'])] + $retry)
            ->sortBy('retry_at')
            ->values();
    }

    /**
     * Record a job released to run again after an exception, the delayed queue is too large to search for them
     */
    public static function jobReleased(Job $job, int $backoff): void
    {
        if (! $job instanceof RedisJob) {
            return;
        }

        $payload = $job->payload();
        Redis::connection('horizon')->command('hset', [self::RETRYING_KEY, (string) $job->getJobId(), json_encode([
            'device_id' => self::deviceIdFromTags($payload['tags'] ?? []),
            'name' => class_basename((string) ($payload['displayName'] ?? '')),
            'queue' => $job->getQueue(),
            'attempt' => $job->attempts(),
            'tries' => $job->maxTries(),
            'retry_at' => time() + $backoff,
        ])]);
    }

    public static function jobStarted(Job $job): void
    {
        if ($job instanceof RedisJob) {
            Redis::connection('horizon')->command('hdel', [self::RETRYING_KEY, (string) $job->getJobId()]);
        }
    }

    /**
     * Queues worked by the running supervisors on all nodes
     *
     * @return string[]
     */
    public function workedQueues(): array
    {
        return collect($this->supervisors->all())
            ->flatMap(fn (\stdClass $supervisor): array => explode(',', (string) ($supervisor->options['queue'] ?? '')))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * When DispatchPollingWork last completed a run, null if it has not in the completed job retention (trim.completed).
     * It is silenced, so its runs are listed apart from the other completed jobs, newest first.
     */
    public function lastDispatched(): ?Carbon
    {
        $job = $this->jobs->getSilenced()->first(fn (object $job): bool => $job->name == DispatchPollingWork::class);

        return $job?->completed_at ? Carbon::createFromTimestamp((float) $job->completed_at) : null;
    }

    /**
     * Nodes running queue workers and their supervisors, named like the node on the pollers page.
     * A reload briefly runs two Horizons on a node, they are shown together.
     *
     * @return Collection<int, array{name: string, status: string, supervisors: Collection<int, array{name: string, status: string, processes: array<array-key, int>}>}>
     */
    public function workers(): Collection
    {
        $supervisors = collect($this->supervisors->all())->groupBy('master');
        $names = PollerCluster::pluck('poller_name', 'node_id')
            ->mapWithKeys(fn (?string $name, string $node_id): array => [WorkerNode::masterBasename($node_id) => $name ?: $node_id]);

        return collect($this->masters->all())
            ->groupBy(fn (\stdClass $master): string => Str::beforeLast((string) $master->name, '-'))
            ->map(fn (Collection $masters, string $node): array => [
                'name' => (string) ($names[$node] ?? $node),
                'status' => $masters->contains('status', 'running') ? 'running' : (string) $masters->first()->status,
                'supervisors' => $masters
                    ->flatMap(fn (\stdClass $master): Collection => $supervisors->get($master->name, collect()))
                    ->sortBy('name')
                    ->map(fn (\stdClass $supervisor): array => [
                        'name' => Str::after((string) $supervisor->name, ':'),
                        'status' => (string) $supervisor->status,
                        'processes' => array_map(intval(...), (array) $supervisor->processes),
                    ])->values(),
            ])
            ->sortBy('name')
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function failedJobs(?string $tag = null): Collection
    {
        $jobs = $tag === null
            ? $this->jobs->getFailed()
            : $this->jobs->getJobs($this->tags->paginate("failed:$tag", 0, 50));

        return $jobs->map($this->present(...))->values();
    }

    public function failedCount(): int
    {
        return (int) $this->jobs->countFailed();
    }

    /**
     * Jobs recorded for a monitored tag
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function monitoredJobs(string $tag, int $limit = 50): Collection
    {
        return $this->jobs->getJobs($this->tags->paginate($tag, 0, $limit))
            ->map($this->present(...))
            ->values();
    }

    public function isMonitoring(string $tag): bool
    {
        return in_array($tag, $this->tags->monitoring());
    }

    public function monitor(string $tag): void
    {
        $this->tags->monitor($tag);
    }

    /**
     * Stop monitoring and delete the recorded jobs
     */
    public function stopMonitoring(string $tag): void
    {
        Bus::dispatchSync(new StopMonitoringTag($tag));
    }

    public function retry(string $id): void
    {
        Bus::dispatchSync(new RetryFailedJob($id));
    }

    public function forget(string $id): void
    {
        $this->jobs->deleteFailed($id);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(object $job): array
    {
        $payload = json_decode((string) $job->payload, true) ?: [];
        $started = $job->reserved_at ? Carbon::createFromTimestamp((float) $job->reserved_at) : null;
        $finished = $job->failed_at ?: $job->completed_at;
        $finished = $finished ? Carbon::createFromTimestamp((float) $finished) : null;
        $exception = mb_convert_encoding((string) $job->exception, 'UTF-8');

        return [
            'id' => $job->id,
            'name' => class_basename((string) $job->name),
            'queue' => $job->queue,
            // released jobs go back to pending, a pending job that already ran is a down device being retried
            'status' => $job->status == 'pending' && ($payload['attempts'] ?? 0) > 0 ? 'retrying' : $job->status,
            'device_id' => self::deviceIdFromTags($payload['tags'] ?? []),
            'attempts' => $payload['attempts'] ?? null,
            'started' => $started,
            'finished' => $finished,
            'runtime' => $started && $finished ? $started->diffInSeconds($finished) : null,
            'error' => self::exceptionSummary($exception),
            'exception' => $exception,
            'retried' => ! empty($job->retried_by),
        ];
    }

    /**
     * @param  array<int, mixed>  $tags
     */
    private static function deviceIdFromTags(array $tags): ?int
    {
        $tag = Arr::first($tags, fn ($tag): bool => str_starts_with((string) $tag, 'device:'));

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
}
