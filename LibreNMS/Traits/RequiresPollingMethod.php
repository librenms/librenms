<?php

namespace LibreNMS\Traits;

use App\Models\Device;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\OS;

/**
 * A module that runs when one of its polling methods is available.
 * Modules override shouldDiscover() or shouldPoll() when they need more than that.
 */
trait RequiresPollingMethod
{
    /**
     * @return list<PollingMethodType>
     */
    abstract public function pollingMethods(Device $device): array;

    public function shouldDiscover(OS $os): bool
    {
        return $this->hasAvailableMethod($os);
    }

    public function shouldPoll(OS $os): bool
    {
        return $this->hasAvailableMethod($os);
    }

    /**
     * One of the polling methods this module can get data from is available, checked in order of preference.
     */
    protected function hasAvailableMethod(OS $os): bool
    {
        return $os->getMethodResults()->anyAvailable($this->pollingMethods($os->getDevice()));
    }
}
