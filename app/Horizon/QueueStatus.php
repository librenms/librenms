<?php

namespace App\Horizon;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
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

    public static function deviceTag(int $device_id): string
    {
        return "device:$device_id";
    }

    /**
     * Queues being worked, with job counts by state.
     * Delayed jobs are split into retries of down devices and work scheduled by the dispatcher.
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
        $queue = Queue::connection('redis');
        $redis = $queue->getConnection();

        return collect($this->workload->get())
            ->flatMap(function (array $workload) use ($queue, $redis): array {
                $delayed = $redis->zRangeByScore($queue->getQueue($workload['name']) . ':delayed', '-inf', '+inf', ['withscores' => true]);
                $retries = [];

                foreach ($delayed as $raw => $retryAt) {
                    $payload = json_decode((string) $raw, true) ?: [];
                    if (($payload['attempts'] ?? 0) < 1) {
                        continue; // scheduled by the dispatcher, not a retry
                    }

                    $retries[] = [
                        'device_id' => self::deviceIdFromTags($payload['tags'] ?? []),
                        'name' => (string) ($payload['displayName'] ?? ''),
                        'queue' => $workload['name'],
                        'attempt' => (int) $payload['attempts'],
                        'tries' => isset($payload['maxTries']) ? (int) $payload['maxTries'] : null,
                        'retry_at' => Carbon::createFromTimestamp((float) $retryAt),
                    ];
                }

                return $retries;
            })
            ->sortBy('retry_at')
            ->values();
    }

    /**
     * Running Horizon instances (one per node) and their supervisors
     *
     * @return Collection<int, array{name: string, status: string, supervisors: Collection<int, array{name: string, status: string, processes: array<array-key, int>}>}>
     */
    public function workers(): Collection
    {
        $supervisors = collect($this->supervisors->all())->groupBy('master')->map->all();

        return collect($this->masters->all())
            ->sortBy('name')
            ->map(fn (\stdClass $master): array => [
                'name' => (string) $master->name,
                'status' => (string) $master->status,
                'supervisors' => collect($supervisors[$master->name] ?? [])
                    ->sortBy('name')
                    ->map(fn (\stdClass $supervisor): array => [
                        'name' => Str::after((string) $supervisor->name, ':'),
                        'status' => (string) $supervisor->status,
                        'processes' => array_map(intval(...), (array) $supervisor->processes),
                    ])->values(),
            ])
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
            'name' => $job->name,
            'queue' => $job->queue,
            // released jobs go back to pending, a pending job that already ran is a down device being retried
            'status' => $job->status == 'pending' && ($payload['attempts'] ?? 0) > 0 ? 'retrying' : $job->status,
            'device_id' => self::deviceIdFromTags($payload['tags'] ?? []),
            'attempts' => $payload['attempts'] ?? null,
            'started' => $started,
            'finished' => $finished,
            'runtime' => $started && $finished ? $started->diffInSeconds($finished) : null,
            'error' => Str::before($exception, "\n"),
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
}
