<?php

namespace LibreNMS\Polling\Method\Methods;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\View\FieldSchema\FieldDefinition;
use LibreNMS\Enum\SecretType;
use LibreNMS\Polling\Method\Config\IpmiConfig;
use LibreNMS\Polling\Method\Probe\IpmiProbe;

final class IpmiPollingMethod extends PollingMethod
{
    public function icon(): string
    {
        return 'fa-microchip';
    }

    public function defaultAffectsAvailability(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return [
            'hostname' => FieldDefinition::make('hostname', 'text')
                ->placeholder('Default: device\'s hostname')
                ->rules(['nullable', 'string']),

            'port' => FieldDefinition::make('port', 'number')
                ->default(623)
                ->min(1)
                ->max(65535)
                ->rules(['nullable', 'integer', 'min:1', 'max:65535'])
                ->cast('int'),

            'ciphersuite' => FieldDefinition::make('ciphersuite', 'text')
                ->rules(['nullable', 'string']),

            'timeout' => FieldDefinition::make('timeout', 'number')
                ->default(3)
                ->min(1)
                ->rules(['nullable', 'integer', 'min:1'])
                ->cast('int'),
        ];
    }

    public function probe(): IpmiProbe
    {
        return resolve(IpmiProbe::class);
    }

    public function secretType(): ?SecretType
    {
        return SecretType::Ipmi;
    }

    public function config(DevicePollingMethod $method): IpmiConfig
    {
        return IpmiConfig::fromPollingMethod($method);
    }

    public function fallbackConfig(Device $device): ?IpmiConfig
    {
        return null;
    }
}
