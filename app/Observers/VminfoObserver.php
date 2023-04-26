<?php

namespace App\Observers;

use App\Models\Eventlog;
use App\Models\Vminfo;

class VminfoObserver
{
    /**
     * Handle the Vminfo "created" event.
     *
     * @param  \App\Models\Vminfo  $vminfo
     * @return void
     */
    public function created(Vminfo $vminfo)
    {
        Eventlog::log($vminfo->vmwVmDisplayName . " ($vminfo->vmwVmMemSize GB / $vminfo->vmwVmCpus vCPU) Discovered", $vminfo->device_id, 'system', 3, $vminfo->vmwVmVMID);
    }

    /**
     * Handle the Vminfo "updated" event.
     *
     * @param  \App\Models\Vminfo  $vminfo
     * @return void
     */
    public function updating(Vminfo $vminfo)
    {
        foreach ($vminfo->getDirty() as $field => $value) {
            Eventlog::log($vminfo->vmwVmDisplayName . ' (' . preg_replace('/^vmwVm/', '', $field) . ') -> ' . $value, $vminfo->device_id);
        }
    }

    /**
     * Handle the Vminfo "deleted" event.
     *
     * @param  \App\Models\Vminfo  $vminfo
     * @return void
     */
    public function deleted(Vminfo $vminfo)
    {
        Eventlog::log($vminfo->vmwVmDisplayName . ' Removed', $vminfo->device_id, 'system', 4, $vminfo->vmwVmVMID);
    }

    /**
     * Handle the Vminfo "restored" event.
     *
     * @param  \App\Models\Vminfo  $vminfo
     * @return void
     */
    public function restored(Vminfo $vminfo)
    {
        //
    }

    /**
     * Handle the Vminfo "force deleted" event.
     *
     * @param  \App\Models\Vminfo  $vminfo
     * @return void
     */
    public function forceDeleted(Vminfo $vminfo)
    {
        //
    }
}
