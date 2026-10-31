<?php

namespace App\Horizon;

use App\Facades\LibrenmsConfig;
use App\Models\PollerCluster;
use App\Queue\WorkerNode;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Horizon\MasterSupervisor;
use LibreNMS\Util\Version;

/**
 * Horizon's supervisors come from the node's settings on the pollers page, and its master checks in the node there.
 * Horizon stops when the settings change and the service starts it again, fast termination lets
 * the old workers finish their running jobs while the new ones take work.
 *
 * @phpstan-type SupervisorOptions array{queue: string[], maxProcesses: int}
 * @phpstan-type NodeSupervisors array{'supervisor-poll': SupervisorOptions|null, 'supervisor-discovery': SupervisorOptions|null}
 */
class Supervisors
{
    /** supervisor => WorkerNode::queues() type */
    private const TYPES = ['supervisor-poll' => 'poller', 'supervisor-discovery' => 'discovery'];
    /** seconds between checkins and checks for changed settings */
    private const HEARTBEAT = 60;

    /** @var NodeSupervisors|null supervisor options used when horizon started */
    private ?array $started = null;
    private int $nextCheck = 0;
    private bool $reportedDetails = false;

    /**
     * Horizon only works with the redis queue driver
     */
    public static function supported(): bool
    {
        return config('queue.default') == 'redis';
    }

    /**
     * Horizon master names start with this, it matches the node's settings on the pollers page
     */
    public static function masterBasename(?string $node_id = null): string
    {
        return Str::slug($node_id ?? config('librenms.node_id') ?: gethostname());
    }

    /**
     * Supervisors for the poll and discovery queues of the node's poller groups, null if disabled
     *
     * @return NodeSupervisors
     */
    public static function forNode(PollerCluster $node): array
    {
        $queues = WorkerNode::queues($node);

        return array_map(fn (string $type): ?array => $queues[$type] === null ? null : [
            'queue' => $queues[$type]['queues'],
            'maxProcesses' => $queues[$type]['workers'],
        ], self::TYPES);
    }

    /**
     * Configure this node's supervisors, before the horizon command reads its config
     */
    public function start(?PollerCluster $node = null): void
    {
        if (! config('librenms.node_id')) {
            Log::warning('NODE_ID is not set in .env, this node will not show on the pollers page');
        }

        $this->started = self::forNode($node ?? WorkerNode::settings());

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
    public function looped(MasterSupervisor $master): void
    {
        $now = time();
        if ($this->started === null || $now < $this->nextCheck) {
            return;
        }
        $this->nextCheck = $now + self::HEARTBEAT; // set first, a failure is not retried every loop
        $node = WorkerNode::settings();

        // supervisors are only configured at start, the service starts Horizon again with the new settings
        if (self::forNode($node) !== $this->started) {
            Log::info('Poller settings for this node changed, stopping Horizon to apply them');
            $master->terminate();

            return;
        }

        $this->checkin($node);
    }

    private function checkin(PollerCluster $node): void
    {
        if (! config('librenms.node_id')) {
            return; // warned at start
        }

        if (! $node->exists) {
            $node->node_id = config('librenms.node_id');
            $node->poller_groups = (string) $node->getSettingValue('poller_groups');
        }
        $node->poller_name = LibrenmsConfig::get('distributed_poller_name');
        $node->last_report = now();
        $node->master = false; // DispatchPollingWork runs on any one node, there is no master
        // on the first checkin, Horizon starts again after an update
        if (! $this->reportedDetails) {
            $this->reportedDetails = true;
            $node->poller_details = [
                'librenms_version' => Version::get()->name(),
                'php_version' => PHP_VERSION,
                'os_version' => Version::get()->os(),
            ];
        }
        $node->save();
    }
}
