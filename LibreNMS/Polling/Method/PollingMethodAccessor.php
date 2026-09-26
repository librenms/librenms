<?php

namespace LibreNMS\Polling\Method;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use Illuminate\Support\Collection;
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
        private PollingMethodRegistry $pollingMethods,
    ) {
    }

    public function get(PollingMethodType $type): PollingMethodConfig
    {
        $method = $this->pollingMethods->require($type);
        $deviceMethod = $this->device->pollingMethod($type);

        return $deviceMethod ? $method->config($deviceMethod) : $method->fallbackConfig($this->device);
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
     * The method is configured and enabled for this device.
     */
    public function isEnabled(PollingMethodType $type): bool
    {
        return (bool) $this->device->pollingMethod($type)?->enabled;
    }

    /**
     * The method is enabled and its last check succeeded.
     */
    public function isAvailable(PollingMethodType $type): bool
    {
        $deviceMethod = $this->device->pollingMethod($type);

        return $deviceMethod !== null && $deviceMethod->enabled && $deviceMethod->last_check_successful === true;
    }

    /**
     * Enabled methods that affect availability and failed their last check.
     *
     * @return Collection<int, DevicePollingMethod>
     */
    public function failedAvailabilityChecks(): Collection
    {
        return $this->deviceMethods()->filter(
            fn (DevicePollingMethod $deviceMethod): bool => $deviceMethod->enabled
                && $deviceMethod->affects_availability
                && $deviceMethod->last_check_successful === false
        )->values();
    }

    /**
     * At least one enabled method affects availability.
     */
    public function hasAvailabilityCheck(): bool
    {
        return $this->deviceMethods()->contains(
            fn (DevicePollingMethod $deviceMethod): bool => $deviceMethod->enabled && $deviceMethod->affects_availability
        );
    }

    /**
     * @return Collection<int, DevicePollingMethod>
     */
    private function deviceMethods(): Collection
    {
        if (! $this->device->exists && ! $this->device->relationLoaded('pollingMethods')) {
            return new Collection;
        }

        return $this->device->pollingMethods->toBase();
    }
}
