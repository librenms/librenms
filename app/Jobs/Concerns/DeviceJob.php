<?php

namespace App\Jobs\Concerns;

use App\Facades\LibrenmsConfig;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use LibreNMS\Util\Dns;

/**
 * A job for one device on its poller group's queue
 */
trait DeviceJob
{
    /**
     * Each poller group has its own queue, worked by the nodes in that group
     */
    abstract public static function queueFor(int $poller_group): string;

    /**
     * Unique per queue, a job waiting in the old poller group's queue does not hold up a moved device
     */
    public function uniqueId(): string
    {
        return "$this->device_id:$this->queue";
    }

    /**
     * @return bool false if the device no longer exists or is not in this queue's poller group
     */
    private function initDevice(): bool
    {
        Cache::driver('device')->flush();
        \DeviceCache::setPrimary($this->device_id);
        $this->device = \DeviceCache::getPrimary();

        if (! $this->device->exists) {
            $this->device = null;
            Log::info("Device $this->device_id no longer exists, skipping");

            return false;
        }

        // DispatchPollingWork queues it again for the new group, this job releases only the old group's lock
        if ($this->queue !== null && $this->queue != self::queueFor($this->device->poller_group)) {
            Log::info("Device $this->device_id moved to poller group {$this->device->poller_group}, skipping");
            $this->device = null;

            return false;
        }

        $this->device->ip = Dns::lookupIp($this->device) ?? $this->device->ip;

        $this->deviceArray = $this->device->toArray();
        if ($os_group = LibrenmsConfig::get("os.{$this->device->os}.group")) {
            $this->deviceArray['os_group'] = $os_group;
        }

        Log::info(sprintf(<<<'EOH'
Hostname:  %s %s
ID:        %s
OS:        %s
IP:        %s

EOH, $this->device->hostname, $os_group ? " ($os_group)" : '', $this->device->device_id, $this->device->os, $this->device->ip));

        return true;
    }
}
