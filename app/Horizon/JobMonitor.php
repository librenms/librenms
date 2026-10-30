<?php

namespace App\Horizon;

use App\Queue\QueueStatus;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Laravel\Horizon\Contracts\JobRepository;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;
use Laravel\Horizon\Contracts\TagRepository;
use Laravel\Horizon\Jobs\StopMonitoringTag;

/**
 * Record every job of a device with Horizon's tag monitoring, for troubleshooting
 */
class JobMonitor
{
    public function __construct(
        private readonly JobRepository $jobs,
        private readonly TagRepository $tags,
        private readonly MasterSupervisorRepository $masters,
    ) {
    }

    /**
     * Horizon records the jobs it runs, other queue workers do not
     */
    public function available(): bool
    {
        return Supervisors::available() && ! empty($this->masters->names());
    }

    public static function deviceTag(int $device_id): string
    {
        return "device:$device_id";
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
            'device_id' => QueueStatus::deviceId($payload),
            'attempts' => $payload['attempts'] ?? null,
            'started' => $started,
            'finished' => $finished,
            'runtime' => $started && $finished ? $started->diffInSeconds($finished) : null,
            'error' => QueueStatus::exceptionSummary($exception),
            'exception' => $exception,
            'retried' => ! empty($job->retried_by),
        ];
    }
}
