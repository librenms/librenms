<?php

namespace LibreNMS\Polling\Method\Definitions;

use App\View\FieldSchema\FieldDefinition;
use LibreNMS\Enum\MtuFragOption;

final class MtuDefinition extends PollingMethodDefinition
{
    public function icon(): string
    {
        return 'fa-maximize';
    }

    public function fields(): array
    {
        $options = [
            'default' => 'System Default',
            'pmtu' => 'Allow with PMTU',
            'allow' => 'Allow',
            'deny' => 'Not Allowed',
        ];
        $options = array_filter($options, fn ($label, $value) => MtuFragOption::from($value)->isSupported(), ARRAY_FILTER_USE_BOTH);

        $fields = [
            'fragmentation' => FieldDefinition::make('fragmentation', 'select')
                ->options($options),
        ];

        $fields['ip_version'] = FieldDefinition::make('ip_version', 'select')
            ->options([
                'default' => 'Auto',
                'ipv4' => 'IPv4 Only',
                'ipv6' => 'IPv6 Only',
            ]);

        $fields['packet_size'] = FieldDefinition::make('packet_size', 'number')
            ->min(100)
            ->max(65535);

        return $fields;
    }
}
