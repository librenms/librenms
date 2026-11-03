<?php

namespace LibreNMS\Polling\Method\Methods;

use App\ApiClients\ProxmoxApi;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use LibreNMS\Enum\SecretType;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Config\ProxmoxApiConfig;
use LibreNMS\Polling\Method\DataSources\ProxmoxApiDataSource;
use LibreNMS\Polling\Method\Definitions\ProxmoxApiDefinition;
use LibreNMS\Polling\Method\ProbeResult;
use LibreNMS\Polling\Secrets\Data\ProxmoxApiSecretData;

/**
 * @extends PollingMethod<ProxmoxApiConfig>
 */
final class ProxmoxApiPollingMethod extends PollingMethod
{
    public function definition(): ProxmoxApiDefinition
    {
        return new ProxmoxApiDefinition;
    }

    public function defaultAffectsAvailability(): bool
    {
        return false;
    }

    /**
     * Proxmox VE uses a self-signed certificate by default, so it is not verified unless set.
     *
     * @return array{hostname: string, port: int, timeout: int, verify_ssl: bool}
     */
    public function defaults(?Device $device = null): array
    {
        return [
            'hostname' => $device->hostname ?? '',
            'port' => 8006,
            'timeout' => 10,
            'verify_ssl' => false,
        ];
    }

    public function config(Device $device, ?DevicePollingMethod $deviceMethod = null): ProxmoxApiConfig
    {
        $settings = ($deviceMethod->settings ?? []) + $this->defaults($device);
        $secret = $this->secretData($deviceMethod?->secret) ?? new ProxmoxApiSecretData;
        assert($secret instanceof ProxmoxApiSecretData);

        return new ProxmoxApiConfig(
            hostname: $settings['hostname'],
            port: $settings['port'],
            tokenId: $secret->tokenId,
            tokenSecret: $secret->tokenSecret,
            timeout: $settings['timeout'],
            verifySsl: $settings['verify_ssl'],
        );
    }

    /**
     * Reads the API version, it checks the connection and the token.
     */
    public function probe(Device $device, PollingMethodConfig $config): ProbeResult
    {
        assert($config instanceof ProxmoxApiConfig);

        if ($config->tokenId === '' || $config->tokenSecret === '') {
            return ProbeResult::failure(errorMessage: 'Proxmox API token is not set');
        }

        try {
            $version = (new ProxmoxApi($config))->version();
        } catch (ConnectionException|RequestException $e) {
            return ProbeResult::failure(errorMessage: $e->getMessage());
        }

        return ProbeResult::success(['version' => $version['version'] ?? null]);
    }

    public function dataSourceClass(): string
    {
        return ProxmoxApiDataSource::class;
    }

    public function secretTypes(): array
    {
        return [SecretType::ProxmoxApi];
    }
}
