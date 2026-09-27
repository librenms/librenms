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
                ->placeholder('Default: device\'s hostname'),

            'port' => FieldDefinition::make('port', 'number')
                ->min(1)
                ->max(65535),

            'ciphersuite' => FieldDefinition::make('ciphersuite', 'number')
                ->min(0)
                ->max(255),

            'timeout' => FieldDefinition::make('timeout', 'number')
                ->min(1),

            'type' => FieldDefinition::make('type', 'select')
                ->options([
                    'lanplus' => 'lanplus',
                    'lan' => 'lan',
                    'imb' => 'imb',
                    'open' => 'open',
                ])
                ->placeholder('Auto-detect'),
        ];
    }
}
