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
use LibreNMS\Polling\Method\Definitions\IcmpDefinition;
use LibreNMS\Polling\Method\ProbeResult;

/**
 * @extends PollingMethod<IcmpConfig>
 */
final class IcmpPollingMethod extends PollingMethod
{
    public function definition(): IcmpDefinition
    {
        return new IcmpDefinition;
    }

    public function defaultAffectsAvailability(): bool
    {
        return true;
    }

    /**
     * @return array{ip_version: string}
     */
    public function defaults(?Device $device = null): array
    {
        return [
            'ip_version' => 'default',
        ];
    }

    public function config(Device $device, ?DevicePollingMethod $deviceMethod = null): IcmpConfig
    {
        $settings = ($deviceMethod->settings ?? []) + $this->defaults($device);

        return new IcmpConfig(
            ipVersion: $settings['ip_version'],
        );
    }

    public function resolveAddressFamily(Device $device, ?IcmpConfig $config = null): ?AddressFamily
    {
        $config ??= $device->polling()->icmp();

        return match ($config->ipVersion) {
            'ipv4' => AddressFamily::IPv4,
            'ipv6' => AddressFamily::IPv6,
            'match_snmp_transport' => $this->snmpTransportFamily($device),
            default => null,
        };
    }

    private function snmpTransportFamily(Device $device): AddressFamily
    {
        $transport = (new SnmpPollingMethod)->transport($device);

        return str_ends_with($transport, '6') ? AddressFamily::IPv6 : AddressFamily::IPv4;
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

    public function onProbeComplete(Device $device, ProbeResult $result, bool $commit = false): void
    {
        if ($result->stat('duplicates') && $device->exists) {
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
