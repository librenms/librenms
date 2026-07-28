<?php

namespace LibreNMS\Polling\Method\Definitions;

use LibreNMS\Interfaces\PollingMethodDefinitionInterface;
use LibreNMS\Polling\Method\IpmiPollingMethod;
use LibreNMS\Polling\Secrets\Definitions\IpmiSecretDefinition;
use LibreNMS\Traits\HandlesFieldSchema;

/**
 * @implements PollingMethodDefinitionInterface<IpmiPollingMethod>
 */
class IpmiPollingMethodDefinition implements PollingMethodDefinitionInterface
{
    use HandlesFieldSchema;

    /**
     * @inheritDoc
     */
    public function schema(): array
    {
        return [
            'hostname' => [
                'type' => 'text',
                'default' => '',
            ],
            'port' => [
                'type' => 'number',
                'default' => 623,
            ],
            'ciphersuite' => [
                'type' => 'text',
                'default' => '',
            ],
            'timeout' => [
                'type' => 'number',
                'default' => 3,
            ],
        ];
    }

    public function defaultAffectsAvailability(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            'hostname' => ['required', 'string'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'ciphersuite' => ['nullable', 'string'],
            'timeout' => ['required', 'integer', 'min:1'],
        ];
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
        return IpmiPollingMethod::class;
    }

    /**
     * @inheritDoc
     */
    public function secretDefinition(): IpmiSecretDefinition
    {
        return new IpmiSecretDefinition;
    }
}
