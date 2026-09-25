<?php

namespace LibreNMS\Polling\Method\Methods;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\View\FieldSchema\FieldDefinition;
use LibreNMS\Data\Source\Ipmitool;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Exceptions\IpmiConnectionFailed;
use LibreNMS\Polling\Method\Config\IpmiConfig;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\ProbeResult;
use LibreNMS\Polling\Secrets\Data\IpmiSecretData;

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

    /**
     * @param  Device  $device
     * @param  IpmiConfig  $config
     * @return ProbeResult
     */
    public function probe(Device $device, PollingMethodConfig $config): ProbeResult
    {
        $ipmiConfig = $config instanceof IpmiConfig ? $config : null;
        $ipmi = Ipmitool::init($device, $ipmiConfig);

        if (! $ipmi) {
            return ProbeResult::failure();
        }

        try {
            $ipmi->command(['power', 'status']);

            return ProbeResult::success();
        } catch (IpmiConnectionFailed $e) {
            return ProbeResult::failure(errorMessage: $e->getMessage());
        }
    }

    public function onProbeComplete(Device $device, ProbeResult $result, bool $commit = false): void
    {
    }

    public function secretType(): SecretType
    {
        return SecretType::Ipmi;
    }

    public function config(DevicePollingMethod $deviceMethod): IpmiConfig
    {
        $settings = $deviceMethod->settings ?? [];
        $secretData = $deviceMethod->secret ? IpmiSecretData::fromArray($deviceMethod->secret->data ?? []) : new IpmiSecretData();

        return new IpmiConfig(
            $deviceMethod->enabled ?? true,
            $deviceMethod->affects_availability ?? false,
            $secretData->username,
            $secretData->password,
            $secretData->kgKey,
            ! empty($settings['hostname']) ? (string) $settings['hostname'] : ($deviceMethod->device ? (string) $deviceMethod->device->hostname : ''),
            (int) ($settings['port'] ?? 623),
            (int) ($settings['ciphersuite'] ?? 0),
            (int) ($settings['timeout'] ?? 3),
            (string) ($settings['type'] ?? ''),
        );
    }

    public function fallbackConfig(Device $device): IpmiConfig
    {
        $method = $device->pollingMethod(PollingMethodType::Ipmi);
        if ($method) {
            return $this->config($method);
        }

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
