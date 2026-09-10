<?php

namespace App\Actions\Device;

use App\Models\Device;
use LibreNMS\Enum\AvailabilitySource;
use LibreNMS\Polling\ConnectivityHelper;

class SetDeviceAvailability
{
    /**
     * Set status and status_reason fields based on availability results.
     * Does not persist to the database.
     *
     * @param  Device  $device
     * @param  array<string|\LibreNMS\Enum\AvailabilitySource, bool>  $results  e.g. ['icmp' => true, 'snmp' => false]
     * @return bool true if the status changed
     */
    public function execute(Device $device, array $results): bool
    {
        $connectivity = new ConnectivityHelper($device);

        // Determine which sources are currently enabled on this device
        $enabled = collect(['icmp', 'snmp'])->filter(fn ($source) => $connectivity->{"{$source}IsEnabled"}());

        // Normalize keys: AvailabilitySource enum values or plain strings
        $checked = collect($results)->mapWithKeys(function ($passed, $source) {
            $name = $source instanceof AvailabilitySource ? $source->value : (string) $source;

            return [$name => $passed];
        });

        // Previously failing sources that weren't checked this run are still considered failing
        $previously_failed = collect(explode(',', (string) $device->status_reason))->map(fn ($s) => trim($s))->filter();
        $unchecked_failures = $previously_failed->diff($checked->keys());

        // A source is failing if it failed in this run, or was previously failing and wasn't re-checked
        $failing = $checked->filter(fn ($passed) => ! $passed)->keys()->merge($unchecked_failures);

        // Only report failures for sources that are currently enabled
        $failed_sources = $failing->intersect($enabled)->sort()->values();

        $device->status = $failed_sources->isEmpty();
        $device->status_reason = $failed_sources->implode(',');

        return $device->isDirty('status');
    }
}
