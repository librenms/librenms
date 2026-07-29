<?php

namespace LibreNMS\Polling\Method\Definitions;

use LibreNMS\Interfaces\PollingMethodDefinitionInterface;
use LibreNMS\Polling\Method\Config\UnixAgentConfig;
use LibreNMS\Traits\HandlesFieldSchema;

/**
 * @implements PollingMethodDefinitionInterface<UnixAgentConfig>
 */
class UnixAgentPollingMethodDefinition implements PollingMethodDefinitionInterface
{
    use HandlesFieldSchema;

    /**
     * @inheritDoc
     */
    public function schema(): array
    {
        return [
            'port' => [
                'type' => 'number',
                'default' => 6556,
                'min' => 1,
                'max' => 65535,
            ],
        ];
    }

    public function defaultAffectsAvailability(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function icon(): string
    {
        return 'fa-terminal';
    }

    /**
     * @inheritDoc
     */
    public function class(): string
    {
        return UnixAgentConfig::class;
    }

    /**
     * @inheritDoc
     */
    public function secretDefinition(): null
    {
        return null;
    }
}
