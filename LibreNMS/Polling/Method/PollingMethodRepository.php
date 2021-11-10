<?php

namespace LibreNMS\Polling\Method;

use App\Models\Device;
use LibreNMS\Enum\PollingMethodType;

class PollingMethodRepository
{
    private Device $device;

    public function __construct(
        ?Device $device = null,
    ) {
        $this->device = $device ?? \DeviceCache::getPrimary();
    }

    public function ipmi(): IpmiPollingMethod
    {
        $method = $this->device->pollingMethods->firstWhere('method_type', PollingMethodType::Ipmi);

        if ($method) {
            return IpmiPollingMethod::fromModel($method);
        }

        return IpmiPollingMethod::disabled();
    }

    public function snmp(): SnmpPollingMethod
    {
        $method = $this->device->pollingMethods->firstWhere('method_type', PollingMethodType::Snmp);

        if ($method) {
            return SnmpPollingMethod::fromModel($method);
        }

        return SnmpPollingMethod::disabled();
    }

    public function icmp(): IcmpPollingMethod
    {
        $method = $this->device->pollingMethods->firstWhere('method_type', PollingMethodType::Icmp);

        if ($method) {
            return IcmpPollingMethod::fromModel($method);
        }

        return IcmpPollingMethod::disabled();
    }

    public function unixAgent(): UnixAgentPollingMethod
    {
        $method = $this->device->pollingMethods->firstWhere('method_type', PollingMethodType::UnixAgent);

        if ($method) {
            return UnixAgentPollingMethod::fromModel($method);
        }

        return UnixAgentPollingMethod::disabled();
    }
}
