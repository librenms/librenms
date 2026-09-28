<?php

namespace LibreNMS\Polling\Method\Methods;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Data\Source\Ipmitool;
use LibreNMS\Enum\SecretType;
use LibreNMS\Exceptions\IpmiConnectionFailed;
use LibreNMS\Polling\Method\Config\IpmiConfig;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Definitions\IpmiDefinition;
use LibreNMS\Polling\Method\ProbeResult;
use LibreNMS\Polling\Secrets\Data\IpmiSecretData;

/**
 * @extends PollingMethod<IpmiConfig>
 */
final class IpmiPollingMethod extends PollingMethod
{
    public function definition(): IpmiDefinition
    {
        return new IpmiDefinition;
    }

    public function defaultAffectsAvailability(): bool
    {
        return false;
    }

    /**
     * @return array{hostname: string, port: int, ciphersuite: int, timeout: int, type: string}
     */
    public function defaults(?Device $device = null): array
    {
        return [
            'hostname' => $device->hostname ?? '',
            'port' => 623,
            'ciphersuite' => 0,
            'timeout' => 3,
            'type' => '', // detected
        ];
    }

    public function config(Device $device, ?DevicePollingMethod $deviceMethod = null): IpmiConfig
    {
        $settings = ($deviceMethod->settings ?? []) + $this->defaults($device);
        $secret = IpmiSecretData::fromArray($deviceMethod->secret->data ?? []);

        return new IpmiConfig(
            hostname: $settings['hostname'],
            username: $secret->username,
            password: $secret->password,
            kgKey: $secret->kgKey,
            port: $settings['port'],
            ciphersuite: $settings['ciphersuite'],
            timeout: $settings['timeout'],
            type: $settings['type'],
        );
    }

    public function probe(Device $device, PollingMethodConfig $config): ProbeResult
    {
        assert($config instanceof IpmiConfig);

        $ipmi = Ipmitool::init($device, $config);

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

    public function secretType(): SecretType
    {
        return SecretType::Ipmi;
    }
}
