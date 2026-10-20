<?php

namespace LibreNMS\Polling\Method\Methods;

use App\ApiClients\ProxmoxApi;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use LibreNMS\Enum\ProcessType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Config\ProxmoxConfig;
use LibreNMS\Polling\Method\DataSources\ProxmoxDataSource;
use LibreNMS\Polling\Method\Definitions\ProxmoxDefinition;
use LibreNMS\Polling\Method\ProbeResult;
use LibreNMS\Polling\Secrets\Data\ProxmoxSecretData;

/**
 * @extends PollingMethod<ProxmoxConfig>
 */
final class ProxmoxPollingMethod extends PollingMethod
{
    public function definition(): ProxmoxDefinition
    {
        return new ProxmoxDefinition;
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

    public function config(Device $device, ?DevicePollingMethod $deviceMethod = null): ProxmoxConfig
    {
        $settings = ($deviceMethod->settings ?? []) + $this->defaults($device);
        $secret = $this->secretData($deviceMethod?->secret) ?? new ProxmoxSecretData;
        assert($secret instanceof ProxmoxSecretData);

        return new ProxmoxConfig(
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
        assert($config instanceof ProxmoxConfig);

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

    /**
     * @return array<string, bool>
     */
    public function modules(ProcessType $type): array
    {
        return ['vminfo' => true];
    }

    public function dataSource(Device $device, PollingMethodConfig $config): ProxmoxDataSource
    {
        assert($config instanceof ProxmoxConfig);

        return new ProxmoxDataSource($config);
    }

    public function secretTypes(): array
    {
        return [SecretType::Proxmox];
    }
}
