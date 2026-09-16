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

    abstract public function icon(): string;

    /**
     * @return class-string<T>
     */
    abstract public function class(): string;

    abstract public function probe(): PollingMethodProbe;
}
