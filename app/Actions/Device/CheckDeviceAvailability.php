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
        private readonly DeviceMtuTest $deviceMtuTest,
    ) {
    }

    public function execute(Device $device, bool $commit = false): bool
    {
        $connectivity = new ConnectivityHelper($device);
        $ping_response = $this->deviceIsPingable->execute($device);

        $is_up_snmp = ! $connectivity->snmpIsEnabled() || $this->deviceIsSnmpable->execute($device);

        if ($ping_response->isAlive()) {
            $this->setDeviceAvailability->execute($device, $is_up_snmp, AvailabilitySource::Snmp, $commit);

            $device->mtu_status = $this->deviceMtuTest->execute($device);
        } else { // icmp down
            $reason = $is_up_snmp ? AvailabilitySource::Icmp : AvailabilitySource::Both;
            $this->setDeviceAvailability->execute($device, false, $reason, $commit);
        }

        if ($commit) {
            if ($connectivity->icmpIsEnabled()) {
                $ping_response->saveStats($device);
            }

            $device->save(); // confirm device is saved
        }

        return $device->status;
    }
}
