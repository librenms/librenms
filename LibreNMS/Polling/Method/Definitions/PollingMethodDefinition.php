<?php

namespace LibreNMS\Polling\Method\Definitions;

use App\View\FieldSchema\HandlesFieldSchema;
use App\View\FieldSchema\HasFieldSchema;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Probe\PollingMethodProbe;

class PollingMethodDefinition implements HasFieldSchema
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

    public function onProbeComplete(\App\Models\Device $device, \LibreNMS\Polling\Method\Probe\ProbeResult $result, bool $commit = false): void
    {
    }

    /**
     * Discover or validate a candidate polling method for a device.
     */
    public function discover(\App\Models\Device $device, \App\Models\DevicePollingMethod $method, PollingMethodProbe $probe): \LibreNMS\Polling\Method\Probe\ProbeResult
    {
        $testDevice = clone $device;
        $testDevice->setRelation('pollingMethods', collect([$method]));

        return $probe->check($testDevice);
    }

    public function enrichDeviceMetadata(\App\Models\Device $device): void
    {
    }

    /**
     * @param  class-string<PollingMethodConfig>  $configClass
     */
    public function fallbackConfig(
        \App\Models\Device $device,
        \LibreNMS\Enum\PollingMethodType $type,
        string $configClass,
        bool $defaultAffectsAvailability = true
    ): PollingMethodConfig {
        $method = new \App\Models\DevicePollingMethod([
            'method_type' => $type,
            'enabled' => false,
            'affects_availability' => $defaultAffectsAvailability,
        ]);
        $method->setRelation('device', $device);

        return $configClass::fromPollingMethod($method);
    }
}
