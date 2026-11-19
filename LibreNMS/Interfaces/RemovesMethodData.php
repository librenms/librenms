<?php

namespace LibreNMS\Interfaces;

use App\Models\Device;
use LibreNMS\Enum\PollingMethodType;

/**
 * A module with data from polling methods, without the method the module may not run again to remove it.
 */
interface RemovesMethodData
{
    /**
     * Delete the data of a polling method that was removed from the device, a disabled method keeps it.
     */
    public function removeMethodData(Device $device, PollingMethodType $type): void;
}
