<?php

namespace App\Actions\Device;

use App\Models\Device;
use App\Models\Eventlog;
use LibreNMS\Data\Source\Fping;
use LibreNMS\Data\Source\FpingResponse;
use LibreNMS\Enum\Severity;
use LibreNMS\Polling\ConnectivityHelper;

class DeviceIsPingable
{
    public function __construct(
        private Fping $fping,
    ) {}

    public function execute(Device $device): FpingResponse
    {
        if (! ConnectivityHelper::pingIsAllowed($device)) {
            return FpingResponse::artificialUp($device->pollerTarget());
        }

        $status = $this->fping->ping($device->pollerTarget(), $device->ipFamily());

        if ($status->duplicates > 0) {
            Eventlog::log('Duplicate ICMP response detected! This could indicate a network issue.', $device, 'icmp', Severity::Warning);
            $status->ignoreFailure(); // when duplicate is detected fping returns 1. The device is up, but there is another issue. Clue admins in with above event.
        }

        return $status;
    }
}
