<?php

namespace App\ApiClients;

use App\ApiClients\Data\ProxmoxFilesystem;
use App\ApiClients\Data\ProxmoxGuest;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use LibreNMS\Polling\Method\Config\ProxmoxApiConfig;
use LibreNMS\Util\Http;
use LibreNMS\Util\Rewrite;

/**
 * Proxmox VE API client. The node "localhost" is the node the API is served from, each node reports its own guests.
 */
readonly class ProxmoxApi
{
    /** Per guest calls at once, so stuck guest agents cost less time without flooding the host */
    private const int GUEST_CONCURRENCY = 3;

    private string $baseUri;

    public function __construct(
        private ProxmoxApiConfig $config,
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
     * Identifies the cluster of this node, also when it is renamed or another cluster has the same name.
     * The root CA is created with the cluster and shared by its nodes, a standalone host has its own.
     *
     * @return string|null null when the API does not list the root CA
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function clusterId(): ?string
    {
        $certificates = $this->callApi('nodes/localhost/certificates/info')->throw()->json('data') ?? [];

        foreach ($certificates as $certificate) {
            if (($certificate['filename'] ?? '') === 'pve-root-ca.pem' && ! empty($certificate['fingerprint'])) {
                return 'proxmox-' . substr(strtolower(str_replace(':', '', (string) $certificate['fingerprint'])), 0, 32);
            }
        }

        return null;
    }

    /**
     * Every guest of the cluster, also those on offline nodes. Of those only the id is known, not that they are templates.
     *
     * @return array{qemu: list<ProxmoxGuest>, lxc: list<ProxmoxGuest>}
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function listClusterGuests(): array
    {
        $guests = $this->callApi('cluster/resources', ['type' => 'vm'])->throw()->json('data') ?? [];

        $ofType = fn (string $type): array => array_map(
            ProxmoxGuest::fromArray(...),
            array_values(array_filter($guests, fn (array $guest): bool => ($guest['type'] ?? '') === $type)),
        );

        return ['qemu' => $ofType('qemu'), 'lxc' => $ofType('lxc')];
    }

    /**
     * With the full status of running VMs, such as disk activity and whether they are paused.
     *
     * @return list<ProxmoxGuest>
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function listVms(): array
    {
        return $this->guests($this->callApi('nodes/localhost/qemu', ['full' => 1]));
    }

    /**
     * The list does not take the full option, it always has the status of the containers.
     *
     * @return list<ProxmoxGuest>
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function listContainers(): array
    {
        return $this->guests($this->callApi('nodes/localhost/lxc'));
    }

    /**
     * Filesystems reported by the guest agents, empty for VMs whose agent is not running or not responding.
     *
     * @param  list<int>  $vmIds
     * @return array<int, list<ProxmoxFilesystem>> keyed by VM id
     */
    public function getVmsFsInfo(array $vmIds): array
    {
        return array_map(
            fn (?Response $response): array => array_map(ProxmoxFilesystem::fromArray(...), $response?->json('data.result') ?? []),
            $this->callGuests($vmIds, fn (int $vmId): string => "nodes/localhost/qemu/$vmId/agent/get-fsinfo"),
        );
    }

    /**
     * The OS names reported by the guest agents, empty for VMs whose agent is not running or not responding.
     *
     * @param  list<int>  $vmIds
     * @return array<int, string> keyed by VM id
     */
    public function getVmsGuestOs(array $vmIds): array
    {
        return array_map(
            fn (?Response $response): string => $response?->json('data.result.pretty-name') ?? '',
            $this->callGuests($vmIds, fn (int $vmId): string => "nodes/localhost/qemu/$vmId/agent/get-osinfo"),
        );
    }

    /**
     * The OS types set in the container configs, empty for containers whose config could not be read.
     *
     * @param  list<int>  $containerIds
     * @return array<int, string> keyed by container id
     */
    public function getContainersOsType(array $containerIds): array
    {
        return array_map(
            fn (?Response $response): string => $response?->json('data.ostype') ?? '',
            $this->callGuests($containerIds, fn (int $containerId): string => "nodes/localhost/lxc/$containerId/config"),
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
     * @return list<ProxmoxGuest>
     *
     * @throws RequestException
     */
    private function guests(Response $response): array
    {
        return array_map(ProxmoxGuest::fromArray(...), $response->throw()->json('data') ?? []);
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
