<?php

namespace LibreNMS\Polling\Method;

use App\Models\Device;
use Illuminate\Support\Str;
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

    /**
     * Get the resolved configuration for a polling method on this device.
     */
    public function get(PollingMethodType $type): ?PollingMethodConfig
    {
        $method = $this->device->pollingMethod($type);

        if ($method) {
            return $method->toConfig();
        }

        $definition = $this->registry->get($type);

        return $definition?->fallbackConfig($this->device);
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

    public function ipmi(): IpmiConfig
    {
        /** @var IpmiConfig */
        return $this->get(PollingMethodType::Ipmi);
    }

    public function unixAgent(): UnixAgentConfig
    {
        /** @var UnixAgentConfig */
        return $this->get(PollingMethodType::UnixAgent);
    }

    public function __call(string $name, array $arguments): ?PollingMethodConfig
    {
        $type = PollingMethodType::tryFrom(Str::kebab($name));

        return $type ? $this->get($type) : null;
    }
}
