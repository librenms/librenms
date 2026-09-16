<?php

namespace LibreNMS\Polling\Method\Definitions;

use App\Facades\LibrenmsConfig;
use App\View\FieldSchema\FieldDefinition;
use Illuminate\Validation\Rule;
use LibreNMS\Enum\PortAssociationMode;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Polling\Secrets\Definitions\SnmpSecretDefinition;

/**
 * @extends PollingMethodDefinition<SnmpConfig>
 */
class SnmpPollingMethodDefinition extends PollingMethodDefinition
{
    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return [
            'transport' => FieldDefinition::make('transport', 'select')
                ->options([
                    'udp' => 'UDP',
                    'tcp' => 'TCP',
                    'udp6' => 'UDP6',
                    'tcp6' => 'TCP6',
                ])
                ->default('udp')
                ->rules(['nullable', 'string', 'in:udp,tcp,udp6,tcp6']),

            'port' => FieldDefinition::make('port', 'number')
                ->default(fn () => (int) LibrenmsConfig::get('snmp.port', 161))
                ->min(1)
                ->max(65535)
                ->rules(['nullable', 'integer', 'min:1', 'max:65535'])
                ->cast('int'),

            'timeout' => FieldDefinition::make('timeout', 'number')
                ->default(fn () => (float) LibrenmsConfig::get('snmp.timeout', 1))
                ->min(0.1)
                ->max(60)
                ->rules(['nullable', 'numeric', 'min:0.1', 'max:60'])
                ->cast('float'),

            'retries' => FieldDefinition::make('retries', 'number')
                ->default(fn () => (int) LibrenmsConfig::get('snmp.retries', 5))
                ->min(0)
                ->max(10)
                ->rules(['nullable', 'integer', 'min:0', 'max:10'])
                ->cast('int'),

            'max_repeaters' => FieldDefinition::make('max_repeaters', 'number')
                ->default(fn () => (int) LibrenmsConfig::get('snmp.max_repeaters', 10))
                ->min(0)
                ->max(30)
                ->rules(['nullable', 'integer', 'min:0', 'max:30'])
                ->cast('int'),

            'max_oid' => FieldDefinition::make('max_oid', 'number')
                ->default(fn () => max(1, (int) LibrenmsConfig::get('snmp.max_oid', 10)))
                ->min(1)
                ->max(100)
                ->rules(['nullable', 'integer', 'min:1', 'max:100'])
                ->cast('int'),

            'port_association_mode' => FieldDefinition::make('port_association_mode', 'select')
                ->options(array_combine(PortAssociationMode::getModes(), PortAssociationMode::getModes()))
                ->default(fn () => LibrenmsConfig::get('default_port_association_mode', 'ifIndex'))
                ->rules(['nullable', 'string', Rule::in(PortAssociationMode::getModes())]),
        ];
    }

    public function defaultAffectsAvailability(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function icon(): string
    {
        return 'fa-server';
    }

    /**
     * @inheritDoc
     */
    public function class(): string
    {
        return SnmpConfig::class;
    }

    /**
     * @inheritDoc
     */
    public function probe(): \LibreNMS\Polling\Method\Probe\SnmpProbe
    {
        return new \LibreNMS\Polling\Method\Probe\SnmpProbe();
    }

    /**
     * @inheritDoc
     */
    public function secretDefinition(): SnmpSecretDefinition
    {
        return new SnmpSecretDefinition;
    }
}
