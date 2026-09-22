<?php

namespace LibreNMS\Polling\Method\Methods;

use App\Models\Device;
use App\Models\Eventlog;
use LibreNMS\Enum\Severity;
use LibreNMS\Polling\Method\Probe\ProbeResult;

final class IcmpPollingMethod extends PollingMethod
{
    public function icon(): string
    {
        return 'fa-exchange';
    }

    public function defaultAffectsAvailability(): bool
    {
        return true;
    }

    public function probe(): \LibreNMS\Polling\Method\Probe\IcmpProbe
    {
        return resolve(\LibreNMS\Polling\Method\Probe\IcmpProbe::class);
    }

    public function config(\App\Models\DevicePollingMethod $method): \LibreNMS\Polling\Method\Config\IcmpConfig
    {
        return \LibreNMS\Polling\Method\Config\IcmpConfig::fromPollingMethod($method);
    }

    public function fallbackConfig(Device $device): \LibreNMS\Polling\Method\Config\IcmpConfig
    {
        return new \LibreNMS\Polling\Method\Config\IcmpConfig(enabled: false, affectsAvailability: true);
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
