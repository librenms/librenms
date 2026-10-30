<?php

namespace App\Queue;

use App\Jobs\PollDevice;
use App\Models\Device;
use Illuminate\Bus\UniqueLock;
use Illuminate\Contracts\Cache\Repository;
use LibreNMS\Util\ModuleList;

/**
 * The unique lock a device's poll job holds from when it is queued until it is done.
 * DispatchPollingWork does not queue the device while it is held.
 */
class DeviceLocks
{
    public function __construct(
        private readonly Repository $cache,
    ) {
    }

    /**
     * A poll job holds the lock: it is queued, running, waiting to retry, or it was lost and the lock was left behind
     */
    public function held(Device $device): bool
    {
        $job = $this->pollJob($device);
        $lock = new UniqueLock($this->cache);

        // taking it is the only way to check, give it back right away
        if ($lock->acquire($job)) {
            $lock->release($job);

            return false;
        }

        return true;
    }

    /**
     * Let DispatchPollingWork queue the device again
     */
    public function release(Device $device): void
    {
        (new UniqueLock($this->cache))->release($this->pollJob($device));
    }

    private function pollJob(Device $device): PollDevice
    {
        return (new PollDevice($device->device_id, ModuleList::fromUserOverrides([])))
            ->onQueue(PollDevice::queueFor((int) $device->poller_group));
    }
}
