<?php

namespace App\Queue;

use App\Models\FailedJob;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

/**
 * Jobs that crashed, timed out or ran out of memory, recorded by the queue failer in failed_jobs.
 * Horizon keeps its own list too, retrying or deleting a job in one does not change the other.
 *
 * @phpstan-type FailedJobRow array{id: string, name: string, queue: string, status: 'failed', device_id: int|null, attempts: int|null, finished: Carbon|null, error: string, exception: string}
 */
class FailedJobs
{
    /**
     * Newest first
     *
     * @param  int|null  $device_id  only jobs for this device
     * @param  class-string|null  $job  only jobs of this class
     * @return Collection<int, FailedJobRow>
     */
    public function list(?int $device_id = null, ?string $job = null, int $limit = 50): Collection
    {
        return FailedJob::query()
            ->when($device_id !== null, fn ($query) => $query->where('payload->device_id', $device_id))
            ->when($job !== null, fn ($query) => $query->where('payload->displayName', $job))
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map($this->present(...));
    }

    public function count(): int
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
     * @return FailedJobRow
     */
    private function present(FailedJob $job): array
    {
        $payload = (array) $job->payload;
        $exception = mb_convert_encoding((string) $job->exception, 'UTF-8');

        return [
            'id' => (string) $job->uuid,
            'name' => JobPayload::name($payload),
            'queue' => (string) $job->queue,
            'status' => 'failed',
            'device_id' => JobPayload::deviceId($payload),
            'attempts' => isset($payload['attempts']) ? (int) $payload['attempts'] : null,
            'finished' => $job->failed_at,
            'error' => self::exceptionSummary($exception),
            'exception' => $exception,
        ];
    }
}
