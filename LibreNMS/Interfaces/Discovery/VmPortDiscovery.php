<?php

namespace LibreNMS\Interfaces\Discovery;

use App\Models\Vminfo;
use App\Models\VmPort;
use Illuminate\Support\Collection;

interface VmPortDiscovery
{
    /**
     * Discover all the VMs and return a collection of VmPort models
     *
     * @param  Collection<Vminfo>  $vms
     * @return Collection<VmPort>
     */
    public function discoverVmPorts(Collection $vms): Collection;
}
