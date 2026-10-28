<?php

namespace App\Horizon;

use App\Facades\LibrenmsConfig;
use App\Jobs\DiscoverDevice;
use App\Jobs\DispatchPollingWork;
use App\Jobs\PollDevice;
use App\Models\PollerCluster;
use App\Models\PollerClusterStat;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use LibreNMS\Util\Version;

/**
 * This node's queue workers: the worker command runs Horizon, Horizon's supervisors come from
 * the node's settings on the pollers page and it reports to the poller cluster like the dispatcher service does.
 * On reload the worker command starts a new Horizon before the old one finishes its running jobs.
 *
 * @phpstan-type SupervisorOptions array{queue: string[], maxProcesses: int}
 * @phpstan-type Supervisors array{'supervisor-poll': SupervisorOptions|null, 'supervisor-discovery': SupervisorOptions|null}
 */
class WorkerNode
{
    /** poller_version of queue worker nodes, the dispatcher service uses librenms-service */
    public const VERSION = 'librenms-worker';
    /** set for Horizon started by the worker command, the pid of the worker command */
    public const PARENT_ENV = 'LIBRENMS_WORKER_PID';
    /** schedule_type.poller and schedule_type.discovery value to run them on the queue workers */
    public const SCHEDULE_TYPE = 'worker';
    /** seconds between checkins and checks for changed settings */
    public const HEARTBEAT = 60;
    /** seconds before asking for a reload again if the last one did not replace this Horizon */
    public const RELOAD_RETRY = 600;
    private const DETAILS_REFRESH = 21600;
    /** poller_cluster_stats type => supervisor */
    private const STATS = ['poller' => 'supervisor-poll', 'discovery' => 'supervisor-discovery'];
    /** job class => poller_cluster_stats type */
    private const JOBS = [PollDevice::class => 'poller', DiscoverDevice::class => 'discovery'];

    /** @var Supervisors|null supervisor options used when horizon started */
    private ?array $started = null;
    private int $nextReload = 0;
    private int $nextHeartbeat = 0;
    private int $nextStats = 0;
    private int $nextDetails = 0;
    /** @var array<string, float> job id => start time, in a worker process */
    private array $jobStarts = [];

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
     * Horizon master names start with this, it matches the node's settings when more than one Horizon runs during a reload
     */
    public static function masterBasename(?string $node_id = null): string
    {
        return Str::slug($node_id ?? config('librenms.node_id') ?: gethostname());
    }

    public static function pidFile(): string
    {
        return storage_path('framework/worker.pid');
    }

    /**
     * The worker command running on this host, it holds a lock on the pid file while it runs
     */
    public static function runningPid(): ?int
    {
        $file = self::pidFile();
        $handle = is_file($file) ? fopen($file, 'r') : false;
        if ($handle === false) {
            return null;
        }

        try {
            if (flock($handle, LOCK_SH | LOCK_NB)) {
                return null; // left by a worker command that exited
            }

            $pid = (int) stream_get_contents($handle);

            return $pid > 1 ? $pid : null;
        } finally {
            fclose($handle);
        }
    }

    /**
     * Ask the worker command that started this Horizon to replace it
     *
     * @return bool false if this Horizon was not started by the worker command
     */
    public static function requestReload(): bool
    {
        $parent = (int) getenv(self::PARENT_ENV);

        return $parent > 1 && $parent === posix_getppid() && posix_kill($parent, SIGHUP);
    }

