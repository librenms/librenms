<?php

namespace LibreNMS\Polling\Method\Methods;

use App\Actions\Device\DeviceMtuTest;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Eventlog;
use LibreNMS\Data\Source\Icmp\Fping;
use LibreNMS\Enum\Severity;
use LibreNMS\Polling\Method\Config\IcmpConfig;
use LibreNMS\Polling\Method\ProbeResult;

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

    public function probe(Device $device): ProbeResult
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

    public function config(DevicePollingMethod $deviceMethod): IcmpConfig
    {
        return IcmpConfig::fromPollingMethod($deviceMethod);
    }

    public function fallbackConfig(Device $device): IcmpConfig
    {
        return new IcmpConfig(enabled: false, affectsAvailability: true);
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
