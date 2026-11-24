<?php

namespace LibreNMS\Polling\Method\Methods;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Data\Source\Redfish;
use LibreNMS\Enum\SecretType;
use LibreNMS\Exceptions\RedfishConnectionFailed;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Config\RedfishConfig;
use LibreNMS\Polling\Method\DataSources\RedfishDataSource;
use LibreNMS\Polling\Method\Definitions\RedfishDefinition;
use LibreNMS\Polling\Method\ProbeResult;
use LibreNMS\Polling\Secrets\Data\RedfishSecretData;

/**
 * @extends PollingMethod<RedfishConfig>
 */
final class RedfishPollingMethod extends PollingMethod
{
    public function definition(): RedfishDefinition
    {
        return new RedfishDefinition;
    }

    public function defaultAffectsAvailability(): bool
    {
        return false;
    }

    /**
     * @return array{hostname: string, scheme: string, port: ?int, insecure: bool, timeout: int}
     */
    public function defaults(?Device $device = null): array
    {
        return [
            'hostname' => $device->hostname ?? '',
            'scheme' => 'https',
            'port' => null,
            'insecure' => false,
            'timeout' => 10,
        ];
    }

    public function config(Device $device, ?DevicePollingMethod $deviceMethod = null): RedfishConfig
    {
        $settings = ($deviceMethod->settings ?? []) + $this->defaults($device);
        $secret = $this->secretData($deviceMethod?->secret) ?? new RedfishSecretData;
        assert($secret instanceof RedfishSecretData);

        return new RedfishConfig(
            hostname: $settings['hostname'],
            username: $secret->username,
            password: $secret->password,
            scheme: $settings['scheme'],
            port: $settings['port'],
            insecure: $settings['insecure'],
            timeout: $settings['timeout'],
        );
    }

    /**
     * Crawls the sensors of all chassis, the data source discovers and polls the sensors from the readings.
     */
    public function probe(Device $device, PollingMethodConfig $config): ProbeResult
    {
        assert($config instanceof RedfishConfig);

        $redfish = Redfish::init($device, $config);

        if (! $redfish) {
            return ProbeResult::failure();
        }

        try {
            return ProbeResult::success(['readings' => $redfish->sensors()]);
        } catch (RedfishConnectionFailed $e) {
            return ProbeResult::failure(errorMessage: $e->getMessage());
        }
    }

    public function dataSourceClass(): string
    {
        return RedfishDataSource::class;
    }

    public function dataSource(Device $device, PollingMethodConfig $config, ProbeResult $result): RedfishDataSource
    {
        return new RedfishDataSource($result);
    }

    public function secretTypes(): array
    {
        return [SecretType::Redfish];
    }
}
