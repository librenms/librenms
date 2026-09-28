<?php

namespace LibreNMS\Polling\Method\Methods;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Enum\SecretType;
use LibreNMS\Exceptions\MissingSecretException;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Definitions\PollingMethodDefinition;
use LibreNMS\Polling\Method\ProbeResult;

/**
 * @template-covariant TConfig of PollingMethodConfig
 */
abstract class PollingMethod
{
    public function secretType(): ?SecretType
    {
        return null;
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
        return $this->probe($device, $this->config($device, $deviceMethod));
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
     * Whether a newly added method of this type affects device availability.
     */
    abstract public function defaultAffectsAvailability(): bool;

    /**
     * The value of each setting when it is not set, keyed like the definition fields.
     *
     * @return array<string, mixed>
     */
    abstract public function defaults(?Device $device = null): array;

    /**
     * The config for the device from its stored method, or the defaults if the device does not have this method.
     *
     * @return TConfig
     */
    abstract public function config(Device $device, ?DevicePollingMethod $deviceMethod = null): PollingMethodConfig;
}
