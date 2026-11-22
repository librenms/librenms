<?php

namespace LibreNMS\Polling\Method\Definitions;

use App\View\FieldSchema\FieldDefinition;

final class UnixAgentDefinition extends PollingMethodDefinition
{
    public function icon(): string
    {
        return 'fa-terminal';
    }

    public function fields(): array
    {
        return [
            'use_localhost' => FieldDefinition::make('use_localhost', 'checkbox'),

            'port' => FieldDefinition::make('port', 'number')
                ->min(1)
                ->max(65535),

            'timeout' => FieldDefinition::make('timeout', 'number')
                ->min(1)
                ->max(300),
        ];
    }
}
