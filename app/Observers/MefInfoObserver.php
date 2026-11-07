<?php

namespace App\Observers;

use App\Models\Eventlog;
use App\Models\MefInfo;
use LibreNMS\Enum\Severity;

class MefInfoObserver
{
    /**
     * Handle the MefInfo "created" event.
     *
     * @return void
     */
    public function created(MefInfo $mef): void
    {
        Eventlog::log('MEF link: ' . $mef->mefIdent . ' (' . $mef->mefID . ') Discovered', $mef->device_id, 'system', Severity::Info);
    }

    /**
     * Handle the MefInfo "deleted" event.
     *
     * @return void
     */
    public function deleted(MefInfo $mef): void
    {
        Eventlog::log('MEF link: ' . $mef->mefIdent . ' Removed', $mef->device_id, 'system', Severity::Notice);
    }
}
