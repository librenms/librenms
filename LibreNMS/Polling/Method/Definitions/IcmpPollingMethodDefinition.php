<?php

namespace LibreNMS\Polling\Method\Definitions;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Eventlog;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\Severity;
use LibreNMS\Polling\Method\Config\IcmpConfig;
use LibreNMS\Polling\Method\Probe\IcmpProbe;
use LibreNMS\Polling\Method\Probe\ProbeResult;

/**
 * @extends PollingMethodDefinition<IcmpConfig>
 */
class IcmpPollingMethodDefinition extends PollingMethodDefinition
{
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

    public function fallbackConfig(Device $device): IcmpConfig
    {
        $method = new DevicePollingMethod([
            'method_type' => PollingMethodType::Icmp,
            'enabled' => false,
            'affects_availability' => $this->defaultAffectsAvailability(),
        ]);
        $method->setRelation('device', $device);

        return IcmpConfig::fromPollingMethod($method);
    }

    public function icon(): string
    {
        return 'fa-exchange';
    }

    public function class(): string
    {
        return IcmpConfig::class;
    }

    public function probe(): IcmpProbe
    {
        return new IcmpProbe();
    }
}
