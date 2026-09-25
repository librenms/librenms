<?php

namespace LibreNMS\Polling\Method\Methods;

use App\Actions\Device\DeviceMtuTest;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Eventlog;
use App\View\FieldSchema\FieldDefinition;
use LibreNMS\Data\Source\Icmp\Fping;
use LibreNMS\Enum\AddressFamily;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\Severity;
use LibreNMS\Exceptions\FpingUnparsableLine;
use LibreNMS\Polling\Method\Config\IcmpConfig;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
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

    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return [
            'ip_version' => FieldDefinition::make('ip_version', 'select')
                ->options([
                    'default' => 'Default',
                    'match_snmp_transport' => 'Match SNMP Transport',
                    'ipv4' => 'IPv4 Only',
                    'ipv6' => 'IPv6 Only',
                ])
                ->default('default')
                ->rules(['nullable', 'string', 'in:default,match_snmp_transport,follow_snmp,ipv4,ipv6']),
        ];
    }

    public function resolveAddressFamily(Device $device, ?IcmpConfig $config = null): ?AddressFamily
    {
        $config ??= $this->fallbackConfig($device);

        return match ($config->ipVersion) {
            'ipv4' => AddressFamily::IPv4,
            'ipv6' => AddressFamily::IPv6,
            'match_snmp_transport', 'follow_snmp' => $device->ipFamily(),
            default => null,
        };
    }

    /**
     * @param  Device  $device
     * @param  IcmpConfig  $config
     * @return ProbeResult
     *
     * @throws FpingUnparsableLine
     */
    public function probe(Device $device, PollingMethodConfig $config): ProbeResult
    {
        $fping = app(Fping::class);
        $icmpConfig = $config instanceof IcmpConfig ? $config : null;
        $status = $fping->ping($device->pollerTarget(), $this->resolveAddressFamily($device, $icmpConfig));
        $hasDuplicates = $status->duplicates > 0;

        if ($hasDuplicates) {
            $status->ignoreFailure();
        }

        $mtuStatus = null;
        if ($status->isAlive()) {
            $mtuStatus = app(DeviceMtuTest::class)->execute($device);
        }

        $error = (! $status->isAlive()) ? (string) $status : null;

        return new ProbeResult($status->isAlive(), [
            'duplicates' => $hasDuplicates,
            'fping_status' => $status,
            'mtu_status' => $mtuStatus,
            'error' => $error,
        ], $error);
    }

    public function config(DevicePollingMethod $deviceMethod): IcmpConfig
    {
        return new IcmpConfig(
            enabled: $deviceMethod->enabled ?? true,
            affectsAvailability: $deviceMethod->affects_availability ?? false,
            ipVersion: $deviceMethod->settings['ip_version'] ?? 'default',
        );
    }

    public function fallbackConfig(Device $device): IcmpConfig
    {
        $method = $device->pollingMethod(PollingMethodType::Icmp);
        if ($method) {
            return $this->config($method);
        }

        return new IcmpConfig(enabled: false, affectsAvailability: true, ipVersion: 'default');
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
