<?php

namespace LibreNMS\Polling\Method\Definitions;

use App\View\FieldSchema\FieldDefinition;

final class IcmpDefinition extends PollingMethodDefinition
{
    public function icon(): string
    {
        return 'fa-exchange';
    }

    public function fields(): array
    {
        return [
            'ip_version' => FieldDefinition::make('ip_version', 'select')
                ->options([
                    'default' => 'Auto',
                    'match_snmp_transport' => 'Match SNMP Transport',
                    'ipv4' => 'IPv4 Only',
                    'ipv6' => 'IPv6 Only',
                ])
                ->rules(['nullable', 'string', 'in:default,match_snmp_transport,ipv4,ipv6']),
        ];
    }
}
