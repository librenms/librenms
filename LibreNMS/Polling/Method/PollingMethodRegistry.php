<?php

namespace LibreNMS\Polling\Method;

use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Methods\IcmpPollingMethod;
use LibreNMS\Polling\Method\Methods\IpmiPollingMethod;
use LibreNMS\Polling\Method\Methods\PollingMethod;
use LibreNMS\Polling\Method\Methods\ProxmoxPollingMethod;
use LibreNMS\Polling\Method\Methods\SnmpPollingMethod;
use LibreNMS\Polling\Method\Methods\UnixAgentPollingMethod;
use LibreNMS\Polling\Method\Methods\UnknownPollingMethod;

/**
 * The polling method implementation for each type.
 */
class PollingMethodRegistry
{
    /**
     * @return PollingMethod<PollingMethodConfig>
     */
    public function get(PollingMethodType $type): PollingMethod
    {
        return resolve(match ($type) {
            PollingMethodType::Icmp => IcmpPollingMethod::class,
            PollingMethodType::Ipmi => IpmiPollingMethod::class,
            PollingMethodType::Proxmox => ProxmoxPollingMethod::class,
            PollingMethodType::Snmp => SnmpPollingMethod::class,
            PollingMethodType::UnixAgent => UnixAgentPollingMethod::class,
            PollingMethodType::Unknown => UnknownPollingMethod::class,
        });
    }

    /**
     * The methods with a data source that implements the module interface.
     *
     * @param  class-string  $interface
     * @return list<PollingMethodType>
     */
    public function providing(string $interface): array
    {
        return array_values(array_filter(
            PollingMethodType::cases(),
            fn (PollingMethodType $type): bool => $this->get($type)->providesData($interface),
        ));
    }
}
