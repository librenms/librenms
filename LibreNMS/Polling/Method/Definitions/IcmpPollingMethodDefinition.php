<?php

namespace LibreNMS\Polling\Method\Definitions;

use LibreNMS\Interfaces\PollingMethodDefinitionInterface;
use LibreNMS\Polling\Method\Config\IcmpConfig;
use LibreNMS\Traits\HandlesFieldSchema;

/**
 * @implements PollingMethodDefinitionInterface<IcmpConfig>
 */
class IcmpPollingMethodDefinition implements PollingMethodDefinitionInterface
{
    use HandlesFieldSchema;

    /**
     * @inheritDoc
     */
    public function schema(): array
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
    public function rules(): array
    {
        return [];
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
    public function secretDefinition(): null
    {
        return null;
    }
}
