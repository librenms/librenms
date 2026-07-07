<?php

namespace App\Actions\Device;

use App\Models\Device;
use LibreNMS\Enum\AvailabilitySource;

class SetDeviceAvailability
{
    public function __construct(
    ) {
    }

    /**
     * Set status and status_reason fields based on availability results
     * Does not persist to the database
     *
     * @param  Device  $device
     * @param  array<string|\LibreNMS\Enum\AvailabilitySource, bool>  $results  e.g. ['icmp' => true, 'snmp' => false]
     * @return bool true if the status changed
     */
    public function execute(Device $device, array $results): bool
    {
        // Parse current failed sources from status_reason
        $current_failed = collect(explode(',', (string) $device->status_reason))
            ->map(fn($s) => trim($s))
            ->filter()
            ->all();

        $failed_in_run = [];
        $checked = [];
        foreach ($results as $source => $status) {
            $sourceName = $source instanceof AvailabilitySource ? $source->value : (string) $source;
            $checked[] = $sourceName;
            if (! $status) {
                $failed_in_run[] = $sourceName;
            }
        }

        // Failed sources are:
        // 1. Those that failed in this run
        // 2. Those that were previously failed and were not checked in this run
        $not_checked = array_diff($current_failed, $checked);
        $all_failed = array_unique(array_merge($failed_in_run, $not_checked));

        // Filter failed sources to only those that are currently enabled
        $connectivity = new \LibreNMS\Polling\ConnectivityHelper($device);
        $enabled_sources = [];
        if ($connectivity->icmpIsEnabled()) {
            $enabled_sources[] = 'icmp';
        }
        if ($connectivity->snmpIsEnabled()) {
            $enabled_sources[] = 'snmp';
        }
        // In the future, additional availability sources can be appended to $enabled_sources here.
        $all_failed = array_intersect($all_failed, $enabled_sources);

        // Sort failed sources for consistency in status_reason
        sort($all_failed);

        $available = empty($all_failed);

        $device->status = $available;
        $device->status_reason = implode(',', $all_failed);

        return $device->isDirty('status');
    }
}
