<?php

namespace App\Actions\Device;

use App\Models\Device;
use LibreNMS\Polling\ConnectivityHelper;

readonly class CheckDeviceAvailability
{
    public function __construct(
        private SetDeviceAvailability $setDeviceAvailability,
        private DeviceIsPingable $deviceIsPingable,
        private DeviceIsSnmpable $deviceIsSnmpable,
        private DeviceMtuTest $deviceMtuTest,
    ) {
    }

    public function execute(Device $device, bool $commit = false): bool
    {
        $connectivity = new ConnectivityHelper($device);
        $ping_response = $this->deviceIsPingable->execute($device);

        $results = [];
        if ($connectivity->icmpIsEnabled()) {
            $results['icmp'] = $ping_response->isAlive();
        }
        if ($connectivity->snmpIsEnabled()) {
            $results['snmp'] = $this->deviceIsSnmpable->execute($device);
        }

        $this->setDeviceAvailability->execute($device, $results);

        if ($ping_response->isAlive()) {
            $device->mtu_status = $this->deviceMtuTest->execute($device);
        }

        if ($commit) {
            if ($connectivity->icmpIsEnabled()) {
                $ping_response->saveStats($device);
            }

            $device->save();
        }

        return $device->status;
    }
}
