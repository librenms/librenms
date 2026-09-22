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

    public function get(PollingMethodType $type): ?PollingMethodConfig
    {
        $method = $this->device->pollingMethod($type);

        if ($method) {
            return $method->toConfig();
        }

        $definition = $this->registry->definition($type);

        return $definition?->fallbackConfig(
            $this->device,
            $type,
            $this->registry->configClass($type),
            $this->registry->defaultAffectsAvailability($type),
        );
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

    /**
     * @param  array<int, mixed>  $arguments
     */
    public function __call(string $name, array $arguments): ?PollingMethodConfig
    {
        $type = PollingMethodType::tryFrom(Str::kebab($name));

        return $type ? $this->get($type) : null;
    }
}
