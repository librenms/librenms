<?php

namespace App\Queue;

use App\Jobs\DiscoverDevice;
use App\Jobs\PollDevice;
use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Device jobs waiting to retry a device that was down.
 * They sit in the delayed queue, which is too large to search, so they are recorded as they are released.
 */
class RetryTracker
{
    /** only these jobs are released to retry, and only when their device is down */
    private const RETRY_JOBS = [PollDevice::class, DiscoverDevice::class];
    /** seconds a retry is listed after it was due, it is missed if its job was removed from the queue */
    private const STALE = 3600;

    /**
     * @return Collection<int, array{device_id: int, name: string, queue: string, attempt: int, tries: int|null, retry_at: Carbon}>
     */
    public function retrying(): Collection
    {
        // a retry is recorded per device and job, for devices that are down
        $keys = Device::where('status', 0)->pluck('device_id')
            ->crossJoin(array_map(class_basename(...), self::RETRY_JOBS))
            ->map(fn (array $key): string => self::key(...$key))
            ->all();

        /** @var array<string, array{device_id: int, name: string, queue: string, attempt: int, tries: int|null, retry_at: int}|null> $retries */
        $retries = empty($keys) ? [] : Cache::many($keys);

        return collect($retries)
            ->filter()
            ->map(fn (array $retry): array => ['retry_at' => Carbon::createFromTimestamp($retry['retry_at'])] + $retry)
            ->sortBy('retry_at')
            ->values();
    }

    public function jobReleased(Job $job, int $backoff): void
    {
        $payload = $job->payload();
        $device_id = JobPayload::deviceId($payload);
        if ($device_id === null) {
            return;
        }

        $name = JobPayload::name($payload);
        Cache::put(self::key($device_id, $name), [
            'device_id' => $device_id,
            'name' => $name,
            'queue' => $job->getQueue(),
            'attempt' => $job->attempts(),
            'tries' => $job->maxTries(),
            'retry_at' => now()->getTimestamp() + $backoff,
        ], $backoff + self::STALE);
    }

    public function jobStarted(Job $job): void
    {
        // only a job that ran before can be a retry
        $payload = $job->payload();
        $device_id = JobPayload::deviceId($payload);
        if ($job->attempts() > 1 && $device_id !== null) {
            Cache::forget(self::key($device_id, JobPayload::name($payload)));
        }
    }

    private static function key(int $device_id, string $name): string
    {
        return "queue-retrying:$device_id:$name";
    }
}
