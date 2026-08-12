<?php

namespace LibreNMS\Polling\Method;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\PollingMethodConfigInterface;
use LibreNMS\Polling\Method\Config\IcmpConfig;
use LibreNMS\Polling\Method\Config\IpmiConfig;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Polling\Method\Config\UnixAgentConfig;

readonly class PollingMethodAccessor
{
    public function __construct(
        private Device $device,
    ) {
    }

    /**
     * @template T of PollingMethodConfigInterface
     *
     * @param  class-string<T>  $class
     * @return T
     */
    private function get(PollingMethodType $type, string $class): PollingMethodConfigInterface
    {
        $method = $this->device->pollingMethod($type)
            ?? DevicePollingMethod::transient($type, device: $this->device, enabled: false);

        return $method->toConfig();
    }

    public function snmp(): SnmpConfig
    {
        return $this->get(PollingMethodType::Snmp, SnmpConfig::class);
    }

    public function icmp(): IcmpConfig
    {
        return $this->get(PollingMethodType::Icmp, IcmpConfig::class);
    }

    public function ipmi(): IpmiConfig
    {
        return $this->get(PollingMethodType::Ipmi, IpmiConfig::class);
    }

    public function unixAgent(): UnixAgentConfig
    {
        return $this->get(PollingMethodType::UnixAgent, UnixAgentConfig::class);
    }
}
