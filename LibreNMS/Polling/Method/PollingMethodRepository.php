<?php

namespace LibreNMS\Polling\Method;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\PollingMethodInterface;

readonly class PollingMethodRepository
{
    public function __construct(
        private Device $device,
        private PollingMethodManager $manager = new PollingMethodManager,
    ) {
    }

    /**
     * Create or update a polling method row (and its associated Secret, if applicable).
     * Delegates all type-specific logic to PollingMethodManager.
     *
     * @param  array<string, mixed>  $settings  Non-sensitive per-device settings (merged with existing).
     * @param  array<string, mixed>  $secretData  Credential fields (ignored for methods without a secret).
     */
    public function save(PollingMethodType $type, array $settings = [], array $secretData = [], bool $enabled = true, bool $affectsAvailability = false): DevicePollingMethod
    {
        return $this->manager->save(
            $this->device,
            $type,
            $settings,
            $secretData,
            'default',
            null,
            null,
            false,
            $enabled,
            $affectsAvailability
        );
    }

    /**
     * @template T of PollingMethodInterface
     *
     * @param  class-string<T>  $class
     * @return T
     */
    private function pollingMethod(PollingMethodType $type, string $class): PollingMethodInterface
    {
        return  $this->device->pollingMethods->firstWhere('method_type', $type)?->toPollingMethod() ?? $class::disabled();
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
