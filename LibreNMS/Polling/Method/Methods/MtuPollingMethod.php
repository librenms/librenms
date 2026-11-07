<?php

namespace LibreNMS\Polling\Method\Methods;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Data\Source\Icmp\Fping;
use LibreNMS\Data\Source\Icmp\Ping;
use LibreNMS\Enum\AddressFamily;
use LibreNMS\Polling\Method\Config\MtuConfig;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Definitions\MtuDefinition;
use LibreNMS\Polling\Method\ProbeResult;

/**
 * @extends PollingMethod<MtuConfig>
 */
final class MtuPollingMethod extends PollingMethod
{
    public function definition(): MtuDefinition
    {
        return new MtuDefinition;
    }

    public function defaultAffectsAvailability(): bool
    {
        return true;
    }

    /**
     * @return array{ip_version: string, packet_size: int}
     */
    public function defaults(?Device $device = null): array
    {
        return [
            'ip_version' => 'default',
            'packet_size' => LibrenmsConfig::get('mtu_options.bytes'),
            'fragmentation' => LibrenmsConfig::get('mtu_options.fragmentation'),
        ];
    }

    public function config(Device $device, ?DevicePollingMethod $deviceMethod = null): MtuConfig
    {
        $settings = ($deviceMethod->settings ?? []) + $this->defaults($device);

        return new MtuConfig(
            ipVersion: $settings['ip_version'],
            packetSize: $settings['packet_size'],
            fragmentation: $settings['fragmentation'],
        );
    }

    public function resolveAddressFamily(Device $device, ?MtuConfig $config = null): ?AddressFamily
    {
        $config ??= $device->polling()->mtu();

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
     */
    public function probe(Device $device, PollingMethodConfig $config): ProbeResult
    {
        assert($config instanceof MtuConfig);

        $tester = $config->fragmentation == 'allow' ? app(Ping::class) : app(Fping::class);
        $status = $tester->testMtu($device->pollerTarget(), $config->packetSize, $config->fragmentation, $this->resolveAddressFamily($device, $config));

        return new ProbeResult($status, [], $status ? null : (string) 'MTU Test failed');
    }
}
