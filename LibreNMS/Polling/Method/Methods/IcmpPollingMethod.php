<?php

namespace LibreNMS\Polling\Method\Methods;

use App\Actions\Device\DeviceMtuTest;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Eventlog;
use LibreNMS\Data\Source\Icmp\Fping;
use LibreNMS\Enum\AddressFamily;
use LibreNMS\Enum\Severity;
use LibreNMS\Exceptions\FpingUnparsableLine;
use LibreNMS\Polling\Method\Config\IcmpConfig;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\ProbeResult;

final class IcmpPollingMethod extends PollingMethod
{
    public function defaultConfig(): IcmpConfig
    {
        return IcmpConfig::default();
    }

    public function resolveAddressFamily(Device $device, ?IcmpConfig $config = null): ?AddressFamily
    {
        $config ??= $device->polling()->icmp();

        return match ($config->ipVersion) {
            'ipv4' => AddressFamily::IPv4,
            'ipv6' => AddressFamily::IPv6,
            'match_snmp_transport' => $device->ipFamily(),
            default => null,
        };
    }

    /**
     * @throws FpingUnparsableLine
     */
    public function probe(Device $device, PollingMethodConfig $config): ProbeResult
    {
        assert($config instanceof IcmpConfig);

        $status = app(Fping::class)->ping($device->pollerTarget(), $this->resolveAddressFamily($device, $config));
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
        ], $status->isAlive() ? null : (string) $status);
    }

    protected function configFromSettings(DevicePollingMethod $deviceMethod): IcmpConfig
    {
        return IcmpConfig::fromSettings($deviceMethod->settings ?? []);
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
}
