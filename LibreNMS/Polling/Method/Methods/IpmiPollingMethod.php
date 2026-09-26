<?php

namespace LibreNMS\Polling\Method\Methods;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Data\Source\Ipmitool;
use LibreNMS\Enum\SecretType;
use LibreNMS\Exceptions\IpmiConnectionFailed;
use LibreNMS\Polling\Method\Config\IpmiConfig;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\ProbeResult;
use LibreNMS\Polling\Secrets\Data\IpmiSecretData;

final class IpmiPollingMethod extends PollingMethod
{
    public function defaultConfig(?Device $device = null): IpmiConfig
    {
        return IpmiConfig::default();
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

    protected function configFromSettings(DevicePollingMethod $deviceMethod): IpmiConfig
    {
        return IpmiConfig::fromSettings(
            settings: $deviceMethod->settings ?? [],
            secretData: IpmiSecretData::fromArray($deviceMethod->secret->data ?? []),
            fallbackHostname: $deviceMethod->device?->hostname,
        );
    }
}
