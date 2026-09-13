<?php

namespace LibreNMS\Polling\Method\Definitions;

use App\View\FieldSchema\HandlesFieldSchema;
use LibreNMS\Interfaces\PollingMethodDefinitionInterface;
use LibreNMS\Polling\Method\Config\IcmpConfig;

/**
 * @implements PollingMethodDefinitionInterface<IcmpConfig>
 */
class IcmpPollingMethodDefinition implements PollingMethodDefinitionInterface
{
    use HandlesFieldSchema;

    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return [];
    }

    public function defaultAffectsAvailability(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function icon(): string
    {
        return 'fa-exchange';
    }

    /**
     * @inheritDoc
     */
    public function class(): string
    {
        return IcmpConfig::class;
    }

    /**
     * @inheritDoc
     */
    public function probe(): \LibreNMS\Polling\Method\Probe\IcmpProbe
    {
        return new \LibreNMS\Polling\Method\Probe\IcmpProbe();
    }

    /**
     * @inheritDoc
     */
    public function secretDefinition(): null
    {
        return null;
    }
}
