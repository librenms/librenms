<?php

namespace LibreNMS\Polling\Method\Definitions;

use App\View\FieldSchema\FieldDefinition;

final class IpmiDefinition extends PollingMethodDefinition
{
    public function icon(): string
    {
        return 'fa-microchip';
    }

    public function fields(): array
    {
        return [
            'hostname' => FieldDefinition::make('hostname', 'text')
                ->placeholder('Default: device\'s hostname')
                ->rules(['nullable', 'string']),

            'port' => FieldDefinition::make('port', 'number')
                ->min(1)
                ->max(65535)
                ->rules(['nullable', 'integer', 'min:1', 'max:65535']),

            'ciphersuite' => FieldDefinition::make('ciphersuite', 'text')
                ->rules(['nullable', 'string']),

            'timeout' => FieldDefinition::make('timeout', 'number')
                ->min(1)
                ->rules(['nullable', 'integer', 'min:1']),
        ];
    }
}
