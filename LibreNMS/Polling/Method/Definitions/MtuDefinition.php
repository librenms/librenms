<?php

namespace LibreNMS\Polling\Method\Definitions;

use App\View\FieldSchema\FieldDefinition;

final class MtuDefinition extends PollingMethodDefinition
{
    public function icon(): string
    {
        return 'fa-maximize';
    }

    public function fields(): array
    {
        return [
            'fragmentation' => FieldDefinition::make('fragmentation', 'select')
                ->options([
                    'default' => 'System Default',
                    'pmtu' => 'Allow with PMTU',
                    'allow' => 'Allow',
                    'deny' => 'Not Allowed',
                ]),

            'ip_version' => FieldDefinition::make('ip_version', 'select')
                ->options([
                    'default' => 'Auto',
                    'match_snmp_transport' => 'Match SNMP Transport',
                    'ipv4' => 'IPv4 Only',
                    'ipv6' => 'IPv6 Only',
                ]),

            'packet_size' => FieldDefinition::make('packet_size', 'number')
                ->min(100)
                ->max(65535),
        ];
    }
}
