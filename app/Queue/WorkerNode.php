<?php

namespace App\Queue;

use App\Facades\LibrenmsConfig;
use App\Jobs\DiscoverDevice;
use App\Jobs\DispatchPollingWork;
use App\Jobs\PollDevice;
use App\Models\PollerCluster;
use App\Models\PollerClusterStat;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use LibreNMS\Util\Version;

/**
 * This node's queue workers, run by Horizon or any other queue worker with any queue driver.
 * Each worker process records the queues it works, one of them checks in for the node
 * and reports to the poller cluster like the dispatcher service does.
 *
 * @phpstan-type NodeQueues array{queues: string[], workers: int}
 */
class WorkerNode
{
    /** poller_version of queue worker nodes, the dispatcher service uses librenms-service */
    public const VERSION = 'librenms-worker';
    /** schedule_type.poller and schedule_type.discovery value to run them on the queue workers */
    public const SCHEDULE_TYPE = 'worker';
    /** seconds between checkins and between the worked queue records of each worker process */
    public const HEARTBEAT = 60;
    private const DETAILS_REFRESH = 21600;

    /** @var string[] queues this worker process works */
    private array $queues = [];
    private int $nextHeartbeat = 0;
    /** when the worked queue records of this process expire */
    private int $workedUntil = 0;
    private bool $reportedDetails = false;

    public function __construct(
        private readonly QueueMetrics $metrics,
    ) {
    }

    /**
     * This node's settings, unsaved with only global settings if it has not checked in yet
     */
    public static function settings(): PollerCluster
    {
        $node = PollerCluster::firstWhere('node_id', config('librenms.node_id')) ?? new PollerCluster;
        $node->poller_version = self::VERSION;

        return $node;
    }

    /**
     * Polling or discovery, or the given task, is scheduled to run on the queue workers
     */
    public static function inUse(?string $task = null): bool
    {
        foreach ($task === null ? ['poller', 'discovery'] : [$task] as $type) {
            if (LibrenmsConfig::get("schedule_type.$type") == self::SCHEDULE_TYPE) {
                return true;
            }
        }

        return false;
    }

    /**
     * Poll and discovery queues of the node's poller groups and the workers for them, null if disabled
     *
     * @return array{poller: NodeQueues|null, discovery: NodeQueues|null}
     */
    public static function queues(PollerCluster $node): array
    {
        $groups = array_map(intval(...), explode(',', (string) $node->getSettingValue('poller_groups')));

        return [
            'poller' => $node->getSettingValue('poller_enabled') ? [
                'queues' => self::queueNames(PollDevice::queueFor(...), $groups),
                'workers' => (int) $node->getSettingValue('poller_workers'),
            ] : null,
            'discovery' => $node->getSettingValue('discovery_enabled') ? [
                'queues' => self::queueNames(DiscoverDevice::queueFor(...), $groups),
                'workers' => (int) $node->getSettingValue('discovery_workers'),
            ] : null,
        ];
    }

    /**
     * @param  callable(int): string  $queueFor
     * @param  int[]  $groups
     * @return string[]
     */
    public static function queueNames(callable $queueFor, array $groups): array
    {
        return array_values(array_unique(array_map($queueFor, $groups)));
    }

    /**
     * Cache key recording a queue is worked, on any node or the given node
     */
    public static function workedKey(string $queue, ?string $node_id = null): string
    {
        return $node_id === null ? "queue-worked:$queue" : "queue-worked:$queue:$node_id";
    }

    /**
     * Runs on every loop of a worker process, between jobs and while it waits for jobs
     *
     * @param  string  $queues  comma separated queues the worker process works
     */
    public function looping(string $queues): void
    {
        $now = time();
        if ($now < $this->nextHeartbeat) {
            return;
        }
        $this->nextHeartbeat = $now + self::HEARTBEAT; // set first, a failure is not retried every loop

        $this->queues = array_values(array_filter(explode(',', $queues)));
        $this->markWorked(self::HEARTBEAT * 2);

        // the first worker process to get here checks in for the node
        $node_id = config('librenms.node_id');
        if (! $node_id) {
            Log::warning('NODE_ID is not set in .env, this node will not show on the pollers page');
        } elseif (Cache::add("queue-worker-checkin:$node_id", true, self::HEARTBEAT - 1)) {
            $this->checkin(self::settings());
        }
    }

    /**
     * A worker process does not loop while it runs a job, its queues stay worked until the job would time out
     */
    public function jobStarted(Job $job): void
    {
        $this->markWorked((int) $job->timeout() + self::HEARTBEAT);
    }

    private function markWorked(int $seconds): void
    {
        $seconds = max($seconds, self::HEARTBEAT * 2);
        $until = time() + $seconds;
        // refreshed when it is extended by a heartbeat or more, which is always the case before it expires
        if ($until - $this->workedUntil < self::HEARTBEAT) {
            return;
        }
        $this->workedUntil = $until;

        $node_id = config('librenms.node_id');
        foreach ($this->queues as $queue) {
            Cache::put(self::workedKey($queue), true, $seconds);
            if ($node_id) {
                Cache::put(self::workedKey($queue, $node_id), true, $seconds);
            }
        }
    }

    private function checkin(PollerCluster $node): void
    {
        $node_id = (string) config('librenms.node_id');
        if (! $node->exists) {
            $node->node_id = $node_id;
            $node->poller_groups = (string) $node->getSettingValue('poller_groups');
        }
        $node->poller_name = LibrenmsConfig::get('distributed_poller_name');
        $node->last_report = now();
        $node->master = false; // DispatchPollingWork runs on any one node, there is no master

        // on the first checkin of each worker process, they start again after an update
        $refresh = Cache::add("queue-worker-details:$node_id", true, self::DETAILS_REFRESH);
        if ($refresh || ! $this->reportedDetails) {
            $this->reportedDetails = true;
            $node->poller_details = [
                'librenms_version' => Version::get()->name(),
                'php_version' => PHP_VERSION,
                'os_version' => Version::get()->os(),
            ];
        }
        $node->save();

        // stats are totals for the poll frequency like the dispatcher service, the first report covers part of it
        if (Cache::add("queue-worker-stats:$node_id", true, DispatchPollingWork::pollFrequency())) {
            $this->writeStats($node, $this->metrics->pullNodeStats($node_id));
        }
    }

    /**
     * @param  array<string, array{devices: int, seconds: float}>  $stats  worker totals since the last report
     */
    private function writeStats(PollerCluster $node, array $stats): void
    {
        $queue = Queue::connection();

        foreach (self::queues($node) as $type => $options) {
            if ($options === null) {
                $node->stats()->where('poller_type', $type)->delete();

                continue;
            }

            /** @var PollerClusterStat $stat */
            $stat = $node->stats()->firstWhere('poller_type', $type) ?? $node->stats()->make();
            $stat->forceFill([
                'poller_type' => $type,
                'depth' => method_exists($queue, 'pendingSize')
                    ? array_sum(array_map(fn (string $name): int => (int) $queue->pendingSize($name), $options['queues']))
                    : 0,
                'devices' => $stats[$type]['devices'] ?? 0,
                'worker_seconds' => round($stats[$type]['seconds'] ?? 0, 3),
                'workers' => $options['workers'],
                'frequency' => DispatchPollingWork::pollFrequency(),
            ])->save();
        }
    }
}
