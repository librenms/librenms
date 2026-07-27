<?php

namespace LibreNMS\Polling\Method;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\PollingMethodInterface;

class PollingMethodRepository
{
    private readonly Device $device;

    /** @var array<string, PollingMethodInterface> */
    private array $pollingMethods = [];

    public function __construct(
        ?Device $device = null,
    ) {
        $this->device = $device ?? \DeviceCache::getPrimary();
    }

    /**
     * Create or update a polling method row (and its associated Secret, if applicable).
     * Delegates all type-specific logic to the PollingMethod class for that type.
     *
     * @param  array<string, mixed>  $settings  Non-sensitive per-device settings (merged with existing).
     * @param  array<string, mixed>  $secretData  Credential fields (ignored for methods without a secret).
     */
    public function save(PollingMethodType $type, array $settings = [], array $secretData = [], bool $enabled = true, bool $affectsAvailability = false): DevicePollingMethod
    {
        return PollingMethodDefinition::for($type)->class()::save($this->device, $settings, $secretData, $enabled, $affectsAvailability);
    }

    /**
     * @template T of PollingMethodInterface
     *
     * @param  class-string<T>  $class
     * @return T
     */
    private function pollingMethod(PollingMethodType $type, string $class): PollingMethodInterface
    {
        if (! isset($this->pollingMethods[$type->value])) {
            $method = $this->device->pollingMethods->firstWhere('method_type', $type);
            $this->pollingMethods[$type->value] = $method ? $class::fromModel($method) : $class::disabled();
        }

        return $this->pollingMethods[$type->value];
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
