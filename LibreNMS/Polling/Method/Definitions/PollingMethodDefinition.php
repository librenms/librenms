<?php

namespace LibreNMS\Polling\Method\Definitions;

use App\View\FieldSchema\HandlesFieldSchema;
use App\View\FieldSchema\HasFieldSchema;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Probe\PollingMethodProbe;

/**
 * @template-covariant T of PollingMethodConfig
 */
abstract class PollingMethodDefinition implements HasFieldSchema
{
    use HandlesFieldSchema;

    public function fields(): array
    {
        return [];
    }

    public function defaultAffectsAvailability(): bool
    {
        return true;
    }

    public function secretDefinition(): ?HasFieldSchema
    {
        return null;
    }

    public function onProbeComplete(\App\Models\Device $device, \LibreNMS\Polling\Method\Probe\ProbeResult $result, bool $commit = false): void
    {
    }

    /**
     * @return T
     */
    abstract public function fallbackConfig(\App\Models\Device $device): PollingMethodConfig;

    abstract public function icon(): string;

    /**
     * @return class-string<T>
     */
    abstract public function class(): string;

    abstract public function probe(): PollingMethodProbe;
}
