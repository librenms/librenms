<?php

namespace LibreNMS\Polling\Method\Methods;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Enum\SecretType;
use LibreNMS\Exceptions\MissingSecretException;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Definitions\PollingMethodDefinition;
use LibreNMS\Polling\Method\ProbeResult;

abstract class PollingMethod
{
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
    abstract public function probe(Device $device, PollingMethodConfig $config): ProbeResult;

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

    /**
     * Identify a newly added device (sysName, os, etc.) after this method checked successfully.
     * Only methods that can query the device for its identity implement this.
     */
    public function enrichDeviceMetadata(Device $device): void
    {
    }

    /**
     * Attach a secret to a method that has none and will be saved without a check.
     *
     * @throws MissingSecretException
     */
    public function assignDefaultSecret(DevicePollingMethod $deviceMethod): void
    {
    }

    /**
     * The settings of this method: form fields, validation rules and casts.
     */
    abstract public function definition(): PollingMethodDefinition;

    /**
     * Config for a newly added method of this type, with defaults for the device if given.
     */
    abstract public function defaultConfig(?Device $device = null): PollingMethodConfig;

    /**
     * Build the config for a configured method from its settings and secret.
     */
    public function config(DevicePollingMethod $deviceMethod): PollingMethodConfig
    {
        $config = $this->defaultConfig($deviceMethod->device)
            ->fill($this->definition()->filterOverrides($deviceMethod->settings ?? []));

        $secretType = $this->secretType();
        if ($secretType !== null) {
            $config->fill(get_object_vars($secretType->definition()->data($deviceMethod->secret->data ?? [])));
        }

        $config->enabled = $deviceMethod->enabled;
        $config->affectsAvailability = $deviceMethod->affects_availability;

        return $config;
    }

    /**
     * Config used when the device does not have this method configured.
     */
    public function fallbackConfig(Device $device): PollingMethodConfig
    {
        $config = $this->defaultConfig($device);
        $config->enabled = false;

        return $config;
    }
}
