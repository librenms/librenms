<?php

namespace App\ApiClients;

use App\Facades\LibrenmsConfig;
use Illuminate\Http\Client\Response;
use LibreNMS\Util\Http;

readonly class ProxmoxApi
{
    private string $tokenName;
    private string $tokenSecret;
    private string $base_uri;

    public function __construct(
        private string $hostname,
        private int $timeout = 10,
    ){
        $this->base_uri = "https://$this->hostname:8006/api2/json";
        $this->tokenName = LibrenmsConfig::get('proxmox.token_id');
        $this->tokenSecret = LibrenmsConfig::get('proxmox.secret');
    }

    public function clusterStatus(): array
    {
        return $this->callApi('cluster/status')->json() ?? [];
    }

    public function listNodes(): array
    {
        return $this->callApi('nodes')->json() ?? [];
    }

    public function netStat(string $node = 'localhost'): array
    {
        return $this->callApi("nodes/$node/netstat")->json() ?? [];
    }

    public function listVms(string $node = 'localhost'): array
    {
        return $this->callApi("nodes/$node/qemu")->json('data') ?? [];
    }

    public function getVmConfig(int $vmId, string $node = 'localhost'): array
    {
        return $this->callApi("nodes/$node/qemu/$vmId/config")->json() ?? [];
    }

    public function getVmStatus(int $vmId, string $node = 'localhost'): array
    {
        return $this->callApi("nodes/$node/qemu/$vmId/status/current")->json('data') ?? [];
    }

    public function getVmFsInfo(int $vmId, string $node = 'localhost'): array
    {
        return $this->callApi("nodes/$node/qemu/$vmId/agent/get-fsinfo")->json('data.result') ?? [];
    }

    public function getVmGuestOs(int $vmId, string $node = 'localhost'): string
    {
        return $this->callApi("nodes/$node/qemu/$vmId/agent/get-osinfo")->json('data.result.pretty-name') ?? '';
    }

    public function listContainers(string $node = 'localhost'): array
    {
        return $this->callApi("nodes/$node/lxc")->json() ?? [];
    }

    public function getContainerConfig(int $containerId, string $node = 'localhost'): array
    {
        return $this->callApi("nodes/$node/lxc/$containerId/config")->json() ?? [];
    }

    public function getContainerStatus(int $containerId, string $node = 'localhost'): array
    {
        return $this->callApi("nodes/$node/lxc/$containerId/status/current")->json() ?? [];
    }

    private function callApi($uri, array $query = []): Response
    {
        return Http::client()->baseUrl($this->base_uri)
            ->timeout($this->timeout)
            ->withHeader('Authorization', "PVEAPIToken=$this->tokenName=$this->tokenSecret")
            ->get($uri, $query);
    }
}
