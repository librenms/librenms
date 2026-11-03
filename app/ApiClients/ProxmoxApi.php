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
    /** Per guest calls at once, so stuck guest agents cost less time without flooding the host */
    private const GUEST_CONCURRENCY = 3;

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
     * The list does not take the full option, it always has the status of the containers.
     *
     * @return list<array<string, mixed>>
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function listContainers(string $node = 'localhost'): array
    {
        return $this->callApi("nodes/$node/lxc")->throw()->json('data') ?? [];
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
            $this->callGuests($vmIds, fn (int $vmId): string => "nodes/$node/qemu/$vmId/agent/get-fsinfo"),
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
            $this->callGuests($vmIds, fn (int $vmId): string => "nodes/$node/qemu/$vmId/agent/get-osinfo"),
        );
    }

    /**
     * The OS types set in the container configs, empty for containers whose config could not be read.
     *
     * @param  list<int>  $containerIds
     * @return array<int, string> keyed by container id
     */
    public function getContainersOsType(array $containerIds, string $node = 'localhost'): array
    {
        return array_map(
            fn (?Response $response): string => $response?->json('data.ostype') ?? '',
            $this->callGuests($containerIds, fn (int $containerId): string => "nodes/$node/lxc/$containerId/config"),
        );
    }

    /**
     * Per guest data is optional, a failed call must not fail the other guests.
     *
     * @param  list<int>  $guestIds
     * @param  callable(int): string  $uri
     * @return array<int, Response|null> keyed by guest id
     */
    private function callGuests(array $guestIds, callable $uri): array
    {
        $client = $this->client();
        $options = $client->getOptions(); // pooled requests do not inherit the options of the client

        $responses = $client->pool(function (Pool $pool) use ($guestIds, $uri, $options): void {
            foreach ($guestIds as $guestId) {
                $pool->as((string) $guestId)->withOptions($options)->get("$this->baseUri/{$uri($guestId)}");
            }
        }, self::GUEST_CONCURRENCY);

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
