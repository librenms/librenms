<?php

namespace App\Observers;

use App\Models\Stp;
use Log;

class StpObserver
{
    /**
     * Handle the Stp "updating" event.
     *
     * @param  \App\Models\Stp  $stp
     * @return void
     */
    public function updating(Stp $stp)
    {
        if ($stp->isDirty('designatedRoot')) {
            Log::event('STP designated root changed: ' . $stp->getOriginal('designatedRoot') . ' > ' . $stp->designatedRoot, $stp->device_id, 'stp', 4);
        }

        if ($stp->isDirty('rootPort')) {
            Log::event('STP root port changed: ' . $stp->getOriginal('rootPort') . ' > ' . $stp->rootPort, $stp->device_id, 'stp', 4);
        }

        if ($stp->isDirty('rootPort')) {
            $time = \LibreNMS\Util\Time::formatInterval($stp->timeSinceTopologyChange);
            Log::event('STP topology changed after: ' . $time, $stp->device_id, 'stp', 4);
        }
    }
}
