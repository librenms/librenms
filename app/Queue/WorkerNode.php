<?php

namespace App\Queue;

use App\Facades\LibrenmsConfig;
use App\Models\PollerCluster;

/**
 * Queue worker nodes and their settings on the pollers page.
 * The settings (poller groups, workers) configure Horizon's supervisors, other queue workers work the queues they are started with.
 *
 * @phpstan-type NodeQueues array{queues: string[], workers: int}
 */
class WorkerNode
{
    /** poller_version of queue worker nodes, the dispatcher service uses librenms-service */
    public const VERSION = 'librenms-worker';
    /** schedule_type.poller and schedule_type.discovery value to run them on the queue workers */
    public const SCHEDULE_TYPE = 'worker';

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
     * Poll and discovery queues of the node's poller groups and the workers configured for them, null if disabled
     *
     * @return array{poller: NodeQueues|null, discovery: NodeQueues|null}
     */
    public static function queues(PollerCluster $node): array
    {
        $groups = array_map(intval(...), explode(',', (string) $node->getSettingValue('poller_groups')));

        return [
            'poller' => $node->getSettingValue('poller_enabled') ? [
                'queues' => PollingQueues::forGroups($groups, poll: true, discover: false),
                'workers' => (int) $node->getSettingValue('poller_workers'),
            ] : null,
            'discovery' => $node->getSettingValue('discovery_enabled') ? [
                'queues' => PollingQueues::forGroups($groups, poll: false, discover: true),
                'workers' => (int) $node->getSettingValue('discovery_workers'),
            ] : null,
        ];
    }
}
