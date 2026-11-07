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
        return $this->pollingMethods->get($type)->config($this->device, $this->device->pollingMethod($type));
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
     * At least one of the methods is configured and enabled for this device.
     *
     * @param  PollingMethodType[]  $types
     */
    public function anyEnabled(array $types): bool
    {
        foreach ($types as $type) {
            if ($this->isEnabled($type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enabled methods that affect availability and failed their last check.
     *
     * @return Collection<int, DevicePollingMethod>
     */
    public function failedAvailabilityChecks(): Collection
    {
        return $this->methods()->filter(
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
        return $this->methods()->contains(
            fn (DevicePollingMethod $deviceMethod): bool => $deviceMethod->enabled && $deviceMethod->affects_availability
        );
    }

    /**
     * The device's methods, without unknown types, which are never used.
     *
     * @return Collection<int, DevicePollingMethod>
     */
    public function methods(): Collection
    {
        if (! $this->device->exists && ! $this->device->relationLoaded('pollingMethods')) {
            return new Collection;
        }

        return $this->device->pollingMethods->toBase()
            ->reject(fn (DevicePollingMethod $deviceMethod): bool => $deviceMethod->method_type === PollingMethodType::Unknown)
            ->values();
    }
}
