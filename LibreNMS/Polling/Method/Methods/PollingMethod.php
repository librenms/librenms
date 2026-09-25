<?php

namespace LibreNMS\Polling\Method\Methods;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\View\FieldSchema\HandlesFieldSchema;
use App\View\FieldSchema\HasFieldSchema;
use LibreNMS\Enum\SecretType;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\ProbeResult;

abstract class PollingMethod implements HasFieldSchema
{
    use HandlesFieldSchema;

    public function icon(): string
    {
        return 'fa-server';
    }

    public function defaultAffectsAvailability(): bool
    {
        return true;
    }

    public function fields(): array
    {
        return [];
    }

    public function secretType(): ?SecretType
    {
        return null;
    }

    public function hasSecret(): bool
    {
        return $this->secretType() !== null;
    }

    /**
     * Perform the cheapest reachability check for this polling method.
     * Must avoid side effects on the target where possible.
     * Returns a failure rather than throwing for an unreachable target.
     * Called at most once per method per run by the framework.
     */
    abstract public function probe(Device $device, ?PollingMethodConfig $config = null): ProbeResult;

    /**
     * Discover or validate a candidate polling method for a device.
     */
    public function discover(Device $device, DevicePollingMethod $deviceMethod): ProbeResult
    {
        return $this->probe($device, $this->config($deviceMethod));
    }

    public function onProbeComplete(Device $device, ProbeResult $result, bool $commit = false): void
    {
    }

    public function enrichDeviceMetadata(Device $device): void
    {
    }

    abstract public function config(DevicePollingMethod $deviceMethod): PollingMethodConfig;

    abstract public function fallbackConfig(Device $device): PollingMethodConfig;
}
