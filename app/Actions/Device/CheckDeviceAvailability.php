<?php

namespace App\Actions\Device;

use App\Models\Device;
use LibreNMS\Enum\AvailabilitySource;
use LibreNMS\Polling\ConnectivityHelper;

class CheckDeviceAvailability
{
    public function __construct(
        private readonly SetDeviceAvailability $setDeviceAvailability,
        private readonly DeviceIsPingable $deviceIsPingable,
        private readonly DeviceIsSnmpable $deviceIsSnmpable,
    ) {
    }

    public function execute(Device $device, bool $commit = false): bool
    {
        $ping_response = $this->deviceIsPingable->execute($device);

        if ($ping_response->success()) {
            $is_up_snmp = ! ConnectivityHelper::snmpIsAllowed($device) || $this->deviceIsSnmpable->execute($device);
            $this->setDeviceAvailability->execute($device, $is_up_snmp, AvailabilitySource::SNMP, $commit);
        } else { // icmp down
            $this->setDeviceAvailability->execute($device, false, AvailabilitySource::ICMP, $commit);
        }

        if ($commit) {
            if (ConnectivityHelper::pingIsAllowed($device)) {
                $ping_response->saveStats($device);
            }

            $device->save(); // confirm device is saved
        }

        return $device->status;
    }
}
