<?php

namespace LibreNMS\Polling\Method\Methods;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\View\FieldSchema\FieldDefinition;
use LibreNMS\Data\Source\Ipmitool;
use LibreNMS\Enum\SecretType;
use LibreNMS\Exceptions\IpmiConnectionFailed;
use LibreNMS\Polling\Method\Config\IpmiConfig;
use LibreNMS\Polling\Method\ProbeResult;

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

    public function probe(Device $device): ProbeResult
    {
        $ipmi = Ipmitool::init($device);

        if (! $ipmi) {
            return ProbeResult::failure();
        }

        try {
            $ipmi->command(['power', 'status']);

            return ProbeResult::success();
        } catch (IpmiConnectionFailed $e) {
            return ProbeResult::failure([], $e->getMessage());
        }
    }

    public function secretType(): SecretType
    {
        return SecretType::Ipmi;
    }

    public function config(DevicePollingMethod $deviceMethod): IpmiConfig
    {
        return IpmiConfig::fromPollingMethod($deviceMethod);
    }

    public function fallbackConfig(Device $device): IpmiConfig
    {
        return new IpmiConfig(
            enabled: false,
            affectsAvailability: false,
            username: '',
            password: '',
            kgKey: '',
            hostname: (string) $device->hostname,
            port: 623,
            cipherSuite: 0,
            timeout: 3,
            type: '',
        );
    }
}
