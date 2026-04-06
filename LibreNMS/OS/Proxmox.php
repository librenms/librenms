<?php

namespace LibreNMS\OS;

use App\ApiClients\ProxmoxApi;
use App\Models\Vminfo;
use Illuminate\Support\Collection;
use LibreNMS\Enum\PowerState;
use LibreNMS\Interfaces\Discovery\VminfoDiscovery;
use LibreNMS\OS;

class Proxmox extends OS implements VminfoDiscovery
{
    public function discoverVminfo(): Collection
    {
        $vms = new Collection;
        $api = new ProxmoxApi($this->getDevice()->hostname);

        foreach ($api->listVms() as $vm) {
            $vms->push(new Vminfo([
                'vm_type' => 'proxmox',
                'vmwVmVMID' => $vm['vmid'],
                'vmwVmDisplayName' => $vm['name'],
                'vmwVmMemSize' => $vm['maxmem'] / 1048576,
                'vmwVmCpus' => $vm['cpus'],
                'vmwVmGuestOS' => $api->getVmGuestOs($vm['vmid']),
                'vmwVmState' => PowerState::parse($vm['status']),
            ]));
        }

        return $vms;
    }
}
