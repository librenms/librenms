<?php

namespace App\ApiClients;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use LibreNMS\Polling\Method\Config\ProxmoxConfig;
use LibreNMS\Util\Http;
use LibreNMS\Util\Rewrite;

/**
 * Proxmox VE API client, the node "localhost" is the node the API is served from.
 */
readonly class ProxmoxApi
{
    private string $baseUri;

    public function __construct(
        private ProxmoxConfig $config,
    ) {
        $this->baseUri = 'https://' . Rewrite::addIpv6Brackets($this->config->hostname) . ':' . $this->config->port . '/api2/json';
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function version(): array
    {
        return $this->callApi('version')->throw()->json('data') ?? [];
    }

    /**
     * @return list<array<string, mixed>>
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function listVms(string $node = 'localhost'): array
    {
        return $this->callApi("nodes/$node/qemu")->throw()->json('data') ?? [];
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function getVmStatus(int $vmId, string $node = 'localhost'): array
    {
        return $this->callApi("nodes/$node/qemu/$vmId/status/current")->throw()->json('data') ?? [];
    }

    /**
     * Filesystems reported by the guest agent, empty if the agent is not running.
     *
     * @return list<array<string, mixed>>
     *
     * @throws ConnectionException
     */
    public function getVmFsInfo(int $vmId, string $node = 'localhost'): array
    {
        return $this->callApi("nodes/$node/qemu/$vmId/agent/get-fsinfo")->json('data.result') ?? [];
    }

    /**
     * The OS name reported by the guest agent, empty if the agent is not running.
     *
     * @throws ConnectionException
     */
    public function getVmGuestOs(int $vmId, string $node = 'localhost'): string
    {
        return $this->callApi("nodes/$node/qemu/$vmId/agent/get-osinfo")->json('data.result.pretty-name') ?? '';
    }

    /**
     * @param  array<string, mixed>  $query
     *
     * @throws ConnectionException
     */
    private function callApi(string $uri, array $query = []): Response
    {
        return Http::client()->baseUrl($this->baseUri)
            ->timeout($this->config->timeout)
            ->withOptions(['verify' => $this->config->verifySsl])
            ->withHeader('Authorization', "PVEAPIToken={$this->config->tokenId}={$this->config->tokenSecret}")
            ->acceptJson()
            ->get($uri, $query);
    }
}
