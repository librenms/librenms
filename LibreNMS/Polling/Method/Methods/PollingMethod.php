<?php

namespace LibreNMS\Polling\Method\Methods;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\View\FieldSchema\HandlesFieldSchema;
use App\View\FieldSchema\HasFieldSchema;
use LibreNMS\Enum\SecretType;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Probe\PollingMethodProbe;
use LibreNMS\Polling\Method\Probe\ProbeResult;

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

    abstract public function probe(): PollingMethodProbe;

    public function check(Device $device): ProbeResult
    {
        return $this->probe()->check($device);
    }

    /**
     * Discover or validate a candidate polling method for a device.
     */
    public function discover(Device $device, DevicePollingMethod $deviceMethod): ProbeResult
    {
        $testDevice = clone $device;
        $testDevice->setRelation('pollingMethods', collect([$deviceMethod]));

        return $this->check($testDevice);
    }

    public function onProbeComplete(Device $device, ProbeResult $result, bool $commit = false): void
    {
    }

    public function enrichDeviceMetadata(Device $device): void
    {
    }

    abstract public function config(DevicePollingMethod $deviceMethod): PollingMethodConfig;

    public function fallbackConfig(Device $device): ?PollingMethodConfig
    {
        return null;
    }
}
