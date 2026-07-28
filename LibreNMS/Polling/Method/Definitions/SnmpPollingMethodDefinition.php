<?php

namespace LibreNMS\Polling\Method\Definitions;

use App\Facades\LibrenmsConfig;
use Illuminate\Validation\Rule;
use LibreNMS\Enum\PortAssociationMode;
use LibreNMS\Interfaces\PollingMethodDefinitionInterface;
use LibreNMS\Polling\Method\SnmpPollingMethod;
use LibreNMS\Polling\Secrets\Definitions\SnmpSecretDefinition;
use LibreNMS\Traits\HandlesFieldSchema;

/**
 * @implements PollingMethodDefinitionInterface<SnmpPollingMethod>
 */
class SnmpPollingMethodDefinition implements PollingMethodDefinitionInterface
{
    use HandlesFieldSchema;

    /**
     * @inheritDoc
     */
    public function schema(): array
    {
        return [
            'transport' => [
                'type' => 'select',
                'options' => [
                    'udp' => 'UDP',
                    'tcp' => 'TCP',
                    'udp6' => 'UDP6',
                    'tcp6' => 'TCP6',
                ],
                'default' => 'udp',
            ],
            'port' => [
                'type' => 'number',
                'default' => 161,
            ],
            'timeout' => [
                'type' => 'number',
                'default' => 3,
            ],
            'retries' => [
                'type' => 'number',
                'default' => 1,
            ],
            'max_repeaters' => [
                'type' => 'number',
                'default' => 0,
            ],
            'max_oid' => [
                'type' => 'number',
                'default' => 10,
            ],
            'port_association_mode' => [
                'type' => 'select',
                'options' => array_combine(PortAssociationMode::getModes(), PortAssociationMode::getModes()),
                'default' => LibrenmsConfig::get('default_port_association_mode', 'ifIndex'),
            ],
        ];
    }

    public function defaultAffectsAvailability(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            'transport' => ['required', 'string', 'in:udp,tcp,udp6,tcp6'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'timeout' => ['required', 'integer', 'min:1', 'max:60'],
            'retries' => ['required', 'integer', 'min:0', 'max:10'],
            'max_repeaters' => ['required', 'integer', 'min:0', 'max:30'],
            'max_oid' => ['required', 'integer', 'min:1', 'max:100'],
            'port_association_mode' => ['required', 'string', Rule::in(PortAssociationMode::getModes())],
        ];
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
        return SnmpPollingMethod::class;
    }

    /**
     * @inheritDoc
     */
    public function secretDefinition(): SnmpSecretDefinition
    {
        return new SnmpSecretDefinition;
    }
}
