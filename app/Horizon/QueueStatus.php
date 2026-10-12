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
     * Queues being worked, with job counts by state
     *
     * @return Collection<int, array{name: string, pending: int, delayed: int, reserved: int, processes: int, wait: int}>
     */
    public function queues(): Collection
    {
        $queue = Queue::connection('redis');

        return collect($this->workload->get())
            ->sortBy('name')
            ->map(fn (array $workload): array => [
                'name' => $workload['name'],
                'pending' => $queue->pendingSize($workload['name']),
                'delayed' => $queue->delayedSize($workload['name']),
                'reserved' => $queue->reservedSize($workload['name']),
                'processes' => (int) $workload['processes'],
                'wait' => (int) $workload['wait'],
            ])
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
        app('queue.failer')->forget($id);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(object $job): array
    {
        $payload = json_decode((string) $job->payload, true) ?: [];
        $tags = $payload['tags'] ?? [];
        $device_tag = Arr::first($tags, fn ($tag): bool => str_starts_with((string) $tag, 'device:'));
        $started = $job->reserved_at ? Carbon::createFromTimestamp((float) $job->reserved_at) : null;
        $finished = $job->failed_at ?: $job->completed_at;
        $finished = $finished ? Carbon::createFromTimestamp((float) $finished) : null;
        $exception = mb_convert_encoding((string) $job->exception, 'UTF-8');

        return [
            'id' => $job->id,
            'name' => $job->name,
            'queue' => $job->queue,
            'status' => $job->status,
            'device_id' => $device_tag ? (int) Str::after($device_tag, ':') : null,
            'attempts' => $payload['attempts'] ?? null,
            'started' => $started,
            'finished' => $finished,
            'runtime' => $started && $finished ? $started->diffInSeconds($finished) : null,
            'error' => Str::before($exception, "\n"),
            'exception' => $exception,
            'retried' => ! empty($job->retried_by),
        ];
    }
}
