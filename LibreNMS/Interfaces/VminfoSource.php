<?php

namespace LibreNMS\Interfaces;

/**
 * A source of VMs, each source only syncs and polls the VMs of its types, so several sources can share a device.
 */
interface VminfoSource
{
    /**
     * The vm_type of the VMs this source provides. Static because it is needed without a working source, see Vminfo::removeMethodData().
     *
     * @return string[]
     */
    public static function vmTypes(): array;
}
