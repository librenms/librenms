<?php

namespace App\Actions\Device;

use App\Models\Device;
use LibreNMS\Enum\AvailabilitySource;

class SetDeviceAvailability
{
    public function __construct(
        private UpdateDeviceOutage $updateDeviceOutage,
    ) {
    }

    /**
     * @param  Device  $device
     * @param  bool  $available
     * @param  AvailabilitySource  $source
     * @param  bool  $commit  Save changes to the database
     * @return bool true if the status changed
     */
    public function execute(Device $device, bool $available, AvailabilitySource $source = AvailabilitySource::NONE, bool $commit = true): bool
    {
        // if device was down and is now up, if reason was snmp and source is icmp, ignore
        if ($available && ! $device->status && $device->status_reason == AvailabilitySource::SNMP->value) {
            if ($source == AvailabilitySource::ICMP) {
                return false;
            }
        }

        $device->status = $available;
        $device->status_reason = $available ? AvailabilitySource::NONE->value : $source->value;
        $changed = $device->isDirty('status');

        if ($commit) {
            $device->save();
            $this->updateDeviceOutage->execute($device, $available);
        }

        return $changed;
    }
}
