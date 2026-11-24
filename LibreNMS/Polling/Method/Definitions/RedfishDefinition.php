<?php

namespace LibreNMS\Polling\Method\Definitions;

use App\View\FieldSchema\FieldDefinition;

final class RedfishDefinition extends PollingMethodDefinition
{
    public function icon(): string
    {
        return 'fa-fish';
    }

    public function fields(): array
    {
        return [
            'hostname' => FieldDefinition::make('hostname', 'text')
                ->placeholder('Default: device\'s hostname'),

            'scheme' => FieldDefinition::make('scheme', 'select')
                ->options([
                    'https' => 'HTTPS',
                    'http' => 'HTTP',
                ]),

            'port' => FieldDefinition::make('port', 'number')
                ->min(1)
                ->max(65535)
                ->placeholder('Default: 443 (HTTPS), 80 (HTTP)'),

            'insecure' => FieldDefinition::make('insecure', 'select')
                ->options([
                    0 => 'No',
                    1 => 'Yes',
                ])
                ->cast('bool'),

            'timeout' => FieldDefinition::make('timeout', 'number')
                ->min(1)
                ->max(300),
        ];
    }
}
