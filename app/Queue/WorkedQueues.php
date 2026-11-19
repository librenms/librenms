<?php

namespace App\Queue;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Support\Facades\Cache;

/**
 * Queues that have a running worker, recorded by every queue worker (Horizon, queue:work) in the cache.
 * The records expire on their own when no worker refreshes them.
 */
class WorkedQueues
{
    /** seconds between the records of each worker process */
    public const HEARTBEAT = 60;

    /** @var string[] queues this worker process works */
    private array $queues = [];
    private int $nextHeartbeat = 0;
    /** when the records of this process expire */
    private int $recordedUntil = 0;

    /**
     * The given queues that a worker works
     *
     * @param  string[]  $queues
     * @return string[]
     */
    public function worked(array $queues): array
    {
        if (empty($queues)) {
            return [];
        }

        $records = Cache::many(array_map(self::key(...), $queues));

        return array_values(array_filter($queues, fn (string $queue): bool => ! empty($records[self::key($queue)])));
    }

    /**
     * Runs on every loop of a worker process, between jobs and while it waits for jobs
     *
     * @param  string  $queues  comma separated queues the worker works
     */
    public function looping(string $queues): void
    {
        $now = time();
        if ($now < $this->nextHeartbeat) {
            return;
        }
        $this->nextHeartbeat = $now + self::HEARTBEAT;

        $this->queues = array_values(array_filter(explode(',', $queues)));
        $this->record(self::HEARTBEAT * 2);
    }

    /**
     * A worker does not loop while it runs a job, its queues stay worked until the job would time out
     */
    public function jobStarted(Job $job): void
    {
        $this->record((int) $job->timeout() + self::HEARTBEAT);
    }

    /**
     * Record this process's queues as worked for the given seconds, without shortening a longer record of another process
     */
    private function record(int $seconds): void
    {
        $seconds = max($seconds, self::HEARTBEAT * 2);
        $until = time() + $seconds;
        // refreshed when it is extended by a heartbeat or more, which is always the case before it expires
        if (empty($this->queues) || $until - $this->recordedUntil < self::HEARTBEAT) {
            return;
        }
        $this->recordedUntil = $until;

        // the value is when the record expires
        foreach (Cache::many(array_map(self::key(...), $this->queues)) as $key => $expires) {
            if ((int) $expires < $until) {
                Cache::put($key, $until, $seconds);
            }
        }
    }

    private static function key(string $queue): string
    {
        return "queue-worked:$queue";
    }
}
