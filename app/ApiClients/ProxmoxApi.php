<?php

namespace App\ApiClients;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Pool;
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
    /** Guest agent calls at once, so stuck agents cost less time without flooding the host */
    private const AGENT_CONCURRENCY = 3;

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
     * @param  bool  $full  include the full status of running VMs, such as disk activity
     * @return list<array<string, mixed>>
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function listVms(string $node = 'localhost', bool $full = false): array
    {
        return $this->callApi("nodes/$node/qemu", $full ? ['full' => 1] : [])->throw()->json('data') ?? [];
    }

    /**
     * Filesystems reported by the guest agents, empty for VMs whose agent is not running or not responding.
     *
     * @param  list<int>  $vmIds
     * @return array<int, list<array<string, mixed>>> keyed by VM id
     */
    public function getVmsFsInfo(array $vmIds, string $node = 'localhost'): array
    {
        return array_map(
            fn (?Response $response): array => $response?->json('data.result') ?? [],
            $this->callAgents($vmIds, $node, 'get-fsinfo'),
        );
    }

    /**
     * The OS names reported by the guest agents, empty for VMs whose agent is not running or not responding.
     *
     * @param  list<int>  $vmIds
     * @return array<int, string> keyed by VM id
     */
    public function getVmsGuestOs(array $vmIds, string $node = 'localhost'): array
    {
        return array_map(
            fn (?Response $response): string => $response?->json('data.result.pretty-name') ?? '',
            $this->callAgents($vmIds, $node, 'get-osinfo'),
        );
    }

    /**
     * Guest agent data is optional, a failed agent must not fail the other VMs.
     *
     * @param  list<int>  $vmIds
     * @return array<int, Response|null> keyed by VM id
     */
    private function callAgents(array $vmIds, string $node, string $command): array
    {
        $client = $this->client();
        $options = $client->getOptions(); // pooled requests do not inherit the options of the client

        $responses = $client->pool(function (Pool $pool) use ($vmIds, $node, $command, $options): void {
            foreach ($vmIds as $vmId) {
                $pool->as((string) $vmId)->withOptions($options)->get("$this->baseUri/nodes/$node/qemu/$vmId/agent/$command");
            }
        }, self::AGENT_CONCURRENCY);

        return array_map(fn (mixed $response): ?Response => $response instanceof Response ? $response : null, $responses);
    }

    /**
     * @param  array<string, mixed>  $query
     *
     * @throws ConnectionException
     */
    private function callApi(string $uri, array $query = []): Response
    {
        return $this->client()->baseUrl($this->baseUri)->get($uri, $query);
    }

    private function client(): PendingRequest
    {
        return Http::client()
            ->timeout($this->config->timeout)
            ->withOptions(['verify' => $this->config->verifySsl])
            ->withHeader('Authorization', "PVEAPIToken={$this->config->tokenId}={$this->config->tokenSecret}")
            ->acceptJson();
    }
}
