<?php

namespace LibreNMS\Polling\Method\Definitions;

use App\Facades\LibrenmsConfig;
use App\View\FieldSchema\FieldDefinition;

final class UnixAgentPollingMethodDefinition extends PollingMethodDefinition
{
    public function icon(): string
    {
        return 'fa-terminal';
    }

    public function defaultAffectsAvailability(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return [
            'port' => FieldDefinition::make('port', 'number')
                ->default(fn () => (int) LibrenmsConfig::get('unix-agent.port', 6556))
                ->min(1)
                ->max(65535)
                ->rules(['nullable', 'integer', 'min:1', 'max:65535'])
                ->cast('int'),

            'timeout' => FieldDefinition::make('timeout', 'number')
                ->default(fn () => (int) LibrenmsConfig::get('unix-agent.connection-timeout', 10))
                ->min(1)
                ->max(300)
                ->rules(['nullable', 'integer', 'min:1', 'max:300'])
                ->cast('int'),
        ];
    }
}
