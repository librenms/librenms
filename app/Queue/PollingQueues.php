<?php

namespace App\Queue;

use App\Jobs\DiscoverDevice;
use App\Jobs\PollDevice;

/**
 * Names of the queues polling work runs on
 */
class PollingQueues
{
    /** DispatchPollingWork runs here, kept separate so other work cannot delay dispatching */
    public const SCHEDULER = 'scheduler';
    /** maintenance and other jobs */
    public const DEFAULT = 'default';

    /**
     * Poll and/or discovery queues of poller groups, each group has its own queues worked by the nodes in that group
     *
     * @param  int[]  $groups
     * @return string[]
     */
    public static function forGroups(array $groups, bool $poll, bool $discover): array
    {
        $queues = [];

        foreach (array_unique($groups) as $group) {
            if ($poll) {
                $queues[] = PollDevice::queueFor($group);
            }
            if ($discover) {
                $queues[] = DiscoverDevice::queueFor($group);
            }
        }

        return $queues;
    }
}
