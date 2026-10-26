<?php

namespace LibreNMS\Polling\Method\Definitions;

use App\View\FieldSchema\FieldDefinition;
use LibreNMS\Enum\PortAssociationMode;
use LibreNMS\Enum\SecretType;

final class SnmpDefinition extends PollingMethodDefinition
{
    public function icon(): string
    {
        return 'fa-server';
    }

    public function fields(): array
    {
        return [
            // a v3 secret is always v3, so the version is only chosen for a community
            'version' => FieldDefinition::make('version', 'select')
                ->options([
                    'v2c' => 'v2c',
                    'v1' => 'v1',
                ])
                ->forSecretTypes(SecretType::SnmpCommunity),

            'transport' => FieldDefinition::make('transport', 'select')
                ->options([
                    'udp' => 'UDP',
                    'tcp' => 'TCP',
                    'udp6' => 'UDP6',
                    'tcp6' => 'TCP6',
                ]),

            'port' => FieldDefinition::make('port', 'number')
                ->min(1)
                ->max(65535),

            'timeout' => FieldDefinition::make('timeout', 'number')
                ->min(0.1)
                ->max(60)
                ->cast('float'),

            'retries' => FieldDefinition::make('retries', 'number')
                ->min(0)
                ->max(10),

            'max_repeaters' => FieldDefinition::make('max_repeaters', 'number')
                ->min(0)
                ->max(30),

            'max_oid' => FieldDefinition::make('max_oid', 'number')
                ->min(1)
                ->max(100),

            'bulk' => FieldDefinition::make('bulk', 'select')
                ->options([
                    1 => 'Yes',
                    0 => 'No',
                ])
                ->cast('bool'),

            'context' => FieldDefinition::make('context', 'text')
                ->rules(['nullable', 'string', 'max:255']),

            'port_association_mode' => FieldDefinition::make('port_association_mode', 'select')
                ->options(array_combine(PortAssociationMode::getModes(), PortAssociationMode::getModes())),
        ];
    }
}
