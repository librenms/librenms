<?php

namespace LibreNMS\Polling\Method\Methods;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Data\Source\Icmp\Fping;
use LibreNMS\Enum\AddressFamily;
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
     * The ping response is in the fping_status stat, the core module stores it.
     *
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

        return new ProbeResult($status->isAlive(), [
            'duplicates' => $hasDuplicates,
            'fping_status' => $status,
        ], $status->isAlive() ? null : (string) $status);
    }
}
