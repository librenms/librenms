<?php

namespace LibreNMS\OS;

use App\Models\Vminfo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\PowerState;
use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Interfaces\Discovery\VminfoDiscovery;
use LibreNMS\OS;
use LibreNMS\Util\JsonApp;

class Proxmox extends OS implements VminfoDiscovery
{
    public function discoverVminfo(): Collection
    {
        try {
            $app = JsonApp::fetch('proxmox', '2.0.0');
            $vms = new Collection;
            foreach ($app->data['vms'] as $vm) {
                $vms->push(new Vminfo([
                    'vm_type' => 'proxmox',
                    'vmwVmVMID' => $vm['id'],
                    'vmwVmDisplayName' => $vm['name'],
                    'vmwVmMemSize' => $vm['mem_size'],
                    'vmwVmCpus' => $vm['cpus'],
                    'vmwVmGuestOS' => $vm['guest_os'],
                    'vmwVmState' => PowerState::parse($vm['state']),
                ]));
            }

            return $vms;
        } catch (JsonAppException $e) {
            Log::error($e->getMessage());

            return new Collection;
        }
    }
}
