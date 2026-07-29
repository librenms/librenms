<?php

namespace LibreNMS\Polling\Method\Probe;

use App\Actions\Device\DeviceMtuTest;
use App\Models\Device;
use LibreNMS\Data\Source\Icmp\Fping;
use LibreNMS\Interfaces\PollingMethodProbeInterface;

final readonly class IcmpProbe implements PollingMethodProbeInterface
{
    public function check(Device $device): ProbeResult
    {
        $fping = app(Fping::class);
        $status = $fping->ping($device->pollerTarget(), $device->ipFamily());
        $hasDuplicates = $status->duplicates > 0;

        if ($hasDuplicates) {
            $status->ignoreFailure();
        }

        $mtuStatus = null;
        if ($status->isAlive()) {
            $mtuStatus = app(DeviceMtuTest::class)->execute($device);
        }

        return new ProbeResult($status->isAlive(), [
            'duplicates' => $hasDuplicates,
            'fping_status' => $status,
            'mtu_status' => $mtuStatus,
        ]);
    }
}
