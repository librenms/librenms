<?php

namespace LibreNMS\Polling\Method\Definitions;

use App\View\FieldSchema\FieldDefinition;
use LibreNMS\Polling\Method\Config\UnixAgentConfig;

final class UnixAgentDefinition extends PollingMethodDefinition
{
    public function icon(): string
    {
        return 'fa-terminal';
    }

    protected function defaultConfig(): UnixAgentConfig
    {
        return UnixAgentConfig::default();
    }

    public function fields(): array
    {
        return [
            'port' => FieldDefinition::make('port', 'number')
                ->min(1)
                ->max(65535)
                ->rules(['nullable', 'integer', 'min:1', 'max:65535']),

            'timeout' => FieldDefinition::make('timeout', 'number')
                ->min(1)
                ->max(300)
                ->rules(['nullable', 'integer', 'min:1', 'max:300']),
        ];
    }
}
