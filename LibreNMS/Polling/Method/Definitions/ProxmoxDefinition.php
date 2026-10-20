<?php

namespace LibreNMS\Polling\Method\Definitions;

use App\View\FieldSchema\FieldDefinition;

final class ProxmoxDefinition extends PollingMethodDefinition
{
    public function icon(): string
    {
        return 'fa-cubes';
    }

    public function fields(): array
    {
        return [
            'hostname' => FieldDefinition::make('hostname', 'text')
                ->placeholder('Default: device\'s hostname'),

            'port' => FieldDefinition::make('port', 'number')
                ->min(1)
                ->max(65535),

            'timeout' => FieldDefinition::make('timeout', 'number')
                ->min(1)
                ->max(300),

            'verify_ssl' => FieldDefinition::make('verify_ssl', 'select')
                ->options([
                    1 => 'Yes',
                    0 => 'No',
                ])
                ->cast('bool'),
        ];
    }
}
