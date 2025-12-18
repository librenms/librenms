<?php

namespace App\Actions\Device;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use LibreNMS\Data\Source\Fping;
use LibreNMS\Enum\AvailabilitySource;
use LibreNMS\Polling\ConnectivityHelper;

class CheckDeviceAvailability
{
    public function __construct(
        private readonly SetDeviceAvailability $setDeviceAvailability,
        private readonly DeviceIsPingable $deviceIsPingable,
        private readonly DeviceIsSnmpable $deviceIsSnmpable,
        private readonly DeviceMtuTest $deviceMtuTest,
        private readonly Fping $fping,
    ) {
    }

    public function execute(Device $device, bool $commit = false): bool
    {
        $ping_response = null;

        if (ConnectivityHelper::pingIsAllowed($device)) {
            // I think this does not cover installs where a separate ping process is not running.  I'm not sure how to test for this if we need to
            // ConnectivityHelper::pingIsAllowed() already makes sure icmp_check is enabled
            if (LibrenmsConfig::get('service_poller_frequency') == LibrenmsConfig::get('ping_rrd_step')) {
                // Poller frequency matches ping frequency - fetch ping stats here
                $ping_response = $this->deviceIsPingable->execute($device);
                $ping_success = $ping_response->success();

                // Do not keep stats if a device has pings disabled
                if (! ConnectivityHelper::pingIsAllowed($device)) {
                    $ping_response = null;
                }
            } else {
                // Pings are being done more frequently with an external process - just check for any successful ping
                $ping_success = $this->fping->alive($device->pollerTarget(), $device->ipFamily());
            }
        } else {
            // We are not allowed to ping, so assume success
            $ping_success = true;
        }

        if ($ping_success) {
            $is_up_snmp = ! ConnectivityHelper::snmpIsAllowed($device) || $this->deviceIsSnmpable->execute($device);
            $this->setDeviceAvailability->execute($device, $is_up_snmp, AvailabilitySource::SNMP, $commit);

            $device->mtu_status = $this->deviceMtuTest->execute($device);
        } else { // icmp down
            $this->setDeviceAvailability->execute($device, false, AvailabilitySource::ICMP, $commit);
        }

        if ($commit) {
            if ($ping_response) {
                $ping_response->saveStats($device);
            }

            $device->save(); // confirm device is saved
        }

        return $device->status;
    }
}
