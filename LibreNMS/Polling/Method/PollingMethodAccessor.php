<?php

namespace LibreNMS\Polling\Method;

use App\Models\Device;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\Config\IcmpConfig;
use LibreNMS\Polling\Method\Config\IpmiConfig;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Polling\Method\Config\UnixAgentConfig;

readonly class PollingMethodAccessor
{
    public function __construct(
        private Device $device,
        private PollingMethodRegistry $registry,
    ) {
    }

    public function get(PollingMethodType $type): ?PollingMethodConfig
    {
        $definition = $this->registry->require($type);
        $method = $this->device->pollingMethod($type);

        if ($method) {
            return $definition->config($method);
        }

        return $definition->fallbackConfig($this->device);
    }

    public function snmp(): SnmpConfig
    {
        /** @var SnmpConfig */
        return $this->get(PollingMethodType::Snmp);
    }

    public function icmp(): IcmpConfig
    {
        /** @var IcmpConfig */
        return $this->get(PollingMethodType::Icmp);
    }

    public function ipmi(): ?IpmiConfig
    {
        /** @var ?IpmiConfig */
        return $this->get(PollingMethodType::Ipmi);
    }

    public function unixAgent(): ?UnixAgentConfig
    {
        /** @var ?UnixAgentConfig */
        return $this->get(PollingMethodType::UnixAgent);
    }
}
