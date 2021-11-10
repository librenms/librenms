<?php

namespace App\Actions\Device;

use App\Models\Device;
use LibreNMS\Enum\PollingMethodType;

class CheckDeviceAvailability
{
    public function __construct(
        private readonly SetDeviceAvailability $setDeviceAvailability,
        private readonly DeviceIcmpIsAvailable $deviceIcmpIsAvailable,
        private readonly DeviceSnmpIsAvailable $deviceSnmpIsAvailable,
        private readonly DeviceMtuTest $deviceMtuTest,
    ) {
    }

    public function execute(Device $device, bool $commit = false): bool
    {
        $icmpMethod = $device->getPollingMethod(PollingMethodType::Icmp);
        if ($icmpMethod?->enabled) {
            $ping_response = $this->deviceIcmpIsAvailable->execute($device);
            $icmpMethod->last_check_successful = $ping_response->isAlive();
            $icmpMethod->last_checked_at = now();

            if ($commit) {
                $ping_response->saveStats($device);
            }

            if ($icmpMethod->last_check_successful) {
                $device->mtu_status = $this->deviceMtuTest->execute($device);
            }
        }

        $snmpMethod = $device->getPollingMethod(PollingMethodType::Snmp);
        if ($snmpMethod?->enabled) {
            $icmp_success = ! $icmpMethod?->enabled || $icmpMethod->last_check_successful || ! $icmpMethod->affects_availability;
            if ($icmp_success) {
                $snmpMethod->last_check_successful = $this->deviceSnmpIsAvailable->execute($device);
                $snmpMethod->last_checked_at = now();
            } else {
                $snmpMethod->last_check_successful = false;
            }
        }

        $this->setDeviceAvailability->execute($device, $commit);

        if ($commit) {
            $icmpMethod?->save();
            $snmpMethod?->save();
            $device->save(); // confirm device is saved
        }

        return $device->status;
    }
}
