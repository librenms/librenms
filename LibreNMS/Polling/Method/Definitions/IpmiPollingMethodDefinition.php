<?php

namespace LibreNMS\Polling\Method\Definitions;

use App\View\FieldSchema\FieldDefinition;
use App\View\FieldSchema\HandlesFieldSchema;
use LibreNMS\Interfaces\PollingMethodDefinitionInterface;
use LibreNMS\Polling\Method\Config\IpmiConfig;
use LibreNMS\Polling\Secrets\Definitions\IpmiSecretDefinition;

/**
 * @implements PollingMethodDefinitionInterface<IpmiConfig>
 */
class IpmiPollingMethodDefinition implements PollingMethodDefinitionInterface
{
    use HandlesFieldSchema;

    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return [
            'hostname' => FieldDefinition::make('hostname', 'text')
                ->placeholder('Default: device\'s hostname')
                ->rules(['required', 'string']),

            'port' => FieldDefinition::make('port', 'number')
                ->default(623)
                ->min(1)
                ->max(65535)
                ->rules(['required', 'integer', 'min:1', 'max:65535'])
                ->cast('int'),

            'ciphersuite' => FieldDefinition::make('ciphersuite', 'text')
                ->rules(['nullable', 'string']),

            'timeout' => FieldDefinition::make('timeout', 'number')
                ->default(3)
                ->min(1)
                ->rules(['required', 'integer', 'min:1'])
                ->cast('int'),
        ];
    }

    public function defaultAffectsAvailability(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function icon(): string
    {
        return 'fa-microchip';
    }

    /**
     * @inheritDoc
     */
    public function class(): string
    {
        return IpmiConfig::class;
    }

    /**
     * @inheritDoc
     */
    public function secretDefinition(): IpmiSecretDefinition
    {
        return new IpmiSecretDefinition;
    }
}
