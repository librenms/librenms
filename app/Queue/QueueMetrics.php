<?php

namespace App\Queue;

use App\Facades\LibrenmsConfig;
use App\Facades\Rrd;
use App\Jobs\DiscoverDevice;
use App\Jobs\PollDevice;
use App\Models\Device;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\RRD\RrdPath;

/**
 * Job counts and runtimes, recorded by any queue worker (Horizon, queue:work) with any queue driver.
 * Totals are kept in the cache shared by all nodes until the node checkin or the datastore snapshot takes them.
 */
class QueueMetrics
{
    /** rrd folder, underscore so it cannot collide with a device hostname */
    public const HOSTNAME = '_queues';
    /** job class => poller_cluster_stats type */
    public const NODE_STATS = [PollDevice::class => 'poller', DiscoverDevice::class => 'discovery'];
    /** job:<name> and queue:<name> that have totals */
    private const NAMES_KEY = 'queue-metrics:names';
    /** stores that do not create missing counters get them with an expiry, they are created again once it passes */
    private const COUNTER_TTL = 604800;

    /** @var array<string, float> job uuid => start time, in a worker process */
    private array $starts = [];

    public function jobStarted(Job $job): void
    {
        $this->starts[(string) $job->uuid()] = microtime(true);
    }

    /**
     * @param  bool  $completed  false if the job threw an exception (failed or released to retry)
     */
    public function jobFinished(Job $job, bool $completed): void
    {
        $start = Arr::pull($this->starts, (string) $job->uuid());
        if ($start === null) {
            return;
        }

        $ms = (int) round((microtime(true) - $start) * 1000);
        $class = (string) ($job->payload()['data']['commandName'] ?? $job->resolveName());

        foreach (['job:' . class_basename($class), 'queue:' . $job->getQueue()] as $name) {
            $this->increment("$name:count", 1);
            $this->increment("$name:ms", $ms);
            $this->addName($name);
        }

        $type = self::NODE_STATS[$class] ?? null;
        $node_id = config('librenms.node_id');
        if ($type !== null && $node_id) {
            $this->increment("node:$node_id:$type:ms", $ms);
            if ($completed) {
                $this->increment("node:$node_id:$type:devices", 1);
            }
        }
    }

    /**
     * Worker totals of a node since the last report, reset for the next interval
     *
     * @return array<string, array{devices: int, seconds: float}> keyed by poller_cluster_stats type
     */
    public function pullNodeStats(string $node_id): array
    {
        $stats = [];

        foreach (array_unique(self::NODE_STATS) as $type) {
            $totals = $this->pull(["node:$node_id:$type:devices", "node:$node_id:$type:ms"]);
            $stats[$type] = [
                'devices' => $totals["node:$node_id:$type:devices"],
                'seconds' => $totals["node:$node_id:$type:ms"] / 1000.0,
            ];
        }

        return $stats;
    }

    /**
     * Write the totals since the last snapshot to the datastores.
     * Runtime is the average in milliseconds, null when nothing ran.
     */
    public function snapshot(): void
    {
        $names = (array) Cache::get(self::NAMES_KEY, []);
        if (empty($names)) {
            return;
        }

        if (LibrenmsConfig::get('rrd.enable', true)) {
            Rrd::checkDirExists(RrdPath::make(self::HOSTNAME));
        }

        $meta = [
            'device' => new Device(['hostname' => self::HOSTNAME]),
            'rrd_def' => RrdDefinition::make()
                ->addDataset('throughput', 'GAUGE', 0)
                ->addDataset('runtime', 'GAUGE', 0),
        ];

        foreach ($names as $key) {
            [$type, $name] = explode(':', (string) $key, 2) + [1 => ''];
            $totals = $this->pull(["$key:count", "$key:ms"]);
            $count = $totals["$key:count"];

            app('Datastore')->write('queue', [
                'throughput' => $count,
                'runtime' => $count ? $totals["$key:ms"] / (float) $count : null,
            ], ['type' => $type, 'name' => $name], [
                ...$meta,
                'rrd_name' => ['queue', $type, $name],
            ]);
        }
    }

    /**
     * Read counters and subtract what was read, this keeps anything workers add in the meantime
     *
     * @param  string[]  $keys
     * @return array<string, int>
     */
    private function pull(array $keys): array
    {
        $values = [];

        foreach ($keys as $key) {
            $values[$key] = (int) Cache::get(self::key($key));
            if ($values[$key] !== 0) {
                Cache::decrement(self::key($key), $values[$key]);
            }
        }

        return $values;
    }

    private function increment(string $key, int $value): void
    {
        if (Cache::increment(self::key($key), $value) === false) {
            Cache::add(self::key($key), 0, self::COUNTER_TTL);
            Cache::increment(self::key($key), $value);
        }
    }

    /**
     * Workers adding different names at once can drop one, it is added again when its next job finishes
     */
    private function addName(string $name): void
    {
        $names = (array) Cache::get(self::NAMES_KEY, []);

        if (! in_array($name, $names)) {
            Cache::forever(self::NAMES_KEY, [...$names, $name]);
        }
    }

    private static function key(string $key): string
    {
        return "queue-metrics:$key";
    }
}
