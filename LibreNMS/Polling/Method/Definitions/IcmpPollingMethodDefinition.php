<?php

namespace LibreNMS\Polling\Method\Definitions;

use App\Models\Device;
use App\Models\Eventlog;
use LibreNMS\Enum\Severity;
use LibreNMS\Polling\Method\Probe\ProbeResult;

final class IcmpPollingMethodDefinition extends PollingMethodDefinition
{
    public function icon(): string
    {
        return 'fa-exchange';
    }

    public function defaultAffectsAvailability(): bool
    {
        return true;
    }

    public function onProbeComplete(Device $device, ProbeResult $result, bool $commit = false): void
    {
        if ($result->stat('duplicates')) {
            Eventlog::log('Duplicate ICMP response detected! This could indicate a network issue.', $device, 'icmp', Severity::Warning);
        }

        $fpingStatus = $result->stat('fping_status');
        if ($commit && $fpingStatus) {
            $fpingStatus->saveStats($device);
        }

        $mtuStatus = $result->stat('mtu_status');
        if ($result->isSuccess() && $mtuStatus !== null) {
            $device->mtu_status = $mtuStatus;
        }
    }

    public function enrichDeviceMetadata(Device $device): void
    {
        if ($device->os === 'generic' || empty($device->os)) {
            $device->os = 'ping';
        }
    }
}
