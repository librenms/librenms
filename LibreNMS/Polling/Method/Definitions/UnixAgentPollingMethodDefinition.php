<?php

namespace LibreNMS\Polling\Method\Definitions;

use App\Facades\LibrenmsConfig;
use App\View\FieldSchema\FieldDefinition;
use App\View\FieldSchema\HandlesFieldSchema;
use LibreNMS\Interfaces\PollingMethodDefinitionInterface;
use LibreNMS\Polling\Method\Config\UnixAgentConfig;

/**
 * @implements PollingMethodDefinitionInterface<UnixAgentConfig>
 */
class UnixAgentPollingMethodDefinition implements PollingMethodDefinitionInterface
{
    use HandlesFieldSchema;

    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return [
            'port' => FieldDefinition::make('port', 'number')
                ->fallback(fn () => LibrenmsConfig::get('unix-agent.port'))
                ->min(1)
                ->max(65535)
                ->rules(['required', 'integer', 'min:1', 'max:65535'])
                ->cast('int'),
        ];
    }

    public function defaultAffectsAvailability(): bool
    {
        return false;
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