    /**
     * Supervisors for the poll and discovery queues of the node's poller groups, null if disabled
     *
     * @return Supervisors
     */
    public static function supervisors(PollerCluster $node): array
    {
        $groups = array_map(intval(...), explode(',', (string) $node->getSettingValue('poller_groups')));

        return [
            'supervisor-poll' => $node->getSettingValue('poller_enabled') ? [
                'queue' => self::queueNames(PollDevice::queueFor(...), $groups),
                'maxProcesses' => (int) $node->getSettingValue('poller_workers'),
            ] : null,
            'supervisor-discovery' => $node->getSettingValue('discovery_enabled') ? [
                'queue' => self::queueNames(DiscoverDevice::queueFor(...), $groups),
                'maxProcesses' => (int) $node->getSettingValue('discovery_workers'),
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
     * Configure this node's supervisors, before the horizon command reads its config
     */
    public function start(?PollerCluster $node = null): void
    {
        $this->started = self::supervisors($node ?? self::settings());

        foreach ($this->started as $name => $options) {
            if ($options === null) {
                // every environment starts all supervisors in the defaults
                config([
                    'horizon.defaults' => Arr::except(config('horizon.defaults'), $name),
                    'horizon.environments' => array_map(fn (array $supervisors): array => Arr::except($supervisors, $name), config('horizon.environments')),
                ]);

                continue;
            }

            foreach ($options as $option => $value) {
                config(["horizon.defaults.$name.$option" => $value]);
            }
        }
    }

    /**
     * Runs on every loop of the master supervisor (about every second)
     */
    public function looped(): void
    {
        $now = time();
        if ($this->started === null || $now < $this->nextHeartbeat) {
            return;
        }
        $this->nextHeartbeat = $now + self::HEARTBEAT; // set first, a failure is not retried every loop

        LibrenmsConfig::reload(); // global settings may have changed
        $node = self::settings();

        // supervisors are only configured at start, the worker command starts a new Horizon and this one finishes its running jobs
        if ($now >= $this->nextReload && self::supervisors($node) !== $this->started) {
            $retry = $this->nextReload > 0;
            $this->nextReload = $now + self::RELOAD_RETRY;
            $reloading = self::requestReload();
            if ($reloading && $retry) {
                Log::warning('Poller settings for this node are still not applied, reloading the queue workers again');
            } elseif ($reloading) {
                Log::info('Poller settings for this node changed, reloading the queue workers');
            } elseif (! $retry) {
                Log::warning('Poller settings for this node changed, Horizon was not started by the worker command, restart it to apply them');
            }
        }

        $this->checkin($node);
    }

    /**
     * Record a poll or discovery job starting in a worker on this node
     */
    public function jobStarted(Job $job): void
    {
        if (self::jobType($job)) {
            $this->jobStarts[(string) $job->getJobId()] = microtime(true);
        }
    }

    /**
     * Count worker time and finished devices for this node's stats
     */
    public function jobFinished(Job $job, bool $finished): void
    {
        $type = self::jobType($job);
        $start = Arr::pull($this->jobStarts, (string) $job->getJobId());
        if ($type === null || $start === null || ! QueueStatus::available() || ! config('librenms.node_id')) {
            return;
        }

        $redis = Redis::connection('horizon');
        $redis->command('hincrbyfloat', [self::statsKey(), "$type:seconds", microtime(true) - $start]);
        if ($finished) {
            $redis->command('hincrby', [self::statsKey(), "$type:devices", 1]);
        }
    }

    private function checkin(PollerCluster $node): void
    {
        if (! config('librenms.node_id')) {
            Log::warning('NODE_ID is not set in .env, this node will not show on the pollers page');

            return;
        }

        $now = time();
        if (! $node->exists) {
            $node->node_id = config('librenms.node_id');
            $node->poller_groups = (string) $node->getSettingValue('poller_groups');
        }
        $node->poller_name = LibrenmsConfig::get('distributed_poller_name');
        $node->last_report = now();
        $node->master = false; // DispatchPollingWork runs on any one node, there is no master
        if ($now >= $this->nextDetails) {
            $this->nextDetails = $now + self::DETAILS_REFRESH;
            $node->poller_details = [
                'librenms_version' => Version::get()->name(),
                'php_version' => PHP_VERSION,
                'os_version' => Version::get()->os(),
            ];
        }
        $node->save();

        if ($now >= $this->nextStats) {
            // stats are totals for the poll frequency like the dispatcher service, the first report covers part of it
            $this->nextStats = $now + DispatchPollingWork::pollFrequency();
            $this->writeStats($node, $this->pullStats());
        }
    }

    /**
     * @param  array<string, string>  $stats  worker totals since the last report
     */
    private function writeStats(PollerCluster $node, array $stats): void
    {
        $queue = Queue::connection('redis');

        foreach (self::STATS as $type => $supervisor) {
            $options = $this->started[$supervisor] ?? null;
            if ($options === null) {
                $node->stats()->where('poller_type', $type)->delete();

                continue;
            }

            /** @var PollerClusterStat $stat */
            $stat = $node->stats()->firstWhere('poller_type', $type) ?? $node->stats()->make();
            $stat->forceFill([
                'poller_type' => $type,
                'depth' => array_sum(array_map(fn (string $name): int => (int) $queue->pendingSize($name), $options['queue'])),
                'devices' => (int) ($stats["$type:devices"] ?? 0),
                'worker_seconds' => round((float) ($stats["$type:seconds"] ?? 0), 3),
                'workers' => $options['maxProcesses'],
                'frequency' => DispatchPollingWork::pollFrequency(),
            ])->save();
        }
    }

    /**
     * Worker totals since the last report, reset for the next interval.
     * Subtracting what was read keeps anything workers add in the meantime.
     *
     * @return array<string, string>
     */
    private function pullStats(): array
    {
        $redis = Redis::connection('horizon');
        $stats = (array) $redis->command('hgetall', [self::statsKey()]);

        foreach ($stats as $field => $value) {
            str_ends_with((string) $field, ':devices')
                ? $redis->command('hincrby', [self::statsKey(), $field, -(int) $value])
                : $redis->command('hincrbyfloat', [self::statsKey(), $field, -(float) $value]);
        }

        return $stats;
    }

    private static function statsKey(): string
    {
        return 'node-stats:' . config('librenms.node_id');
    }

    private static function jobType(Job $job): ?string
    {
        return self::JOBS[$job->payload()['data']['commandName'] ?? ''] ?? null;
    }
}
