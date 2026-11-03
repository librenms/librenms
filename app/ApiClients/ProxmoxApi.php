<?php

namespace App\ApiClients;

use App\ApiClients\Data\ProxmoxFilesystem;
use App\ApiClients\Data\ProxmoxGuest;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use LibreNMS\Polling\Method\Config\ProxmoxApiConfig;
use LibreNMS\Util\Http;
use LibreNMS\Util\Rewrite;

/**
 * Proxmox VE API client. Any node of a cluster answers for the whole cluster, a standalone host is a cluster of one.
 */
readonly class ProxmoxApi
{
    /** Node and guest calls at once, so stuck nodes and guest agents cost less time without flooding the host */
    private const CONCURRENCY = 3;

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
     * The nodes that can answer requests.
     *
     * @return list<string>
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function listOnlineNodes(): array
    {
        $nodes = $this->callApi('nodes')->throw()->json('data') ?? [];

        return array_values(array_column(array_filter($nodes, fn (array $node): bool => ($node['status'] ?? '') === 'online'), 'node'));
    }

    /**
     * Every guest of the cluster, also those on offline nodes, see ProxmoxGuest::isKnown().
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
     * @param  list<string>  $nodes
     * @return list<ProxmoxGuest>
     */
    public function listVms(array $nodes): array
    {
        return $this->guestsOnNodes($nodes, fn (string $node): string => "nodes/$node/qemu?full=1");
    }

    /**
     * The list does not take the full option, it always has the status of the containers.
     *
     * @param  list<string>  $nodes
     * @return list<ProxmoxGuest>
     */
    public function listContainers(array $nodes): array
    {
        return $this->guestsOnNodes($nodes, fn (string $node): string => "nodes/$node/lxc");
    }

    /**
     * Filesystems reported by the guest agents, empty for VMs whose agent is not running or not responding.
     *
     * @param  list<ProxmoxGuest>  $vms
     * @return array<int, list<ProxmoxFilesystem>> keyed by VM id
     */
    public function getVmsFsInfo(array $vms): array
    {
        return array_map(
            fn (?Response $response): array => array_map(ProxmoxFilesystem::fromArray(...), $response?->json('data.result') ?? []),
            $this->callGuests($vms, fn (ProxmoxGuest $vm): string => "nodes/$vm->node/qemu/$vm->id/agent/get-fsinfo"),
        );
    }

    /**
     * The OS names reported by the guest agents, empty for VMs whose agent is not running or not responding.
     *
     * @param  list<ProxmoxGuest>  $vms
     * @return array<int, string> keyed by VM id
     */
    public function getVmsGuestOs(array $vms): array
    {
        return array_map(
            fn (?Response $response): string => $response?->json('data.result.pretty-name') ?? '',
            $this->callGuests($vms, fn (ProxmoxGuest $vm): string => "nodes/$vm->node/qemu/$vm->id/agent/get-osinfo"),
        );
    }

    /**
     * The OS types set in the container configs, empty for containers whose config could not be read.
     *
     * @param  list<ProxmoxGuest>  $containers
     * @return array<int, string> keyed by container id
     */
    public function getContainersOsType(array $containers): array
    {
        return array_map(
            fn (?Response $response): string => $response?->json('data.ostype') ?? '',
            $this->callGuests($containers, fn (ProxmoxGuest $container): string => "nodes/$container->node/lxc/$container->id/config"),
        );
    }

    /**
     * A node that can not be read is left out, it must not fail the other nodes.
     *
     * @param  list<string>  $nodes
     * @param  callable(string): string  $uri
     * @return list<ProxmoxGuest>
     */
    private function guestsOnNodes(array $nodes, callable $uri): array
    {
        $guests = [];

        foreach ($this->callEach(array_combine($nodes, array_map($uri, $nodes))) as $node => $response) {
            if (! $response?->successful()) {
                Log::warning("Could not read the guests of Proxmox node $node");

                continue;
            }

            foreach ($response->json('data') ?? [] as $guest) {
                $guests[] = ProxmoxGuest::fromArray(['node' => (string) $node] + $guest);
            }
        }

        return $guests;
    }

    /**
     * Per guest data is optional, a failed call must not fail the other guests.
     *
     * @param  list<ProxmoxGuest>  $guests
     * @param  callable(ProxmoxGuest): string  $uri
     * @return array<int, Response|null> keyed by guest id
     */
    private function callGuests(array $guests, callable $uri): array
    {
        return $this->callEach(array_combine(array_column($guests, 'id'), array_map($uri, $guests)));
    }

    /**
     * @template TKey of array-key
     *
     * @param  array<TKey, string>  $uris
     * @return array<TKey, Response|null> null for a request that got no response
     */
    private function callEach(array $uris): array
    {
        $client = $this->client();
        $options = $client->getOptions(); // pooled requests do not inherit the options of the client

        $responses = $client->pool(function (Pool $pool) use ($uris, $options): void {
            foreach ($uris as $key => $uri) {
                $pool->as((string) $key)->withOptions($options)->get("$this->baseUri/$uri");
            }
        }, self::CONCURRENCY);

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
