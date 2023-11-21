<?php

namespace App\Observers;

use App\Models\Eventlog;
use App\Models\Stp;
use LibreNMS\Enum\Severity;
use LibreNMS\Util\Time;

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
            Eventlog::log('STP designated root changed: ' . $stp->getOriginal('designatedRoot') . ' > ' . $stp->designatedRoot, $stp->device_id, 'stp', Severity::Warning);
        }

        if ($stp->isDirty('rootPort')) {
            Eventlog::log('STP root port changed: ' . $stp->getOriginal('rootPort') . ' > ' . $stp->rootPort, $stp->device_id, 'stp', Severity::Warning);
        }

        if ($stp->isDirty('rootPort')) {
            $time = Time::formatInterval((int) $stp->timeSinceTopologyChange);
            Eventlog::log('STP topology changed after: ' . $time, $stp->device_id, 'stp', Severity::Warning);
        }
    }
}
