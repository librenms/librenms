<?php

namespace LibreNMS\Polling\Method;

use App\Models\Device;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\PollingMethodInterface;

readonly class PollingMethodAccessor
{
    public function __construct(
        private Device $device,
    ) {
    }

    /**
     * @template T of PollingMethodInterface
     *
     * @param  class-string<T>  $class
     * @return T
     */
    private function pollingMethod(PollingMethodType $type, string $class): PollingMethodInterface
    {
        return $this->device->pollingMethod($type)?->toPollingMethod() ?? $class::disabled();
    }

    public function snmp(): SnmpPollingMethod
    {
        return $this->pollingMethod(PollingMethodType::Snmp, SnmpPollingMethod::class);
    }

    public function icmp(): IcmpPollingMethod
    {
        return $this->pollingMethod(PollingMethodType::Icmp, IcmpPollingMethod::class);
    }

    public function ipmi(): IpmiPollingMethod
    {
        return $this->pollingMethod(PollingMethodType::Ipmi, IpmiPollingMethod::class);
    }

    public function unixAgent(): UnixAgentPollingMethod
    {
        return $this->pollingMethod(PollingMethodType::UnixAgent, UnixAgentPollingMethod::class);
    }
}
