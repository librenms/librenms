<?php

namespace LibreNMS\Polling\Method;

use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Methods\IcmpPollingMethod;
use LibreNMS\Polling\Method\Methods\IpmiPollingMethod;
use LibreNMS\Polling\Method\Methods\PollingMethod;
use LibreNMS\Polling\Method\Methods\SnmpPollingMethod;
use LibreNMS\Polling\Method\Methods\UnixAgentPollingMethod;

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
            PollingMethodType::Snmp => SnmpPollingMethod::class,
            PollingMethodType::UnixAgent => UnixAgentPollingMethod::class,
        });
    }
}
