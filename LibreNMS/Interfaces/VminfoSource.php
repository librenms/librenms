<?php

namespace LibreNMS\Interfaces;

/**
 * A source of VMs, each source only syncs and polls the VMs of its types, so several sources can share a device.
 */
interface VminfoSource
{
    /**
     * The vm_type of the VMs this source provides.
     *
     * @return string[]
     */
    public function vmTypes(): array;
}
